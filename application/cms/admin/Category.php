<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Category as CategoryModel;
use util\Tree;
use think\Db;

/**
 * 分类控制器
 * @package app\cms\admin
 */
class Category extends Admin
{
    /**
     * 菜单列表
     * @param null $id 导航id
     * @return mixed
     */
    public function index($id = null)
    {
        // 查询
        $map = $this->getMap();

        // 数据列表
        $data_list = Db::view('cms_category', true)
            ->order('cms_category.id')
            ->select();

        if (empty($map)) {
            $data_list = Tree::toList($data_list);
        }

        $btnAdd = ['icon' => 'fa fa-plus', 'title' => '新增子菜单', 'href' => url('add', ['pid' => '__id__'])];

        

        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['title', '标题', 'callback', function($value, $data){
                    return isset($data['title_prefix']) ? $data['title_display'] : $value;
                }, '__data__'],
                ['create_time', '创建时间', 'datetime'],
                ['update_time', '更新时间', 'datetime'],
                ['sort', '排序', 'text.edit'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('add', ['href' => url('add')])
            ->addTopButtons('enable,disable')// 批量添加顶部按钮
            // ->addRightButton('custom', $btnAdd)
            ->replaceRightButton(['pid' => ['<>', 0]], '', ['custom'])
            ->addRightButton('edit')
            ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 新增
     * @param int $pid 菜单父级id
     * @author zg
     * @return mixed
     */
    public function add($pid = 0)
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            $data['create_time'] = time();
            $data['update_time'] = time();
            // 验证
            $result = $this->validate($data, 'Category');
            if(true !== $result) $this->error($result);

            if ($Category = CategoryModel::create($data)) {
                // 记录行为
                action_log('category_add', 'cms_Category', $Category['id'], UID, $data['title']);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }
        
        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'pid', $pid],
                ['select', 'pid', '父类', '<code>必选</code>', CategoryModel::getTreeList(),$pid],
                ['text', 'title', '分类标题'],
                ['text', 'sort', '排序', '', 100],
                ['image', 'cover', '分类图片'],
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
            $data['create_time'] = time();
            $data['update_time'] = time();
            // 验证
            $result = $this->validate($data, 'Category');
            if(true !== $result) $this->error($result);

            if (CategoryModel::update($data)) {
                // 记录行为
                action_log('category_edit', 'cms_Category', $id, UID, $data['title']);
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'pid', '父类', '<code>必选</code>', CategoryModel::getTreeList(0)],
                ['text', 'title', '分类标题'],
                ['text', 'sort', '排序', '', 100],
                ['image', 'cover', '分类图片'],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1]
            ])
            ->setFormData(CategoryModel::get($id))
            ->fetch();
    }

    /**
     * 删除菜单
     * @param null $ids 菜单id
     * @return mixed
     */
    public function delete($ids = null)
    {
        // 检查是否有子菜单
        if (CategoryModel::where('pid', $ids)->find()) {
            $this->error('请先删除或移动该菜单下的子菜单');
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
        $menu_title = CategoryModel::where('id', 'in', $ids)->column('title');
        return parent::setStatus($type, ['category_'.$type, 'cms_Category', 0, UID, implode('、', $menu_title)]);
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
        $menu    = CategoryModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $menu . ')，新值：(' . $value . ')';
        return parent::quickEdit(['category_edit', 'cms_Category', $id, UID, $details]);
    }
    function replace($data) {
    // 取出右侧按钮的Str字符串
        $rightButtonStr = $data['right_button'];
        // 根据自己的自定义规则任意处理正则隐藏不需要的按钮或修改内容
        if ($data['pid'] != 0) {
            $rightButtonStr = preg_replace('/<a\stitle="新增子菜单".*?<\/a>/', '', $rightButtonStr);
        }
        // 将新的按钮组覆盖原行数据(此时Bulider已处理完按钮的编译，覆盖即覆盖到已编译完成的结果)
        $data['right_button'] = $rightButtonStr;
        // 返回本列原有的内容
        return $data['right_button1'];
    }
}