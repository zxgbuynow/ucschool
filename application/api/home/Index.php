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
     * login 用户端
     * @param string $value [description]
     */
    public function login($params)
    {   
        //参数手机号，密码
        $phone = trim($params['login_name']);
        $password = trim($params['login_password']);
        $deviceid = trim($params['deviceid']);

        $ret = array();
        //是否是会员 不是注册
        if ($user = db('member')->where(['username'=>$phone])->find()) {
            //密码判断
            if ($user != md5($password)) {
                $this->error('密码不对');
            }
            $ret = db('member')->where(['username'=>$phone])->find();
            unset($ret['password']);
        }else{
            //注册
            $data['username'] = $phone;
            $data['password'] = md5($password);
            $data['deviceid'] = $deviceid;
            $data['nickname'] = $phone;
            $data['mobile'] = $phone;
            $data['create_time'] = time();
            db('member')->insert($data);
            $ret = db('member')->where(['username'=>$phone])->find();
            unset($ret['password']);
        }

        $ret['accessToken'] = md5($ret['id']);
        $data = [
            'Success'=>true,
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret
        ];
        return json($data);
    }

    /**
     * [memberinfo 会员信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function memberinfo($params)
    {
        //参数手机号，密码
        $phone = trim($params['account']);

        $ret = db('member')->where(['username'=>$phone])->find();
        unset($ret['password']);

        $data = [
            'Success'=>true,
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>$ret
        ];
        return json($data);
    }

    public function updatenickname($params)
    {

        //参数手机号，Nickname
        $phone = trim($params['account']);
        $nickname = trim($params['nickname']);

        $data['nickname'] = $nickname;
        db('member')->where(['username'=>$phone])->update($data);

        $data = [
            'Success'=>true,
            'Code'=>'0',
            'Msg'=>'操作成功',
            'Data'=>1
        ];
        return json($data);
    }

    /**
     * [advlist 广告列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function advlist($params)
    {
        //广告标识
        $type = trim($params['type']);

        $map['tagname'] = $type;
        $map['status'] = 1;
        $info = db('cms_advert')->where($map)->order('create_time DESC')->limit(5)->select();

        $ret = array();
        foreach ($info as $key => $value) {
            $ret[$key]['pic'] = $value['cover'];
            $ret[$key]['webview'] = $value['link'];
            $ret[$key]['webparam'] = $value['params'];
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
     * [story 故事列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function story($params)
    {

        //params
        $page = trim($params['page']);
        $size = trim($params['size']);

        //学院老师
        $map['status'] = 1;
        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;
        $story = db('story')->where($map)->limit($limit, $size)->select();

        $ret = array();

        foreach ($story as $key => $value) {
            $ret[$key]['id'] = $value['id'];
            $ret[$key]['title'] = $value['title'];
            $ret[$key]['source'] = $value['source'];
            $ret[$key]['view'] = $value['view'];
            $ret[$key]['description'] = $value['description'];
            $ret[$key]['pic'] = get_file_path($value['pic']);
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
     * [searchlist 搜索]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function searchlist($params)
    {
        //params
        $page = trim($params['page']);
        $size = trim($params['size']);
        $title = trim($params['title']);

        if ($title) {
            $map['title'] = array('like','%'.$title.'%');
        }
        //学院老师
        $map['status'] = 1;
        $page = $page ==''?0:$page;
        $size = $size == ''?10:$size;

        $limit = $page*$size;
        $story = db('story')->where($map)->limit($limit, $size)->select();

        $ret = array();

        foreach ($story as $key => $value) {
            $ret[$key]['id'] = $value['id'];
            $ret[$key]['title'] = $value['title'];
            $ret[$key]['source'] = $value['source'];
            $ret[$key]['view'] = $value['view'];
            $ret[$key]['description'] = $value['description'];
            $ret[$key]['pic'] = get_file_path($value['pic']);
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
     * [storydetail 祥情页]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function storydetail($params)
    {
        //params
        $storyid = trim($params['storyid']);
        $info = db('story')->where(['id'=>$storyid])->find();

        $ret = array();
        if ($info) {
            $ret['title'] = $info['title'];
            $ret['create_time'] = $info['create_time'];
            $ret['content'] = $info['content'];
            $ret['source'] = $info['source'];
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

    /**
     * [_seve_img 上传头像]
     * @param  [type] $avar [description]
     * @return [type]       [description]
     */
    public function _seve_img($avar)
    {
        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
        
        $path = 'http://'.$_SERVER['HTTP_HOST']."/public/uploads/images/".date("Ymd",time());
        $path = "public/uploads/images/".date("Ymd",time());
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }
        $imageSrc=  $path."/". $imageName;  //图片名字

        $r = file_put_contents(ROOT_PATH ."public/".$imageSrc, base64_decode($avar));//返回的是字节数
        if (!$r) {
            return false;
        }else{
            return $imageSrc;
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
}
