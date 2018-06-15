<?php
namespace app\api\home;

use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;
use think\Session;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index
{
    public function index()
    {
        $request = Request::instance();
        $params = $request->param();
        //获得定义接口
        $api =  array_flip(config('api'));
        if (isset($params['method'])) {
            //判断是否存在该方法
            $func = $api[$params['method']];
            if (method_exists($this,$func)) {
                //处理参数
                $res = $this->getParams($params, $func);
                if($res!=1){
                    return $this->error($res);
                };
                return $this->$func($params);
            }else{
                return $this->error($params['method'].'方法不存在');
            }
            
        }else{
            return $this->error('method参数缺失');
        }
    }




    public function uploadFile($file,$ext = ""){
       $data['ext'] = $ext;
       if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
        $data['file'] = new \CURLFile($file);
    } else {
        $data['file'] = "@" . $file;
    }




    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "http://139.196.20.81:8077/?mod=public&app=public&action=upload");
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);//设置curl执行超时时间最大是多少

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output,true);








    }
    /**
     * [getParams 处理参数]
     * @param  [type] &$params [description]
     * @return [type]          [description]
     */
    public function getParams(&$params, $func)
    {
        if (!isset(config('param')[$func])) {
            return true;
        }
        $s =  config('param')[$func];
        $p = [];
        foreach ($s as $key => $value) {

            if ($value['valid']&&!isset($params[$key])) {//必填
                return $key.'参数必填';
                // return $this->error($key.'参数必填');
            }
            $p[$key] = isset($params[$key])?$params[$key]:'';
        }
        $params = $p;
        return true;
    }
    /**
     * [error 错误返回]
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    public function error($msg)
    {
        $data = [
            'Success'=>false,
            'Code'=>'1',
            'Msg'=>$msg,
            'Data'=>null
        ];
        return json($data);
    }

    /**
     * [wrong token失效提醒]
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    public function wrong($msg)
    {
        $data = [
            'Success'=>false,
            'Code'=>'9',
            'Msg'=>$msg,
            'Data'=>null
        ];
        return json($data);
    }
    
    /**
     * login 用户端
     * @param string $value [description]
     */
    public function login($params)
    {   
        //参数手机号，密码
        $phone = trim($params['phone']);
        $password = trim($params['password']);


        //是否存在
        $map['mobile'] = $phone;
        $user = db('toplearning_login')->where($map)->find();
        if (!$user) {
            return $this->error('用户不存在或被禁用！');
        }
        //密码是否正确

        // if (!Hash::check((string)$password, $user['password'])) {
        if (md5($password) != $user['password']) {
           return $this->error( '密码错误！');
       }

        //生成token
       $ret['token'] = $this->encrypt($user['user_id']);
        //设置过期时间
       cache($ret['token'], time() + 3600) ;

        //用户类型
       $ret['customerType'] = $user['group_id']==3?'2':'1';
        // $customerType = db('toplearning_user_account')->where('user_id',$user['user_id'])->column('type');
        // if ($customerType) {
        //    $ret['customerType'] = $customerType[0];
        // }

        //组数据  
       $ret['userid'] = $user['user_id'];
       $ret['phone'] = $user['mobile'];

       $ret['nickname'] = $user['nickname'];
       $ret['birthday'] = $user['birthday'];
       $ret['sex'] = $user['sex'];

       $ret['headurl'] = $user['avatar'];
       $ret['tokenlife'] = 1;

       $ret['wechat'] = $user['weixin'];
       $ret['qq'] = $user['qq'];
       $ret['city'] = $user['city'];
        //todo
       $ret['signture'] = $user['introduce'];
       $ret['coins'] = 10;

       $ret['email'] = $user['email'];
       $ret['collegeid'] = $user['school_id'];

       $ret['identifier'] = $user['im_account'];

     // $userSig = db('toplearning_login')->where(1)->order('user_id DESC')->value('userSig');
       @$ret['userSig'] = $user['userSig'];

       $data = [
        'Success'=>true,
        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$ret
    ];
    return json($data);
}

    /**
     * [findPassword 找回密码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function findPassword($params)
    {
        //参数
        $phone = trim($params['phone']);
        $code = trim($params['code']);
        $password = trim($params['password']);


        //检查过期时间
        // if (cache($username.$code)&&cache($username.$code)<time()) {
        //     return $this->error('验证码已过期');
        // }
        
        //检查是否正确
        if (cache($phone.'vcode')!=$code) {
            return $this->error('验证码不正确');
        }

        //生成密码
        $data['password'] =  md5((string)trim($params['password']));

        //更新
        if(!db('toplearning_login')->where(['mobile'=>$phone])->update($data)){
            return $this->error('服务器忙，请稍后');
        }

        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];
        return json($data);
    }

    /**
     * [register 注册]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function register($params)
    {

        //参数
        $data['group_id'] = trim($params['type'])=='1'?'5':'3';
        $data['nickname'] = trim($params['name']);
        $data['realname'] = $data['nickname'];
        $data['code'] = trim($params['code']);
        $data['mobile'] = trim($params['phone']);
        $data['create_time'] = time();

        if (db('toplearning_login')->where(['mobile'=>$data['mobile']])->find()) {
            return $this->error('账号已存在！');
        }
        // error_log(cache($data['mobile'].'vcode').'||'.$data['code'],3,'/home/wwwroot/ucschool/logl.log');
        //检查是否正确
        if (cache($data['mobile'].'vcode')!=$data['code']) {
            return $this->error('验证码不正确');
        }

        //生成密码 md5
        // $data['password'] =  Hash::make((string)trim($params['password']));
        $data['password'] =  md5((string)trim($params['password']));
        // $data['school_ids_own'] = 7;//U学院
        $data['school_id'] = 7;//U学院

        //同步注册腾讯IM
        
        // $post['method'] = 'regist';
        // $post['identifier'] = $data['mobile'];
        // $post['nick'] = $data['nickname'];
        // $post['face_url'] = 'http://www.qq.com';

        // $rs = $this->tenxunim($post);

        // if ($rs['usersig']) {
        //     $pattern = '#"(.*?)"#i'; 
        //     preg_match_all($pattern, $rs['usersig'], $matches); 
        //     $rs['usersig'] = $matches[1][0];
        // }

        // if ($rs && $rs['errorcode']!=0) {
        //     $this->error('同步注册腾讯IM失败');
        // }

        $resp = $this->createUser([
            'account'=>$data['mobile'],
            'name'=>$data['realname'],
            'avatar'=>"",
        ]); 

 if($resp['ActionStatus'] != "OK"){
            return $this->error($resp['ErrorInfo']);
        }




        $data['im_account'] = $data['mobile'];
        $data['userSig'] = $this->getUserSign($data['mobile']);//Hashtable.php
        

        //插入数据
        $me = db('toplearning_login')->insert($data);
        if (!$me) {
            return $this->error('注册失败！请稍后重试');
        }

        if($data['group_id'] == '3'){
            db("toplearning_teacher")->insert([
                'user_id'=>db("toplearning_login")->getLastInsID(),
                'teacher_name'=>$data['realname'],
                'type'=>2,
            ]);
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$me,
            'Success'=>true
        ];
        return json($data);
    }
    /**
     * [bregister 后台注册IM]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function bregister($params)
    {   
        // $post['method'] = 'regist';
        // $post['identifier'] = $params['mobile'];
        // $post['nick'] = $params['nickname'];
        // $post['face_url'] = 'http://www.qq.com';


        // $rs = $this->tenxunim($post);

        // if ($rs['usersig']) {
        //     $pattern = '#"(.*?)"#i'; 
        //     preg_match_all($pattern, $rs['usersig'], $matches); 
        //     $rs['usersig'] = $matches[1][0];
        // }

        // if ($rs && $rs['errorcode']!=0) {
        //     return $this->error('同步注册腾讯IM失败'.$rs['msg']);
        // }    



 if(empty($params['nickname'])){
            return $this->error("参数不对");
        }
        if(empty($params['mobile'])){
            return $this->error("参数不对");
        }
        // if(empty($params['face_url'])){
        //     return $this->error("参数不对");
        // }
 





        $resp = $this->createUser([
            'account'=>$params['mobile'],
            'name'=>$params['nickname'],
            'avatar'=>"",
        ]); 

 if($resp['ActionStatus'] != "OK"){
            return $this->error($resp['ErrorInfo']);

 
        }





                $save['im_account'] = $params['mobile'];
        $save['userSig'] = $this->getUserSign($params['mobile']);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$save,
            'Success'=>true
        ];
        return json($data);
    }

    /**
     * [getUserInfo 会员信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getUserInfo($params)
    {
        //params
        $token = trim($params['token']);
        $userid = trim($params['userid']);

        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }


        //是否存在
        $map['user_id'] = $token_uid;
        //用户信息
        $user = db('toplearning_login')->where($map)->find();
        if (!$user) {
            return $this->error('用户不存在');
        }

        //组数据
        $ret['token'] = $token;
        $ret['userid'] = $user['user_id'];
        $ret['phone'] = $user['mobile'];

        $ret['nickname'] = $user['nickname'];
        $ret['birthday'] = $user['birthday'];
        $ret['sex'] = $user['sex'];

        $ret['headurl'] = $user['avatar'];
        $ret['tokenlife'] = 1;
        
        $ret['wechat'] = $user['weixin'];
        $ret['qq'] = $user['qq'];
        $ret['city'] = $user['city'];
        //todo
        $ret['signture'] = $user['introduce'];
        $ret['coins'] = 10;

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];
        return json($data);
    }
    
    /**
     * [sendSms 发送短信]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function sendSms($params)
    {
        //参数
        $phone = trim($params['phone']);
        $type = trim($params['type']);

        //是否是手机号
        if(!preg_match('/^1([0-9]{9})/',$phone)){
            return $this->error('手机号格式不对');
        }
        // if (!db('toplearning_login')->where(['mobile'=>$phone])->find()) {
        //     return $this->error('账号不存在');
        // }
        
        //短信
        $code = $this->sendmsg($phone, $type);
        if (!$code) {
            return $this->error('发送失败，1小时只能获得3次');
        }
        
        //生成session 
        if (cache($phone.'vcode')) {
            cache($phone.'vcode', NULL);
        }
        cache($phone.'vcode',$code);

        //设置过期时间
        // cache($account.$code,time() + 1800);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [findSearch  搜索学院]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function findSearch($params)
    {
        //params
        $token = trim($params['token']);
        $text = trim($params['text']);

        $type = trim($params['type']);

        //通过token获取 uid
        // $token_uid = $this->decrypt($token);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }


        $map['school_name'] = array('like','%'.$text.'%');
        $map['del'] = 0;
        //用户信息
        $school = db('toplearning_school')->where($map)->select();
        
        $ret = array();
        foreach ($school as $key => $value) {
            $ret[$key]['collegeid'] = $value['school_id'];
            $ret[$key]['title'] = $value['school_name'];
            $ret[$key]['image'] = $value['logo'];
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getFocusCollege 2.  获取关注的学院]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getFocusCollege($params)
    {
        //params
        $token = trim($params['token']);

        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }


        // $map['a.type'] = 2;
        // $map['a.del'] = 0;
        // $map['a.user_id'] = $token_uid;
        //关注信息
        



        $school_ids = db('toplearning_concern')->where(['user_id'=>$token_uid])->order("id")->column("school_id");


        $school_ids = $school_ids?$school_ids:[];
        array_unshift($school_ids,"7"); 
        
        $attention = db("toplearning_school")->where(" school_id IN (".implode(",",$school_ids).")")->order(" field(school_id,".implode(",",$school_ids).")")->select();         




        //处理
        $ret = array();
        foreach ($attention as $key => $value) {

            $ret[$key]['collegeid'] = $value['school_id'];
            $ret[$key]['title'] = $value['school_name'];
            $ret[$key]['image'] = $value['logo'];
            $ret[$key]['del'] = $value['del'];
        }




        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [changeCollege 切换学院]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function changeCollege($params)
    {

        //params
        $token = trim($params['token']);
        $collegeid = trim($params['collegeid']);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }

        $map['school_id'] = $collegeid;
        //是否有该学院
        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        if (!db('toplearning_school')->where($map)->find()) {
            return $this->error('该学院不存在或删除');
        }

        //更新学院信息
        $umap['user_id'] = $token_uid;
        $save['school_id'] = $collegeid;

        db('toplearning_login')->where($umap)->update($save);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [getCollegeInfo 获得学院信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCollegeInfo($params)
    {

        //params
        $token = trim($params['token']);
        $collegeid = trim($params['collegeid']);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }

        //是否有该学院
        $map['school_id'] = $collegeid;
        $ret = db('toplearning_school')->where($map)->find();
        if (!$ret) {
            return $this->error('该学院不存在或删除');
        }

        //处理返回

        $rs = array();
        $rs['collegeid'] = $ret['school_id'];
        $rs['title'] = $ret['school_name'];
        $rs['introduce'] = $ret['school_profile'];

        $rs['achievement'] = array();
        // $rs['achtitle'] = '';
        // $rs['achdesc'] = '';
        // $rs['achimages'] = '';
        $rs['number'] = $ret['license_num'];
        $rs['isfocus'] = 0;
        $rs['collegeimg'] = $ret['logo'];

        $amap['type'] = 2;
        $amap['del'] = 0;
        $amap['source_id'] = $ret['school_id'];

        $token_uid = $this->decrypt($token);
        // $gz = db('toplearning_login')->where(['user_id'=>$token_uid])->column('school_ids');

        $gz = db('toplearning_concern')->where(['user_id'=>$token_uid,'school_id'=>$collegeid])->count();
        $rs['isfocus'] = $gz >0?1:0;


        // if (db('toplearning_attention')->where($amap)->find()) {
        //     $rs['isfocus'] = 1;
        // }

        //成果
        $sc = db('toplearning_school_extend')->where(['school_id'=>$collegeid])->select();
        $scarr = array();
        foreach ($sc as $key => $value) {
           $scarr[$key]['achtitle'] = $value['title'];
           $scarr[$key]['achdesc'] = $value['content'];
           $imgs = explode(',',$value['achimages']);
           $imgar = array();
           foreach ($imgs as $k => $v) {
             $imgar[$k]['image'] = $v;
         }
         $scarr[$key]['achimages'] = $imgar;
     }
     $rs['achievement'] = $scarr;

        //返回信息
     $data = [
        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$rs,
        'Success'=>true
    ];

    return json($data);
}
    /**
     * [focusCollege 关注]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function focusCollege($params)
    {
        //params
        $token = trim($params['token']);
        $collegeid = trim($params['collegeid']);
        $type = trim($params['type']);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }


        
        //通过token获取 uid
        $token_uid = $this->decrypt($token);


        //关注或取关 （有状态）
        
        $user = db('toplearning_login')->field("group_id,user_id")->where(['user_id'=>$token_uid])->find();


        if($type == 1){


            db("toplearning_concern")
            ->insert([
                'user_id'=>$user['user_id'],
                'school_id'=>$collegeid,
                'type'=>$user['group_id']
            ]);

        }else{

            db("toplearning_concern")
            ->where(['user_id'=>$user['user_id'],'school_id'=>$collegeid])
            ->delete();
        }


        
        $ret = array();
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [getfindIndexCollege 发现首页显示的学院]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getfindIndexCollege($params)
    {   

        //params
        $token = trim($params['token']);

        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }

        //通过token获取 uid
        $token_uid = $this->decrypt($token);
        $user = db('toplearning_login')->where(['user_id'=>$token_uid])->column('school_id');
        //有所属学院
        if ($user&&$user[0]) {
            $map['school_id'] = $user[0];
        }else{
            $map['school_id'] = db('toplearning_school')->where(1)->order('recommended DESC')->column('school_id')[0];
        }

        //学院 TODO
        $map['del'] = 0;
        
        $school = db('toplearning_school')->where($map)->find();

        $ret = array();
        if ($school) {
            $ret['collegeid'] = $school['school_id'];
            $ret['title'] = $school['school_name'];
            $ret['introduce'] = $school['school_profile'];

            $ret['achievement'] = array();
            $ret['number'] = $school['license_num'];//学院号
            $ret['isfocus'] = 0;
            $ret['collegeimg'] = $school['logo'];
            $amap['type'] = 2;
            $amap['del'] = 0;
            $amap['source_id'] = $school['school_id'];



            $gzs = db('toplearning_concern')->where(['user_id'=>$token_uid])->column('school_id');

            if ($gzs) {
                // $gzs = explode(',', $gz[0]);
                if (in_array($school['school_id'], $gzs)) {
                    $ret['isfocus'] = 1;
                }
            }
            //成果
            $sc = db('toplearning_school_extend')->where(['school_id'=>$school['school_id']])->select();
            $scarr = array();
            foreach ($sc as $key => $value) {
               $scarr[$key]['achtitle']=$value['title'];
               $scarr[$key]['achdesc']=$value['content'];
               // $scarr[$key]['achimages']= $value['achimages'];
               $imgs = explode(',',$value['achimages']);
               $imgar = array();
               foreach ($imgs as $k => $v) {
                 $imgar[$k]['image'] = $v;
             }
             $scarr[$key]['achimages'] = $imgar;
         }
         $ret['achievement'] = $scarr;
     }


        //返回信息
     $data = [
        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$ret,
        'Success'=>true
    ];

    return json($data);
}

    /**
     * [getCollegeTeachers 获取学院下的教师]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCollegeTeachers($params)
    {

        //params
        $token = trim($params['token']);
        $collegeid = trim($params['collegeid']);
        $page = trim($params['page']);
        $size = trim($params['size']);


        //学院老师
        $map['del'] = 0;
        $map['school_id'] = $collegeid;
        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;
        $teachers = db('toplearning_school_teacher')->where($map)->limit($limit, $size)->select();

        $ret = array();

        foreach ($teachers as $key => $value) {
            $ret[$key]['teacherid']  = $value['teacher_id'];
            $ret[$key]['name']  = $value['teacher_name'];
            $ret[$key]['headimage']  = '';
            if (db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('avatar')) {
                $ret[$key]['headimage']  = db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('avatar')[0];
            }
            $ret[$key]['score']  = 0;
            if (db('toplearning_teacher')->where(['user_id'=>$value['user_id']])->column('rate')) {
                $ret[$key]['score']  = db('toplearning_teacher')->where(['user_id'=>$value['user_id']])->column('rate')[0];
            }

            $ret[$key]['coursenumber']  = 0;
            
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);

    }
    /**
     * [getFindCourse 课程详情]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getFindCourse($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $token_uid = $this->decrypt($token);

        $value = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->find();

        $user = db('toplearning_login')->where(['user_id'=>$token_uid])->find();
        
        $ret = [];
        if ($value) {
            $ret['courseid'] = $value['net_material_id'];
            $ret['image'] = $value['picture'];
            $ret['title'] = $value['title'];
            $ret['college'] = $value['school_name'];
            $ret['MonthlyPitchNumber'] = $value['month_lessons'];
            $ret['CommonPitchNumber'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->count();
            $ret['type'] = $value['course_type'];
            @$ret['typeName'] = db('toplearning_course_type')->where(['type_id'=>$value['course_type']])->column('type_name')[0];
            $ret['keyword'] = $value['tags'];
            $ret['price'] = $value['price'];
            $ret['paynumber'] = $value['order_num'];
            $ret['limitpaynumber'] = $value['student_num'];
            
            $now = time();
            @$ret['haveStartedClassNum'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->whereTime('stage_start', '<', $now)->count();

            $ret['isOwnCourse'] = $value['user_id'] == $token_uid ?true:false ;
            if ($user&& $user['group_id']==5) {
                $ret['isBuy'] = db('toplearning_order')->where(['user_id'=>$token_uid, 'net_material_id'=>$value['net_material_id']])->count()?true:false;
                $ret['isCollection'] = db('toplearning_favorite')->where(['type'=>0,'source_id'=>$value['net_material_id'],'user_id'=>$token_uid])->count()?true:false;
            }

            $ret['total'] = intval($value['total_lessons']);
        }
        

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getCourseInformation 课程信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCourseInformation($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $token_uid = $this->decrypt($token);

        $info = db('toplearning_net_material')->alias('a')->field("a.*,a.introduce as aintroduce,l.*,l.introduce as lintroduce")->join('toplearning_login l','a.teacher_user_id = l.user_id')->where(['net_material_id'=>$courseid])->find();

        $ret = [];
        if ($info) {
            $ret['courseid'] = $info['net_material_id'];
            $ret['desc'] = $info['aintroduce'];
            $ret['teacherName'] = $info['nickname'];
            $ret['teacherHead'] = $info['avatar'];
            $ret['teacherPersent'] = $info['lintroduce'];
            $ret['teachingOutline'] = [];

        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getLessonFestivalArrangementList 课节安排]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getLessonFestivalArrangementList($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $map['a.net_material_id'] = $courseid;
        $map['f.del'] = 0;
        $info = db('toplearning_net_material')->alias('a')->join('toplearning_class_festival f','a.net_material_id = f.material_id','LEFT')->where($map)->select();
        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['lessonsId'] = $value['class_id'];
            $ret[$key]['name'] = $value['class_name'];
            // $ret[$key]['time'] = date('Y-m-d H:i',strtotime($value['stage_start'])).'-'.date('H:i',strtotime($value['stage_end'])) ;
            $ret[$key]['time'] = date('Y-m-d',strtotime($value['stage_start']));
            $ret[$key]['startTime'] = date("H:i",strtotime($value['stage_start']));
            $ret[$key]['endTime'] =  date("H:i",strtotime($value['stage_end']));
            $ret[$key]['completion'] = '10%';//TODO


            $ret[$key]['year'] = date('Y',strtotime($value['stage_start']));
            $ret[$key]['month'] = date('m',strtotime($value['stage_start']));
            $ret[$key]['day'] = date('d',strtotime($value['stage_start']));
            $ret[$key]['startTime'] = date('H:i',strtotime($value['stage_start']));

            $ret[$key]['lessontime'] = $value['lesson_time'];
            $ret[$key]['lessonWay'] = $value['status'];
            $ret[$key]['guide'] = $value['guide'];

            $ret[$key]['index'] = $value['index'];

            $ret[$key]['status'] = (strtotime($value['stage_start'])>time())?'2':(strtotime($value['stage_end'])<time()?'3':'1');
            
            
            if ($value['status']==1) {
                @$ret[$key]['liveAddress'] = $this->is_serialized($value['video'])?unserialize($value['video'])[0]['video']:$value['video'];
            }else{
             @$ret[$key]['videoBroadcastAddress'] = $this->is_serialized($value['courseware'])?unserialize($value['courseware'])[0]['address']:$value['courseware'];
         }

     }
        //返回信息
     $data = [
        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$ret,
        'Success'=>true
    ];

    return json($data);
}

    /**
     * [getCollegeTeachers  学院下的课程]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCollegeCourse($params)
    {
        //params
        $token = trim($params['token']);
        $collegeid = trim($params['collegeid']);
        $page = trim($params['page']);
        $size = trim($params['size']);

        //学院老师
        $map['del'] = 0;
        $map['school_id'] = $collegeid;
        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $map['type'] = 1;
        $map['reviewed_status'] = 3;
        $limit = $page*$size;
        $teachers = db('toplearning_net_material')->where($map)->limit($limit, $size)->select();

        $ret = array();
        foreach ($teachers as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            $ret[$key]['title'] = $value['title'];
            $ret[$key]['image'] = $value['picture'];
            $ret[$key]['price'] = $value['price'];
            $ret[$key]['buycount'] = $value['order_num'];
            // $ret[$key]['teacher'] = '';
            // if (db('toplearning_teacher')->where(['teacher_id'=>$value['teacher_id']])->find()) {
            //     $ret[$key]['teacher']  = db('toplearning_teacher')->where(['teacher_id'=>$value['teacher_id']])->column('teacher_name')[0];

            // }
            @$ret[$key]['teacherName'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];
            $ret[$key]['collegeName'] = $value['school_name'] ;
            
            $ret[$key]['collegeId'] = $value['school_id'];
            $ret[$key]['teacherId'] = $value['teacher_user_id'];

        }

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [getTodayCourse 今日课程]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getTodayCourse($params)
    {
        //params
        $token = trim($params['token']);

        //今日0点时间戳
        $todaytime = strtotime(date('Y-m-d',time()));
        $todayetime = strtotime(date('Y-m-d',time()))+24 * 60 * 60;

        $map['a.del'] = 0;
        $map['a.reviewed_status'] = 3;
        $map['a.user_id'] = $this->decrypt($token);
        $info = db('toplearning_net_material')->alias('a')->join('toplearning_class_festival f','a.net_material_id = f.material_id')->where($map)->whereTime('f.stage_start', 'today')->select();//3，今日课程数据错误
        // $info = db('toplearning_net_material')->alias('a')->join('toplearning_class_festival f','a.net_material_id = f.material_id','LEFT')->where($map)->select();
        $ret = array();


        $user = db('toplearning_login')->where(['user_id'=>$map['a.user_id']])->find();
        if ($user&& $user['group_id']==3) {//laoshi

            foreach ($info as $key => $value) {
                $ret[$key]['courseid'] = $value['material_id'];
                $ret[$key]['courseName'] = $value['title'];
                $ret[$key]['lessonsId'] = $value['class_id'];
                $ret[$key]['lessonsName'] = $value['class_name'];
                $ret[$key]['status'] = (strtotime($value['stage_start'])>time())?'2':(strtotime($value['stage_end'])<time()?'3':'1');
                $ret[$key]['number'] = $value['off_num'];
                $ret[$key]['time'] = $value['stage_start'];

                //返回时间处理
                //$value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['startTime'];
                $ret[$key]['year'] = date('Y',strtotime($value['stage_start']));
                $ret[$key]['month'] = date('m',strtotime($value['stage_start']));
                $ret[$key]['day'] = date('d',strtotime($value['stage_start']));

                $ret[$key]['type'] = $value['status'];

                //liveAddress |videoBroadcastAddress TODO
                // if ($value['status']==1) {
                @$ret[$key]['liveAddress'] = $this->is_serialized($value['video'])?unserialize($value['video'])[0]['video']:$value['video'];
                // }else{
                @$ret[$key]['videoBroadcastAddress'] = $this->is_serialized($value['courseware'])?unserialize($value['courseware'])[0]['address']:$value['courseware'];
                // }

                $ret[$key]['lessontime'] = $value['lesson_time'];
                $ret[$key]['startTime'] = date('H:i',strtotime($value['stage_start']));
                $ret[$key]['endTime'] = date('H:i',strtotime($value['stage_end']));

                $ret[$key]['collegeName'] = $value['school_name'] ;
                @$ret[$key]['teacherName'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];
                $ret[$key]['collegeId'] = $value['school_id'];
                $ret[$key]['teacherId'] = $value['teacher_user_id'];
            }

        }else{
            $token_uid = $this->decrypt($token);
            $info = db('toplearning_net_material')->alias('a')->field('a.*,s.*,f.*')->join('toplearning_student_material s','a.net_material_id = s.material_id')->join('toplearning_class_festival f','a.net_material_id = f.material_id')->where(['a.del'=>0,'s.user_id'=>$token_uid])->whereTime('f.stage_start', 'today')->select();
            foreach ($info as $key => $value) {
                $ret[$key]['courseid'] = $value['net_material_id'];
                $ret[$key]['courseName'] = $value['title'];
                $ret[$key]['lessonsId'] = $value['class_id'];
                $ret[$key]['lessonsName'] = $value['class_name'];
                $ret[$key]['status'] = (strtotime($value['stage_start'])>time())?'2':(strtotime($value['stage_end'])<time()?'3':'1');
                $ret[$key]['number'] = $value['off_num'];
                $ret[$key]['time'] = $value['stage_start'];

                //返回时间处理
                $ret[$key]['year'] = date('Y',strtotime($value['stage_start']));
                $ret[$key]['month'] = date('m',strtotime($value['stage_start']));
                $ret[$key]['day'] = date('d',strtotime($value['stage_start']));
                @$ret[$key]['liveAddress'] = $this->is_serialized($value['video'])?unserialize($value['video'])[0]['video']:$value['video'];
                @$ret[$key]['videoBroadcastAddress'] = $this->is_serialized($value['courseware'])?unserialize($value['courseware'])[0]['address']:$value['courseware'];
                $ret[$key]['lessontime'] = $value['lesson_time'];
                $ret[$key]['startTime'] = date('H:i',strtotime($value['stage_start']));
                $ret[$key]['endTime'] = date('H:i',strtotime($value['stage_end']));
                $ret[$key]['type'] = $value['status'];

                $ret[$key]['collegeName'] = $value['school_name'] ;
                @$ret[$key]['teacherName'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];
                $ret[$key]['collegeId'] = $value['school_id'];
                $ret[$key]['teacherId'] = $value['teacher_user_id'];
                $ret[$key]['complete'] = 20;//TODO                

            }

        }
        $data = [        //返回信息

        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$ret,
        'Success'=>true
    ];

    return json($data);
}

    /**
     * [getMyCourse 2.  首页我的课程 TODO]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getMyCourse($params)
    {
        //params
        $token = trim($params['token']);

        //通过token获取 uid
        $token_uid = $this->decrypt($token);


        //身份
        $user = db('toplearning_login')->where(['user_id'=>$token_uid])->find();
        $ret = array();
        if ($user&& $user['group_id']==3) {//laoshi
           $info = db('toplearning_net_material')->where(['del'=>0,'user_id'=>$token_uid])->select();
           foreach ($info as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            @$ret[$key]['image'] = json_decode($value['picture'],true)['m'];
            $ret[$key]['type'] = $value['type'];
            $ret[$key]['title'] = $value['title'];
                // $college = db('toplearning_school')->where(['school_id'=>$value['school_id']])->column('school_name');
            $ret[$key]['college'] = $value['school_name'];
            $ret[$key]['total'] = $value['total_lessons'];
            $ret[$key]['release'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->count();
            // $ret[$key]['release'] = $value['release'];

            // $status = 0;
            // switch ($value['reviewed_status']) {
            //     case '0':
            //     $status = 1;
            //     break;
            //     case '1':
            //     $status = 3;

            //     break;
            //     case '2':
            //     $status = 2;

            //     break;
            //     default:
            //     $status = 0;
            //     break;
            // }
            $ret[$key]['status'] = $value['reviewed_status'];

            $ret[$key]['collegeName'] = $value['school_name'] ;
            $ret[$key]['teacherName'] = $user['nickname'];
            $ret[$key]['collegeId'] = $value['school_id'];
            $ret[$key]['teacherId'] = $value['teacher_user_id'];


            // $ret[$key]['type'] = $value['lession_status'];
        }   



    }else{
        $info = db('toplearning_net_material')->alias('a')->field('a.*,s.*')->join('toplearning_student_material s','a.net_material_id = s.material_id')->where(['a.del'=>0,'s.user_id'=>$token_uid])->select();
        foreach ($info as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            $ret[$key]['image'] = $value['picture'];
            $ret[$key]['type'] = $value['lession_status'];
            $ret[$key]['title'] = $value['title'];
            $ret[$key]['type'] = $value['lession_status'];
                // $college = db('toplearning_school')->where(['school_id'=>$value['school_id']])->column('school_name');
                // $ret[$key]['college'] = $college?$college:'';
            $ret[$key]['college'] = $value['school_name'];
            $ret[$key]['total'] = $value['total_lessons'];
                // $ret[$key]['release'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->count();
            $ret[$key]['release'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->count();
            $status = 0;
            switch ($value['reviewed_status']) {
                case '0':
                $status = 1;

                break;
                case '1':
                $status = 3;

                break;
                case '2':
                $status = 2;

                break;
                default:
                $status = 0;
                break;
            }
            $ret[$key]['status'] = $value['reviewed_status'];

            $ret[$key]['collegeName'] = $value['school_name'] ;
            @$ret[$key]['teacherName'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];
            $ret[$key]['collegeId'] = $value['school_id'];
            $ret[$key]['teacherId'] = $value['teacher_user_id'];


            $ret[$key]['complete'] = 20;//TODO
            // $ret[$key]['type'] = $value['lession_status'];

        }
    }



        //返回信息
    $data = [
        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$ret,
        'Success'=>true
    ];

    return json($data);
}

    /**
     * [getCourseCollege 3. 学院列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCourseCollege($params)
    {
        //params
        $token = trim($params['token']);

        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        $school_ids = db("toplearning_school_teacher")->where(['user_id'=>$token_uid])->column("school_id");
        $school_ids = $school_ids?$school_ids:[];
        if(!in_array("7",$school_ids)){
            array_unshift($school_ids, 7);
        }

        $info = db("toplearning_school")->where(" school_id IN (".implode(",",$school_ids).")")->order(" field(school_id,".implode(",",$school_ids).")")->select();         

        // $mobiles = db('toplearning_login')->where(['user_id'=>$token_uid])->column('mobile');
        // $info = [];
        // if ($mobiles) {
        //     $map['l.mobile'] = array('in',implode(',', $mobiles));
        //     $info = db('toplearning_school')->alias('a')->field("a.*")->join('toplearning_login l','a.school_id = l.school_id')->where($map)->select();
        // }





        // $info = db('toplearning_school')->where(['del'=>0])->select();

        $ret = array();
        $i = 0;
        foreach ($info as $key => $value) {
            $ret[$key]['collegeid'] = $value['school_id'];
            $ret[$key]['name'] = $value['school_name'];
            $i++;
        }

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);

    }

    /**
     * [getCourseType 4.    课程分类列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCourseType($params)
    {
        //params
        $token = trim($params['token']);

        $info = db('toplearning_course_type')->where(['del'=>0])->select();
        
        $ret = array();

        foreach ($info as $key => $value) {
            $ret[$key]['typeid'] = $value['type_id'];
            $ret[$key]['name'] = $value['type_name'];
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [addlessons 5.   新增课节 ]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function addlessons($params)
    {
         //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $title = trim($params['title']);
        $way = trim($params['way']);
        $time = trim($params['time']);
        $guide = trim($params['guide']);
        $coursewarename = trim($params['coursewarename']);
        $courseware = trim($params['courseware']);
        $video = trim($params['video']);


        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        $data['material_id'] = $courseid;
        $data['class_name'] = $title;
        $data['status'] = $way;
        $data['stage_start'] = strtotime($time);
        $data['guide'] = $guide;
        $data['coursewarename'] = $coursewarename;
        $data['courseware'] = $courseware;
        $data['video'] = $video;

        if (!db('toplearning_class_festival')->insert($data)) {
            $this->error('新增失败');
        }
        $ret = array();

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getRecordedCourseList 6.    课程列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getRecordedCourseList($params)
    {
        //params
        $token = trim($params['token']);

        $token_uid = $this->decrypt($token);

        $info = db('toplearning_net_material')->where(['del'=>0,'user_id'=>$token_uid,'release_status'=>1])->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            $ret[$key]['name'] = $value['title'];
            @$ret[$key]['image'] = json_decode($value['picture'],true)['m'];
            $ret[$key]['total'] = $value['total_lessons'];
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getRecordedLessonsList 7.   所有课节列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getRecordedLessonsList($params)
    {
        //params
        $token = trim($params['token']);
        $lessonsid = trim($params['lessonsid']);

        // $map['t.type'] =  0;
        //['material_id'=>$lessonsid]
        $map['material_id'] = $lessonsid;
        // $info = db('toplearning_exam')->alias('a')->join('toplearning_do_exam_time t','a.exam_id = t.exam_id')->where(['a.exam_id'=>$examid])->select();
        // $info = db('toplearning_class_festival')->alias('a')->join('toplearning_media t','a.material_id = t.type_id','LEFT')->where($map)->select();
        $info = db('toplearning_class_festival')->where($map)->select();

        $ret = array();
        $i =1 ;
        foreach ($info as $key => $value) {
            if (!$value['video']||($this->is_serialized($value['video'])&&empty(unserialize($value['video']))) ) {
                unset($info[$key]);
                continue;
            }
            $ret[$key]['lessonsid'] = $value['class_id'];
            $ret[$key]['name'] = $value['class_name'];
            $ret[$key]['index'] = $i;

            $ret[$key]['year'] = date('Y',strtotime($value['stage_start']));
            $ret[$key]['month'] = date('m',strtotime($value['stage_start']));
            $ret[$key]['day'] = date('d',strtotime($value['stage_start']));
            $ret[$key]['startTime'] = date('H:i',strtotime($value['stage_start']));
            $ret[$key]['endTime'] = date('H:i',strtotime($value['stage_end']));

            $ret[$key]['lessontime'] = $value['lesson_time'];

            // $video = unserialize($value['video']);

            $ret[$key]['video'] = $this->is_serialized($value['video'])?unserialize($value['video']):$value['video'];
            $ret[$key]['coursewareIdList'] = $this->is_serialized($value['courseware'])?unserialize($value['courseware']):$value['courseware'];
            $i++;
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [saveCourse 8.   保存课程 ]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function saveCourse($params)
    {


        // $data['picture'] =$this->_seve_img($params['head']);
        // exit;

        //params
        $token = trim($params['token']);
        
         //json
        $json = json_decode($params['json'],true);
        // $json = $params['json'];
        //json下
        // $image = trim($json['image']);//TODO
        $title = trim($json['title']);
        $college = trim($json['collegeId']);
        $type = trim($json['typeId']);
        $keyword = trim($json['keyword']);
        $totallessons = trim($json['totallessons']);
        $monthlessons = trim($json['monthlessons']);
        $price = trim($json['price']);
        $limitnumber = trim($json['limitnumber']);
        $desc = trim($json['desc']);
        $courseid = !empty($json['courseid'])?trim($json['courseid']):"";

        $way = isset($json['way'])?trim($json['way']):'';
        
        //classTypeList
        //

        //通过token获取 uid
        $token_uid = $this->decrypt($token);
        //data
        $data['title'] = $title;
        $data['lession_name'] = $title;
        //处理图片
        
        // if(!empty($json['head'])){

        //     @$data['picture'] =$this->_seve_img($json['head']);
        // }


        if(!empty($params['head'])){
            $file = "/tmp/".time().rand(0,10000).".png";
            $r = file_put_contents($file, base64_decode($params['head']));//返回的是字节数
            if(!$r){
                return $this->error('图片格式错误');
            }
            $res = $this->uploadFile($file,"png");
            if($res['code'] != 0){
                return $this->error('更新图片失败');
            }
            $data['picture'] = json_encode(['l'=>$res['path'],'m'=>$res['path'],'s'=>$res['path']]);
        }



        


        // $school = db('toplearning_login')->where(['user_id'=>$token_uid])->find();

        // $data['picture'] = $image;
        $data['school_id'] = $college;
        $school_name = db('toplearning_school')->where(['school_id'=>$college])->value('school_name');
        $data['school_name'] = $school_name?$school_name:'';
        $data['course_type'] = $type;
        $data['type'] = 1;
        $data['type_from'] = 2;//from APP
        $data['tags'] = $keyword;
        $data['total_lessons'] = $totallessons;
        $data['month_lessons'] = $monthlessons;
        $data['price'] = $price;
        $data['student_num'] = $limitnumber;
        $data['lock_pople'] = $limitnumber;
        $data['introduce'] = $desc;
        $data['lession_status'] = 1;
        $data['user_id'] = $token_uid;
        $data['teacher_user_id'] = $token_uid;
        $data['teacher_id'] = db('toplearning_teacher')->where(['user_id'=>$token_uid])->column('teacher_id')?db('toplearning_teacher')->where(['user_id'=>$token_uid])->column('teacher_id')[0]:'';
        $data['school_id'] = $college;
        $data['release_status'] = 1;
        $data['reviewed_status'] = 0;
        
        if(!empty($courseid)){
            $insertid = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->update($data);
        }else{
            $insertid = db('toplearning_net_material')->insert($data);
        }
        if ($insertid === false) {
            return $this->error('保存失败');
        }
        $net_material_id = !empty($courseid)?$courseid:Db::name('toplearning_net_material')->getLastInsID();
        //课程保存 处理课节
        $classTypeList = $json['classTypeList'];
        $save = array();

        $class_ids = db('toplearning_class_festival')->where(['material_id'=>$net_material_id])->column("class_id");
        foreach ($classTypeList as $key => $value) {
            $save['guide'] = $value['guide'];
            $save['class_name'] = $value['name'];
            $save['material_id'] = $net_material_id;
            $save['status'] = $value['way'];

            //时间处理
            $sttime = $value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['startTime'];
            @$save['lesson_time'] = intval($value['lessontime']);
            @$save['stage_start'] = date('Y-m-d H:i:s',strtotime($sttime));
            @$save['stage_end'] = date('Y-m-d H:i:s',strtotime($sttime)+intval($value['lessontime'])*60);

            // $timearr = explode(' ', $value['time']);
            // @$save['lesson_time'] = intval($timearr[1]);
            // $srt = str_replace(array('年','月'),'-',$timearr[0]);
            // $str1 = str_replace(array('日'),' ',$srt);
            // $save['stage_start'] = date('Y-m-d H:i:s',strtotime($str1));
            // @$save['stage_end'] = date('Y-m-d H:i:s',strtotime($str1)+intval($timearr[1])*60);


            $save['index'] = $value['index'];

            //视频
            $save['video'] = serialize($value['video']);
            //课件
            $save['courseware'] = serialize($value['coursewareIdList']);



            if(!empty($value['lessonid'])){

                $f = $value['lessonid'];
                db('toplearning_class_festival')->where(['class_id'=>$f])->update($save);
                $kkk=array_search($f ,$class_ids);
                if($kkk !== false){
                    array_splice($class_ids,$kkk,1);
                }



            }else{
                db('toplearning_class_festival')->insert($save);
                $f = Db::name('toplearning_class_festival')->getLastInsID();
            }

            db("toplearning_teacher_prepare")->where(['class_id'=>$f])->delete();
            foreach ($value['coursewareIdList'] as $k => $v) {
                $cid = $v['coursewareid'];
                $in = Db::name('toplearning_teacher_prepare')->where(['prepare_id'=>$cid])->find();
                $s = $in;
                unset($s['prepare_id']);
                unset($s['class_id']);
                unset($s['create_time']);
                $s['class_id'] = $f;
                $s['create_time'] = time();
                @db('toplearning_teacher_prepare')->insert($s);

            }
        }
        if(!empty($class_ids)){
            db("toplearning_class_festival")->where("class_id in (".implode(",",$class_ids).")")->delete();
        }
        $ret = array('courseid'=>intval($net_material_id));
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [deleteCourse 9. 删除课程]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function deleteCourse($params)
    {   
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        db('toplearning_net_material')->where(['net_material_id'=>$courseid])->update(['del'=>1]);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [publishCourse 10.   发布课程  TODO]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function publishCourse($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        db('toplearning_net_material')->where(['net_material_id'=>$courseid])->update(['release_status'=>1,'reviewed_status'=>1]);

        $info = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->find();

        $ret = array();
        
        $ret['courseid'] = $info['net_material_id'];

        $ret['image'] = $info['picture'];
        $ret['title'] = $info['title'];
        $ret['college'] = $info['create_name'];
        $ret['type'] = $info['type'];
        $ret['keyword'] = $info['tags'];
        $ret['price'] = $info['price'];
        $ret['paynumber'] = $info['order_num'];
        $ret['limitpaynumber'] = $info['student_num'];
        $ret['desc'] = $info['introduce'];

        //课时 
        $lesson =  db('toplearning_class_festival')->where(['material_id'=>$courseid])->select();
        $rs = array();
        foreach ($lesson as $key => $value) {
            $rs[$key]['lessonid'] = $value['class_id'];
            $rs[$key]['index'] = $value['class_id'];
            $rs[$key]['name'] = $value['class_id'];
            $rs[$key]['time'] = strtotime($value['stage_start']);
            $rs[$key]['lessontime'] = $value['lesson_time'];
            $rs[$key]['lessonWay'] = $value['status'];
            $rs[$key]['staus'] = strtotime($value['stage_start'])<time()?'2':(strtotime($value['stage_end'])>time()?'2':'1');
        }
        $ret['lessonsList'] = $rs;

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getUploadCoursewareList 获取可上传课件列表 ]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getUploadCoursewareList($params)
    {
        //params
        $token = trim($params['token']);


        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        $info = db('toplearning_teacher_prepare')->where(['user_id'=>$token_uid])->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['coursewareid'] = $value['prepare_id'];
            $ret[$key]['name'] = $value['unit_name'];
            $ret[$key]['size'] = $value['size'];
            $ret[$key]['time'] = $value['create_time'];
            $ret[$key]['address'] = $value['prepare_file'];
        }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [saveWeike 12.   保存微课 ]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function saveWeike($params)
    {
        //params
        $token = trim($params['token']);
        $json = trim($params['json']);
        $json = json_decode($json,true);
        // $image = trim($json['image']);//todo
        $title = trim($json['title']);
        $keyword = trim($json['keyword']);
        $desc = trim($json['desc']);
        $share = trim($json['share']);


        //通过token获取 uid
        $token_uid = $this->decrypt($token);





        


        if(!empty($params['head'])){
            $file = "/tmp/".time().rand(0,10000).".png";
            $r = file_put_contents($file, base64_decode($params['head']));//返回的是字节数
            if(!$r){
                return $this->error('图片格式错误');
            }
            $res = $this->uploadFile($file,"png");
            if($res['code'] != 0){
                return $this->error('更新图片失败');
            }
            $data['picture'] = json_encode(['l'=>$res['path'],'m'=>$res['path'],'s'=>$res['path']]);
        }




        $data['user_id'] = $token_uid;
        $data['title'] = $title;
        // $data['lession_img'] = $image;
        $data['course_type']  = trim($json['typeid']);
        $data['type'] = 2;
        $data['lesson_type'] = 2;
        $data['lession_status'] = 2;
        $data['keyword'] = $keyword;
        $data['introduce'] = $desc;
        $data['share'] = $share;
        $data['add_time'] = date('Y-m-d H:i:s',time());
        $data['release_status'] = "1";
        $data['reviewed_status'] = "3";//审核通过了
        //toplearning_micro_class
        $data['total_lessons'] = count($json['classFestivalList']);
        $data['release'] = count($json['classFestivalList']);
        if(!empty($json['courseid'])){
            $net_material_id = $json['courseid'];
            $res =    db('toplearning_net_material')->where(['net_material_id'=>$net_material_id])->update($data);

        }else{            
            $res = db('toplearning_net_material')->insert($data);

            $net_material_id = Db::name('toplearning_net_material')->getLastInsID();
        }

        if ($res === false) {
            return $this->error('保存失败');
        }
        //课程保存 处理课节
        $classTypeList = $json['classFestivalList'];
        $save = array();
        // db('toplearning_class_festival')->where(['material_id'=>$net_material_id])->delete();
        $class_ids = db('toplearning_class_festival')->where(['material_id'=>$net_material_id])->column("class_id");

        foreach ($classTypeList as $key => $value) {
            $save['guide'] = $value['guide'];
            $save['class_name'] = $value['name'];
            $save['material_id'] = $net_material_id;
            $save['status'] = 2;
            
            $sttime = $value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['startTime'];

            @$save['stage_start'] = date('Y-m-d H:i:s',strtotime($sttime));

            // $save['stage_start'] = date('Y-m-d H:i:s',strtotime(strtr($value['time'],['年'=>'-','月'=>'-','日'=>""])));


            $save['lesson_time'] = $value['lessontime'];
            $save['add_time'] = date('Y-m-d H:i:s',time());

            // $save['index'] = $value['index'];

            //视频
            $save['video'] = serialize([
              'vdeoId'=>$value['vdeoId'],
              'vdeoDuration'=>$value['vdeoDuration'],
              'lessontime'=>$value['lessontime'],
              'videoUrl'=>$value['videoUrl'],
              
          ]);
            //课件
            $save['courseware'] = serialize($value['coursewareList']);




            if(!empty($value['lessonid'])){

                $f = $value['lessonid'];
                db('toplearning_class_festival')->where(['class_id'=>$f])->update($save);
                $kkk=array_search($f ,$class_ids);
                if($kkk !== false){
                    array_splice($class_ids,$kkk,1);
                }



            }else{
                db('toplearning_class_festival')->insert($save);
                $f = Db::name('toplearning_class_festival')->getLastInsID();
            }







            // db('toplearning_micro_class')->insert($data);
        }

        if(!empty($class_ids)){

            db("toplearning_class_festival")->where("class_id in (".implode(",",$class_ids).")")->delete();
        }



        
        $ret = array('courseid'=>intval($net_material_id));
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [addweikelessons 13. 新增微课课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function addweikelessons($params)
    {
        //params
        $token = trim($params['token']);

        $courseid = trim($params['courseid']);
        $title = trim($params['title']);
        $guide = trim($params['guide']);
        $coursewarename = trim($params['coursewarename']);
        $courseware = trim($params['courseware']);


        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        $data['material_id'] = $courseid;
        $data['class_name'] = $title;
        $data['guide'] = $guide;
        $data['coursewarename'] = $coursewarename;
        $data['courseware'] = $courseware;

        if (!db('toplearning_class_festival')->insert($data)) {
            $this->error('新增失败');
        }
        db("toplearning_net_material")->where(['net_material_id'=>$courseid])->update(['release'=>['exp', 'release+1']]);

        $ret = array();
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getpaynumberlist 14.    购买人数列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getPayNumberList($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $page = !empty($params['page'])?trim($params['page']):0;
        $size = !empty($params['size'])?trim($params['size']):10;

        $info = db('toplearning_order')->where(['net_material_id'=>$courseid])
        ->limit($page*$size.",".$size)
        ->select();
        
        $ret = array();

        foreach ($info as $key => $value) {
            $ret[$key]['userid'] = $value['user_id'];
            $ret[$key]['name'] = $value['nickname'];
            @$ret[$key]['image'] = db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('avatar')[0];
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);

    }

    /**
     * [getCoursewaredataList 15.   查看课件列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCoursewaredataList($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        

        $info = db('toplearning_teacher_prepare')->where(['class_id'=>$courseid])->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['coursewareid'] = $value['prepare_id'];
            $ret[$key]['name'] = $value['unit_name'];
            $ret[$key]['size'] = $value['size'];
            $ret[$key]['time'] = $value['create_time'];
            $ret[$key]['address'] = $value['prepare_file'];
        }

        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getExamList 16. 考试列表 TODO]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getExamList($params)
    {
        //params
        $token = trim($params['token']);

        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        //身份
        $user = db('toplearning_login')->where(['user_id'=>$token_uid])->find();
        $type = $user['group_id'] == 3?'2':'1';//2老师 1学生

        // $info = db('toplearning_exam')->alias('a')->join('toplearning_do_exam_time t','a.exam_id = t.exam_id')->where(['a.exam_id'=>$examid])->select();
        

        $ret = array();
        if ($type==1) {
            $info = db('toplearning_exam')->where(['user_id'=>$token_uid,'owner_type'=>1])->select();
            foreach ($info as $key => $value) {
                $ret[$key]['examid'] = $value['exam_id'];
                $ret[$key]['name'] = $value['exam_name'];
                $ret[$key]['college'] = $value['exam_id'];
                $ret[$key]['time'] = $value['start_time'];
                $ret[$key]['examtime'] = ceil((strtotime($value['end_time'])-strtotime($value['start_time']))/(60*24));
                $ret[$key]['submit'] = db('toplearning_do_exam_time')->where(['exam_id'=>$value['exam_id']])->count();
                $ret[$key]['total'] = $value['exam_num'];
            }
        }
        
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [submitExamList 17.  交作业人列表TODO]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function submitExamList($params)
    {
        //params
        $token = trim($params['token']);
        $examid = trim($params['examid']);

        $info =  db('toplearning_do_exam_time')->where(['exam_id'=>$value['exam_id']])->select();
        $ret = array();

        foreach ($info as $key => $value) {
            $ret[$key]['userid']= $value['user_id'];
            $ret[$key]['name']= db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')?db('toplearning_login')->where(['exam_id'=>$value['user_id']])->column('nickname')[0]:'';
            $ret[$key]['image']= db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('avatar')?db('toplearning_login')->where(['exam_id'=>$value['user_id']])->column('avatar')[0]:'';
        }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getlearningrecord 18.   学习记录]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getlearningrecord($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);


        $info = db('toplearning_learn_situation')->where(['class_id'=>$courseid])->select();

        $ret = array();
        foreach ($ret as $key => $value) {
            $ret[$key]['userid'] =   $value['user_id'];
            $ret[$key]['name'] =  db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')?db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')[0]:'';
            $ret[$key]['time'] =  $value['learn_start_time'];
            $ret[$key]['totaltime'] =  ceil((strtotime($value['learn_end_time'])-strtotime($value['learn_start_time']))/(24*60)) ;
            $ret[$key]['completion'] =  $value['learn_result'];
        }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [getCourseEvaluation 19. 学员评价 ]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCourseEvaluation($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $lessonid = isset($params['lessonid'])?$params['lessonid']:'';

        @$size = trim($params['size']);
        @$page = trim($params['page']);

        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;
        //
        $token_uid = $this->encrypt($token);

        //处理评价
        if (empty($lessonid)) {
            $info = db('toplearning_appraise_dictionary')->alias('a')->join('toplearning_class_festival f','a.class_id = f.class_id')->join('toplearning_net_material n','n.net_material_id = f.material_id')->where(['n.net_material_id'=>$courseid])->limit($limit, $size)->select();
            $count = db('toplearning_appraise_dictionary')->alias('a')->join('toplearning_class_festival f','a.class_id = f.class_id')->join('toplearning_net_material n','n.net_material_id = f.material_id')->where(['n.net_material_id'=>$courseid])->count();
            $totalscore = db('toplearning_appraise_dictionary')->alias('a')->join('toplearning_class_festival f','a.class_id = f.class_id')->join('toplearning_net_material n','n.net_material_id = f.material_id')->where(['n.net_material_id'=>$courseid])->sum('score');
        }else{
            $info = db('toplearning_appraise_dictionary')->where(['class_id'=>$lessonid])->limit($limit, $size)->select();
            $count = db('toplearning_appraise_dictionary')->where(['class_id'=>$lessonid])->count();
            $totalscore = db('toplearning_appraise_dictionary')->where(['class_id'=>$lessonid])->sum('score');
        }
        

        $ret = array();
        $rs['totalscore'] = @number_format($totalscore/$count,1);
        foreach ($info as $key => $value) {
            $ret[$key]['userid'] = $value['user_Id'];
            @$ret[$key]['username'] = db('toplearning_login')->where(['user_id'=>$value['user_Id']])->column('nickname')[0];
            $ret[$key]['time'] = $value['create_time'];
            $ret[$key]['content'] = $value['appraise_name'];
            $ret[$key]['score'] = number_format($value['score'],1);
            $ret[$key]['hade'] = db('toplearning_login')->where(['user_id'=>$value['user_Id']])->column('avatar')[0];
        }
        
        $rs['courseEvaluationlist'] = $ret;
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$rs,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [BusinessCard 3. 个人名片]J9FqNKq0RfqiqBctZrE+PA==
     * @param string $value [description]
     */
    public function BusinessCard($params)
    {
        //params
        $token = trim($params['token']);
        $teacherid = trim($params['teacherid']);


        //user|material
        //检查过期时间
        if (cache($token)&&cache($token)<time()) {
            return $this->wrong('token失效，请重新登录');
        }

        //通过token获取 uid
        // $token_uid = $this->decrypt($token);

        $info = db('toplearning_login')->alias('a')->field('a.*,m.*,a.introduce as aintroduce')->join('toplearning_teacher t','a.user_id = t.user_id')->join('toplearning_net_material m','a.user_id = m.teacher_user_id')->where(['m.teacher_user_id'=>$teacherid,'m.type'=>1,'m.reviewed_status'=>3])->find();
        // echo db('toplearning_login')->getlastsql();exit;
        $ret = array();
        if ($info) {
            $ret['headurl'] = $info['avatar'];
            $ret['name'] = $info['nickname'];
            $ret['score'] = $info['persent'];//TODO
            $ret['school'] = $info['school_name'];
            // $ret['introduction'] = $info['mintroduce'];
            $ret['signture'] = $info['aintroduce'];
            $ret['correlatedCurriculumList'] = array();
            // @$ret['teacherName'] = db('toplearning_teacher')->where(['teacher_id'=>$value['teacher_user_id']])->column('teacher_name')[0];
            // $ret['collegeName'] = $info['school_name'] ;
            
            // $ret['collegeId'] = $info['school_id'];
            // $ret['teacherId'] = $info['teacher_user_id'];

            //相关课程
            $course = db('toplearning_net_material')->where(['del'=>0,'teacher_user_id'=>$teacherid,'type'=>1,'reviewed_status'=>3])->select();
            $coursearr = array();
            if ($course) {
                foreach ($course as $key => $value) {
                    $coursearr[$key]['courseHeadUrl'] = $value['picture'];
                    $coursearr[$key]['courseName'] = $value['title'];
                    $coursearr[$key]['courseNum'] = $value['price'];
                    $coursearr[$key]['purchaseNumber'] = $value['order_num'];
                    $coursearr[$key]['courseType'] = $value['create_name'];
                    $coursearr[$key]['courseid'] = $value['net_material_id'];

                    @$coursearr[$key]['teacherName'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];
                    $coursearr[$key]['collegeName'] = $value['school_name'] ;
                    
                    $coursearr[$key]['collegeId'] = $value['school_id'];
                    $coursearr[$key]['teacherId'] = $value['teacher_user_id'];

                }
                $ret['correlatedCurriculumList'] = $coursearr;
            }
        }
        

        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);

    }

    /**
     * [getCourse 20.   查询课程详情]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCourse($params)
    {


        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        //
        $info = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->find();


        $ret = array();
        
        $ret['courseid'] = $info['net_material_id'];

        @$ret['image'] = json_decode($info['picture'],true)['l'];
        $ret['title'] = $info['title'];
        @$ret['college'] = db('toplearning_school')->where(['school_id'=>$info['school_id']])->column('school_name')?db('toplearning_school')->where(['school_id'=>$info['school_id']])->column('school_name')[0]:'';
        $ret['type'] = db("toplearning_course_type")->where(['type_id'=>$info['course_type']])->value("type_name");
        $ret['keyword'] = $info['tags'];
        $ret['price'] = $info['price'];
        $ret['paynumber'] = $info['order_num'];
        $ret['limitpaynumber'] = $info['student_num'];
        $ret['desc'] = $info['introduce'];

        $ret['total'] = $info['total_lessons'];

        //新加
        $ret['collegeId'] = $info['school_id'];
        $ret['typeId'] = $info['course_type'];
        $ret['monthlyPitchNumber'] = $info['month_lessons'];


        $ret['commonPitchNumber'] = db('toplearning_class_festival')->where(['material_id'=>$info['net_material_id']])->count();

        //课时 
        $lesson =  db('toplearning_class_festival')->where(['del'=>0,'material_id'=>$courseid])->select();
        $rs = array();
        $i = 1;
        foreach ($lesson as $key => $value) {
            $rs[$key]['lessonid'] = $value['class_id'];
            $rs[$key]['index'] = $i;
            $rs[$key]['guide'] = $value['guide'];
            $rs[$key]['name'] = $value['class_name'];
            $rs[$key]['time'] = date("Y-m-d",strtotime($value['stage_start']));
            $rs[$key]['startTime'] = date("H:i",strtotime($value['stage_start']));
            $rs[$key]['endTime'] =  date("H:i",strtotime($value['stage_end']));
            $rs[$key]['lessontime'] = $value['lesson_time'];
            $rs[$key]['lessonWay'] = $value['status'];
            $rs[$key]['completion'] = '10%';//TODO


            $rs[$key]['year'] = date('Y',strtotime($value['stage_start']));
            $rs[$key]['month'] = date('m',strtotime($value['stage_start']));
            $rs[$key]['day'] = date('d',strtotime($value['stage_start']));

            $rs[$key]['status'] = (strtotime($value['stage_start'])>time())?'2':(strtotime($value['stage_end'])<time()?'3':'1');

            //liveAddress |videoBroadcastAddress 
            @$rs[$key]['liveAddress'] = $this->is_serialized($value['video'])?unserialize($value['video'])[0]['video']:$value['video'];
            @$rs[$key]['videoBroadcastAddress'] = $this->is_serialized($value['courseware'])?unserialize($value['courseware'])[0]['address']:$value['courseware'];

            $rs[$key]['video'] = $this->is_serialized($value['video'])?unserialize($value['video']):$value['video'];
            $rs[$key]['coursewareIdList'] = $this->is_serialized($value['courseware'])?unserialize($value['courseware']):$value['courseware'];

            $i++;
        }
        $ret['classTypeList'] = $rs;

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [publishedAddClassFestival 新增课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function publishedAddClassFestival($params)
    {   
        //params

        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $json_arr = $params['json']?json_decode($params['json'],true):[];
        if(empty($json_arr)){
            return json(['code'=>1,"Msg"=>"课节不能为空"]);
        }
        foreach($json_arr as $json){


            //处理课节
            if ($json) {
                $save['guide'] = $json['guide'];
                $save['class_name'] = $json['name'];
                $save['material_id'] = $courseid;
                $save['status'] = $json['way'];

                //时间处理
                // if ($json['time']) {

                $sttime = $json['year'].'-'.$json['month'].'-'.$json['day'].' '.$json['startTime'];
                @$save['lesson_time'] = intval($json['lessontime']);
                @$save['add_time'] = date('Y-m-d H:i:s',strtotime($sttime));

                @$save['stage_start'] = date('Y-m-d H:i:s',strtotime($sttime));
                @$save['stage_end'] = date('Y-m-d H:i:s',strtotime($sttime)+intval($json['lessontime'])*60);

                    // $timearr = explode(' ', $json['time']);
                    // @$save['lesson_time'] = intval($timearr[1]);
                    // $srt = str_replace(array('年','月'),'-',$timearr[0]);
                    // $str1 = str_replace(array('日'),' ',$srt);
                    // $save['add_time'] = date('Y-m-d H:i:s',strtotime($str1));
                // }
                
                $save['index'] = $json['index'];

                //视频
                $save['video'] = serialize($json['video']);
                //课件
                $save['courseware'] = serialize($json['coursewareIdList']);


                db('toplearning_class_festival')->insert($save);

                $class_id = Db::name('toplearning_class_festival')->getLastInsID();

                db("toplearning_teacher_prepare")->where(['class_id'=>$class_id])->delete();
                
                foreach ($json['coursewareIdList'] as $k => $v) {
                    $cid = $v['coursewareid'];
                    $in = Db::name('toplearning_teacher_prepare')->where(['prepare_id'=>$cid])->find();
                    $s = $in;
                    unset($s['prepare_id']);
                    unset($s['class_id']);
                    unset($s['create_time']);
                    $s['class_id'] = $class_id;
                    $s['create_time'] = time();
                    @db('toplearning_teacher_prepare')->insert($s);

                }
            }
        }
        // $ret = array('courseid'=>intval($courseid));

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [deleteFestival 删除课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function deleteFestival($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $lessonid = trim($params['lessonid']);

        db('toplearning_class_festival')->where(['material_id'=>$courseid,'class_id'=>$lessonid])->update(['del'=>1]);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>{},
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [updataFestival 修改课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function updataFestival($params)
    {

        //params

        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $lessonid = trim($params['lessonid']);
        $json = $params['json']?json_decode($params['json'],true):[];




         //处理课节
        if ($json) {
            $save['guide'] = $json['guide'];
            $save['class_name'] = $json['name'];
            $save['material_id'] = $courseid;
            $save['status'] = $json['way'];

            //时间处理
            // if ($json['time']) {
            $sttime = $json['year'].'-'.$json['month'].'-'.$json['day'].' '.$json['startTime'];
            @$save['lesson_time'] = intval($json['lessontime']);
            @$save['add_time'] = date('Y-m-d H:i:s',strtotime($sttime));

            @$save['stage_start'] = date('Y-m-d H:i:s',strtotime($sttime));
            @$save['stage_end'] = date('Y-m-d H:i:s',strtotime($sttime)+intval($json['lessontime'])*60);
                // $timearr = explode(' ', $json['time']);
                // @$save['lesson_time'] = intval($timearr[1]);
                // $srt = str_replace(array('年','月'),'-',$timearr[0]);
                // $str1 = str_replace(array('日'),' ',$srt);
                // $save['add_time'] = date('Y-m-d H:i:s',strtotime($str1));
            // }
            
            $save['index'] = $json['index'];

            //视频
            $save['video'] = serialize($json['video']);
            //课件
            $save['courseware'] = serialize($json['coursewareIdList']);



            db("toplearning_teacher_prepare")->where(['class_id'=>$lessonid])->delete();
            
            foreach ($json['coursewareIdList'] as $k => $v) {
                $cid = $v['coursewareid'];
                $in = Db::name('toplearning_teacher_prepare')->where(['prepare_id'=>$cid])->find();
                $s = $in;
                unset($s['prepare_id']);
                unset($s['class_id']);
                unset($s['create_time']);
                $s['class_id'] = $lessonid;
                $s['create_time'] = time();
                @db('toplearning_teacher_prepare')->insert($s);

            }

            db('toplearning_class_festival')->where(['material_id'=>$courseid,'class_id'=>$lessonid])->update($save);
        }
        $ret = array('courseid'=>intval($courseid));
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [deleteWeikeLessons 25.  删除微课课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function deleteWeikeLessons($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $lessonid = trim($params['lessonid']);

        db('toplearning_class_festival')->where(['material_id'=>$courseid,'class_id'=>$lessonid])->update(['del'=>1]);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>{},
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [updataWeikeLessons 26.  修改微课课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function updataWeikeLessons($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $lessonid = trim($params['lessonid']);
        $json = $params['json']?json_decode($params['json'],true):[];


        //处理课节
        if ($json) {
            $save['guide'] = $json['guide'];
            $save['guide'] = $json['guide'];
            $save['class_name'] = $json['name'];
            $save['material_id'] = $courseid;

            // //时间处理
            // if ($json['time']) {
            //     $srt = str_replace(array('年','月'),'-',$json['time']);
            //     $str1 = str_replace(array('日'),' ',$srt);
            //     $save['stage_start'] = date('Y-m-d H:i:s',strtotime($str1));
            // }
            
            $sttime = $json['year'].'-'.$json['month'].'-'.$json['day'].' '.$json['startTime'];
            @$save['stage_start'] = date('Y-m-d H:i:s',strtotime($sttime));

            $save['lesson_time'] = isset($json['lessontime'])?$json['lessontime']:'';




            $save['video'] = serialize([
              'vdeoId'=>$json['vdeoId'],
              'vdeoDuration'=>$json['vdeoDuration'],
              'lessontime'=>$json['lessontime'],
              'videoUrl'=>$json['videoUrl'],
              
          ]);
            //视频
             //课件
            $save['courseware'] = serialize($json['coursewareList']);
            db('toplearning_class_festival')->where(['material_id'=>$courseid,'class_id'=>$lessonid])->update($save);
        }
        $ret = array('courseid'=>intval($courseid));
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [vdeoUploading 27.   录制视频上传]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function vdeoUploading($params)
    {   
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);



         // $file = dirname($_FILES['vdeoUrl']['tmp_name']) . "/tmp.mp4";
        // move_uploaded_file($_FILES['vdeoUrl']['tmp_name'], $file);

        $file = $_FILES['vdeoUrl']['tmp_name'];

        $res = $this->uploadFile($file,"mp4");


        $map['a.net_material_id'] = $courseid;
        $map['f.del'] = 0;
        $info = db('toplearning_net_material')->alias('a')->field('f.*')->join('toplearning_class_festival f','a.net_material_id = f.material_id','LEFT')->where($map)->select();
        $ret = array();

        foreach ($info as $key => $value) {

            $ret[$key]['year'] = date('Y',strtotime($value['stage_start']));
            $ret[$key]['month'] = date('m',strtotime($value['stage_start']));
            $ret[$key]['day'] = date('d',strtotime($value['stage_start']));
            $ret[$key]['startTime'] = date('H:i',strtotime($value['stage_start']));
            $ret[$key]['lessontime'] = $value['lesson_time'];
            $ret[$key]['endTime'] = date('H:i',strtotime($value['stage_end']));

            @$ret[$key]['liveAddress'] = $this->is_serialized($value['video'])?unserialize($value['video']):$value['video'];

        }




        //返回信息
        $data = [
            'Code'=>$res['code'],
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [btainWeikeo 28. 获取微课详]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function btainWeikeo($params)
    {   
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $info = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->find();


        $ret = array();
        
        $ret['courseid'] = $info['net_material_id'];

        @$ret['image'] =    json_decode($info['picture'],true)['l'];
        ;
        $ret['title'] = $info['title'];
        $ret['college'] = db('toplearning_school')->where(['school_id'=>$info['school_id']])->column('school_name')?db('toplearning_school')->where(['school_id'=>$info['school_id']])->column('school_name')[0]:'';
        $ret['type'] = db("toplearning_course_type")->where(['type_id'=>$info['course_type']])->value("type_name");
        $ret['keyword'] = $info['keyword'];

        $ret['share'] = $info['share'];
        $ret['desc'] = $info['introduce'];

        //新加
        $ret['typeid'] = $info['course_type'];

        //课时 
        $lesson =  db('toplearning_class_festival')->where(['del'=>0,'material_id'=>$courseid])->select();
        $rs = array();
        $i = 1;
        foreach ($lesson as $key => $value) {
            $rs[$key]['lessonid'] = $value['class_id'];
            $rs[$key]['index'] = $i;
            $rs[$key]['name'] = $value['class_name'];
            $rs[$key]['guide'] = $value['guide'];

            $rs[$key]['time'] = date("Y年-m月-d日 H:i",strtotime($value['stage_start']));
            $rs[$key]['vdeoDuration'] = date("H:i",strtotime($value['stage_start']));
            $rs[$key]['lessontime'] = $value['lesson_time'];

            $rs[$key]['year'] = date('Y',strtotime($value['stage_start']));
            $rs[$key]['month'] = date('m',strtotime($value['stage_start']));
            $rs[$key]['day'] = date('d',strtotime($value['stage_start']));
            $rs[$key]['startTime'] = date('H:i',strtotime($value['stage_start']));
            $rs[$key]['endTime'] = date('H:i',strtotime($value['stage_end']));

// var_dump($value['courseware']);
            $rs[$key]['coursewareList'] = unserialize($value['courseware']);

            $rs[$key] = array_merge($rs[$key],unserialize($value['video']));



            $i++;
        }
        $ret['classFestivalList'] = $rs;

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [deleteWeiKe 29. 删除微课]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function deleteWeiKe($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        db('toplearning_net_material')->where(['net_material_id'=>$courseid])->update(['del'=>1]);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>{},
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [evaluateLesson 1.   评价课节]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function evaluateLesson($params)
    {   
        $token = trim($params['token']);
        $courseid = $params['courseid'];
        $lessonid = trim($params['lessonid']);
        $evaluateNum = $params['evaluateNum'];
        $msg = trim($params['msg']);

        $data['appraise_name'] = $msg?$msg:'讲得非常好';
        $data['create_time']  = time();
        $data['class_id'] = $lessonid;
        $data['score'] = $evaluateNum;
        $data['user_Id'] = $this->decrypt($token);

        db('toplearning_appraise_dictionary')->insert($data);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [upDataWeike 更新微课]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function upDataWeike($params)
    {   
        $token = trim($params['token']);
        $courseid = $params['courseid'];
        // $image = trim($json['image']);//todo



        //通过token获取 uid
        $token_uid = $this->decrypt($token);



        if(!empty($params['image'])){
            $file = "/tmp/".time().rand(0,10000).".png";
            $r = file_put_contents($file, base64_decode($params['image']));//返回的是字节数
            if(!$r){
                return $this->error('图片格式错误');

            }


            $res = $this->uploadFile($file,"png");
            if($res['code'] != 0){
                return $this->error('更新图片失败');

            }
            $data['picture'] = json_encode(['l'=>$res['path'],'m'=>$res['path'],'s'=>$res['path']]);
        }

        $data['user_id'] = $token_uid;
        if(!empty($params['title'])){
            $data['title'] = $params['title'];            
        }
        if(!empty($params['typeid'])){
            $data['course_type'] = $params['typeid'];            
        }
        if(!empty($params['keyword'])){
            $data['keyword'] = $params['keyword'];            
        }
        if(!empty($params['desc'])){
            $data['introduce'] = $params['desc'];            
        }
        if(!empty($params['share'])){
            $data['share'] = $params['share'];            
        }





        $res = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->update($data);
        if ($res === false) {
            return $this->error('更新失败');
        }


        return $this->btainWeikeo(['token'=>$params['token'],'courseid'=>$params['courseid']]);
    }
    /**
     * [classFestivalDownCoursewareList 课节下课件列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function classFestivalDownCoursewareList($params)
    {
        //params
        $lessonid = trim($params['lessonid']);
        $courseid = trim($params['courseid']);
        @$size = trim($params['size']);
        @$page = trim($params['page']);

        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;
        $info = db('toplearning_teacher_prepare')->where(['class_id'=>$lessonid])->limit($limit, $size)->select();


        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['coursewareid'] = $value['prepare_id'];
            $ret[$key]['name'] = $value['unit_name'];
            $ret[$key]['size'] = $value['size'];
            $ret[$key]['time'] = $value['create_time'];
            $ret[$key]['address'] = $value['prepare_file'];
        }

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [learningSituationList 学习情况]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function learningSituationList($params)
    {
        //params
        $lessonid = trim($params['lessonid']);
        $courseid = trim($params['courseid']);
        @$size = trim($params['size']);
        @$page = trim($params['page']);

        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;

        $info = db('toplearning_learn_situation')->where(['class_id'=>$lessonid])->limit($limit, $size)->select();


        $ret = array();
        // foreach ($ret as $key => $value) {
        //     $ret[$key]['userid'] =   $value['user_id'];
        //     $ret[$key]['name'] =  db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')?db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')[0]:'';
        //     $ret[$key]['time'] =  $value['learn_start_time'];
        //     $ret[$key]['totaltime'] =  ceil((strtotime($value['learn_end_time'])-strtotime($value['learn_start_time']))/(24*60)) ;
        //     $ret[$key]['completion'] =  $value['learn_result'];
        // }

        foreach ($info as $key => $value) {
            $ret[$key]['time'] = date('Y-m-d H:i',strtotime($value['learn_start_time']));
            $ret[$key]['lengthTime'] =  ceil((strtotime($value['learn_end_time'])-strtotime($value['learn_start_time']))/(24*60)) ;
            $ret[$key]['totalCompletionRate'] = $value['learn_result'].'%';
        }

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [classTimeRecord 2.  上课时间记录]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function classTimeRecord($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);
        $lessonid = trim($params['lessonid']);
        $endTime = trim($params['endTime']);

        //计算出开结时间
        $now = time();
        $data['learn_start_time'] = date('Y-m-d H:i',strtotime(ceil($now-$endTime)));
        $data['learn_end_time'] = date('Y-m-d H:i',strtotime(ceil($now+$endTime)));
        //取出该课节时长 计算完成度
        $lessiontime = 0;
        @$lessiontime = db('toplearning_class_festival')->where(['class_id'=>$lessonid])->column('lesson_time')[0];

        $data['learn_result'] = ceil(($endTime/$lessiontime*10)*100);

        $token_uid = $this->decrypt($token);
        $data['user_id'] = $token_uid;

        //是否有该课节学习记录 有更新
        if (db('toplearning_learn_situation')->where(['class_id'=>$lessonid])->find()) {

            db('toplearning_learn_situation')->where(['class_id'=>$lessonid])->update($data);
        }else{
            $data['class_id'] = $lessonid;
            db('toplearning_learn_situation')->insert($data);
        }
        
        // $info = db('toplearning_learn_situation')->where(['class_id'=>$courseid])->select();

        // $ret = array();
        // foreach ($ret as $key => $value) {
        //     $ret[$key]['userid'] =   $value['user_id'];
        //     $ret[$key]['name'] =  db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')?db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('nickname')[0]:'';
        //     $ret[$key]['time'] =  $value['learn_start_time'];
        //     $ret[$key]['totaltime'] =  ceil((strtotime($value['learn_end_time'])-strtotime($value['learn_start_time']))/(24*60)) ;
        //     $ret[$key]['completion'] =  $value['learn_result'];
        // }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>null,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [lecturerExaminationTeacherList 讲师端试卷列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function lecturerExaminationTeacherList($params)
    {   
        //param
        $token = trim($params['token']);
        $page = trim($params['page']);
        $size = trim($params['size']);

        $token_uid = $this->decrypt($token);

        //学院老师
        $map['del'] = 0;
        $map['exam_type'] = 1;
        $map['user_id'] = $token_uid;
        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;
        $exam = db('toplearning_exam')->where($map)->limit($limit, $size)->select();

        $ret = array();

        foreach ($exam as $key => $value) {
            $ret[$key]['examinationId'] = $value['exam_id'];
            $ret[$key]['name'] = $value['title'];
            $ret[$key]['schoolName'] = $value['create_name'];
            $ret[$key]['propositionalPerson'] = $value['user_name'];
            $ret[$key]['time'] = date("Y.m.d H:i",strtotime($value['start_time'])).' - '.date('H:i',strtotime($value['end_time']));
            $duration = ceil((strtotime($value['end_time'])-strtotime($value['start_time']))/60) ;
            $ret[$key]['time'] .= " ".floor($duration/60)."分钟";
            $ret[$key]['duration'] = $duration;
            $ret[$key]['num'] = db('toplearning_exam_submit')->where(['exam_id'=>$value['exam_id']])->count();
            $ret[$key]['numAll'] = $value['exam_num'];
            $ret[$key]['offTheStocks'] = false;
            if (strtotime($value['end_time'])<time()) {
                $ret[$key]['offTheStocks'] = true;
            }
            $ret[$key]['yesToday'] = false;
            if (date('Y-m-d',strtotime($value['start_time'])) == date('Y-m-d',time()) ) {
                $ret[$key]['yesToday'] = true;
            }
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [yesPaperList 讲师端考卷下考试的人]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function yesPaperList($params)
    {
        //param
        $token = trim($params['token']);
        $page = trim($params['page']);
        $size = trim($params['size']);
        $examinationId = trim($params['examinationId']);


        //学院老师
        
        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        // db('toplearning_chat_group')->alias('a')->join('toplearning_chat_record r','a.group_id = r.group_id')->where(['r.user_id'=>$token_uid])->group('r.group_id')->order('r.id DESC')->select();
        $map['a.owner_type'] = 1;
        $limit = $page*$size;
        $exam = db('toplearning_exam_detail')
        ->where(['exam_id'=>$examinationId,'del'=>0])
        ->order("is_submit desc")
        ->limit($page*$size,$size)
        ->select();

        $ret = array();

        foreach ($exam as $key => $value) {
            $user = db("toplearning_login")->field("realname,avatar")->where(['user_id'=>$value['user_id']])->find();
            $ret[$key]['name'] = $user['realname'];
            $ret[$key]['head'] = json_decode($user['avatar'],true)['m'];
            $ret[$key]['rollUp'] = $value['is_submit'] == 1?true:false;
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [lecturerExaminationStudentList 学生端考试列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function lecturerExaminationStudentList($params)
    {
        //param
        $token = trim($params['token']);
        $typeId = isset($params['typeId']);
        $ret = array();
        if ($typeId) {
            $info = db('toplearning_net_material')->where(['course_type'=>$typeId])->select();
            
            foreach ($info as $key => $value) {
                $ret[$key]['courseid'] =$value['net_material_id'];
                $ret[$key]['title'] =$value['title'];
                $ret[$key]['image'] =$value['picture'];
                $ret[$key]['price'] =$value['price'];
                $ret[$key]['buycount'] =$value['order_num'];
                @$ret[$key]['teacher'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0]; ;
            }
            
            //返回信息
            $data = [
                'Code'=>'0',
                'Msg'=>'操作成功',
                'Data'=>$ret,
                'Success'=>true
            ];

            return json($data); 
        }else{
            @$page = trim($params['page']);
            @$size = trim($params['size']);
            $map['del'] = 0;
            $map['exam_type'] = 1;
            $page = $page ==''?0:$page;
            $size = $size == ''?10:$size;
            $token_uid = $this->decrypt($token);
            $limit = $page*$size;
            $exam = db('toplearning_exam')->where($map)->limit($limit, $size)->select();
            $ret = array();

            foreach ($exam as $key => $value) {
                $ret[$key]['examinationId'] = $value['exam_id'];
                $ret[$key]['name'] = $value['exam_name'];
                $ret[$key]['schoolName'] = $value['create_name'];
                $ret[$key]['propositionalPerson'] = $value['user_name'];
                $ret[$key]['time'] = $value['start_time'].'-'.date('H:i',strtotime($value['end_time']));
                $ret[$key]['duration'] = ceil((strtotime($value['end_time'])-strtotime($value['start_time']))/60) ;

                $ret[$key]['yesToday'] = false;
                if (date('Y-m-d',strtotime($value['start_time'])) == date('Y-m-d',time()) ) {
                    $ret[$key]['yesToday'] = true;
                }
                $ret[$key]['alreadyZtAll'] = $value['total'];

                $ret[$key]['isInTheExamTime'] = false;

                if (strtotime($value['start_time'])<time()&&strtotime($value['end_time'])>time()) {
                 $ret[$key]['isInTheExamTime'] = true;
             }


             $usersumb = db('toplearning_exam_submit')->where(['exam_id'=>$value['exam_id'],'student_user_id'=>$token_uid])->find();
             @$ret[$key]['alreadyZt'] = $usersumb['had_done'];

             @$ret[$key]['type'] = $usersumb['is_marking'];
             if ($ret[$key]['type']==3) {
                @$ret[$key]['score'] = number_format(($usersumb['score']/$usersumb['total'])*$usersumb['had_done'],1);
                @$ret[$key]['pyTime'] = date('Y-m-d H:i',strtotime($usersumb['mark_time'])) ;
            }
            if ($ret[$key]['type']==2) {
                @$ret[$key]['jjTime'] = date('Y-m-d H:i',strtotime($usersumb['submit_time']));
            }




        }

    }


        //返回信息
    $data = [
        'Code'=>'0',
        'Msg'=>'操作成功',
        'Data'=>$ret,
        'Success'=>true
    ];

    return json($data);

}
    /**
     * [lecturerCourse 搜索课程]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function seacherCourse($params)
    {   
        $token = trim($params['token']);
        $page = !empty($params['page'])?$params['page']:0;
        $size = !empty($params['size'])?$params['size']:10;
        $map = [];
        if (isset($params['typeId'])) {
            $map['course_type'] = $params['typeId'];
        }
        if (isset($params['msg'])) {
            $map['title'] = array('like','%'.$params['msg'].'%');
        }

        // $map['lession_status'] = 1;//2，录制课程不出现在搜索课程范围内 后台沟通后去掉

        $map['type'] = 1;
        $map['reviewed_status'] = 3;
        $info = db('toplearning_net_material')->where($map)
        ->limit($page*$size,$size)
        ->select();
        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['courseid'] =$value['net_material_id'];
            $ret[$key]['title'] =$value['title'];
            $ret[$key]['image'] =$value['picture'];
            $ret[$key]['price'] =$value['price'];
            $ret[$key]['buycount'] =$value['order_num'];
            @$ret[$key]['teacher'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];

            $ret[$key]['collegeName'] = $value['school_name'] ;
            @$ret[$key]['teacherName'] = db('toplearning_login')->where(['user_id'=>$value['teacher_user_id']])->column('nickname')[0];
            $ret[$key]['collegeId'] = $value['school_id'];
            $ret[$key]['teacherId'] = $value['teacher_user_id'];
        }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [buyNowCourse 7. 立即购买]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function buyNowCourse($params)
    {
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        
        // $schoolId = trim($params['schoolId']);

        $token_uid = $this->decrypt($token);

        //取数据
        $user = db('toplearning_login')->where(['user_id'=>$token_uid])->find();//用户

        $nt = db('toplearning_net_material')->where(['net_material_id'=>$courseid])->find();//课程

        // $sc = db('toplearning_school')->where(['school_id'=>$schoolId])->find();//学校

        if ($nt) {
            $t = db('toplearning_login')->where(['user_id'=>$nt['teacher_user_id']])->find();//教师
        }
        //查看U豆
        //通过老师查找课程学院ID
        @$school_id = $nt['school_id'];

        @$ud = db('toplearning_ud_school')->where(['user_id'=>$token_uid,'school_id'=>$school_id])->find();
        // var_dump(db()->getlastsql());
        //setDec
        // var_dump($ud,$nt['price']);
        if ( $nt&&( intval($ud['num']) > intval($nt['price']) ) ) { 

            db('toplearning_ud_school')->where(['user_id'=>$token_uid,'school_id'=>$school_id])->setDec( 'num',intval($nt['price']) );

        }else{
            return $this->error('您的U豆不够');
        }

        if (!$t) {
            return $this->error('服务器忙，请稍后');
        }

        //组数据
        $data['user_id'] = $token_uid;
        $data['nickname'] = $user['nickname']?$user['nickname']:$user['realname'];

        $data['seller_id'] = $t['user_id'];
        $data['seller_name'] = $t['nickname'];
        $data['seller_user_id'] = $t['user_id'];

        $data['net_material_id'] = $courseid;
        $data['product_name'] = $nt['title'];
        $data['price'] = $nt['price'];

        $data['order_number'] = date('YmdHis',time()).rand(1000,9999);
        $data['teacher_id'] = $nt['teacher_id'];
        $data['teacher_user_id'] = $nt['teacher_user_id'];
        $data['teacher_name'] = $t['nickname'];
        $data['create_time'] = time();

        db('toplearning_order')->insert($data);

        //学生关联表
        $sdata['material_id'] = $courseid;
        $sdata['user_id'] = $token_uid;
        $sdata['add_time'] = time();

        db('toplearning_student_material')->insert($sdata);

        //更新课程表
        
        db('toplearning_net_material')->where(['net_material_id'=>$courseid])->setInc('order_num');

        //更新用户表 学院

        // $save['school_ids'] = $user['school_ids'].",".$school_id;
        // $save['school_ids_own'] = $user['school_ids'].",".$school_id;
        // db('toplearning_login')->where(['user_id'=>$token_uid])->update($save);
        $concern = db('toplearning_concern')->where(['user_id'=>$user['user_id'],'school_id'=>$school_id])->find();
        if($concern){
            db('toplearning_concern')->where(['user_id'=>$user['user_id'],'school_id'=>$school_id])->update(['is_buy'=>1]);
        }else{
            db('toplearning_concern')->insert([
                'user_id'=>$user['user_id'],
                'school_id'=>$school_id,
                'is_buy'=>1,
                'type'=>$user['group_id']
            ]);

        }

        //加入群聊
        $chatGroup = db('toplearning_chat_group')->where(['lesson_id'=>$courseid])->find();
        // $post['method'] = 'addgroup';
        // //(group_id) (member_id) (silence)
        // $post['group_id'] = $chatGroup['txgroupid'];
        // $post['member_id'] = $user['mobile'];
        // $post['silence'] = '1';

        // $rs = $this->tenxunim($post);

        $resp = $this->addGroupMember([
            'groupid'=>$chatGroup['txgroupid'],
            'account'=>$user['im_account']
        ]);

        

        db("toplearning_chat_group_user")->insert([
            'user_id'=>$token_uid,
            'group_id'=>$chatGroup['id']
        ]);


        // if ($rs && $rs['errorcode']!=0) {
        //     $this->error('同步注册腾讯IM失败');
        // }

        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [CourseCollection 8. 收藏课程]
     * @param [type] $params [description]
     */
    public function CourseCollection($params)
    {
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $token_uid = $this->decrypt($token);

        $data['source_id'] = $courseid;
        $data['user_id'] = $token_uid;
        $data['create_time'] = time();

        db('toplearning_favorite')->insert();

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    //-----------U信------
    /**
     * [ContactList 群聊列表]
     * @param [type] $params [description]
    */
    public function GroupList($params)
    {
        //params
        $token = trim($params['token']);
        // $userid = trim($params['userid']);
        //
        $token_uid = $this->decrypt($token);

        $im_groups  =  $this->getUserGroupList($token_uid);
        // $info = db('toplearning_chat_group')->alias('a')->field('a.*,a.user_id as auid,u.*,r.*')->join('toplearning_chat_group_user u','a.id = u.group_id')->join('toplearning_chat_record r','a.id = r.group_id')->where(['r.user_id'=>$token_uid])->group('r.group_id')->order('r.id DESC')->select();
        // $info = db('toplearning_chat_group')->alias('a')->field('a.*,a.user_id as auid,u.*')->join('toplearning_chat_group_user u','a.id = u.group_id')->where(['u.user_id'=>$token_uid])->select();
        // echo db('toplearning_chat_group')->getlastsql();exit;

        $ret = array();
        foreach ($im_groups as $key => $value) {

            $ret[$key]['groupId'] = $value['GroupId'];
            $ret[$key]['groupName'] = $value['Name'];

            $group = db("toplearning_chat_group")->where(['txgroupid'=>$value['GroupId']])->find();
            $owner_name = db("toplearning_login")->where(['user_id'=>$group['user_id']])->value('nickname');

            $ret[$key]['groupOwnerName'] = $owner_name;
            $ret[$key]['isGroup'] = $token_uid == $group['user_id'];
            $ret[$key]['groupHead'] = $value['FaceUrl'];
            // @$ret[$key]['currentBulletin'] = db('toplearning_chat_group_notice')->where(['group_id'=>$group['id']])->value('content');
            @$ret[$key]['courseName'] = db('toplearning_net_material')->where(['net_material_id'=>$group['lesson_id']])->value('title');
            $ret[$key]['currentBulletin'] = $value['Notification'];
            $ret[$key]['actualite'] = $value['Introduction'];
            // $ret[$key]['actualite'] = $group['group_name'];

            // $r = db('toplearning_chat_record')->where(['group_id'=>$value['group_id']])->order('id DESC')->find();
            $ret[$key]['msg'] = "";
            $ret[$key]['data'] = date("m月d日",$value['LastMsgTime']);
            $ret[$key]['unreadMsgNumber'] = $value['SelfInfo']['UnreadMsgNum'];
            
            
            $ret[$key]['courseid'] = $group['lesson_id'];
            $ret[$key]['courseName'] = db("toplearning_net_material")->where(['net_material_id'=>$group['lesson_id']])->value("title");
            


        }

        $identifier = db("toplearning_login")->where(['user_id'=>$token_uid])->value('im_account');
        $userSig =  $this->getUserSign($identifier);
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>['groupList'=>$ret,'identifier'=>$identifier,'userSig'=>$userSig],
            'Success'=>true
        ];

        return json($data);

    }
    /**
     * [groupMemberList 2.  群聊成员列表（新增]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function groupMemberList($params)
    {


        //params
        $token = trim($params['token']);
        $groupId = trim($params['groupId']);
        // echo urlencode('@TGS#12H6UPIFC');exit;
        $info = db('toplearning_chat_group_user')->alias('a')->join('toplearning_login r','a.user_id = r.user_id')->join('toplearning_chat_group g','a.group_id = g.id')->where(['g.txgroupid'=>$groupId])->select();
        $ret = [];
        foreach ($info as $key => $value) {
            $ret[$key]['name'] = $value['nickname'];
            $ret[$key]['head'] = $value['avatar'];
        }
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [groupMemberList 3.  上传公告（新增]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function addCurrentBulletin($params)
    {
        //params
        $token = trim($params['token']);
        $groupId = trim($params['groupId']);
        $msg = trim($params['msg']);

        $token_uid = $this->decrypt($token);

        $data['group_id'] = $group_id;
        $data['content'] = $msg;
        $data['user_id'] = $token_uid;
        db('toplearning_chat_group_notice')->insert($data);
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [groupMemberList   4. 清除聊天记录]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function cleanUpChatRecords($params)
    {
        //params
        $token = trim($params['token']);
        $groupId = trim($params['groupId']);
        $token_uid = $this->decrypt($token);

        db('toplearning_chat_record')->where(['group_id'=>$groupId,'user_id'=>$token_uid])->delete();
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [groupCreate description]
     * @param  [type] $params [{
    "Owner_Account": "leckie", // 群主的UserId（选填）
    "Type": "Public", // 群组类型：Private/Public/ChatRoom/AVChatRoom/BChatRoom（必填）
    "Name": "TestGroup" // 群名称（必填）
    }]
     * @return [type]         [description]
     */
    public function groupCreate($params)
    {
        // //params
        // $post['method'] = 'group';
        // $post['group_type'] = isset($params['group_type'])?$params['group_type']:'Private';//Private | Public | ChatRoom | AVChatRoom| BChatRoom
        // $post['group_name'] = isset($params['group_name'])?$params['group_name']:'U学院群聊'.time();
        // $post['owner_id'] = isset($params['owner_id'])?$params['owner_id']:'zg';


        // // $post['GroupId'] = 'TGS'.time();
        // $rs = $this->tenxunim($post);
        // if ($rs['groupid']) {
        //     $pattern = '#"(.*?)"#i'; 
        //     preg_match_all($pattern, $rs['groupid'], $matches); 
        //     $rs['groupid'] = $matches[1][0];
        // }

        // if ($rs && $rs['errorcode']!=0) {
        //     return $this->error('创建群组失败');
        // }

 
         if(empty($params['group_name'])){
            return $this->error("参数不对");
        }
        if(empty($params['group_type'])){
            return $this->error("参数不对");
        }
        if(empty($params['owner_id'])){
            return $this->error("参数不对");
        }

        if(empty($params['lesson_id'])){
           return  $this->error("参数不对");
        }
        if(empty($params['user_id'])){
           return  $this->error("参数不对");
        }



        $resp = $this->createGroup([
            'name'=>$params['group_name'],
            'type'=>$params['group_type'],
            'user_id'=>$params['user_id']
        ]);
        if($resp['ActionStatus'] != "OK"){
            return $this->error($resp['ErrorInfo']);

 
        }


        $params['lesson_id'] = isset($params['lesson_id'])?$params['lesson_id']:'';
        $params['user_id'] = isset($params['user_id'])?$params['user_id']:'';
        $params['pic'] = isset($params['pic'])?$params['pic']:'';

        $data['lesson_id'] = $params['lesson_id'];
        $data['user_id'] = $params['user_id'];
        $data['pic'] = $params['pic'];
         $data['owner_id'] = $params['owner_id'];
        $data['group_name'] = $params['group_name'];
        $data['group_type'] = $params['group_type'];
        $data['txgroupid'] = $resp['GroupId'];

        db('toplearning_chat_group')->insert($data);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            // 'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);

    }

    public function ModifyingPersonalInformation($params)
    {
        //参数
        $token = trim($params['token']);
        $json = json_decode($params['json'],true);
        $token_uid = $this->decrypt($token);
        

        // if (!db('toplearning_login')->where(['user_id'=>$token_uid])->find()) {
        //     return $this->error('账号不存在！');
        // }
        if ($json) {
            @$data['nickname'] = trim($json['nickname']);
            @$data['birthday'] = trim($json['birthday']);
            @$data['sex'] = trim($json['sex']);
            @$data['city'] = trim($json['city']);
            @$data['phone'] = trim($json['phone']);
            @$data['wechat'] = trim($json['wechat']);
            @$data['qq'] = trim($json['qq']);
            @$data['email'] = trim($json['email']);
            @$data['IndividualResume'] = trim($json['IndividualResume']);

            if(!empty($json['head'])){
                $file = "/tmp/".time().rand(0,10000).".png";
                $r = file_put_contents($file, base64_decode($json['head']));//返回的是字节数
                if(!$r){
                    return $this->error('图片格式错误');
                }
                $res = $this->uploadFile($file,"png");
                if($res['code'] != 0){
                    return $this->error('更新图片失败');
                }
                $data['avatar'] = json_encode(['l'=>$res['path'],'m'=>$res['path'],'s'=>$res['path']]);
            }

        }
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }
        if (db('toplearning_login')->where(['user_id'=>$token_uid])->update($data)) {
            // return $this->error('更新用户失败！');
        }

        $ret = array();

        //是否存在
        $map['user_id'] = $token_uid;
        $user = db('toplearning_login')->where($map)->find();

        


        //用户类型
       $ret['customerType'] = $user['group_id']==3?'2':'1';
       

        //组数据  
       $ret['userid'] = $user['user_id'];
       $ret['phone'] = $user['mobile'];

       $ret['nickname'] = $user['nickname'];
       $ret['birthday'] = $user['birthday'];
       $ret['sex'] = $user['sex'];

       $ret['headurl'] = $user['avatar'];
       $ret['tokenlife'] = 1;

       $ret['wechat'] = $user['weixin'];
       $ret['qq'] = $user['qq'];
       $ret['city'] = $user['city'];
        //todo
       $ret['signture'] = $user['introduce'];
       $ret['coins'] = 10;

       $ret['email'] = $user['email'];
       $ret['collegeid'] = $user['school_id'];

       $ret['identifier'] = $user['im_account'];

       $ret['userSig'] = $user['userSig'];
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [PaymentDetails 支付列表]
     * @param [type] $params [description]
     */
    public function PaymentDetails($params)
    {   
        //param
        $token = trim($params['token']);
        $userid = trim($params['userid']);
        $token_uid = $this->decrypt($token);

        $info  = db('toplearning_order')->where(['user_id'=>$token_uid])->select();
        $ret = array();
        $m = array();
        foreach ($info as $key => $value) {
            $cdate = date('Y-m',strtotime($value['create_time']));
            array_push($m, date('Y-m',strtotime($value['create_time'])));
            //商品信息
            $goods = db('toplearning_net_material')->where(['net_material_id'=>$value['net_material_id']])->find();
            @$pay['headUrl'] = $goods['picture'];
            @$pay['Name'] = $goods['title'];
            @$pay['absteact'] = $goods['introduce'];
            @$pay['nun'] = '-￥'.$goods['price'];

            $pay['modeOfPayment'] = 'U豆支付';
            $pay['data'] = date('m d H:i',strtotime($value['create_time']));
            $ret['paymentList'][$cdate][] = $pay;
        }
        $nowdate = date('Y-m',time());
        $rs = array();
        if ($ret && $ret['paymentList']) {
            foreach ($ret['paymentList'] as $key => $value) {
                $rs[$key]['data'] = $key;
                $rs[$key]['paymentList'] = $value;
            }
        }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>array_values($rs),
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [surplusDetails Ud祥情]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function surplusDetails($params)
    {
        //param
        $token = trim($params['token']);
        $userid = trim($params['userid']);
        $token_uid = $this->decrypt($token);

        $info = db('toplearning_ud_school')->field('school_id,school_name,sum(num) as total')->where(['user_id'=>$token_uid])->group('school_id')->select();

        $ret = array();
        foreach ($info as $key => $value) {
            if ($value['total']==0) {
                continue;
            }
            $ret[$value['school_id']]['name'] = $value['school_name'];
            $ret[$value['school_id']]['surplus'] = $value['total'];
        }
        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>array_values($ret),
            'Success'=>true
        ];

        return json($data);
    }

    /**
     * [upDatagroupInfo 修改群聊信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function upDatagroupInfo($params)
    {
        //param
        $token = trim($params['token']);
        $groupId = trim($params['groupId']);
        $token_uid = $this->decrypt($token);

        $json = json_decode($params['json'],true);
        //群信息 groupName groupActualite groupCurrentBulletin
        if ($json) {
            $resp = $this->uPgroupInfo([
                'groupId'=>$groupId,
                'groupName'=>isset($json['groupName'])?$json['groupName']:'',
                'groupActualite'=>isset($json['groupActualite'])?$json['groupActualite']:'',
                'groupCurrentBulletin'=>isset($json['groupCurrentBulletin'])?$json['groupCurrentBulletin']:''
            ]);
            if($resp['ActionStatus'] != "OK"){
                return $this->error($resp['ErrorInfo']);     
            }
        }
        //更新群名
        if (isset($json['groupName'])) {
            db('toplearning_chat_group')->where(['txgroupid'=>$groupId])->update(['group_name'=>$json['groupName']]);
        }

        //更新群公告表
        $group = db('toplearning_chat_group')->where(['txgroupid'=>$groupId])->find();
        if (db('toplearning_chat_group_notice')->where(['group_id'=>$group['group_id']])->find()&&isset($json['groupCurrentBulletin'])) {
            db('toplearning_chat_group_notice')->where(['group_id'=>$group['group_id']])->update(['content'=>$json['groupCurrentBulletin']]);
        }else{
            if (isset($json['groupCurrentBulletin'])) {
                $data['group_id'] = $group['group_id'];
                $data['content'] = $json['groupCurrentBulletin'];
                $data['user_id'] = $token_uid;
                db('toplearning_chat_group_notice')->insert($data);
            }
        }

        
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Success'=>true
        ];

        return json($data);
    }
    /**
     * [groupMemberList  5.  登录]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    // public function ulogin($params)
    // {
    //     //返回信息
    //     $data = [
    //         'Code'=>'0',
    //         'Msg'=>'操作成功',
    //         'Data'=>$ret,
    //         'Success'=>true
    //     ];

    //     return json($data);
    // }
    //---------- common function-----------
    /**
     * 发送
     */
    public function sendmsg($mobile,$type)
    {
        $apikey = '071233bb9140590d2dc4e2b8d4a77d90'; 
        $getback = '1349539';
        $regist = '1349531';
        
        $code  = rand(1000,9999);
        $ch = curl_init();
        
        /* 设置验证方式 */
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        $data=array('tpl_id'=>$type==1?$getback:$regist,'tpl_value'=>('#code#').'='.urlencode($code),'apikey'=>$apikey,'mobile'=>$mobile);
        // $data = ['mobile'=>$mobile,'code'=>$code,'type'=>($type==1?$getback:$regist)];
        $json_data = $this->tpl_send($ch,$data);
        $array = json_decode($json_data,true);
     // echo '<pre>';print_r($array);
        
        curl_close($ch);
        if ($array['code']==0) {
            return $code;
        }else{
            return false;
        }
        //打印获得的数据
        return $json_data;
        
    }

    /**
     * [sendmsg description]
     * @param  [type] $mobile [description]
     * @return [type]         [description]
     */
    // public function sendmsg($mobile)
    // {
    //     $apikey = "8df6ed7129c50581eecdf1e875edbaa3"; 

    //     $code  = rand(1000,9999);
    //     $text="【希望24热线】您的验证码是".$code; 

    //     $ch = curl_init();

    //      /* 设置验证方式 */
    //      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
    //          'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
    //      /* 设置返回结果为流 */
    //      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //       设置超时时间
    //      curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    //      /* 设置通信方式 */
    //      curl_setopt($ch, CURLOPT_POST, 1);
    //      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //      // 发送短信
    //      $data = array('text'=>$text,'apikey'=>$apikey,'mobile'=>$mobile);
    //      $json_data = $this->send($ch,$data);
    //      $array = json_decode($json_data,true);  
    //      if ($array['code']==0) {
    //         return $code;
    //      }else{
    //         return false;
    //      }
    // }

    /**
     * @return 模板发送  json
     */
    function tpl_send($ch,$data){
        // var_dump($data);

        // curl_setopt ($ch, CURLOPT_URL, 'http://120.24.215.50/YunPianMobileMessage.php');
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }

    /**
     * [send description]
     * @param  [type] $ch   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function send($ch,$data){
     curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
     $result = curl_exec($ch);
     $error = curl_error($ch);
         // checkErr($result,$error);
     return $result;
 }

 function encrypt($data) { 
    $key = $this->passkey();
    $prep_code = serialize($data); 
    $block = mcrypt_get_block_size('des', 'ecb'); 
    if (($pad = $block - (strlen($prep_code) % $block)) < $block) { 
        $prep_code .= str_repeat(chr($pad), $pad); 
    } 
    $encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB); 

    return base64_encode($encrypt); 
} 

function decrypt($str) { 
    try{


        $key = $this->passkey();
        $str = base64_decode($str); 
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB); 
        $block = mcrypt_get_block_size('des', 'ecb'); 
        $pad = ord($str[($len = strlen($str)) - 1]); 

        if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) { 
            $str = substr($str, 0, strlen($str) - $pad); 
        } 
        $re = unserialize($str);
    }catch(Exception $e){
        return "";
    }


    return $re; 
}

function passkey(){
    return 'ucschool';
} 

    /**
     * [_seve_img 上传头像]
     * @param  [type] $avar [description]
     * @return [type]       [description]
     */
    public function _seve_img($avar)
    {
        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
        
        $path = 'http://'.$_SERVER['HTTP_HOST']."/public/uploads/images/".date("Ymd",time());
        $path = "uploads/images/".date("Ymd",time());
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }
        $imageSrc=  $path."/". $imageName;  //图片名字

        $r = file_put_contents(ROOT_PATH ."public/".$imageSrc, base64_decode($avar));//返回的是字节数
        if (!$r) {
            return false;
        }else{
            return  $imageSrc;
        }
    }

    /**
     * [markImg 合成图片]
     * @param  [type] $picdata [description]
     * @return [type]          [description]
     */
    public function markImg($picdata)
    {
        $pic_list       = $picdata;  

        $pic_list    = array_slice($pic_list, 0, 9); // 只操作前9个图片  

        $bg_w    = 150; // 背景图片宽度  
        $bg_h    = 150; // 背景图片高度  

        $background = imagecreatetruecolor($bg_w,$bg_h); // 背景图片  
        $color   = imagecolorallocate($background, 202, 201, 201); // 为真彩色画布创建白色背景，再设置为透明  
        imagefill($background, 0, 0, $color);  
        imageColorTransparent($background, $color);   

        $pic_count  = count($pic_list);  
        $lineArr    = array();  // 需要换行的位置  
        $space_x    = 3;  
        $space_y    = 3;  
        $line_x  = 0;  
        switch($pic_count) {  
        case 1: // 正中间  
            $start_x    = intval($bg_w/4);  // 开始位置X  
            $start_y    = intval($bg_h/4);  // 开始位置Y  
            $pic_w   = intval($bg_w/2); // 宽度  
            $pic_h   = intval($bg_h/2); // 高度  
            break;  
        case 2: // 中间位置并排  
        $start_x    = 2;  
        $start_y    = intval($bg_h/4) + 3;  
        $pic_w   = intval($bg_w/2) - 5;  
        $pic_h   = intval($bg_h/2) - 5;  
        $space_x    = 5;  
        break;  
        case 3:  
            $start_x    = 40;   // 开始位置X  
            $start_y    = 5;    // 开始位置Y  
            $pic_w   = intval($bg_w/2) - 5; // 宽度  
            $pic_h   = intval($bg_h/2) - 5; // 高度  
            $lineArr    = array(2);  
            $line_x  = 4;  
            break;  
            case 4:  
            $start_x    = 4;    // 开始位置X  
            $start_y    = 5;    // 开始位置Y  
            $pic_w   = intval($bg_w/2) - 5; // 宽度  
            $pic_h   = intval($bg_h/2) - 5; // 高度  
            $lineArr    = array(3);  
            $line_x  = 4;  
            break;  
            case 5:  
            $start_x    = 30;   // 开始位置X  
            $start_y    = 30;   // 开始位置Y  
            $pic_w   = intval($bg_w/3) - 5; // 宽度  
            $pic_h   = intval($bg_h/3) - 5; // 高度  
            $lineArr    = array(3);  
            $line_x  = 5;  
            break;  
            case 6:  
            $start_x    = 5;    // 开始位置X  
            $start_y    = 30;   // 开始位置Y  
            $pic_w   = intval($bg_w/3) - 5; // 宽度  
            $pic_h   = intval($bg_h/3) - 5; // 高度  
            $lineArr    = array(4);  
            $line_x  = 5;  
            break;  
            case 7:  
            $start_x    = 53;   // 开始位置X  
            $start_y    = 5;    // 开始位置Y  
            $pic_w   = intval($bg_w/3) - 5; // 宽度  
            $pic_h   = intval($bg_h/3) - 5; // 高度  
            $lineArr    = array(2,5);  
            $line_x  = 5;  
            break;  
            case 8:  
            $start_x    = 30;   // 开始位置X  
            $start_y    = 5;    // 开始位置Y  
            $pic_w   = intval($bg_w/3) - 5; // 宽度  
            $pic_h   = intval($bg_h/3) - 5; // 高度  
            $lineArr    = array(3,6);  
            $line_x  = 5;  
            break;  
            case 9:  
            $start_x    = 5;    // 开始位置X  
            $start_y    = 5;    // 开始位置Y  
            $pic_w   = intval($bg_w/3) - 5; // 宽度  
            $pic_h   = intval($bg_h/3) - 5; // 高度  
            $lineArr    = array(4,7);  
            $line_x  = 5;  
            break;  
        }  
        foreach( $pic_list as $k=>$pic_path ) {  
            $kk = $k + 1;  
            if ( in_array($kk, $lineArr) ) {  
                $start_x    = $line_x;  
                $start_y    = $start_y + $pic_h + $space_y;  
            }  
            $pathInfo    = pathinfo($pic_path);  
            switch( strtolower($pathInfo['extension']) ) {  
                case 'jpg':  
                case 'jpeg':  
                $imagecreatefromjpeg    = 'imagecreatefromjpeg';  
                break;  
                case 'png':  
                $imagecreatefromjpeg    = 'imagecreatefrompng';  
                break;  
                case 'gif':  
                default:  
                $imagecreatefromjpeg    = 'imagecreatefromstring';  
                $pic_path    = file_get_contents($pic_path);  
                break;  
            }  
            $resource   = $imagecreatefromjpeg($pic_path);  
            // $start_x,$start_y copy图片在背景中的位置  
            // 0,0 被copy图片的位置  
            // $pic_w,$pic_h copy后的高度和宽度  
            imagecopyresized($background,$resource,$start_x,$start_y,0,0,$pic_w,$pic_h,imagesx($resource),imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度  
            $start_x    = $start_x + $pic_w + $space_x;  
        }  

        header("Content-type: image/jpg");  
        return imagejpeg($background);
    }

    // function mb_unserialize($str) {
    //     if(empty($str)){  
    //         return '';  
    //     }  
    //     $str= preg_replace_callback('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $str );  
    //     $str= str_replace("\r", "", $str);        
    //     return unserialize($str);  
    // }
    function is_serialized( $data ) {
       $data = trim( $data );
       if ( 'N;' == $data )
       return true;
       if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
           return false;
           switch ( $badions[1] ) {
               case 'a' :
               case 'O' :
               case 's' :
               if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                   return true;
                   break;
                   case 'b' :
                   case 'i' :
                   case 'd' :
                   if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                       return true;
                       break;
                   }
                   return false;
               }






    //腾讯IM 注册账号|群聊
               function tenxunim($params)
               {   
        //param
                $method = $params['method'];

        //(group_type) (group_name) (owner_id)
        //(identifier) (nick) (face_url)
        // (group_id) (member_id) (silence)
                $commandList = array(
                    'regist'=>'php /home/wwwroot/ucschool/PhpServerSdk/TimRestApiGear.php im_open_login_svc account_import ',
                    'group'=>'php /home/wwwroot/ucschool/PhpServerSdk/TimRestApiGear.php group_open_http_svc create_group ',
                    'addgroup'=>'php /home/wwwroot/ucschool/PhpServerSdk/TimRestApiGear.php group_open_http_svc add_group_member '
                );

        //生成执行命令
                if (!$commandList[$method]) {
                    return $this->error('不存在腾讯IM'.$method);
                }
                unset($params['method']);

        //实例：$command = "php /data/httpd/ucschool/PhpServerSdk/TimRestApiGear.php im_open_login_svc account_import hello2 hello2 null";
                $command = $commandList[$method].implode(' ', $params);

                $retval = array();
                exec($command, $retval, $status);
                $usersig = 0;
        $errorCode = 9;//错误code   
        $groupId  = 0;     
        if ($status == 0) {

            foreach ($retval as $key => $value) {
                // if ($value == 'Request Url:') {
                //     $rs = $retval[$key+1];
                //     $usersig =explode('=', explode('&', explode('?', $rs)[1])[0])[1];
                // }

                if (strstr($value, 'registSig')) {

                    $usersig = explode(':', $value)[1];
                }
                if (strstr($value, 'ErrorCode')) {

                    $errorCode = intval(explode(':', $value)[1]);
                }
                //返回群组ID
                if ($method == 'group'&&strstr($value, 'GroupId')) {
                    $groupId = explode(':', $value)[1];
                }
            }
        }

        //更新usersig 取消
        // if ($errorCode==0) {
        //     $lastid = db('toplearning_login')->where(1)->order('user_id DESC')->value('user_id');
        //     db('toplearning_login')->where(['user_id'=>$lastid])->update(['userSig'=>$usersig]);
        // }
        
        
        $rs = ['usersig'=>$usersig,'errorcode'=>$errorCode,'msg'=>json_encode($retval),'groupid'=>$groupId];

        return $rs;
    }



    function getUserSign($user = "admin"){
        $cache = cache("userSig".$user);
        if(empty($cache)){
            import('tls.TLSSig', EXTEND_PATH);
            $tls =  \TLSSigAPI::getInstance();
            $cache = $tls->genSig($user);
            cache("userSig".$user,$cache,604800);
        }
        return $cache;

    }  





    function createGroup($data){

        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/create_group?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";
        $account = db("toplearning_login")->where(['user_id'=>$data['user_id']])->value("im_account");
        $params = [
             "Owner_Account"=> $account, // 群主的UserId（选填）
            "Type"=> !empty($data['type'])?$data['type']:"Public", // 群组类型：Private/Public/ChatRoom/AVChatRoom/BChatRoom（必填）
            "Name"=> $data['name'] // 群名称（必填）
        ];
        // var_dump($params);
        $params = json_encode($params);
        $resp = curlRequest($url,$params);
        $resp = json_decode($resp,true);






        return $resp;


    }

    function addGroupMember($data){
          $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/add_group_member?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";
        $params = [
             "GroupId"=>$data['groupid'],
   "MemberList"=>[['Member_Account'=>$data['account']]]
        ];
 


        $params = json_encode($params);
        $resp = curlRequest($url,$params);
        $resp = json_decode($resp,true);






        return $resp;
    }

    function createUser($data){
              $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/im_open_login_svc/account_import?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";
        $params = [
             "Identifier"=>$data['account'],
   "Nick"=>$data['name'],
   "FaceUrl"=>$data['avatar']
        ];

        $params = json_encode($params);
        $resp = curlRequest($url,$params);
        $resp = json_decode($resp,true);






        return $resp;
    }

    function uPgroupInfo($data){
        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/modify_group_base_info?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";

        //groupName groupActualite groupCurrentBulletin groupId
        
        $params = [
            "GroupId"=> $data['groupId'], // 要修改哪个群的基础资料（必填）
            "Name"=> $data['groupName'], // 群名称（填）
            "Introduction"=> $data['groupActualite'], // 群简介（选填）
            "Notification"=> $data['groupCurrentBulletin'], // 群公告（选填）
        ];
        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
            }
        }
        $params = json_encode($params);
        $resp = curlRequest($url,$params);
        $resp = json_decode($resp,true);
        return $resp;


    }


    function getUserGroupList($userid){
        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/get_joined_group_list?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";
        $account = db("toplearning_login")->where(['user_id'=>$userid])->value("im_account");
        $params = [
            'Member_Account'=>$account,
            'ResponseFilter'=>[
                'GroupBaseInfoFilter'=>[
                   "Type",
                   "Name",
                   "Introduction",
                   "Notification",
                   "FaceUrl",
                   "CreateTime",
                   "Owner_Account",
                   "LastInfoTime",
                   "LastMsgTime",
                   "NextMsgSeq",
                   "MemberNum",
                   "MaxMemberNum",
                   "ApplyJoinOption",
                   "ShutUpAllMember"

               ],
               'SelfInfoFilter'=>[
                'UnreadMsgNum'
            ]
        ]
    ];

    $params = json_encode($params);
    $resp = curlRequest($url,$params);
    $resp = json_decode($resp,true);
    if($resp['ActionStatus'] != "OK"){
        return [];
    }else{
        return $resp['GroupIdList'];
    }
}
}
