<?php
namespace app\api\home;

use think\Exception;
use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;
use think\Session;

class IMUtil
{


    private static $instance = null;




    public static function getInstance(){

        if (self::$instance == null) {

            self::$instance = new IMUtil();

        }



        return self::$instance;
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


    function modifyUser($data){
        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/profile/portrait_set?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";
        $params = [
            "From_Account"=>$data['account'],
    "ProfileItem"=>$data['item']
        ];
//        var_dump($userSig,$params);
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

    function getGroupLastMg($data){
        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/group_msg_get_simple?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";

        //groupName groupActualite groupCurrentBulletin groupId
        
        $params = [
            "GroupId"=> $data['groupId'], // 要修改哪个群的基础资料（必填）
            "ReqMsgNumber"=> 1, // 群名称（填）
        ];
        
        $params = json_encode($params);
        $resp = curlRequest($url,$params);
        $resp = json_decode($resp,true);
        if($resp['ActionStatus'] != "OK"){
            return [];
        }else{
            if (!empty($resp['RspMsgList'])) {
               return  $resp['RspMsgList'];
            }else{
                return [];
            }
        }


    }

    function sendGroupNotice($data){
        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/send_group_system_notification?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";

        
        $params = [
            "GroupId"=> $data['groupId'], // 要修改哪个群的基础资料（必填）
            "Content"=> '欢迎加入“'.$data['className'].'”课程讨论群', // 系统通知内容
        ];
        
        $params = json_encode($params);
        $resp = curlRequest($url,$params);
        $resp = json_decode($resp,true);
        if($resp['ActionStatus'] != "OK"){
            return true;
        }else{
            return false;
        }


    }

    function getGroupDetail($group_id){
        $userSig = $this->getUserSign();
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/get_group_info?usersig=".$userSig."&identifier=admin&sdkappid=1400099084&random=".rand(100000,999999)."&contenttype=json";
        $params = [
            'GroupIdList'=>[$group_id]
        ];

        $params = json_encode($params);
        $resp = curlRequest($url,$params);


        $resp = json_decode($resp,true);



            return $resp['ActionStatus']=="OK"?$resp['GroupInfo'][0]:[];
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
