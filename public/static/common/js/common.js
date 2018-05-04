function setTab(obj, hoverObj, setObj, current, active, event) {
    var _this = 0;
    if(!active) {
        obj.find(setObj).hide();
        obj.find(setObj).eq(0).show();
    }
    obj.find(hoverObj).on(event, function() {
        _this = $(this).index();
        if(current) {
            $(this).addClass('on').siblings().removeClass('on');
        }
        if(active) {
            obj.find(setObj).eq(_this).removeClass('disnone').siblings().addClass('disnone');
        }else {
            obj.find(setObj).hide();
            obj.find(setObj).eq(_this).show()//.siblings('ul').hide();
        }
    })
}
setTab($('.tab-box'), '.tab-lst span', '.tab-mnc', 'on', null, 'mouseenter');
setTab($('.user-sdc-tabBox'), '.user-sdc-tabLst span', '.user-sdc-tabMnc', 'on', null, 'click');

$('.n-name').hover(function(){
    $('.down-link').slideDown();
}, function(){
    $('.down-link').stop(true,true).slideDown();
    $('.down-link').hide();
});
$('.upbtn').click(function(){
    $(this).parent().toggleClass('up-txt');
});
$('.abtxt').hover(function(){
    $('.abtwo').show();
},function(){
    $('.abtwo').hide();
});
//幻灯片左栏目
$('.bar-sd li').hover(function() {
    $(this).find('.bar-main').show();
}, function() {
    $(this).find('.bar-main').hide();
});
//meixiu
function chkPhone(phone){
    if(phone.length==0){
        return false;
    }
    if (phone.match(/^1[34578]\d{9}$/)) {
        return true;
    }
    return false;
}
function validatePwd(password){
    var pwdRule = /^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z\d@~$!%^*#?_\-+=\(<>\)&]{6,16}$/;
    if(!pwdRule.test(password)){
        return false;
    }else{
        return true;
    }
}

//筛选条件搜索
$("input.xint-key-ss").keyup(function(oEventData){
    if(oEventData.keyCode === 13){
        $("#btn-search").click();
    }
});
$("#btn-search").click(function(){
    var keyword=$("#ipt-val").val();
    var url=window.location.pathname;
    if(keyword.length>0 && keyword!="请输入搜索关键字"){
        var newurl=url+"?keyword="+keyword;
        window.location.href=newurl;
    }
    else{
        var newurl=url;
        window.location.href=newurl;
    }
});
function isIEBlowNine()
{
    var rv = -1; // Return value assumes failure.
    if (navigator.appName == 'Microsoft Internet Explorer')
    {
        var ua = navigator.userAgent;
        var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat( RegExp.$1 );
    }
    if(rv < 9){
        return true;
    }else{
        return false;
    }
}

timeSec = 120;
/*************注册 start****************/
function phoneBlur(phone) {
    if (chkPhone(phone)) {
        $(".phone-text").hide();
    } else {
        $(".phone-text").show();
        return false;
    }
    return true;
}

function passwordBlur(pwd) {
    if (!validatePwd(pwd) && pwd != '') {
        $(".password-text").show();
        $(".password1-text").children().addClass('cold7');
    } else {
        $(".password-text").hide();
        $(".password1-text").children().removeClass('cold7');
        return false;
    }
    return true;
}

function repasswordBlur(pwd, pwd_again) {
    if (pwd != pwd_again && pwd_again != '') {
        $(".repassword-text").show();
        return false;
    } else {
        $(".repassword-text").hide();
    }
    return true;
}
// //详情页左侧滚动导航
// var oNav = $('.leftnav');//导航壳
// var aNav = oNav.find('a');//导航
// var aDiv = $('.leftcont .item');//楼层
// //回到顶部
// $(window).scroll(function(){
//     var winH = $(window).height();//可视窗口高度
//     var iTop = $(window).scrollTop();//鼠标滚动的距离
//
//     if(iTop > 480){
//         oNav.removeClass('disnone');
//         //鼠标滑动式改变
//         aDiv.each(function(){
//             if(winH+iTop - $(this).offset().top+80 > winH/1){
//                 aNav.removeClass('on');
//                 aNav.eq($(this).index()).addClass('on');
//             }
//         })
//     }else{
//         oNav.addClass('disnone');
//     }
// })
// //点击回到当前楼层
// aNav.click(function(){
//     var t = aDiv.eq($(this).index()).offset().top-55;
//     $('body,html').scrollTop(t);
//     $(this).addClass('on').siblings().removeClass('on');
// });
// $('.proddtl-frul li:last-child').css('border','none')
//详情页左侧滚动导航
var oNav = $('.leftnav');//导航壳
var aNav = oNav.find('a');//导航
var aDiv = $('.leftcont .item');//楼层
//回到顶部
$(window).scroll(function(){
    var winH = $(window).height();//可视窗口高度
    var dinH = $(document).height();//窗口高度
    var iTop = $(window).scrollTop();//鼠标滚动的距离

    if(iTop > 480){
        oNav.removeClass('disnone');
        //鼠标滑动式改变
        aDiv.each(function(){
            if(winH+iTop - $(this).offset().top >= winH){
                aNav.removeClass('on');
                aNav.eq($(this).index()).addClass('on');
            }
        })
    }else{
        oNav.addClass('disnone');
    }
})
//点击回到当前楼层
aNav.click(function(){
    var t = aDiv.eq($(this).index()).offset().top;
    $('body,html').scrollTop(t);
    $(this).addClass('on').siblings().removeClass('on');
});
$('.proddtl-frul li:last-child').css('border','none')

//count down
function time(o, wait) {
    if (wait == 0) {
        o.attr('data-clickable', 2);
        o.text("获取验证码");
        wait = 60;
    } else {
        o.attr('data-clickable', 0);
        o.text(wait + "s后重发");
        if (wait == 30) {
            //provide a flag for its focus event to choose to show which div
            $('.register-verificationCode').attr('data-now', 30);
        }
        if (wait < 30) {
            $('.display-1').addClass("disnone");
            $('.display-2').removeClass("disnone");
        } else {
            $('.display-1').removeClass("disnone");
            $('.display-2').addClass("disnone");
        }
        wait--;
        setTimeout(function () {
            time(o, wait);
        }, 1000);
    }
}
$(document).ready(function(){
    //meixiu
    $("#phone").on('blur',function (oEventData) {
        var phone = $(this).val();
        phoneBlur(phone);
    });
    $('#password').on('blur', function (oEventData) {
        var pwd = $(this).val();
        passwordBlur(pwd);
    });
    $('#password').on('keyup', function (oEventData) {
        var pwd = $(this).val();
        var n = 0;
        pwd.match(/[a-z]/g) && n++;
        pwd.match(/[A-Z]/g) && n++;
        pwd.match(/[0-9]/g) && n++;
        pwd.match(/[^a-zA-Z0-9]/g) && n++;
        n = n > 3 ? 3 : n;
        pwd.length < 6 && n > 1 && (n = 1);
        var levelClass = ['', 'ps-one', 'ps-two', 'ps-three',''];
        // $('.password' + n).removeClass(levelClass[n]);
        $('.password' + n).addClass(levelClass[n]);
        for(var i=n;i<4;i++){
            $('.password' + n).nextAll().removeClass(levelClass[i]);
        }
        $('.password_grade').val(n);
    });

    $('#repassword').on('blur', function (oEventData) {
        var pwd_again = $(this).val();
        var pwd = $('#password').val();
        repasswordBlur(pwd, pwd_again)
    });

    $(".agreed-register").click(function () {
        if (!$("#chk_agreement").is(':checked')) {
            alert("请勾选确定为合格投资者");
            return false;
        }
        var obj = $("#register_form").serialize();
        $.ajax({
            url: "/user/register.html",
            type: 'post',
            data: obj,
            dataType: 'json',
            success: function (res) {
                $("input[name='tokenKey']").val(res.tokenKey);
                $("input[name='tokenValue']").val(res.tokenValue);
                if (res.valid == true) {
                    window.location.href = "/user/login.html";
                } else {
                    if(res.responseCode == 1){
                        $(".phone-text").show();
                        return false;
                    }else if(res.responseCode == 2){
                        $(".code-text").show();
                        return false;
                    }else if(res.responseCode == 3){
                        $(".password1-text").children().addClass('cold7');
                        return false;
                    }else if(res.responseCode == 4){
                        $(".repassword-text").show();
                        return false;
                    }else if(res.responseCode == 5){
                        $(".phone-text").show();
                        $(".phone-text").children().text('手机号码已注册');
                        return false;
                    }else{
                        alert(res.message);
                        return false;
                    }
                }
            }
        })
        // console.log($("register_form").serialize());
    })
});


// $(document).on("click", ".get-yzm", function () {
//     // $(".get-yzm").click(function(){
//     var phone = $("#phone").val();
//     if (!phoneBlur(phone)) {
//         $(".phone-text").show();
//         return false;
//     } else {
//         $(".phone-text").hide();
//     }
//     // setTimeout(function() {
//     //     countTime(timeSec)
//     // },1000);
//     // $(this).hide();
//     // $(".get-time").show();
//     $.ajax({
//         url: "/user/sendPhoneCode.html",
//         type: 'post',
//         data: {"phone": phone, "msgType": 1, 'usage': 1},
//         dataType: 'json',
//         success: function (oData) {
//             if (oData.result) {//验证通过
//                 // $('.pop-box.pop-captcha').hide();
//                 // $('.pop-by').addClass('disnone');
//                 //count down
//                 var o = $('.get-yzm');
//                 var wait = 120;
//                 time(o, wait);
//             }
//         }
//     })
// })
/*************注册 end****************/
/***************登录start***********/


$(document).ready(function(){
    $(".login").click(function () {
        if($(".password-text ").is(':visible') || $(".phone-text").is(':visible')){
            return false;
        }
        var href = window.location.href;
        // var flag = $(this).attr('data');
        var tiaozhuan = window.location.pathname;
        if(tiaozhuan != "/user/login.html"){
            var flag = 1;
        }else{
            var flag = 0;
        }
        var obj = $("#login_form").serialize();
        var myurl=window.location.search;
       var jumpUrl = "/user/login.html"+myurl;
        $.ajax({
            url: jumpUrl,
            type: 'post',
            data: obj,
            dataType: 'json',
            success: function (res) {
                $("input[name='tokenKey']").val(res.tokenKey);
                $("input[name='tokenValue']").val(res.tokenValue);
                if (res.valid == true) {
                    if(flag == 1){
                        window.location.href = href;
                    }else {
                        if(res.jump != null){
                            window.location.href = res.jump;
                        }else{
                            window.location.href = "/user/index.html";
                        }

                    }
                } else {
                    if(flag == 1) {
                        if (!res.login_failed) {
                            $(".password-text").html('<p class="f12 cold7">'+res.message+'</p>');
                            // $(".id-open").text(res.message);
                            $(".password-text").show();
                        } else {
                            if(res.login_failed < 5) {
                                $(".failed_time").text(parseInt(res.login_failed) + 1);
                                $(".id-open").show();
                            }else{
                                $(".id-open").html(res.message);
                                $(".id-open").show();
                            }
                        }
                    }else{
                        if (res.code == 4 || !res.login_failed) {
                            $(".ct-open").text(res.message);
                            $(".ct-open").show();
                        } else {
                            $(".failed_time").text(parseInt(res.login_failed)+1);
                            $(".ct-open").show();
                        }
                    }
                }
            }
        })
    });
});

/***************登录end***********/

//全局搜索
$(".head-sch-ipt").keyup(function(oEventData){
    if(oEventData.keyCode === 13){
        $(".head-sch-mn>.head-btn").click();
    }
});
$(document).on('click','.head-sshints p',function(){
    var text = $(this).text();
    //$(".head-sshints").hide();
    $("#head-search").val(text);
    $(".head-sch-mn>.head-btn").click();
});
if($(".head-sch-mn>.head-btn").length > 0){
    $(".head-sch-mn>.head-btn").on('click',function(oEventData){
        var $searchInput = $(".head-sch-ipt");
        var val = $searchInput.val();
        var val = $.trim(val);
        var sInput = encodeURI(val);
        if(val.length > 0) {
            var sInput = encodeURI(val);
        }else{
            alert("请输入搜索关键字");
            return false;
        }
        if(val.length > 0 &&val != "请输入搜索关键字") {
            window.location.href = '/search/index.html?key=' + sInput;
        }
    });
}
//验证手机号是否正确
function validatePhone(mobileNumber) {
    if(mobileNumber.length==0){
        return false;
    }
    if (mobileNumber.match(/^1[34578]\d{9}$/)) {
        return true;
    }
    return false;
};

function validatePwd(password){
    var pwdRule = /^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z\d@~$!%^*#?_\-+=\(<>\)&]{6,16}$/;
    if(!pwdRule.test(password)){
        return false;
    }else{
        return true;
    }
}
//moming
//滚动导航条
$(document).ready(function(oEvent){
    var viHeight = $(window).height();
    var myHeight = $(document).height();
    if(viHeight >= myHeight ){
        $('.r-spnav,.n-nav').removeClass('disnone');
    }
    var userBot = $('.user-foot-two').height();
    $('.x-txt').css({
        minHeight:viHeight-userBot-318
    })
});
$(window).scroll(function() {
    var sorTop = $(window).scrollTop();
    //console.log(sorTop);
    if (sorTop > 203) {
        $('.n-nav').removeClass('disnone');
    } else {
        $('.n-nav').addClass('disnone');
    }
    if(sorTop >= 20 ){
        $('.r-spnav').removeClass('disnone');
    }
    else{
        $('.r-spnav').addClass('disnone');
    }
})
$('.focus-my span').hover(function () {
    $(this).children('img').show();
}, function () {
    $(this).children('img').hide();
})

$('.r-spnav p').click(function(){
    $("html,body").animate({scrollTop:0},1000)
})
$('.n-name').hover(function(){
    $('.down-link').slideDown();
}, function(){
    $('.down-link').stop(true,true).slideDown();
    $('.down-link').hide();
});
$('.upbtn').click(function(){
    $(this).parent().toggleClass('up-txt');
})
$('.abtxt').hover(function(){
    $('.abtwo').show();
},function(){
    $('.abtwo').hide();
})
//幻灯片左栏目
$('.bar-sd li').hover(function() {
    $(this).find('.bar-main').show();
}, function() {
    $(this).find('.bar-main').hide();
})
//预约咨询--------------------start-----------
$(document).ready(function(){
    $('.book-submit-btn').bind('click', function(oEventData){
        var phoneRule = /^1[34578]\d{9}$/;
        var nameRule=/^[\u4E00-\u9FA5]+$/;
        var $parent = $(this).parent();
        var $self = $(this);
        var phoneNumber = $('.bookMobileNum').val();
        var phoneNumber = $.trim(phoneNumber);
        var bookNumber = parseInt($(this).attr('bookNum'));
        var sName = $('.bookName').val();;
        var sName = $.trim(sName);
        var invalid = false;


        var fnCallback = function(oData){
            if(oData.success){
                $parent.find(".bookName").val('');
                $parent.find(".bookMobileNum").val('');
                $(".appoiment-by").show();
                $(".appoiment-box").show();
                // $(".open-cg.submit-info").show();
                // $(".zhezhao").addClass('disnone');
                // freezeScroll(true);
                // $(".mobile-book").addClass('disnone');
                // setTimeout(function(){
                //     $(".open-cg.submit-info").hide();
                // }, 2000);
            }else{
                $(".open-cg.submit-info").text(oData.message||'预约失败');
                $(".open-cg.submit-info").show();
                $(".mobile-book").addClass('disnone');
                setTimeout(function(){
                    $(".open-cg.submit-info").hide();
                }, 2000);
            }
            //TODO should be a popup to notify users.
        };
        if(!phoneRule.test(phoneNumber)){
            $parent.find(".book-phone-error").show();
            invalid = true;
        }
        if(!nameRule.test(sName) || sName == '请输入您的中文姓名' || sName == '请输入您的姓名'){
            $parent.find(".book-name-error").show();
            invalid = true;
        }
        if(invalid){
            return;
        }

        var oExtraInfo = extractBookData($self);

        var oBookInfo = {
            'phone' : phoneNumber,
            'name' : sName
        };
        oBookInfo = $.extend({},oBookInfo,oExtraInfo);
        oBookInfo['logined'] = true;
        window.booking.submitBookInfo(oBookInfo,fnCallback);
    });
    //点击预约，显示弹窗

    $('.book-consult').click(function(){
        $('.mobile-book').removeClass('disnone');
        $('.zhezhao').removeClass("disnone");
        freezeScroll(true);
    });
    $('.bookName').on('blur',function(){
        var nameRule=/^[\u4E00-\u9FA5]+$/;
        var nameValue = $.trim($(this).val());
        var $parent = $(this).parent();
        if(!nameRule.test(nameValue) || nameValue == '请输入您的中文姓名' || nameValue == '请输入您的姓名'){
            $parent.find(".book-name-error").show();
            return;
        }
    })
    $('.bookName').on('focus',function(){
        var $parent = $(this).parent();
        $parent.find(".book-name-error").hide();
    })
    $('.bookMobileNum').on('blur',function(){
        var phoneRule = /^1[34578]\d{9}$/;
        var phoneNumber = $(this).val();
        var phoneNumber = $.trim(phoneNumber);
        var $parent = $(this).parent();
        if(!phoneRule.test(phoneNumber)){
            $parent.find(".book-phone-error").show();
            return;
        }
    })
    $('.bookMobileNum').on('focus',function(){
        var $parent = $(this).parent();
        $parent.find(".book-phone-error").hide();
    })
    $(".appoiment-close").on('click',function(){
        $(".appoiment-by").hide();
        $(".appoiment-box").hide();
    })
});

function extractBookData($button){
    var id = $button.attr("data-id");
    var bookNumber = parseInt($button.attr('bookNum'));
    var sCategoryType = $button.attr('data-category-type');
    if(!sCategoryType){
        var sURL = window.location.href;
        if(sURL.indexOf('/simu/') !== -1){
            sCategoryType = 'sf_fund_name';
        }else if(sURL.indexOf('/xintuo/') !== -1){
            sCategoryType = 'trust_short_name';
        }else if(sURL.indexOf('/pe/') !== -1){
            sCategoryType = 'pe_short_name';
        }
//            else if(sURL.indexOf('/insurance/') !== -1){
//                type = '香港保险';
//            }
    }
    if(!sCategoryType){
        sCategoryType = '';
    }

    var sTitle = $button.attr('data-title');
    if(!sTitle){
        sTitle = $('title').text();
    }
    var iOrderType = 0;
    if(!!$button.attr('data-order-type')){
        iOrderType = $button.attr('data-order-type');
    }
    return {
        'number' : bookNumber,
        'id' : id,
        'title' : sTitle,
        'categoryType': sCategoryType,
        'orderType' : iOrderType
    };
}
$(window).resize(function(){
    $('.public-open').css({
        position:'fixed',
        left: ($(window).width() - $('.public-open').outerWidth())/2,
        top: ($(window).height() - $('.public-open').outerHeight())/2
    });
});
$(window).resize(function(){
    $('.public-open1').css({
        position:'fixed',
        left: ($(window).width() - $('.public-open1').outerWidth())/2,
        top: ($(window).height() - $('.public-open1').outerHeight())/2
    });
});
$(window).resize();
$(".feedback-content").focus(function(){
    $(".error-mes").hide();
})
$(".submit_feedback").click(function(){
    var len = $(".feedback-content ").val().length;
    if(len < 5){
        $(".error-mes").show();
        return false;
    }
    var obj = $(".feedback-form").serialize();
    $.ajax({
        url: "/feedback/index.html",
        type: 'post',
        data: obj,
        dataType: 'json',
        success: function (res) {
            $("input[name='tokenKey']").val(res.tokenKey);
            $("input[name='tokenValue']").val(res.tokenValue);
            if (res.code == 0) {

                    window.location.href = "/feedback/reaction.html";

            } else {
                if(flag == 1) {
                    if (!res.login_failed) {
                        $(".password-text").html('<p class="f12 cold7">'+res.message+'</p>');
                        // $(".id-open").text(res.message);
                        $(".password-text").show();
                    } else {
                        if(res.login_failed < 5) {
                            $(".failed_time").text(res.login_failed + 1);
                            $(".id-open").show();
                        }else{
                            $(".id-open").html(res.message);
                            $(".id-open").show();
                        }
                    }
                }else{
                    if (res.code == 4 || !res.login_failed) {
                        $(".ct-open").text(res.message);
                        $(".ct-open").show();
                    } else {
                        $(".failed_time").text(res.login_failed+1);
                        $(".ct-open").show();
                    }
                }
            }
        }
    })
})
$('.picCode-check').click(function(){
    var picCodeValue = $("input[name='picCode']").val();
    var phone = $("input[name='phone']").val();
    if(picCodeValue.length !=4){
        $('.code_msg').text('图形验证码错误！');
        $('.code_msg').show();
        return false;
    }

    $.ajax({
        url:'/user/codeCheck.html',
        data:{picCode:picCodeValue,phone:phone},
        dataType:'json',
        type:'post',
        success:function(oData){
            if(oData.result){//验证通过
                $('.pop-yzm').hide();
                $('.pop-by').hide();
                //count down
                // var o = $('.user-form-yzm');
                _requestPhoneCode(phone);
            }else{
                $('.code_msg').text('图形验证码错误！');
                $('.code_msg').show();
                return false;
            };
        }
    })
});
$(".picCode").on('focus',function(){
    $(".code_msg").hide();
})
$(".close-icon").on('click',function(){
    $(".pop-by").hide();
    $(".pop-yzm").hide();
    $(".pop-loading").hide();
})
$('.pic-code-check').on('blur',function(oEventData){
    var picCodeValue = $("input[name='picCode']").val();
    if(picCodeValue !=''){
        if(picCodeValue.length != 4){
            $('.code_msg').text('图形验证码错误！');
            $('.code_msg').show();
        }else{
            $('.code_msg').hide();
        }
    }else{
        $('.code_msg').hide();
    }
});
$('.showcode').click(function(oEventData){
    var $self = $(oEventData.target);
    if($self.attr('data-clickable') === '0'){
        return false;
    }
    //check phone is null or not
    var phone = $("input[name='phone']").val();
    if(parseInt(phone) != phone || !validatePhone(phone)){
        $(".notice1").addClass('disnone');
        if(phone.length == 0){
            $(".user-error.phoneNum p").text('请填写手机号码');
        }else{
            $(".user-error.phoneNum p").text('手机号码格式不正确');
        }
        $(".user-error.phoneNum").removeClass('disnone');
        return false;
    }
    //get and check picture verification code
    var $self = $(this);
    if($self.attr('data-clickable') === '1' && $self.attr('data-code-need') === '1'){
        $(".pop-by").show();
        $(".pop-yzm").show();
        var sSrc = _getVCodeSrc();
        $('.phone-vcode').attr('src',sSrc);;
        $('.pop-box.pop-captcha').show();$('.pic-code-check').focus();
        return ;
    }

    //if data-clickable equals 2 means there is no needs to pop the vcode pic dialog
    if($self.attr('data-clickable') === '2'){
        //ajax request
        var phone = $("input[name='phone']").val();
        _requestPhoneCode(phone);
    }
});
function _getVCodeSrc(){
    var sSrc = $('.phone-vcode').attr('src');
    var aSrc = sSrc.split('&random');
    sSrc = aSrc[0]+'&random='+Math.random();
    return sSrc;
}
function _requestPhoneCode(phone){
//发送验证码
// $(document).on("click", ".get-yzm", function () {

    // $(".get-yzm").click(function(){
    // var phone = $("#phone").val();
    if (!phoneBlur(phone)) {
        $(".phone-text").show();
        return false;
    } else {
        $(".phone-text").hide();
    }
    $.ajax({
        url: "/user/sendPhoneCode.html",
        type: 'post',
        data: {"phone": phone, "msgType": 1, 'usage': 1},
        dataType: 'json',
        success: function (oData) {
            if (oData.result) {//验证通过
                // $('.pop-box.pop-captcha').hide();
                // $('.pop-by').addClass('disnone');
                //count down
                var o = $('.get-yzm');
                var wait = 120;
                time(o, wait);
            }
        }
    })
}

/*$(document).ready(function(){
    $('.public-open').click(function(){
        $('.public-open').hide();
        $('.box-black').hide();
        document.cookie="openEmailKnew=1";
    })
    var strCookie=document.cookie;
    var arrCookie=strCookie.split("; ");
    var openEmailKnew;
    for(var i=0;i<arrCookie.length;i++){
        var arr=arrCookie[i].split("=");
        if("openEmailKnew"==arr[0]){
            openEmailKnew=arr[1];
            break;
        }
    }
    if(openEmailKnew ==1){
        $('.public-open').hide();
        $('.box-black').hide();
    }
});*/
//meixiu
setTab($('.xt-knowl-box'), '.xt-knowl-tab span', '.xt-knowl-tabmnc', 'on', null, 'click');
setTab($('.sgt-lstbox'), '.xt-knowl-tab span', '.sgt-lstmnc', 'on', null, 'click');
setTab($('.search-box'), '.search-tab span', '.search-tabmnc', 'on', null, 'click');
setTab($('.search-box2'), '.search-tab span', '.search-tabmnc2', 'on', null, 'click');
setTab($('.search-box3'), '.search-tab span', '.search-tabmnc3', 'on', null, 'click');
setTab($('.searchjx-box'), '.searchjx-tab span', '.searchjx-tabmnc', 'on', null, 'click');
//jing
setTab($('.l-achivtbbox'), '.l-achivtb span', '.l-achivtbmn', 'on', null, 'click');
setTab($('.l-yg-tabbox'), '.l-yg-tab span', '.l-yg-tabmn', 'on', null, 'click');

//右侧微信
$(document).ready(function(){
    $('.r-wechat-close,.r-wechat-pic').click(function(){
        $('.r-wechat-main,.r-wechat-pic').toggleClass('disnone');
    })
    //2017.12.01 视频弹框
    $('.news-video-go').click(function(){
        $('.video-pop,.box-black').show();
    });
    $('.box-black,.video-pop-close').click(function(){
        $('.video-pop,.box-black').hide();
    });
    //首页 登录注册
    // $('.loginReg-switch-toggle').click(function(){
    //     if($(this).hasClass('on')){
    //         $('.loginReg-switch').hide();
    //         $('.loginReg-switch').eq(0).show();
    //         $(this).removeClass('on').html('立即注册');
    //     }else{
    //         $('.loginReg-switch').hide();
    //         $('.loginReg-switch').eq(1).show();
    //         $(this).addClass('on').html('登录')
    //     }
    // })
});

