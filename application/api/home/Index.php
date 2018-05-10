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
        $ret['customerType'] = 1;
        $customerType = db('toplearning_user_account')->where('user_id',$user['user_id'])->column('type');
        if ($customerType) {
           $ret['customerType'] = $customerType[0];
        }
        
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
        $data['type'] = trim($params['type']);
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
        $attention = db('toplearning_attention')->alias('a')->join('toplearning_school s','a.source_id = s.school_id')->where($map)->select();


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
        db('toplearning_login')->where($map)->update($save);

        //返回信息
        $data = [
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1,
            'Success'=>true
        ];

        return json($data);
    }

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
        $rs['achtitle'] = '';
        $rs['achdesc'] = '';
        $rs['achimages'] = '';
        $rs['number'] = $ret['school_name'];
        $rs['isfocus'] = 0;
        $rs['collegeimg'] = $ret['logo'];

        $amap['type'] = 2;
        $amap['del'] = 0;
        $amap['source_id'] = $ret['school_id'];
        if (db('toplearning_attention')->where($amap)->find()) {
            $rs['isfocus'] = 1;
        }
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
        
        $map['source_id'] = $collegeid;
        $map['user_id'] = $token_uid;
        $map['type'] = 2;
        //存在关注记录
        if (db('toplearning_attention')->where($map)->find()) {
            //type 1 关注 2取消
            if ($type == 1) {
                //更新学院信息
                $umap['user_id'] = $token_uid;
                $save['del'] = 0;
                db('toplearning_attention')->where($map)->update($save);
            }else{
                //更新学院信息
                $umap['user_id'] = $token_uid;
                $save['del'] = 1;
                db('toplearning_attention')->where($map)->update($save);
            }

        }else{
            //type 1 关注 2取消
            if ($type == 1) {
                //更新学院信息
                $save['user_id'] = $token_uid;
                $save['source_id'] = $collegeid;
                $save['type'] = 2;
                $save['create_time'] = time();
                $save['del'] = 0;
                db('toplearning_attention')->insert($save);
            }else{
                //更新学院信息
                $save['user_id'] = $token_uid;
                $save['source_id'] = $collegeid;
                $save['type'] = 2;
                $save['create_time'] = time();
                $save['del'] = 1;
                db('toplearning_attention')->insert($save);
            }
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
     * [getTodayCourse 今日课程]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getTodayCourse($params)
    {

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
