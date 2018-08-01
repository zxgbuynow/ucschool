<?php


return [
    'api'=>[
        //登录
        'login'=>'user.login',//登录
        'story'=>'user.story',//故事
        'memberinfo'=>'member.info',//会员列表
        'updatenickname'=>'update.nickname',//更新会员Nickname
        'advlist'=>'adv.list',//广告列表
        'searchlist'=>'search.list',//搜索列表
        'storydetail'=>'story.detail',//祥情
        'upview'=>'up.view',//更新view
        'upfeedback'=>'up.feedback'//更新view

    ],
    'param'=>[
        'login'=>[
            'login_name'=>['valid'=>true],
            'login_password'=>['valid'=>true],
            'deviceid'=>['valid'=>true]
        ],
    ]
];
