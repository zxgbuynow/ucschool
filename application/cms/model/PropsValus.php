<?php


namespace app\cms\model;

use think\Model as ThinkModel;
use util\Tree;
/**
 * 菜单模型
 * @package app\cms\model
 */
class PropsValus extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_PROPS_VALUES__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

}