<?php


namespace app\cms\validate;

use think\Validate;

/**
 * 菜单验证器
 * @package app\cms\validate
 */
class Props extends Validate
{
    //定义验证规则
    protected $rule = [
        'prop_name|属性名'      => 'require',
        'cate_id|分类id'      => 'require',
    ];

    //定义验证提示
    protected $message = [
        'prop_name.require' => '必填项不能为空',
        'cate_id.require' => '必填项不能为空',
    ];
}
