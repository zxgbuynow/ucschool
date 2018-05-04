<?php


namespace app\cms\validate;

use think\Validate;

/**
 * 验证器
 * @package app\cms\validate
 */
class Item extends Validate
{
    //定义验证规则
    protected $rule = [
        'cate_id|类目ID'      => 'require',
        'title|产品全称'      => 'require',
        'rate|预期年化收益率'      => 'require',
        'issuer|发行机构'      => 'require',
        'term|投资期限'      => 'require',
        'interest|付息方式'      => 'require',
        'riseamount|起投金额'      => 'require',
        'scale|募集规模'      => 'require',
        'direction|投资方向'      => 'require',
        'recruitment|募集状态'      => 'require',
        'site|项目所在地'      => 'require',
        'income|收益详情说明'      => 'require',
        'collect|募集账户'      => 'require',
        'capital|资金投向'      => 'require',
        'payment|还款来源'      => 'require',
        'measures|风控措施'      => 'require',
        'other|其他说明'      => 'require',
        'issuerintr|发行机构说明'      => 'require',
        'subscription|认购流程'      => 'require',
    ];

    //定义验证提示
    protected $message = [
        'cate_id.require' => '1必填项不能为空',
        'title.require' => '2必填项不能为空',
        'rate.require' => '3必填项不能为空',
        'issuer.require' => '4必填项不能为空',
        'term.require' => '5必填项不能为空',
        'interest.require' => '6必填项不能为空',
        'riseamount.require' => '7必填项不能为空',
        'scale.require' => '8必填项不能为空',
        'title.require' => '9必填项不能为空',
        'direction.require' => '10必填项不能为空',
        'recruitment.require' => '11必填项不能为空',
        'income.require' => '12必填项不能为空',
        'collect.require' => '13必填项不能为空',
        'capital.require' => '14必填项不能为空',
        'payment.require' => '15必填项不能为空',
        'measures.require' => '16必填项不能为空',
        'other.require' => '17必填项不能为空',
        'issuerintr.require' => '18必填项不能为空',
        'subscription.require' => '19必填项不能为空',
    ];
}
