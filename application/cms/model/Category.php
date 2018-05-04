<?php


namespace app\cms\model;

use think\Model as ThinkModel;
use util\Tree;
/**
 * 菜单模型
 * @package app\cms\model
 */
class Category extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_CATEGORY__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;


    /**
     * 获取栏目列表
     * @return array|mixed
     */
    public static function getList()
    {
        $data_list = cache('cms_categroy');
        if (!$data_list) {
            $data_list = self::where('status', 1)->column(true, 'id');
            // 非开发模式，缓存数据
            if (config('develop_mode') == 0) {
                cache('cms_categroy', $data_list);
            }
        }
        return $data_list;
    }

    /**
     * 获取树状栏目
     * @param int $id 需要隐藏的栏目id
     * @param string $default 默认第一个节点项，默认为“顶级栏目”，如果为false则不显示，也可传入其他名称
     * @return array|mixed
     */
    public static function getTreeList($id = 0, $default = '')
    {
        $result[0] = '顶级栏目';
        // 排除指定节点及其子节点
        if ($id !== 0) {
            $hide_ids    = array_merge([$id], self::getChildsId($id));
            $where['id'] = ['notin', $hide_ids];
        }
        $where['status'] = 1;

        $data_list = Tree::config(['title' => 'title'])->toList(self::where($where)->order('pid,id')->column('id,pid,title'));
        foreach ($data_list as $item) {
            $result[$item['id']] = $item['title'];
        }


        // 设置默认节点项标题
        if ($default != '') {
            $result[0] = $default;
        }
        // 隐藏默认节点项
        if ($default === false) {
            unset($result[0]);
        }

        return $result;
    }

    /**
     * 获取树状栏目
     * @param int $id 需要隐藏的栏目id
     * @param string $default 默认第一个节点项，默认为“顶级栏目”，如果为false则不显示，也可传入其他名称
     * @return array|mixed
     */
    public static function getTreeLists()
    {
        // $result[0] = '顶级栏目';
        // 排除指定节点及其子节点
        // if ($id !== 0) {
        //     $hide_ids    = array_merge([$id], self::getChildsId($id));
        //     $where['id'] = ['notin', $hide_ids];
        // }
        // $where['pid'] = array('gt',1);
        $where['status'] = 1;

        $data_list = Tree::config(['title' => 'title'])->toList(self::where($where)->order('pid,id')->column('id,pid,title'));
        foreach ($data_list as $item) {
            if ($item['pid']==0) {
                continue;
            }
            $result[$item['id']] = $item['title'];
        }


        return $result;
    }

    /**
     * 获取所有子栏目id
     * @param int $pid 父级id
     * @return array
     */
    public static function getChildsId($pid = 0)
    {
        $ids = self::where('pid', $pid)->column('id');
        foreach ($ids as $value) {
            $ids = array_merge($ids, self::getChildsId($value));
        }
        return $ids;
    }

}