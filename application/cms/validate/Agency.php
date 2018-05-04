<?php


namespace app\cms\validate;

use think\Validate;

/**
 * 菜单验证器
 * @package app\cms\validate
 * @author zg
 */
class Agency extends Validate
{
    //定义验证规则
    protected $rule = [
        'title|分机构名'      => 'require',
        'description|描述'      => 'require',
    ];

    //定义验证提示
    protected $message = [
        'title.require' => '必填项不能为空',
        'description.require' => '必填项不能为空',
    ];
}
