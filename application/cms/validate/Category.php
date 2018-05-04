<?php


namespace app\cms\validate;

use think\Validate;

/**
 * 菜单验证器
 * @package app\cms\validate
 * @author zg
 */
class Category extends Validate
{
    //定义验证规则
    protected $rule = [
        'title|分类标题'      => 'require',
        // 'create_time|创建时间'      => 'require',
        'update_time|更新时间'      => 'require',
        'pid|父级id'      => 'require',
    ];

    //定义验证提示
    protected $message = [
        'title.require' => '1必填项不能为空',
        // 'create_time.require' => '2必填项不能为空',
        'update_time.require' => '3必填项不能为空',
        'pid.require' => '4必填项不能为空',
    ];
}
