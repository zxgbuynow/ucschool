<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Props as PropsModel;
use app\cms\model\PropsValus as PropsValusModel;
use app\cms\model\Category as CategoryModel;
use util\Tree;
use think\Db;

/**
 * 属性控制器
 * @package app\cms\admin
 */
class Props extends Admin
{
    /**
     * 菜单列表
     * @return mixed
     */
    public function index()
    {
        // 查询
        $map = $this->getMap();

        // 数据列表
        $data_list = Db::view('cms_props', true)
            ->order('cms_props.sort')
            ->select();
        $btnAdd = ['icon' => 'fa fa-fw fa-navicon', 'title' => '编辑属性值', 'href' => url('Values/index', ['id' => '__id__'])];

        $list_module = CategoryModel::where(1)->order('id desc')->column('id as cate_id,title');
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['prop_name' => '标题'])// 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['cate_id', '分类名','','',$list_module],
                ['prop_name', '属性名'],
                ['sort', '排序', 'text.edit'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('add', ['href' => url('add')])
            ->addTopButtons('enable,disable')// 批量添加顶部按钮
            ->addRightButton('edit')
            ->addRightButton('custom', $btnAdd)
            ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 新增
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Props');
            if(true !== $result) $this->error($result);

            if ($props = PropsModel::create($data)) {
                // 记录行为
                action_log('props_add', 'cms_props', $props['id'], UID, $data['prop_name']);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'cate_id', '分类', '<code>必选</code>', CategoryModel::getTreeLists()],
                ['text', 'prop_name', '标题'],
                ['text', 'sort', '排序', '', 100],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1]
            ])
            ->fetch();
    }

    /**
     * 编辑
     * @param null $id 菜单id
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Props');
            if(true !== $result) $this->error($result);

            if (PropsModel::update($data)) {
                // 记录行为
                action_log('props_edit', 'cms_props', $id, UID, $data['prop_name']);
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'cate_id', '分类', '<code>必选</code>', CategoryModel::getTreeLists()],
                ['text', 'prop_name', '标题'],
                ['text', 'sort', '排序', '', 100],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1]
            ])
            ->setFormData(PropsModel::get($id))
            ->fetch();
    }

    /**
     * 删除菜单
     * @param null $ids 菜单id
     * @author zg
     * @return mixed
     */
    public function delete($ids = null)
    {
        // 检查是否有子菜单
        if (PropsValusModel::where('props_id', $ids)->find()) {
            $this->error('请先删除该属性下的值');
        }
        return $this->setStatus('delete');
    }

    /**
     * 启用菜单
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function enable($record = [])
    {
        return $this->setStatus('enable');
    }

    /**
     * 禁用菜单
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function disable($record = [])
    {
        return $this->setStatus('disable');
    }

    /**
     * 设置菜单状态：删除、禁用、启用
     * @param string $type 类型：delete/enable/disable
     * @param array $record
     * @author zg
     * @return mixed
     */
    public function setStatus($type = '', $record = [])
    {
        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $menu_title = PropsModel::where('id', 'in', $ids)->column('prop_name');
        return parent::setStatus($type, ['props_'.$type, 'cms_props', 0, UID, implode('、', $menu_title)]);
    }

    /**
     * 快速编辑
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $menu    = PropsModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $menu . ')，新值：(' . $value . ')';
        return parent::quickEdit(['props_edit', 'cms_props', $id, UID, $details]);
    }
}