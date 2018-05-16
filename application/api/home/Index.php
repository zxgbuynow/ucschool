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
                return $this->$func($params);
            }else{
                return $this->error($params['method'].'方法不存在');
            }
            
        }else{
            return $this->error('method参数缺失');
        }
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
        if (!Hash::check((string)$password, $user['password'])) {
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
        $ret['collegeid'] = db('toplearning_school')->where(1)->order('recommended DESC')->column('school_id')[0];

                
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
        $data['password'] =  Hash::make((string)trim($params['password']));

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
        $data['group_id'] = trim($params['type'])=='1'?'4':'3';
        $data['nickname'] = trim($params['name']);
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

        //生成密码
        $data['password'] =  Hash::make((string)trim($params['password']));

        //插入数据
        $me = db('toplearning_login')->insert($data);
        if (!$me) {
            return $this->error('注册失败！请稍后重试');
        }

        //插入关联表
        
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
            return $this->error('token失效，请重新登录');
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
            return $this->error('token失效，请重新登录');
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
            return $this->error('token失效，请重新登录');
        }

       
        $map['a.type'] = 2;
        $map['a.del'] = 0;
        $map['a.user_id'] = $token_uid;
        //关注信息
        //
        $school_ids = db('toplearning_login')->where(['user_id'=>$token_uid])->find();

        $attention = array();
        if ($school_ids&&$school_ids['school_ids']) {
            $map1['school_id'] = array('in',$school_ids['school_ids']);
            $attention = db('toplearning_school')->where($map1)->select();
        }
        
        // $attention = db('toplearning_login')->alias('a')->join('toplearning_school s','a.source_id = s.school_id')->where($map)->select();


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
            return $this->error('token失效，请重新登录');
        }


        //是否有该学院
        //通过token获取 uid
        $token_uid = $this->decrypt($token);
        $map['school_id'] = $collegeid;

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
            return $this->error('token失效，请重新登录');
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
        $gz = db('toplearning_login')->where(['user_id'=>$token_uid])->column('school_ids');

        if ($gz) {
            $gzs = explode(',', $gz[0]);
            if (in_array($collegeid, $gzs)) {
                $rs['isfocus'] = 1;
            }
        }

        // if (db('toplearning_attention')->where($amap)->find()) {
        //     $rs['isfocus'] = 1;
        // }

        //成果
        $sc = db('toplearning_school_extend')->where(['school_id'=>$collegeid])->select();
        $scarr = array();
        foreach ($sc as $key => $value) {
           $scarr[$key]['achtitle']=$value['title'];
           $scarr[$key]['achdesc']=$value['content'];
           $scarr[$key]['achimages']= $value['achimages'];
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
            return $this->error('token失效，请重新登录');
        }


        
        //通过token获取 uid
        $token_uid = $this->decrypt($token);


        //关注或取关 （有状态）
        
        $users = db('toplearning_login')->where(['user_id'=>$token_uid])->find();

        if ($users['school_ids']) {
            $school_ids = explode(',', $users['school_ids']);
            if (in_array($collegeid, $school_ids)) {
                if ($type == 2) {//关注
                    foreach ($school_ids as $key => $value) {
                        if ($value == $collegeid) {
                            unset($school_ids[$key]);
                        }
                    }
                    $save['school_ids'] = implode(',', $school_ids);
                    db('toplearning_login')->where(['user_id'=>$token_uid])->update($save);
                }
            }else{

                $save['school_ids'] = $users['school_ids'].",".$collegeid;
                db('toplearning_login')->where(['user_id'=>$token_uid])->update($save);
            }
        }
        // $map['source_id'] = $collegeid;
        // $map['user_id'] = $token_uid;
        // $map['type'] = 2;
        // //存在关注记录
        // if (db('toplearning_attention')->where($map)->find()) {
        //     //type 1 关注 2取消
        //     if ($type == 1) {
        //         //更新学院信息
        //         $umap['user_id'] = $token_uid;
        //         $save['del'] = 0;
        //         db('toplearning_attention')->where($map)->update($save);
        //     }else{
        //         //更新学院信息
        //         $umap['user_id'] = $token_uid;
        //         $save['del'] = 1;
        //         db('toplearning_attention')->where($map)->update($save);
        //     }

        // }else{
        //     //type 1 关注 2取消
        //     if ($type == 1) {
        //         //更新学院信息
        //         $save['user_id'] = $token_uid;
        //         $save['source_id'] = $collegeid;
        //         $save['type'] = 2;
        //         $save['create_time'] = time();
        //         $save['del'] = 0;
        //         db('toplearning_attention')->insert($save);
        //     }else{
        //         //更新学院信息
        //         $save['user_id'] = $token_uid;
        //         $save['source_id'] = $collegeid;
        //         $save['type'] = 2;
        //         $save['create_time'] = time();
        //         $save['del'] = 1;
        //         db('toplearning_attention')->insert($save);
        //     }
        // }

        
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
            return $this->error('token失效，请重新登录');
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

            $gz = db('toplearning_login')->where(['user_id'=>$token_uid])->column('school_ids');

            if ($gz) {
                $gzs = explode(',', $gz[0]);
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
               $scarr[$key]['achimages']= $value['achimages'];
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

        $limit = $page*$size;
        $teachers = db('toplearning_net_material')->where($map)->limit($limit, $size)->select();

        $ret = array();
        foreach ($teachers as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            $ret[$key]['title'] = $value['title'];
            $ret[$key]['image'] = $value['picture'];
            $ret[$key]['price'] = $value['price'];
            $ret[$key]['buycount'] = $value['order_num'];
            $ret[$key]['teacher'] = '';
            if (db('toplearning_teacher')->where(['teacher_id'=>$value['teacher_id']])->find()) {
                $ret[$key]['teacher']  = db('toplearning_teacher')->where(['teacher_id'=>$value['teacher_id']])->column('teacher_name')[0];
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
        $map['a.release_status'] = 1;
        $map['a.reviewed_status'] = 1;
        // $info = db('toplearning_net_material')->alias('a')->join('toplearning_class_festival f','a.net_material_id = f.material_id')->where($map)->whereTime('stage_start', 'between', [$todaytime, $todayetime])->select();
        $info = db('toplearning_net_material')->alias('a')->join('toplearning_class_festival f','a.net_material_id = f.material_id')->where($map)->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            $ret[$key]['title'] = $value['title'];
            $ret[$key]['desc'] = $value['introduce'];
            $ret[$key]['status'] = strtotime($value['stage_start'])>time()?'1':(strtotime($value['stage_end'])>time()?'2':'0');
            $ret[$key]['number'] = $value['off_num'];
            $ret[$key]['time'] = $value['stage_start'];
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
           $info = db('toplearning_net_material')->where(['user_id'=>$token_uid])->select();
           foreach ($info as $key => $value) {
                $ret[$key]['courseid'] = $value['net_material_id'];
                $ret[$key]['image'] = $value['picture'];
                $ret[$key]['title'] = $value['title'];
                $ret[$key]['college'] = $value['create_name'];
                $ret[$key]['total'] = $value['lession_num'];
                $ret[$key]['release'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->count();

                $ret[$key]['status'] = $value['release_status'];


            }
        }else{
            $info = db('toplearning_net_material')->alias('a')->join('toplearning_student_material s','a.net_material_id = f.material_id')->where(['user_id'=>$token_uid])->select();
            foreach ($info as $key => $value) {
                $ret[$key]['courseid'] = $value['net_material_id'];
                $ret[$key]['image'] = $value['picture'];
                $ret[$key]['title'] = $value['title'];
                $ret[$key]['college'] = $value['create_name'];
                $ret[$key]['total'] = $value['lession_num'];
                $ret[$key]['release'] = db('toplearning_class_festival')->where(['material_id'=>$value['net_material_id']])->count();

                $ret[$key]['complete'] = 20;

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

        $info = db('toplearning_school')->where(['del'=>0])->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['collegeid'] = $value['school_id'];
            $ret[$key]['name'] = $value['school_name'];
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

        $info = db('toplearning_net_material')->where(['del'=>0])->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['courseid'] = $value['net_material_id'];
            $ret[$key]['name'] = $value['title'];
            $ret[$key]['image'] = $value['picture'];
            $ret[$key]['total'] = $value['lession_num'];
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

        $info = db('toplearning_class_festival')->where(['material_id'=>$lessonsid])->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['lessonsid'] = $value['class_id'];
            $ret[$key]['name'] = $value['class_name'];
            $ret[$key]['index'] = $value['index'];//TODO
            $ret[$key]['video'] = $value['video'];//TODO
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
        //params
        $token = trim($params['token']);

        $image = trim($params['image']);//TODO
        $title = trim($params['title']);
        $college = trim($params['college']);
        $type = trim($params['type']);
        $keyword = trim($params['keyword']);
        $totallessons = trim($params['totallessons']);
        $monthlessons = trim($params['monthlessons']);
        $price = trim($params['price']);
        $limitnumber = trim($params['limitnumber']);
        $desc = trim($params['desc']);
        $way = trim($params['way']);


        //通过token获取 uid
        $token_uid = $this->decrypt($token);
        //data
        $data['title'] = $title;
        $data['picture'] = $image;//TODO
        $data['create_name'] = $college;
        $data['course_type'] = $type;
        $data['tags'] = $keyword;
        $data['lession_num'] = $totallessons;
        $data['month_lessons'] = $monthlessons;
        $data['price'] = $price;
        $data['student_num'] = $limitnumber;
        $data['introduce'] = $desc;
        $data['lession_status'] = $way;
        $data['user_id'] = $token_uid;
        $data['teacher_user_id'] = $token_uid;
        $data['teacher_id'] = db('toplearning_teacher')->where(['user_id'=>$token_uid])->column('teacher_id')[0];
        $data['school_id'] = db('toplearning_teacher')->where(['user_id'=>$token_uid])->column('school_id')[0];
        
        if (!db('toplearning_net_material')->insert($data)) {
            return $this->error('保存失败');
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

        db('toplearning_net_material')->where(['net_material_id'=>$courseid])->update(['del'=>1]);

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

        $image = trim($params['image']);//todo
        $title = trim($params['title']);
        $type = trim($params['type']);
        $keyword = trim($params['keyword']);
        $desc = trim($params['desc']);
        $share = trim($params['share']);


        //通过token获取 uid
        $token_uid = $this->decrypt($token);

        $data['user_id'] = $token_uid;
        $data['lession_name'] = $title;
        $data['lession_img'] = $image;
        $data['lession_type_id'] = $type;
        $data['keyword'] = $keyword;
        $data['lession_desc'] = $desc;
        $data['share'] = $share;
        $data['add_time'] = date('Y-m-d H:i:s',time());

        //toplearning_micro_class
        $courseid = db('toplearning_micro_class')->insert($data);
        if (!$courseid) {
            $this->error('保存失败');
        }
        $ret = array();
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$courseid,
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
    public function getpaynumberlist($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        $info = db('toplearning_order')->where(['net_material_id'=>$collegeid])->select();
        
        $ret = array();

        foreach ($info as $key => $value) {
            $ret[$key]['userid'] = $value['user_id'];
            $ret[$key]['name'] = $value['nickname'];
            $ret[$key]['image'] = db('toplearning_login')->where(['user_id'=>$value['user_id']])->column('avatar')[0];
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

        
        $ret = array();

        
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
     * [getlearningrecord 18.   学习记录]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getlearningrecord($params)
    {
        //params
        $token = trim($params['token']);

        
        $ret = array();

        
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
     * [getCourseEvaluation 19. 学员评价 TODO]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getCourseEvaluation($params)
    {
        //params
        $token = trim($params['token']);
        $courseid = trim($params['courseid']);

        
        $ret = array();

        
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
            return $this->error('token失效，请重新登录');
        }

        //通过token获取 uid
        // $token_uid = $this->decrypt($token);

        $info = db('toplearning_login')->alias('a')->join('toplearning_teacher t','a.user_id = t.user_id')->join('toplearning_net_material m','a.user_id = m.teacher_user_id')->where(['a.user_id'=>$teacherid])->find();
        $ret = array();
        if ($info) {
            $ret['headurl'] = $info['avatar'];
            $ret['name'] = $info['nickname'];
            $ret['score'] = $info['persent'];//TODO
            $ret['school'] = $info['school_name'];
            $ret['introduction'] = $info['introduce'];
            $ret['correlatedCurriculumList'] = array();
            
        }
        

        //相关课程
        $course = db('toplearning_net_material')->where(['teacher_user_id'=>$teacherid])->select();
        $coursearr = array();
        if ($course) {
            foreach ($course as $key => $value) {
                $coursearr[$key]['courseHeadUrl'] = $value['picture'];
                $coursearr[$key]['courseName'] = $value['title'];
                $coursearr[$key]['courseNum'] = $value['price'];
                $coursearr[$key]['purchaseNumber'] = $value['order_num'];
                $coursearr[$key]['courseType'] = $value['create_name'];
            }
           $ret['correlatedCurriculumList'] = $coursearr;
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

        // $ret['lessonsList'] = array();//TODO
        // $ret['lessonid'] = '';
        // $ret['index'] = '';
        // $ret['name'] = '';
        // $ret['time'] = '';
        // $ret['lessontime'] = '';
        // $ret['lessonWay'] = '';
        // $ret['staus'] = 1;
        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret,
            'Success'=>true
        ];

        return json($data);
    }
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
        $key = $this->passkey();
        $str = base64_decode($str); 
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB); 
        $block = mcrypt_get_block_size('des', 'ecb'); 
        $pad = ord($str[($len = strlen($str)) - 1]); 

        if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) { 
            $str = substr($str, 0, strlen($str) - $pad); 
        } 

        return unserialize($str); 
    }

    function passkey(){
        return 'ucschool';
    } 
}
