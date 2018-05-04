<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Item as ItemModel;
use app\cms\model\Category as CategoryModel;
use util\Tree;
use think\Db;

/**
 * 商品控制器
 * @package app\cms\admin
 */
class Item extends Admin
{
    /**
     * 菜单列表
     * @param null $id 导航id
     * @author zg
     * @return mixed
     */
    public function index($id = null)
    {
        // 查询
        $map = $this->getMap();

        // 数据列表
        $data_list = Db::view('cms_item', true)
            ->order('cms_item.id')
            ->select();

        $list_module = CategoryModel::where(1)->order('id desc')->column('id,title');

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['cate_id', '类型','','',$list_module],
                ['title', '产品名'],
                ['rate', '预期年化'],
                ['issuer', '发行机构'],
                ['term', '投资期限'],
                ['interest', '付息方式'],
                ['riseamount', '起投金额'],
                ['scale', '募集规模'],
                ['direction', '投资方向'],
                ['recruitment', '募集状态', 'text', '', ['在售', '售罄', '停售']],
                ['site', '项目所在地'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('add', ['href' => url('add')])
            // ->addTopButtons('enable,disable')// 批量添加顶部按钮
            ->addRightButton('edit')
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
            $result = $this->validate($data, 'Item');
            if(true !== $result) $this->error($result);

            if ($menu = ItemModel::create($data)) {
                // 记录行为
                action_log('item_add', 'cms_item', $menu['id'], UID, $data['title']);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '产品名', ''],
                ['text', 'sub_title', '产品全名', ''],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1],
                ['select', 'cate_id', '分类', '<code>必选</code>', CategoryModel::getTreeLists()],
                ['text', 'rate', '预期年化收益率', ''],
                ['text', 'issuer', '发行机构', ''],
                ['text', 'term', '投资期限', ''],
                ['text', 'interest', '付息方式', ''],
                ['text', 'riseamount', '起投金额', ''],
                ['text', 'scale', '募集规模', ''],
                ['text', 'direction', '投资方向', ''],
                ['text', 'site', '项目所在地', ''],
                ['radio', 'recruitment', '募集状态', '', ['在售', '售罄', '停售'], 0]
            ])
            ->addTextarea( 'income','收益详情说明')
            ->addText('collect', '募集账户')
            ->addTextarea( 'capital','资金投向')
            ->addTextarea( 'payment','还款来源')
            ->addTextarea( 'measures','风控措施')
            ->addWangeditor('other', '其他说明')
            ->addTextarea( 'issuerintr','发行机构说明')
            ->addWangeditor('subscription', '认购流程')
            ->fetch();
    }

    /**
     * 编辑
     * @param null $id id
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
            $result = $this->validate($data, 'Item');
            if(true !== $result) $this->error($result);

            if (ItemModel::update($data)) {
                // 记录行为
                action_log('item_edit', 'cms_item', $id, UID, $data['title']);
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '产品名', ''],
                ['text', 'sub_title', '产品全名', ''],
                ['radio', 'status', '立即启用', '', ['否', '是'], 1],
                ['select', 'cate_id', '分类', '<code>必选</code>', CategoryModel::getTreeLists()],
                ['text', 'rate', '预期年化收益率', ''],
                ['text', 'issuer', '发行机构', ''],
                ['text', 'term', '投资期限', ''],
                ['text', 'interest', '付息方式', ''],
                ['text', 'riseamount', '起投金额', ''],
                ['text', 'scale', '募集规模', ''],
                ['text', 'direction', '投资方向', ''],
                ['text', 'site', '项目所在地', ''],
                ['radio', 'recruitment', '募集状态', '', ['在售', '售罄', '停售']]
                
            ])
            ->addTextarea( 'income','收益详情说明')
            ->addText('collect', '募集账户')
            ->addTextarea( 'capital','资金投向')
            ->addTextarea( 'payment','还款来源')
            ->addTextarea( 'measures','风控措施')
            ->addWangeditor('other', '其他说明')
            ->addTextarea( 'issuerintr','发行机构说明')
            ->addWangeditor('subscription', '认购流程')
            ->setFormData(ItemModel::get($id))
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
        $menu_title = ItemModel::where('id', 'in', $ids)->column('title');
        return parent::setStatus($type, ['item_'.$type, 'cms_item', 0, UID, implode('、', $menu_title)]);
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
        $menu    = ItemModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $menu . ')，新值：(' . $value . ')';
        return parent::quickEdit(['item_edit', 'cms_item', $id, UID, $details]);
    }
}