<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Counsellor as CounsellorModel;
use app\cms\model\Trade as TradeModel;
use app\cms\model\Agency as AgencyModel;
use util\Tree;
use think\Db;
use think\Hook;

/**
 * 分机构收入默认控制器
 * @package app\member\admin
 */
class Agencyicome extends Admin
{
    /**
     * 咨询师首页
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = AgencyModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        $incomeBtn = ['icon' => 'fa fa-fw fa-cny', 'title' => '收入明细', 'href' => url('income', ['id' => '__id__'])];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('机构收入管理') // 设置页面标题
            ->setTableName('shop_agency') // 设置数据表名
            ->setSearch(['title' => '分机构名']) // 设置搜索参数
            ->hideCheckbox()
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['title', '分机构名'],
                ['income', '收入'],
                ['right_button', '操作', 'btn']
            ])
            ->raw('income')
            ->addRightButton('custom', $incomeBtn)
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
            $result = $this->validate($data, 'User');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if ($user = UserModel::create($data)) {
                Hook::listen('user_add', $user);
                // 记录行为
                action_shop_log('user_add', 'shop_user', $user['id'], UID);
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
                ['select', 'role', '角色', '', RoleModel::getTree(null, false)],
                ['text', 'email', '邮箱', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['image', 'avatar', '头像'],
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

            // 禁止修改分中心超级管理员的角色和状态
            if ($data['id'] == 1 && $data['role'] != 1) {
                $this->error('禁止修改分中心超级管理员角色');
            }

            // 禁止修改分中心超级管理员的状态
            if ($data['id'] == 1 && $data['status'] != 1) {
                $this->error('禁止修改分中心超级管理员状态');
            }

            // 验证
            $result = $this->validate($data, 'User.update');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            if (UserModel::update($data)) {
                $user = UserModel::get($data['id']);
                Hook::listen('user_edit', $user);
                // 记录行为
                action_shop_log('user_edit', 'shop_user', $user['id'], UID, get_shop_nickname($user['id']));
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = UserModel::where('id', $id)->field('password', true)->find();

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('编辑') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['hidden', 'id'],
                ['static', 'username', '用户名', '不可更改'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['select', 'role', '角色', '', RoleModel::getTree(null, false)],
                ['text', 'email', '邮箱', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['image', 'avatar', '头像'],
                ['radio', 'status', '状态', '', ['禁用', '启用']]
            ])
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }

    /**
     * [income description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function income($id = null)
   {
       if ($id === null) $this->error('缺少参数');

        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        $map['shopid'] = $id;
        $map['status'] = 1;//已支付

        // 数据列表
        $data_list = TradeModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        //机构列表
        // $agency_list = AgencyModel::where('status', 1)->column('id,title');

        // print_r(db('member')->where(['id'=>1])->column('nickname')[0]);exit;
        //用户列表
        // $counsellor_list =  CounsellorModel::where('status', 1)->column('id,username');

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('收入明细管理') // 设置页面标题
            ->setTableName('trade') // 设置数据表名
            // ->setSearch(['id' => '订单编号']) // 设置搜索参数
            ->hideCheckbox()
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['title', '交易标题'],
                ['payment', '支付金额'],
                ['username', '用户'],
                ['created_time', '创建时间', 'datetime'],
                ['status', '状态', 'text', '', ['待支付', '已支付', '取消', '冻结']],
                
            ])
            ->raw('username')
            ->addTopButton('back', [
                'title' => '返回收入列表',
                'icon'  => 'fa fa-reply',
                'href'  => url('agencyicome/index')
            ])
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
   }

    /**
     * [cancle description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function cancle($id = null)
    {
        if (!$id) {
            $this->error('操作失败');
        }
        if (db('trade')->where(['id'=>$id])->update(['status'=>2])) {
            $this->success('取消成功', url('index'));
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * [frzee description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function frzee($id = null)
    {
        if (!$id) {
            $this->error('操作失败');
        }
        if (db('trade')->where(['id'=>$id])->update(['status'=>3])) {
            $this->success('冻结成功', url('index'));
        }else{
            $this->error('操作失败');
        }
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
        $menu_title = MemberModel::where('id', 'in', $ids)->column('mobile');
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
