var config = {
    // server:'http://47.97.97.95/api.php/',
    // imgser:'http://47.97.97.95',
    server:'http://zg.daguan.com/api.php',
    imgser:'http://zg.daguan.com',
    source: 'custom', //来源
    pagesize: 10, // 分页组件每页显示数量
    cpage: 1, //分页当前页
    apimethod: { //接口method集合
        login: 'user.login', //用户登录
        logout: 'user.logout', //用户登出
        register: 'user.register', //注册
        vcode: 'user.vcode', //验证码
        confirm: 'user.confirm', //注册验证手机
        userinfo: 'user.userinfo', //我的、
        article: 'home.article', //好文推荐
        lunbo: 'home.lunbo', //广告
        category: 'counsellor.category', //咨询分类
        recommend: 'counsellor.recommend', //推荐咨询师
        counsellor: 'counsellor.info', //咨询师
        ondate: 'counsellor.ondate', //预约
        point: 'user.point', //积分明细
        trade: 'user.trade',//订单信息
        updateloginpwd:'user.uppw',//更新密码
        checkpassword:'user.checkpassword',//检查密码
        updatenickname:'user.updatenickname',//更新密码
        updategender:'user.updategender',//检查密码
        upavar:'user.upavar',//
        contentinfo:'article.contentinfo',//文章
        allcategory: 'all.category', //咨询分类
        counsellorlist:'counsellor.list',//咨询列表
        articallist:'artical.list',//咨询列表
        agency:'agency.list',//机构列表
        createTrade:'create.trade',//生成订单
        tradepay:'trade.pay'//支付
    }
}
