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
class Values extends Admin
{
    /**
     * 菜单列表
     * @return mixed
     */
    public function index($id = null)
    {
        $id === null && $this->error('参数错误');
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 查询
        $map = $this->getMap();
        $map['props_id'] = $id;

        // 数据列表
        $data_list = PropsValusModel::where($map)->order('id desc')->paginate();


        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['value' => '属性值'])// 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['value', '属性值'],
                ['sort', '排序', 'text.edit'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('back', ['href' => url('props/index')]) // 批量添加顶部按钮
            ->addTopButton('add', ['href' => url('add', ['props_id' => $id])])
            ->addTopButtons('enable,disable')// 批量添加顶部按钮
            ->addRightButton('edit')
            ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 新增
     * @return mixed
     */
    public function add($props_id='')
    {
        $props_id === null && $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $data['props_id'] = $props_id;
            $result = $this->validate($data, 'PropsValues');
            if(true !== $result) $this->error($result);

            if ($props = PropsValusModel::create($data)) {
                // 记录行为
                action_log('props_values_add', 'cms_props_values', $props['id'], UID, $data['value']);
                $this->success('新增成功', url('index',['id' => $props_id]));
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'value', '标题'],
                ['text', 'sort', '排序', '', 100],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1]
            ])
            ->fetch();
    }

    /**
     * 编辑
     * @param null $id 属性值id
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
            $result = $this->validate($data, 'PropsValues');
            if(true !== $result) $this->error($result);

            if (PropsValusModel::update($data)) {
                // 记录行为
                action_log('props_values_edit', 'cms_props_values', $id, UID, $data['value']);
                // $this->success('编辑成功', url('index',['id' => $id]));
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'value', '标题'],
                ['text', 'sort', '排序', '', 100],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1]
            ])
            ->setFormData(PropsValusModel::get($id))
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
        $menu_title = PropsValusModel::where('id', 'in', $ids)->column('value');
        return parent::setStatus($type, ['props_values_'.$type, 'cms_props_values', 0, UID, implode('、', $menu_title)]);
    }

    /**
     * 快速编辑
     * @param array $record 行为日志
     * @return mixed
     */
    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $menu    = PropsValusModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $menu . ')，新值：(' . $value . ')';
        return parent::quickEdit(['props_values_edit', 'cms_props_values', $id, UID, $details]);
    }
}