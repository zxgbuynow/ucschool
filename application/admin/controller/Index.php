<?php


namespace app\admin\controller;

use think\Cache;
use think\helper\Hash;
use think\Db;
use app\common\builder\ZBuilder;
use app\user\model\User as UserModel;
use app\admin\model\Qustion as QustionModel;
use app\admin\model\Wallqustion as WallqustionModel;
use app\admin\model\Plugqustion as PlugqustionModel;

/**
 * 后台默认控制器
 * @package app\admin\controller
 */
class Index extends Admin
{
    /**
     * 后台首页
     * @author zg
     * @return string
     */
    public function index()
    {
        $this->redirect('Log/index');exit;
        $admin_pass = Db::name('admin_user')->where('id', 1)->value('password');

        if (UID == 1 && $admin_pass && Hash::check('admin', $admin_pass)) {
            $this->assign('default_pass', 1);
        }
        return $this->fetch();
    }

    /**
     * 清空系统缓存
     * @author zg
     */
    public function wipeCache()
    {
        if (!empty(config('wipe_cache_type'))) {
            foreach (config('wipe_cache_type') as $item) {
                if ($item == 'LOG_PATH') {
                    $dirs = (array) glob(constant($item) . '*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*.log'));
                    }
                    array_map('rmdir', $dirs);
                } else {
                    array_map('unlink', glob(constant($item) . '/*.*'));
                }
            }
            Cache::clear();
            $this->success('清空成功');
        } else {
            $this->error('请在系统设置中选择需要清除的缓存类型');
        }
    }

    /**
     * 个人设置
     * @author zg
     */
    public function profile()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $data['nickname'] == '' && $this->error('昵称不能为空');
            $data['id'] = UID;

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            $UserModel = new UserModel();
            if ($user = $UserModel->allowField(['nickname', 'email', 'password', 'mobile', 'avatar'])->update($data)) {
                // 记录行为
                action_log('user_edit', 'admin_user', UID, UID, get_nickname(UID));
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = UserModel::where('id', UID)->field('password', true)->find();

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->addFormItems([ // 批量添加表单项
                ['static', 'username', '用户名', '不可更改'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['text', 'email', '邮箱', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['image', 'avatar', '头像']
            ])
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }

    public function question()
    {
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('create_time desc');
        // 数据列表
        $data_list = QustionModel::where($map)->order($order)->paginate();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['phone' => '手机号']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['shopname', '店铺名称'],
                ['username', '用户名称'],
                ['location', '店铺地址'],
                ['phone', '手机号'],
                ['q1', '选项1'],
                ['q2', '选项2'],
                ['create_time', '创建时间', 'datetime']
            ])
            ->setTableName('question')
            ->addOrder('id,create_time')
            ->setRowList($data_list) // 设置表格数据
            ->hideCheckbox()
            ->fetch(); // 渲染模板
    }

    public function wallquestion()
    {
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('create_time desc');
        // 数据列表
        $data_list = WallqustionModel::where($map)->order($order)->paginate();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['phone' => '手机号']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['shopname', '网点名称'],
                ['location', '地址'],
                ['phone', '联系电话'],
                ['q1', '进货金额'],
                ['q2', '数据线'],
                ['q3', '车充'],
                ['q4', '移动电源'],
                ['create_time', '创建时间', 'datetime']
            ])
            ->setTableName('wall_question')
            ->addOrder('id,create_time')
            ->setRowList($data_list) // 设置表格数据
            ->hideCheckbox()
            ->fetch(); // 渲染模板
    }

    public function plugquestion()
    {
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('create_time desc');
        // 数据列表
        $data_list = PlugqustionModel::where($map)->order($order)->paginate();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['phone' => '手机号']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['shopname', '网点名称'],
                ['username', '签收人'],
                ['location', '地址'],
                ['phone', '联系电话'],
                ['q1', '进货金额'],
                ['q2', '香满园御品国珍五常大米'],
                ['q3', '优质东北大米'],
                ['q4', '阳光葵籽油'],
                ['q5', '稻米油'],
                ['q6', '特香花生油'],
                ['q7', '阳光葵花籽油'],
                ['q8', '黄金调和油'],
                ['create_time', '创建时间', 'datetime']
            ])
            ->setTableName('plug_question')
            ->addOrder('id,create_time')
            ->setRowList($data_list) // 设置表格数据
            ->hideCheckbox()
            ->fetch(); // 渲染模板
    }
    /**
     * 检查版本更新
     * @author zg
     * @return \think\response\Json
     */
    public function checkUpdate()
    {
        $params = config('dolphin');
        $params['domain']  = request()->domain();
        $params['website'] = config('web_site_title');
        $params['ip']      = $_SERVER['SERVER_ADDR'];
        $params['php_os']  = PHP_OS;
        $params['php_version'] = PHP_VERSION;
        $params['mysql_version'] = db()->query('select version() as version')[0]['version'];
        $params['server_software'] = $_SERVER['SERVER_SOFTWARE'];
        $params = http_build_query($params);

        $opts = [
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => config('dolphin.product_update'),
            CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $params
        ];

        // 初始化并执行curl请求
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($data, true);

        if ($result['code'] == 1) {
            return json([
                'update' => '<a class="badge badge-primary" href="http://www.dolphinphp.com/download" target="_blank">有新版本：'.$result["version"].'</a>',
                'auth'   => $result['auth']
            ]);
        } else {
            return json([
                'update' => '',
                'auth'   => $result['auth']
            ]);
        }
    }
}