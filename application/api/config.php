<?php


return [
    'api'=>[
        //登录
        'login'=>'user.login',//登录
        'story'=>'user.story',//故事
        'memberinfo'=>'member.info',//会员列表

    ],
    'param'=>[
        'login'=>[
            'login_name'=>['valid'=>true],
            'login_password'=>['valid'=>true],
            'deviceid'=>['valid'=>true]
        ],
    ]
];
