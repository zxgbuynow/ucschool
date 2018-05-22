<?php


return [
    'api'=>[
        //登录
        'login'=>'user.login',//登录
        'register'=>'user.register',//注册
        'findPassword'=>'user.findPassword',
        'sendSms'=>'user.sendSms',
        'getUserInfo'=>'user.getUserInfo',
        //发现
        'findSearch'=>'find.findSearch',
        'getFocusCollege'=>'find.getFocusCollege',
        'changeCollege'=>'find.changeCollege',
        'getCollegeInfo'=>'find.getCollegeInfo',
        'focusCollege'=>'find.focusCollege',

        'getfindIndexCollege'=>'find.getfindIndexCollege',
        'getCollegeCourse'=>'find.getCollegeCourse',
        'getCollegeTeachers'=>'find.getCollegeTeachers',
        //课程
        'getTodayCourse'=>'class.getTodayCourse',
        'getMyCourse'=>'class.getMyCourse',
        'getCourseCollege'=>'class.getCourseCollege',
        'getCourseType'=>'class.getCourseType',
        'addlessons'=>'class.addlessons',

        'getRecordedCourseList'=>'class.getRecordedCourseList',
        'getRecordedLessonsList'=>'class.getRecordedLessonsList',
        'saveCourse'=>'class.saveCourse',
        'deleteCourse'=>'class.deleteCourse',
        'publishCourse'=>'class.publishCourse',

        'getUploadCoursewareList'=>'class.getUploadCoursewareList',
        'saveWeike'=>'class.saveWeike',
        'addweikelessons'=>'class.addweikelessons',
        'getPayNumberList'=>'class.getPayNumberList',
        'getCoursewaredataList'=>'class.getCoursewaredataList',

        'getExamList'=>'class.getExamList',
        'submitExamList'=>'class.submitExamList',
        'getlearningrecord'=>'class.getlearningrecord',
        'getCourseEvaluation'=>'class.getCourseEvaluation',
        'getCourse'=>'class.getCourse',
        //U学院
        'ContactList'=>'class.ContactList',
        'FriendApplicationList'=>'class.FriendApplicationList',
        'SearchForFriends'=>'class.SearchForFriends',
        'AddFriends'=>'class.AddFriends',
        'LaunchAGroupChat'=>'class.LaunchAGroupChat',

        'GroupList'=>'class.GroupList',
        'ModifyingPersonalInformation'=>'class.ModifyingPersonalInformation',
        'BusinessCard'=>'class.BusinessCard',
        'ClassroomSetting'=>'class.ClassroomSetting',
        'ResourceLibrary'=>'class.ResourceLibrary',

        'PaymentDetails'=>'class.PaymentDetails',
        'surplusDetails'=>'class.surplusDetails',
        'modificationNotification'=>'class.modificationNotification',

        'publishedAddClassFestival'=>'class.publishedAddClassFestival',
        'deleteFestival'=>'class.deleteFestival',
        'updataFestival'=>'class.updataFestival',

        
    ],
    'param'=>[
        'login'=>[
            'phone'=>['valid'=>true],
            'password'=>['valid'=>true]
        ],
    ]
];
