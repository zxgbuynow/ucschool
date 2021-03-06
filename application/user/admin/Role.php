<?php


namespace app\user\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\user\model\Role as RoleModel;
use app\admin\model\Menu as MenuModel;
use util\Tree;
use think\Db;

/**
 * 角色控制器
 * @package app\admin\controller
 */
class Role extends Admin
{
    /**
     * 角色列表页
     * @author zg
     * @return mixed
     */
    public function index()
    {
        // 获取查询条件
        $map = $this->getMap();
        // 数据列表
        $data_list = RoleModel::where($map)->paginate();
        // 分页数据
        $page = $data_list->render();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('角色管理') // 页面标题
            ->setTableName('admin_role') // 设置表名
            ->setSearch(['name' => '角色名称', 'id' => 'ID']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['name', '角色名称'],
                ['description', '描述'],
                ['create_time', '创建时间', 'datetime'],
                ['access', '是否可登录后台', 'switch'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('add,enable,disable,delete') // 批量添加顶部按钮
            ->addRightButtons('edit,delete') // 批量添加右侧按钮
            ->replaceRightButton(['id' => 1], '<button class="btn btn-danger btn-xs" type="button" disabled>不可操作</button>') // 修改id为1的按钮
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染模板
    }

    /**
     * 新增
     * @author zg
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!isset($data['menu_auth'])) {
                $data['menu_auth'] = [];
            } else {
                $data['menu_auth'] = explode(',', $data['menu_auth']);
            }
            // 验证
            $result = $this->validate($data, 'Role');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            // 添加数据
            if ($role = RoleModel::create($data)) {
                // 记录行为
                action_log('role_add', 'admin_role', $role['id'], UID, $data['name']);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 菜单列表
        $menus = cache('access_menus');
        if (!$menus) {
            $modules = Db::name('admin_module')->where('status', 1)->column('name');
            $menus = MenuModel::where('module', 'in', $modules)->order('sort,id')->column('id,pid,sort,title,icon');
            $menus = Tree::toLayer($menus);
            $menus = $this->buildJsTree($menus);

            // 非开发模式，缓存菜单
            if (config('develop_mode') == 0) {
                cache('access_menus', $menus);
            }
        }

        $this->assign('page_title', '新增');
        $this->assign('role_list', RoleModel::getTree());
        $this->assign('menus', $menus);
        return $this->fetch();
    }

    /**
     * 编辑
     * @param null $id 角色id
     * @author zg
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');
        if ($id == 1) $this->error('超级管理员不可修改');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!isset($data['menu_auth'])) {
                $data['menu_auth'] = [];
            } else {
                $data['menu_auth'] = explode(',', $data['menu_auth']);
            }
            // 验证
            $result = $this->validate($data, 'Role');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if (RoleModel::update($data)) {
                role_auth();
                // 记录行为
                action_log('role_edit', 'admin_role', $id, UID, $data['name']);
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info       = RoleModel::get($id);
        $role_list  = RoleModel::getTree($id, '顶级角色');
        $modules    = Db::name('admin_module')->where('status', 1)->column('name');
        $menus      = MenuModel::where('module', 'in', $modules)->order('sort,id')->column('id,pid,sort,title,icon');
        $menus      = Tree::toLayer($menus);
        $menus      = $this->buildJsTree($menus, $info);

        $this->assign('page_title', '编辑');
        $this->assign('role_list', $role_list);
        $this->assign('menus', $menus);
        $this->assign('info', $info);
        return $this->fetch('edit');
    }

    /**
     * 构建jstree代码
     * @param array $menus 菜单节点
     * @param array $user 用户信息
     * @author zg
     * @return string
     */
    private function buildJsTree($menus = [], $user = [])
    {
        $result = '';
        if (!empty($menus)) {
            $option = [
                'opened'   => true,
                'selected' => false,
                'icon'     => '',
            ];
            foreach ($menus as $menu) {
                $option['icon'] = $menu['icon'];
                if (isset($user['menu_auth'])) {
                    $option['selected'] = in_array($menu['id'], $user['menu_auth']) ? true : false;
                }
                if (isset($menu['child'])) {
                    $result .= '<li id="'.$menu['id'].'" data-jstree=\''.json_encode($option).'\'>'.$menu['title'].$this->buildJsTree($menu['child'], $user).'</li>';
                } else {
                    $result .= '<li id="'.$menu['id'].'" data-jstree=\''.json_encode($option).'\'>'.$menu['title'].'</li>';
                }
            }
        }

        return '<ul>'.$result.'</ul>';
    }

    /**
     * 删除角色
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function delete($record = [])
    {
        return $this->setStatus('delete');
    }

    /**
     * 启用角色
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function enable($record = [])
    {
        return $this->setStatus('enable');
    }

    /**
     * 禁用角色
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function disable($record = [])
    {
        return $this->setStatus('disable');
    }

    /**
     * 设置角色状态：删除、禁用、启用
     * @param string $type 类型：delete/enable/disable
     * @param array $record
     * @author zg
     * @return mixed
     */
    public function setStatus($type = '', $record = [])
    {
        $ids     = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $role_id = is_array($ids) ? 0 : $ids;
        $ids     = RoleModel::where('id', 'in', $ids)->column('name');
        return parent::setStatus($type, ['role_'.$type, 'admin_role', $role_id, UID, implode('、', $ids)]);
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
        $config  = RoleModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['role_edit', 'admin_role', $id, UID, $details]);
    }
}
