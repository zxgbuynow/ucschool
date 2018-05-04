<?php


namespace app\cms\validate;

use think\Validate;

/**
 * 菜单验证器
 * @package app\cms\validate
 */
class PropsValues extends Validate
{
    //定义验证规则
    protected $rule = [
        'value|属性值'      => 'require',
        'props_id|属性id'      => 'require',
    ];

    //定义验证提示
    protected $message = [
        'value.require' => '必填项不能为空',
        'props_id.require' => '必填项不能为空',
    ];
}
