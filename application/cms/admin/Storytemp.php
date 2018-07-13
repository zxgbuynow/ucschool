<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Storytemp as StorytempModel;
use app\cms\model\Category as CategoryModel;
use util\Tree;
use think\Db;

/**
 * 分类控制器
 * @package app\cms\admin
 */
class Storytemp extends Admin
{
    /**
     * 故事库首页
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $map['isuse'] = 0;
        $data_list = StorytempModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        //分类
        $list = CategoryModel::where(1)->column('id,title');

        $btnAdd = ['icon' => 'fa fa-plus','class' => 'btn btn-primary ajax-post', 'title' => '导入库', 'href' => url('export')];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('故事群集管理') // 设置页面标题
            ->setTableName('story_temp') // 设置数据表名
            ->setSearch(['title' => '标题','cate'=>'分类']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['title', '标题'],
                ['cate', '分类'],
                ['description', '描述'],
                ['create_time', '创建时间', 'datetime'],
                ['status', '状态', 'switch'],
                // ['select', 'cateid', '分类', '', $list],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('delete') // 批量添加顶部按钮
            ->addTopButton('custom',$btnAdd) // 添加顶部按钮
            // ->addRightButtons('delete,edit') // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
    }

    public function export($ids = [])
    {   
        //处理id
        $map['id'] = array('in',$ids);
        $info = db('story_temp')->where($map)->select();
        foreach ($info as $key => $value) {
            //组数据&&分类查找无则生成
            $save['title'] = $value['title'];
            $save['source'] = $value['source'];
            $save['content'] = $value['content'];
            $save['create_time'] = time();
            $save['pic'] = $value['pic'];
            $save['view'] = rand(10,1500);
            $save['description'] = trim(mb_substr(trim(strip_tags($value['content'])),0,40));
            $m['title'] = array('like','%'.$value['cate'].'%');
            if ($c = db('cms_category')->where($m)->find()) {
                $save['cateid'] = $c['id'];
            }else{
                $s['title'] = $value['cate'];
                $s['create_time'] = time();
                $s['update_time'] = time();
                db('cms_category')->insert($s);
                $save['cateid'] = db('cms_category')->getLastInsID();
            }
            //插入
            if ($save) {
               db('story')->insert($save);
            }
            //更新状态
            db('story_temp')->where(['id'=>$value['id']])->update(['isuse'=>1]);
        }
        $this->success('导入成功'); 
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
            $result = $this->validate($data, 'Story');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            $data['create_time'] = time();
            if ($data = StoryModel::create($data)) {
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }
        $list = CategoryModel::where(1)->column('id,title');
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('新增') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['text', 'title', '标题'],
                ['text', 'source', '来源'],
                ['textarea', 'description', '描述'],
                ['image', 'pic', '单页封面'],
                ['select', 'cateid', '分类', '', $list],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->addWangeditor('content', '内容')
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


            // 验证
            $result = $this->validate($data, 'Story');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if (StoryModel::update($data)) {
                
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = StoryModel::where('id', $id)->find();

        $list = CategoryModel::where(1)->column('id,title');
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('编辑') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['hidden', 'id'],
                ['text', 'title', '标题'],
                ['text', 'source', '来源'],
                ['textarea', 'description', '描述'],
                ['image', 'pic', '单页封面'],
                ['select', 'cateid', '分类', '', $list],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->addWangeditor('content', '内容')
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }

   public function admin($id = null)
   {
       if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $data['role'] = 1;
            $data['access'] = 1;
            $data['shopid'] = $id;
            $result = $this->validate($data, 'User');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            
            if ($user = UserModel::create($data)) {
                //更新字段
                $adata['adminid'] = $user;
                $adata['id'] = $id;
                AgencyModel::update($adata);
                
                // 记录行为
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
                ['text', 'email', '邮箱', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->fetch();
   }

   public function cate($id = null)
   {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            if (CateAccessModel::where('shopid',$id)->find()) {//编辑
                $map['shopid'] = $id;
                $save['cids'] = implode(',', $data['cids']);
                if (db('shop_cate_access')->where($map)->update($save)) {
                    
                    // 记录行为
                    $this->success('编辑成功', url('index'));
                } else {
                    $this->error('编辑失败');
                }  
                
            }else{//添加

                $save['cids'] = implode(',', $data['cids']);
                $save['shopid'] = $id;
                if (db('shop_cate_access')->insert($save)) {
                    
                    // 记录行为
                    $this->success('新增成功', url('index'));
                } else {
                    $this->error('新增失败');
                }
            }
            
            
        }

        $info = CateAccessModel::where('shopid',$id)->find();

        $list_type = CategoryModel::where('status', 1)->column('id,title');

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('业务分类') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                
            ])
            ->addSelect('cids', '业务分类', '', $list_type,'','multiple')
            ->setFormData($info) // 设置表单数据
            ->fetch();
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
        $menu_title = StorytempModel::where('id', 'in', $ids)->column('mobile');
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
        $config  = AgencyModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['user_edit', 'shop_user', $id, UID, $details]);
    }
}