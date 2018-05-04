<?php


namespace app\admin\controller;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\admin\model\Counsellor as CounsellorModel;
use app\admin\model\Agency as AgencyModel;
use util\Tree;
use think\Db;
use think\Hook;

/**
 * 咨询师默认控制器
 * @package app\member\admin
 */
class Counsellor extends shop
{
    /**
     * 咨询师首页
     * @TODO 所属机构
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        $map['type'] = 1;
        // 数据列表
        $data_list = CounsellorModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        $list_type = AgencyModel::where('status', 1)->column('id,title');

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('咨询师管理') // 设置页面标题
            ->setTableName('member') // 设置数据表名
            ->setSearch(['mobile' => '手机号']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['mobile', '手机号'],
                ['typeid', '机构', 'select', $list_type],
                ['create_time', '创建时间', 'datetime'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('enable,disable,delete') // 批量添加顶部按钮
            ->addRightButtons('delete,edit') // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
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
            // 验证
            $result = $this->validate($data, 'Counsellor');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            $data['create_time']= time();
            $data['type'] = 1;
            if ($user = CounsellorModel::create($data)) {
                // Hook::listen('counsellor_add', $user);
                // 记录行为
                action_log('counsellor_add', 'admin_counsellor', $user['id'], UID);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('新增') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['text', 'username', '用户名', '必填，可由英文字母、数字组成'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->fetch();
    }

    /**
     * 编辑
     * @param null $id 用户id
     * @author zg
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();


            if (CounsellorModel::update($data)) {
                $user = CounsellorModel::get($data['id']);
                // 记录行为
                action_shop_log('user_edit', 'admin_counsellor', $user['id'], UID, get_nickname($user['id']));
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        // $info = CounsellorModel::where('id', $id)->find();
        $info = CounsellorModel::getCounsellorList($id);
        print_r($info);exit;

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('编辑') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['hidden', 'id'],
                ['text', 'username', '用户名', '必填，可由英文字母、数字组成'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['radio', 'status', '状态', '', ['禁用', '启用']],
                ['radio', 'recommond', '推荐', '', ['不推荐', '推荐']]
            ])
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }

    /**
     * 积分处理
     * @return mixed
     */
    public function point($id = null)
    {
       if ($id === null) $this->error('缺少参数');

        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        $map['memberid'] = $id;
        // 数据列表
        $data_list = PointModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();


        $list_type = CounsellorModel::where('status', 1)->column('id,username');

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('会员管理') // 设置页面标题
            ->setTableName('member_point') // 设置数据表名
            ->setSearch(['mobile' => '手机号']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['behavior_type', '行为类型',['获得','消费']],
                ['behavior', '行为描述'],
                ['memberid', '会员', 'select', $list_type],
                ['point', '积分值'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('back', [
                'title' => '返回咨询师列表',
                'icon'  => 'fa fa-reply',
                'href'  => url('counsellor/index')
            ])
            ->addTopButtons('delete') // 批量添加顶部按钮
            ->addRightButtons('delete') // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
   }
   
    /**
     * 删除用户
     * @param array $ids 用户id
     * @author zg
     * @return mixed
     */
    public function delete($ids = [])
    {
        // Hook::listen('user_delete', $ids);
        return $this->setStatus('delete');
    }

    /**
     * 启用用户
     * @param array $ids 用户id
     * @author zg
     * @return mixed
     */
    public function enable($ids = [])
    {
        // Hook::listen('user_enable', $ids);
        return $this->setStatus('enable');
    }

    /**
     * 禁用用户
     * @param array $ids 用户id
     * @author zg
     * @return mixed
     */
    public function disable($ids = [])
    {
        // Hook::listen('user_disable', $ids);
        return $this->setStatus('disable');
    }

    /**
     * 设置用户状态：删除、禁用、启用
     * @param string $type 类型：delete/enable/disable
     * @param array $record
     * @author zg
     * @return mixed
     */
    public function setStatus($type = '', $record = [])
    {
        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $menu_title = CounsellorModel::where('id', 'in', $ids)->column('mobile');
        return parent::setStatus($type, ['member_'.$type, 'member', 0, UID, implode('、', $menu_title)]);
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
        $id      == UID && $this->error('禁止操作当前账号');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $config  = UserModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['user_edit', 'shop_user', $id, UID, $details]);
    }
}
