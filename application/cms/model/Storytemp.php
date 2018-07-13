<?php


namespace app\cms\model;

use think\Model as ThinkModel;

/**
 * 故事列
 * @package app\cms\model
 */
class Storytemp extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__STORY_TEMP__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}