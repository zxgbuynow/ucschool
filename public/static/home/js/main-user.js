var base = document.getElementById('x-base') ? document.getElementById('x-base').value : 'scredit-web';
var resRoot = document.getElementById('x-resRoot') ? document.getElementById('x-resRoot').value : '/static/common';
require.config({
	baseUrl : resRoot + '/js/',
	paths : {
		jquery : 'jquery',
		common : 'common',
		jqueryScroll:'scroll',
        jQueryUi : 'jquery-ui/jquery-ui.min',
		highcharts:'highstock',
		wowmin:'wow.min',
		calendar: 'calendar',
        ajaxFileUpload:'ajaxFileUpload',
        placeholder : 'placeholder'
	},
	shim : {
		'common' : {
			deps : [ 'jquery']
		},
		'jqueryScroll' : {
			deps : [ 'jquery']
		},
        'jQueryUi' : {
            deps : ['jquery']
        },
		'highstock' : {
			deps : [ 'jquery']
		},
		'wowmin' : {
			deps : [ 'jquery']
		},
		'calendar': {
			deps : ['jquery']
		},
        'ajaxFileUpload' : {
            deps : ['jquery']
        },
        'placeholder' : {
			deps : [ 'jquery']
		}
	}
});



require([ 'jquery','common','jqueryScroll','jQueryUi','highstock','wowmin','calendar','ajaxFileUpload','placeholder'], function($) {
    //placeholder
    $(function(){ $('input').placeholder();});
    //initialize the datepicker
    $.datepicker.regional["zh-CN"] = { closeText: "关闭", prevText: "&#x3c;上月", nextText: "下月&#x3e;", currentText: "今天", monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"], monthNamesShort: ["一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二"], dayNames: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"], dayNamesShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"], dayNamesMin: ["日", "一", "二", "三", "四", "五", "六"], weekHeader: "周", dateFormat: "yy-mm-dd", firstDay: 1, isRTL: !1, showMonthAfterYear: !0, yearSuffix: "年" };
    $.datepicker.setDefaults($.datepicker.regional["zh-CN"]);

    /*============================================================*/
    //日历页面
    var myDate = new Date();
    var currentdate = myDate.getFullYear() + "." + (myDate.getMonth()+1) + "." + myDate.getDate();
    $(".start-time").datepicker({
        showOn: "button",
        changeYear: true,
        changeMonth: true,
        buttonImage: "/static/common/images/user/time-ico.png",
        buttonImageOnly: true,
        buttonText: "Select date",
        dateFormat: 'yy.mm.dd',
        onSelect: function (selectedDate) { //选择日期后执行的操作
            var start_time = $("input[name='start_time']").val();
            var url = window.location.pathname;
            var search = window.location.search;
            if (search) {
                var searchname = search.substring(1);
                if (search.indexOf("start_time") > -1) {
                    var newArr = [];
                    var arr = searchname.split("&");
                    arr.forEach(function (value) {
                        if (value.indexOf("num") <= -1) {
                            if (value.indexOf("start_time") > -1) {
                                value = "start_time=" + start_time;
                            }
                            newArr.push(value);
                        }
                    })
                    var newSearch = newArr.join("&");
                    var newurl = url + "?" + newSearch;
                } else {
                    var newurl = url + "?start_time=" + start_time + "&" + searchname;
                }
            } else {
                var newurl = url + "?start_time=" + start_time;
            }
            window.location.href = newurl;
        }
    });
    $(".end-time").datepicker({
        showOn: "button",
        changeYear: true,
        changeMonth: true,
        buttonImage: "/static/common/images/user/time-ico.png",
        buttonImageOnly: true,
        buttonText: "Select date",
        dateFormat: 'yy.mm.dd',
        maxDate: currentdate,
        onSelect: function (selectedDate) { //选择日期后执行的操作
            var end_time = $("input[name='end_time']").val();
            var url = window.location.pathname;
            var search = window.location.search;
            if (search) {
                var searchname = search.substring(1);
                if (search.indexOf("end_time") > -1) {
                    var newArr = [];
                    var arr = searchname.split("&");
                    arr.forEach(function (value) {
                        if (value.indexOf("num") <= -1) {
                            if (value.indexOf("end_time") > -1) {
                                value = "end_time=" + end_time;
                            }
                            newArr.push(value);
                        }
                    })
                    var newSearch = newArr.join("&");
                    var newurl = url + "?" + newSearch;
                } else {
                    var newurl = url + "?end_time=" + end_time + "&" + searchname;
                }
            } else {
                var newurl = url + "?end_time=" + end_time;
            }

            window.location.href = newurl;
        }
    });
    //日历结束
    /*============================================================*/
    /**
    * 日历和资金详情
    */
    $(".int-month a").click(function () {
        var num = $(this).index() + 1;
        var url = window.location.pathname;
        var search = window.location.search;
        if (search) {
            var searchname = search.substring(1);
            if (search.indexOf("num") > -1) {
                var newArr = [];
                var arr = searchname.split("&");
                arr.forEach(function (value) {
                    if (value.indexOf("num") > -1) {
                        value = "num=" + num;
                    }
                    newArr.push(value);
                })
                var newSearch = newArr.join("&");
                var newurl = url + "?" + newSearch;
            } else {
                var newurl = url + "?num=" + num + "&" + searchname;
            }
        } else {
            var newurl = url + "?num=" + num;
        }
        window.location.href = newurl;
    });

    //日历查看更多事件
    $(".more_events").click(function () {
        var currentDate = $("#event_remind").attr('data-current');
        var arr = [];
        arr = currentDate.split('年');
        var year = arr[0];
        var month = arr[1].split('月')[0];
        var last_day  = new Date(year, month, 0).getDate();

        var end_time = year +'.' +month+ '.'+last_day;
        var start_time = year + '.' +month +'.1';
        var url = '/user/calendar.html?start_time=' + start_time +'&end_time='+ end_time;
        window.location.href = url;
    });


    $("#calen").calendar({
        width: 280,
        height: 219,
        view: 'month',
        label: '{m}\n{v}',
        onSelected: function(view, date) {
            var last_query_date = $('#calen').attr('data-query-date');//上一个查询的日期
            var currentDate = new Date(date).format("yyyy-MM");
            if( last_query_date == currentDate){
                return false;
            }else{
                $('#calen').attr('data-query-date',currentDate);
            }
            var isSameYear = 1;//用于判断当前查询的年份是否与之前查询年份为同一年,触发这个方法的肯定是同一年
            var currentMonth = currentDate.substr(5,7);
            if(currentMonth <10){
                currentMonth = currentMonth.substr(1,2);
            }
            $($('.month-items>li')[currentMonth-1]).addClass('now').siblings().removeClass('now');
            $('#event_remind').html(new Date(date).format("yyyy年M月")+'事项提醒');
            $('#event_remind').attr('data-current',new Date(date).format("yyyy年M月"));
            $.ajax({
                url:'/user/investCalendar.html',
                data:{queryDate:currentDate,isSameYear:isSameYear},
                dataType:'json',
                type:'post',
                success:function(data){
                    if(data.code && _isSessionOut(data.code)){
                        return _handleRequestCode();
                    }
                    if(data.currentMonthInfo && data.currentMonthInfo.length>0){
                        var str = '';
                        var html = '';
                        $.each(data.currentMonthInfo,function(index,obj){
                            $('.t-remind ul').remove();
                            str =   '<ul>' +
                                    ' <li class="br-bottom"><p>'+obj.event_content+'</p>'+
                                    '<span class="the-future">'+obj.event_date+'</span>'+
                                    '</li>'+
                                    '</ul>';
                            html += str ;
                        });
                        $('.n-remind.fr').addClass('disnone');
                        $('.t-remind.fr').removeClass('disnone');
                        $('#event_remind').after(html);
                    }else{
                        $('.t-remind.fr').addClass('disnone');
                        $('.n-remind.fr').removeClass('disnone');
                    }
                }
            });
        },
        onYearSwitched:function(view,date){
            $('#calen').attr('data-query-date',date);
            //年份切换不存在相同的情况，所以肯定是false
            var isSameYear = 0;//参数不能少
            //默认选择当前年的一月
            var currentDate = date + '-01';
            $('.month-items>li').removeClass();
            $('.month-items>li a i').removeClass();
            $($('.month-items>li')[0]).addClass('now');
            $('#event_remind').attr('data-current',new Date(currentDate).format("yyyy年M月"));
            var now =  new Date().getFullYear();
            var currentMonth = new Date().getMonth() + 1;
            $.ajax({
                url:'/user/investCalendar.html',
                data:{queryDate:currentDate,isSameYear:isSameYear},
                dataType:'json',
                type:'post',
                success:function(data){
                    if(data.code && _isSessionOut(data.code)){
                        return _handleRequestCode();
                    }
                    $('.month-items>li a i').removeClass('red-border');
                    if(data.currentYearInfo.length>0){
                        $($('.month-items>li')[0]).addClass('now');
                        if(now ==date){//转回到当前年
                            $.each(data.currentYearInfo,function(idx,obj){
                                var index = obj.event_month -1;
                                if(parseInt(obj.event_month) > parseInt(currentMonth)){
                                    $($('.month-items>li a i')[index]).addClass('red-border');
                                }else if(parseInt(obj.event_month) < parseInt(currentMonth)){
                                    $($('.month-items>li')[index]).addClass('before');
                                }else{
                                    $($('.month-items>li a i')[index]).addClass('red-border');
                                    $('#event_remind').html(new Date(currentDate).format("yyyy年M月")+'事项提醒');
                                }
                            });
                        }else if(now <date){//未来的年份,所有事件月份的显示都为‘class=red-border’
                            $.each(data.currentYearInfo,function(idx,obj){
                                var index = obj.event_month -1;
                                $($('.month-items>li a i')[index]).addClass('red-border');
                            });
                        }else{//过去的年份，所有事件月份的显示都为‘class=before’
                            $.each(data.currentYearInfo,function(idx,obj){
                                var index = obj.event_month -1;
                                $($('.month-items>li')[index]).addClass('before');
                            });
                        }
                    }
                    if(data.currentMonthInfo.length>0){
                        var str = '';
                        var html = '';
                        $.each(data.currentMonthInfo,function(index,obj){
                            $('.t-remind ul').remove();
                            str =   '<ul>' +
                                    ' <li class="br-bottom"><p>'+obj.event_content+'</p>'+
                                    '<span class="the-future">'+obj.event_date+'</span>'+
                                    '</li>'+
                                    '</ul>';
                            html += str ;
                        });
                        $('.n-remind.fr').addClass('disnone');
                        $('.t-remind.fr').removeClass('disnone');
                        $('#event_remind').after(html);
                    }else{
                        $('.t-remind.fr').addClass('disnone');
                        $('.n-remind.fr').removeClass('disnone');
                    }
                }
            });

        },
        onBackToCurrent: function(view,date){
            var last_query_date = $('#calen').attr('data-query-date');//上一个查询的日期
            var currentDate ;
            if(new Date().getMonth() <9){
                currentDate = new Date().getFullYear() + '-0' + (new Date().getMonth()+1);
            }else{
                currentDate = new Date().getFullYear() + '-' +(new Date().getMonth()+1);
            }
            if( last_query_date == currentDate){
                return false;
            }else{
                $('#calen').attr('data-query-date',currentDate);
            };
            var isSameYear = 0;//用于判断当前查询的年份是否与之前查询年份为同一年
            var last_query_year = last_query_date.substr(0,4);
            var currentYear = currentDate.substr(0,4);
            var currentMonth = currentDate.substr(5,7);
            if(currentMonth <10){
                currentMonth = currentMonth.substr(1,2);
            }
            if(last_query_year == currentYear){
                $isSameYear = 1;
            }
            $('#event_remind').attr('data-current',new Date(currentDate).format("yyyy年M月"));

            $.ajax({
                url:'/user/investCalendar.html',
                data:{queryDate:currentDate,isSameYear:isSameYear},
                dataType:'json',
                type:'post',
                success:function(data){
                    if(data.code && _isSessionOut(data.code)){
                        return _handleRequestCode();
                    }
                    $('.month-items>li a i').removeClass('red-border');
                    if(data.currentYearInfo.length>0){
                        $.each(data.currentYearInfo,function(idx,obj){
                            var index = obj.event_month -1;
                            if(parseInt(obj.event_month) > parseInt(currentMonth)){
                                $($('.month-items>li a i')[index]).addClass('red-border');
                            }else if(parseInt(obj.event_month) < parseInt(currentMonth)){
                                $($('.month-items>li')[index]).addClass('before');
                            }else{
                                $($('.month-items>li')[index]).addClass('now');
                                $($('.month-items>li a i')[index]).addClass('red-border');
                                $('#event_remind').html(new Date(currentDate).format("yyyy年M月")+'事项提醒');
                            }
                        });
                    }
                    if(data.currentMonthInfo.length>0){
                        var str = '';
                        var html = '';
                        $.each(data.currentMonthInfo,function(index,obj){
                            $('.t-remind ul').remove();
                            str =   '<ul>' +
                                    ' <li class="br-bottom"><p>'+obj.event_content+'</p>'+
                                    '<span class="the-future">'+obj.event_date+'</span>'+
                                    '</li>'+
                                    '</ul>';
                            html += str ;
                        });
                        $('.n-remind.fr').addClass('disnone');
                        $('.t-remind.fr').removeClass('disnone');
                        $('#event_remind').after(html);
                    }else{
                        $('.t-remind.fr').addClass('disnone');
                        $('.n-remind.fr').removeClass('disnone');
                    }
                }
            });
        },
        onAfterRender:function(oEventData){
            var currentMonth = new Date().getMonth() + 1;
            if(monthWithEvents.length>0){
                $.each(monthWithEvents,function(idx,obj){
                    var index = obj.event_month -1;
                    if(obj.event_month > currentMonth){
                        $($('.month-items>li a i')[index]).addClass('red-border');
                    }else if(obj.event_month < currentMonth){
                        $($('.month-items>li')[index]).addClass('before');
                    }else{
                        $($('.month-items>li')[index]).addClass('now');
                        $($('.month-items>li a i')[index]).addClass('red-border');
                    }
                });
            }
        }
    });

    /**日期转化方法**/
    Date.prototype.format = function(format) //author: meizz
    {
      var o = {
        "M+" : this.getMonth()+1, //month
        "d+" : this.getDate(),    //day
        "h+" : this.getHours(),   //hour
        "m+" : this.getMinutes(), //minute
        "s+" : this.getSeconds(), //second
        "q+" : Math.floor((this.getMonth()+3)/3),  //quarter
        "S" : this.getMilliseconds() //millisecond
      }
      if(/(y+)/.test(format)) format=format.replace(RegExp.$1,
        (this.getFullYear()+"").substr(4 - RegExp.$1.length));
      for(var k in o)if(new RegExp("("+ k +")").test(format))
        format = format.replace(RegExp.$1,
          RegExp.$1.length==1 ? o[k] :
            ("00"+ o[k]).substr((""+ o[k]).length));
      return format;
    }

//valuefocus($(".ipt"));
$('.user-form-ipt,.user-form-iptyzm,.captcha-iptyzm').bind("input" ,function(){
		var iptSize  = $(this).val().length;
		if( iptSize >0 ){
            $(this).addClass('col555');
        }else{
            $(this).removeClass('col555');
        }
});


//已认证饼形图
if( $('.pieChart').length>0 ){
	$('.pieChart').highcharts({
        chart: {
                renderTo: 'pie_chart',
                plotBackgroundColor: '#f8f8f8',//背景颜色
				spacing:0,
                plotBorderWidth: 0,
                plotShadow: false
            },
            title: {
                text:null
            },
           tooltip: {//鼠标移动到每个饼图显示的内容
                pointFormat: '{point.name}: <b>{point.percentage}%</b>',
				backgroundColor: '#fff',
				borderRadius: 5,
				borderWidth:1,
				shadow: true,
                percentageDecimals: 1,
				style: {                      // 文字内容相关样式
					color: "#333",
					//fontWeight:'bold',
					//fontFamily: "Microsoft YaHei",
					fontSize: "12px"
				},
                formatter: function() {
                    //return this.point.name+':'+this.y;
                    var nbrch =(totalMoney*this.point.percentage/100)/totalMoney*100;
                    return this.point.name+':' + nbrch.toFixed(2) +'%';
                }

            },
			credits:{
				 enabled:false // 禁用版权信息
			},
            plotOptions: {
                pie: {
                    size:'100%',
                    borderWidth: 0,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                    enabled: true,
                    color: '#000',
                    distance: -28,//通过设置这个属性，将每个小饼图的显示名称和每个饼图重叠

                    style: {
                        fontSize: '0px',
                       // lineHeight: '10px'  ,
						plotBackgroundColor: '#000'
                    }/*,
                    formatter: function(index) {
							var nbrch =(totalMoney*this.point.percentage/100)/totalMoney*100;
                            return  '<span style="color:#fff;font-weight:normal;font-size:14px">'+ nbrch.toFixed(2) +'%</span>';
                       }  */
                  },
                 padding:20
                }
            },
            series: [{//设置每小个饼图的颜色、名称、百分比
                type: 'pie',
                name: null,
                data: [
                    {name:'信托资管',color:'#e33338',y:xtzgChart},
                    {name:'阳光私募',color:'#ffb820',y:ygsmChart},
					{name:'私募股权',color:'#ffe26c',y:smgqChart}

                ]
            }]
        });
    }
    if(!isIEBlowNine()){
        var wow = new WOW({
            boxClass: 'wow',
            animateClass: 'animated',
            offset: 200,
            mobile: true,
            live: true
        });
        wow.init();
    }
    var Accordion = function(el, multiple) {
        this.el = el || {};
        this.multiple = multiple || false;

        // Variables privadas
        var links = this.el.find('.menu-link');
        // Evento
        var verification = $(".menu-link").attr('data-verification');
        if(verification == 3 || !!!verification){
            links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
        }
    }

    Accordion.prototype.dropdown = function(e) {
        var $el = e.data.el;
        $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('current');

        if (!e.data.multiple) {
            $el.find('.none-menu').not($next).slideUp().parent().removeClass('current');
        };
    }

    var accordion = new Accordion($('#accordion'), false);

    $(".p-rzyh span").hover(function(){
        $(this).children(".none-prompt").show();
    },function(){
        $(this).children(".none-prompt").hide();
    });
    $(".sj-time i").hover(function(){
            $(".as-time dl").show();
        },function(){
            $(".as-time dl").hide();
        }
    )
    $(".as-number dl dd i,.equity-number dd i").hover(function(){
            $(this).parent().find(".none-prompt").show();
        },function(){
            $(this).parent().find(".none-prompt").hide();
        }
    )

    $('.head-menu').hover(function(){
        $('.h-down-menu').slideDown();
        $('.head-menu').addClass('shadow');
        $('.head-menu span').addClass('current');
    }, function() {
        $('.h-down-menu').hide();
        $('.head-menu span').removeClass('current');
        $('.head-menu').removeClass('shadow');
    });
    /*var oDown =$(".m-down p a").text();
     $(".m-down span").click(function(){
     $(".m-down").animate({height:102},300)
     });

     $(".m-down p a").click(function(){
     $(".m-down").animate({height:28},300)
     $(".m-down span em").html(oDown)
     });*/

    $(window).resize(function(){
        $(".open-back").css({
            position:'fixed',
            left:($(window).width() - $(".open-back").outerWidth())/2,
            top:($(window).height() - $(".open-back").outerHeight())/2 + $(document).scrollTop()
        });
    });
    $(window).resize();
    $(".pr-name").hover(function(){
        $(this).parent().next(".o-introduce").show();
    },function(){
        $(".o-introduce").hide();
    });
    /*意见反馈*/
    $(".close-open").click(function(){
        $(".open-back").hide();
        $(".feedback-done").hide();
        $('.pop-by').hide();
    });
    $(".click-back").click(function(){
        $(".feedback-pop").show();
        $('.pop-by').show();
    });
    $('.feedback-submit').click(function(oEvent){
        var $self = $(oEvent.target);
        var $dialog = $('.feedback-pop');
        var sContent = $('.feedback-content').val();
        var sPlaceHolder = '感谢您对我们的支持，请输入您的意见或建议！';
        if(sContent && sContent !== sPlaceHolder){
            $.ajax({
                url: '/feedback/add',
                type: "POST",
                dataType: "text",
                contentType: "application/x-www-form-urlencoded; charset=utf-8",
                data: {
                    content:sContent,
                    placeholder_text:sPlaceHolder
                },
                error: function () {
                    $dialog.hide();
                    $('.pop-by').hide();
                    alert("网络超时，请稍后再试","error");
                },
                success: function (result) {
                    if(result.code && _isSessionOut(oData.data.code)){
                        return _handleRequestCode();
                    }
                    $dialog.hide();
                    if(result == '2'){
                        $(".feedback-pop").hide();
                        $('.feedback-done').show();
                    }else{
                        $('.pop-by').hide();
                        alert('保存信息失败');
                    }
                }
            });
        }else{
            $dialog.effect('shake');
        }
    });
//$(function(){
//			 var oTop =$(".t-mn").outerHeight();
//			 var oMenu =$(".p-menu").outerHeight();
//			 var oScroll =oTop+oMenu;
//			 $(window).scroll(function(){
//				 if($(window).scrollTop() > oScroll){
//					 $(".p-ct-menu").css("position","fixed")
//					 }
//				else{
//					$(".p-ct-menu").css("position","absolute")
//					}
//				 })
//			 })
    setTimeout(function(){
        var oCright = $('.p-ct-data').height();
        $('.p-ct-menu').css('height',oCright+'px');
    },0);
    $(document).ready(function(){
        $(".cursor").focus();
    });

    //--------------------------------------------------------
    //注册开始
    //自动获取焦点
    $('.register-phoneNum').focus();
    //check phone number
    $('.register-phoneNum').on('blur',function(oEventData){
        var username = $("input[name='phoneNum']").val();
        if(!validatePhone(username) && username!=''){
            $(".user-error.phoneNum p").text('手机号码格式不正确');
            $(".notice1").addClass('disnone');
            $(".user-error.phoneNum").removeClass('disnone');
            return false;
        }else{
            $(".phone-notice").addClass('disnone');
        }
    });

    $('.register-phoneNum').on('focus',function(oEventData){
        $(".user-error.phoneNum").addClass('disnone');
        $(".notice1").removeClass('disnone');
    });
    //phone verification code 's focus event
    $('.register-verificationCode').on('focus',function(oEventData){
        $('.phone-code-err').addClass('disnone');
        var current =  $('.register-verificationCode').attr('data-now');
        if(current==='1'){
            $('.display-1').removeClass('disnone');
        }else{
            $('.display-2').removeClass('disnone');
        }
    });
    //phone verification code 's blur event
    $('.register-verificationCode').on('blur',function(oEventData){
        var phoneCode = $("input[name='verificationCode']").val();
        if(phoneCode !=''){
            if(parseInt(phoneCode) != phoneCode || phoneCode.length != 6){
                $(".phone-code-err p").text('验证码应为6位数字');
                $(".display-1").addClass('disnone');
                $(".display-2").addClass('disnone');
                $(".phone-code-err").removeClass('disnone');
            }else{
                $(".display-1").addClass('disnone');$(".display-2").addClass('disnone');
            }
        }else{
            $(".display-1").addClass('disnone');
            $(".display-2").addClass('disnone');
        }
    });
    //check password
    $('.register-password').on('blur',function(oEventData){
        var pwd = $("input[name='password']").val();
        if(!validatePwd(pwd)&& pwd!=''){
            $(".user-error.password p").text('密码应为6~16位字符，且至少包含数字、字母（区分大小写）、符号中的2种。');
            $(".notice3").addClass('disnone');
            $(".user-error.password").removeClass('disnone');
            return false;
        }else{
            $('.notice3').addClass('disnone');
        }
    });

    $('.register-password').on('focus',function(oEventData){
        $(".user-error.password").addClass('disnone');
        $('.notice3').removeClass('disnone');

    });
    //check password strength
    $('.register-password').on('keyup',function(oEventData){
        var pwd = $("input[name='password']").val();
        var n = 0;
        pwd.match(/[a-z]/g) && n++;
        pwd.match(/[A-Z]/g) && n++;
        pwd.match(/[0-9]/g) && n++;
        pwd.match(/[^a-zA-Z0-9]/g) && n++;
        n = n > 3 ? 3 : n;
        pwd.length < 8 && n > 1 && (n = 1);
//moming
        $('.focus-my span').hover(function () {
            $(this).children('img').show();
        }, function () {
            $(this).children('img').hide();
        })


        //倒计时
        var levelClass = ['','mass-1','mass-2','mass-3'];
        $('.password-on').removeClass('mass-1').removeClass('mass-2').removeClass('mass-3').addClass(levelClass[n]);

    });

    //check password again
    $('.register-password-again').on('blur',function(oEventData){
        var pwd_again = $("input[name='password-again']").val();
        var pwd = $("input[name='password']").val();
        if(pwd != pwd_again && pwd_again !=''){
            if(pwd != pwd_again){
                $(".user-error.password-again p").text('两次密码输入不一致！');
            }else{
                $(".user-error.password-again p").text('请填写密码');
            }

            $(".user-error.password-again").removeClass('disnone');
            return false;
        }else{
            $('.notice4').addClass('disnone');
        }

    });

    $('.register-password-again').on('focus',function(oEventData){
        $(".user-error.password-again").addClass('disnone');
        $('.notice4').removeClass('disnone');
    });

    //send voice code
    $('.voiceCode').click(function(){
        var phone = $("input[name='phoneNum']").val();
        if(parseInt(phone) != phone || !validatePhone(phone)){
            return false;
        }
        var msgType = 2;
        _requestPhoneCode(phone,msgType);
    });

    //checkbox event
    $('.iread-igree').on('change',function(){
        if($('input[name="agreement"]').is(':checked')){
            $('.agree-register').removeClass('no-by');
        }else{
            $('.agree-register').addClass('no-by');
        }
    });

    //agree and register, obosolted
    $('.user-form-btn.agree-register').click(function(){
        var status = 1;
        //check phone null or not
        var phone = $("input[name='phoneNum']").val();
        if(phone.length == 0){
            $(".user-error.phoneNum p").text('请填写手机号码');
            $(".user-error.phoneNum").removeClass('disnone');
            status = 0;
        }else if(parseInt(phone) != phone){
            $(".user-error.phoneNum").removeClass('disnone');
            status = 0;
        }

        //check phone code null
        var phoneCode = $("input[name='verificationCode']").val();
//        if(phoneCode=='请输入验证码' || phoneCode.length ==0){
//            $(".phone-code-err p").text('请填写验证码');
//            $(".phone-code-err").removeClass('disnone');
//            $(".display-1").addClass('disnone');
//            $(".display-2").addClass('disnone');
//            status = 0;
//        }
        var geeChallenge = $('input[name=geetest_challenge]').val();
        var geeValidate = $('input[name=geetest_validate]').val();
        var geeSeccode = $('input[name=geetest_seccode]').val();
        if(!geeChallenge || !geeValidate || !geeSeccode){
            $('.login-p p').text('请先完成验证');
        }
        //check pwd
        var pwd = $("input[name='password']").val();
        if(pwd.length == 0){
            $(".user-error.password p").text('请填写密码');
            $(".user-error.password").removeClass('disnone');
            status = 0;
        }else if(!validatePwd(pwd)){
            $(".user-error.password").removeClass('disnone');
            $(".notice3").addClass('disnone');
            status = 0;
        }

        //check pwd again
        var pwd_again = $("input[name='password-again']").val();
        var pwd = $("input[name='password']").val();
        if(pwd_again.length == 0){
            $(".user-error.password-again p").text('请填写密码');
            $(".user-error.password-again").removeClass('disnone');
            status = 0;
        }else if(pwd != pwd_again){

            $(".user-error.password-again p").text('您两次密码输入不一致！');

            $(".user-error.password-again").removeClass('disnone');
            status = 0;
        }

        //check checkbox is checked or not
        if(!$('input[name="agreement"]').is(':checked')){
            status = 0;
        }
        if(status == 0){// register conditions aren't met
            return false;
        }
        var tokenKey = $('#csrf-token-key').val(),
            tokenValue = $('#csrf-token-value').val();
        //submit
        $.ajax({
            url: '/user/register.html',
            type: "POST",
            dataType: "json",
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: {
                'phoneNum':phone,
                'verificationCode':phoneCode,
                'geetest_challenge':geeChallenge,
                'geetest_validate':geeValidate,
                'geetest_seccode':geeSeccode,
                'password':pwd,
                'password-again':pwd_again,
                'tokenKey' : tokenKey,
                'tokenValue':tokenValue
            },
            error: function () {
                alert("网络超时，请稍后再试","error");
            },
            success: function (result) {
                var oResult = result;
                if(typeof oResult == 'string'){
                    var oResult = JSON.parse(result);
                }
                if(!oResult.valid){
                    $('.login-p p').text(oResult.message);
                    $('.login-p').removeClass('disnone');
                    $('#csrf-token-key').val(oResult.tokenKey);
                    $('#csrf-token-value').val(oResult.tokenValue);
                }else{
                    window.location.href='/user/login.html';
                }
            }
        });
        //var a = $('#registerForm').submit();

    });
    //注册结束
    //--------------------------------------------------------

    $('.user-phone').on('blur',function(oEventData){
        var userPhone = $('.user-phone').val();
        var validate = validatePhone(userPhone);
        if(!validate && userPhone!=''){
            $(".user-phone-err p").text('手机号码格式不正确');
            $(".user-phone-err").removeClass("disnone");
            return false;
        }
    });

    $('.user-phone').on('focus',function(oEventData){
        $(".user-phone-err").addClass("disnone");
    });

    $('.verify-code').on('focus',function(oEventData){
        $(".user-pass-two").addClass("disnone");
    });

    $('.modify-pwd-first').on('click',function(oEventData){
        var userPhone = $('.user-phone').val();
        var validate = validatePhone(userPhone);
        if(userPhone == ""){
            $(".user-phone-err").removeClass("disnone");
            $(".user-phone-err p").text("手机号码不能为空");
            return false;
        }
        if(!validate){
            $(".user-phone-err").removeClass("disnone");
            $(".user-phone-err p").text("手机号码格式不正确");
            return false;
        }
        var verify = $('.verify-code').val();
        if(!verify.match(/^[a-zA-Z\d]{4}$/)){
            $(".user-pass-two").removeClass("disnone");
            return false;
        }
        $.ajax({
            url: '/user/modifyPwdFirst.html',
            type: "POST",
            dataType: "json",
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: {
                'phone':userPhone,
                'verify':verify
            },
            error: function () {
                alert("网络超时，请稍后再试","error");
            },
            success: function (result) {
                var oResult = result;
                if(typeof oResult == 'string'){
                    oResult = JSON.parse(result);
                }
                if(!oResult.valid){
                    if(oResult.phone_err){
                        $('.user-phone-err p').text(oResult.phone_err);
                        $('.user-phone-err').removeClass('disnone');
                    }else{
                        $('.user-phone-err').addClass('disnone');
                    }
                    if(oResult.verify_err){
                        $('.user-pass-two p').text(oResult.verify_err);
                        $('.user-pass-two').removeClass('disnone');
                    }else{
                        $('.user-pass-two').addClass('disnone');
                    }
                }else{
                    window.location.href='/user/modifyPwdSecond.html';
                }
            }
        });
    });

    $('.send-phone-code').on('click',function(oEventData){
        var sendStatus = $('.send-phone-code').attr('data-value');
        if(sendStatus == 1){
            $.ajax({
                type: 'post',
                url:'/user/sendModifyCode.html',
                data:{msgType:1},
                dataType: 'json',
                success: function (res) {
                    if(res == 10){
                        window.location.href = "/user/modifyPwdFirst.html";
                    }
                    if (res != 1) {
                        alert(res.msg);
                    } else {
                        $('.send-phone-code').attr("data-value",0);
                        $("#forgetPwdGetCode").text("60秒");
                        $sec = 60;
                        my_interval = setInterval(timeOut, 1000);
                    }
                }
            })
        }
    });

    $("#modifyPwdVoice").on('click',function(oEventData){
        var sendStatus = $('#modifyPwdVoice').attr('data-value');
        if(sendStatus == 1){
            $.ajax({
                type: 'post',
                url:'/user/sendModifyCode.html',
                data:{msgType:2},
                dataType: 'json',
                success: function (res) {
                    if(res == 1){
                        alert("语音已发送");
                    }
                }
            })
        }
    });

    $('.modify-pwd-second').on('click',function(oEventData){
        var phoneCode = $("input[name='phoneCode']").val();
        if(!phoneCode || phoneCode == "请输入动态码"){
            $('.phone-code-err').removeClass("disnone");
            return false;
        }
        $.ajax({
            url: '/user/modifyPwdSecond.html',
            type: "POST",
            dataType: "json",
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: {
                'phoneCode':phoneCode
            },
            error: function () {
                alert("网络超时，请稍后再试","error");
            },
            success: function (result) {
                var oResult = result;
                if(typeof oResult == 'string'){
                    oResult = JSON.parse(result);
                }
                if(oResult == 10){
                    window.location.href = "/user/modifyPwdFirst.html";
                }
                if(!oResult.valid){
                    if(oResult.phone_code_err){
                        $('.phone-code-err p').text(oResult.phone_code_err);
                        $('.phone-code-err').removeClass('disnone');
                    }else{
                        $('.phone-code-err').addClass('disnone');
                    }
                }else{
                    window.location.href='/user/modifyPwdThird.html';
                }
            }
        });
    });
    $(".new-password-one").on('focus',function(oEventData){
        $(".password-tips").removeClass("disnone");
        $(".new-password-one-err").hide();
    });
    $(".new-password-two").on('focus',function(oEventData){
        $(".new-password-two-err").hide();
    });
    $('.new-password-one').on('blur',function(oEventData){
        var pwd = $("input[name='newPwdOne']").val();
        if(!validatePwd(pwd)){
            $(".password-tips").addClass("disnone");
            $(".new-password-one-err").show();
            return false;
        }
    });
    $('.new-password-two').on('blur',function(oEventData){
        var pwd = $("input[name='newPwdOne']").val();
        var pwd1 = $("input[name='newPwdTwo']").val();
        if(pwd != pwd1){
            $(".new-password-two-err").show();
            return false;
        }
    });

    $('.modify-pwd-three').on('click',function(oEventData){
        var pwd = $("input[name='newPwdOne']").val();
        var pwd1 = $("input[name='newPwdTwo']").val();
        if(!validatePwd(pwd)){
            $(".new-password-one-err").removeClass('disnone');
            return false;
        }
        var tokenKey = $('#csrf-token-key').val(),
            tokenValue = $('#csrf-token-value').val();
        $.ajax({
            url: '/user/modifyPwdThird.html',
            type: "POST",
            dataType: "json",
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: {
                'newPwdOne':pwd,
                'newPwdTwo':pwd1,
                'tokenKey' : tokenKey,
                'tokenValue' : tokenValue
            },
            error: function () {
                alert("网络超时，请稍后再试","error");
            },
            success: function (result) {
                var oResult = result;
                if(typeof oResult == 'string'){
                    oResult = JSON.parse(result);
                }
                if(oResult == 10 ){
                    window.location.href = "/user/modifyPwdFirst.html";
                }
                if(!oResult.valid){
                    if(oResult.pwd_one_err){
                        $('.new-password-one-err p').text(oResult.pwd_one_err);
                        $('.new-password-one-err').removeClass('disnone');
                    }else{
                        $('.new-password-one-err').addClass('disnone');
                    }
                    if(oResult.pwd_two_err){
                        $('.new-password-two-err p').text(oResult.pwd_two_err);
                        $('.new-password-two-err').removeClass('disnone');
                    }else{
                        $('.new-password-two-err').addClass('disnone');
                    }
                    $('#csrf-token-key').val(oResult.tokenKey);
                    $('#csrf-token-value').val(oResult.tokenValue);
                }else{
                    window.location.href='/user/modifyPwdFour.html';
                }
            }
        });
    });

    //--------------------------------------------------------
    //预约开始
    $('.book-login-btn').bind('click',function(oEvent){
        var $self = $(oEvent.target);
        var oExtractedInfo = extractBookData($self);
        var oBookInfo = jQuery.extend({},oExtractedInfo);
        oBookInfo['logined'] = true;
        var fnCallback = function(oData){
            if(oData.success){
                $(".pop-online-appointment").hide();
                if(oBookInfo['orderType'] == 2){
                    $(".pop-dysucc.phone-additional").show();
                }else if(oBookInfo['orderType'] == 1){
                    $(".pop-dysucc.phone-redeem").show();
                }else{
                    $(".pop-dysucc.phone-success").show();
                }
            }else{
                $(".pop-online-appointment").hide();

                if(oData.data.code && _isSessionOut(oData.data.code)){
                    return _handleRequestCode();
                }
                alert(oData.message||'预约失败');
            }
            //TODO should be a popup to notify users.
        };
        $(".pop-by").show();
        window.booking.submitBookInfo(oBookInfo,fnCallback,true);
    });
    //预约结束
    //--------------------------------------------------------

    function _getVCodeSrc(){
        var sSrc = $('.phone-vcode').attr('src');
        var aSrc = sSrc.split('&random');
        sSrc = aSrc[0]+'&random='+Math.random();
        return sSrc;
    }

    function _requestPhoneCode(phone,msgType){
        msgType = msgType || 1;
        $.ajax({
            url:'/user/sendPhoneCode.html',
            data:{phone:phone,msgType:msgType},
            dataType:'json',
            type:'post',
            success:function(oData){
                //code=2 means that send times is used out
                if(oData.code == 2){
                    var sSrc = _getVCodeSrc();
                    $('.phone-vcode').attr('src',sSrc);;
                    $('.pop-box.pop-captcha').show();$('.pic-code-check').focus();
                }else{
                    if(msgType === 1){
                        //count down
                        var o = $('.user-form-yzm');
                        var wait = 60;
                        time(o,wait);
                        $('.user-form-yzm').attr('data-clickable',2);
                    }
                    if(msgType=== 2){
                        alert('语音验证码正在发送，请注意接听!');
                    }

                }
            }
        });
    }

    function timeOut() {
        if ($sec > 1) {
            $sec--;
            $("#forgetPwdGetCode").text($sec + "秒");
            if ($sec < 30){
                $("#modifyPwdVoice").removeClass("disnone");
            }
        } else {
            clearInterval(my_interval);
            $("#forgetPwdGetCode").text("获取验证码");
            $('.send-phone-code').attr("data-value",1);
        }
    }

    //count down
    function time(o,wait) {
        if (wait == 0) {
            o.attr('data-clickable',2);
            o.text("获取验证码");
            wait = 60;
        } else {
            o.attr('data-clickable',0);
            o.text( wait + "s后重发");
            if(wait==30){
                //provide a flag for its focus event to choose to show which div
                $('.register-verificationCode').attr('data-now',30);
            }
            if(wait<30){
                $('.display-1').addClass("disnone");
                $('.display-2').removeClass("disnone");
            }else{
                $('.display-1').removeClass("disnone");
                $('.display-2').addClass("disnone");
            }
            wait--;
            setTimeout(function() {
                time(o,wait);
            },1000);
        }
    }


    /**************************************取消收藏************************************/
    /*$('.cold0').click(function(){
     var item_id = $('.cold0').attr('data-item_id');
     var type = $('.cold0').attr('data-type');
     $.ajax({
     url:'/user/deleteFavorite',
     data:{type:type,item_id:item_id},
     dataType:'json',
     type:'post',
     success:function(data){
     if(_isSessionOut(data.code)){
     return _handleRequestCode();
     }
     if(data.code ==1){
     alert('删除成功！');
     if(type ==1){
     type = 'tr'
     }else if(type ==2){
     type = 'sf';
     }else{
     type = 'pe';
     }
     window.location.href='/user/favorite.html?type='+ type;
     }else{
     alert('删除失败！');
     }
     }
     })
     });*/

    /*****************************************站内信********************************/
    $('.message-wd6').click(function(){
        var that = this;
        $(this).parent().parent().toggleClass('book-lst').siblings().removeClass('book-lst');
        var bootDoc = $(this).parent().find('.message-wd6');
        var bookText = bootDoc.text();
        var alltext = $('.message-wd6');
        var id = $(this).data("id");
        var type = $(".message-wd2 i").attr("class");
        alltext.html('展开阅读');
        $(this).prevAll(".message-wd2").children("i").removeClass("list-up");
        $(this).prevAll(".message-wd2 ").children("i").addClass("list-pack");
        if ( bookText == '展开阅读' ){
            bootDoc.html('收起关闭');
            if(type == 'list-up'){
                $.ajax({
                    type:"POST",
                    url:"/user/mesUpdate.html",
                    data:{id:id},
                    dataType:"json",
                    success:function(data){
                        if(data == 1){
                            $(that).prevAll(".message-wd2").children("i").removeClass("list-up");
                            $(that).prevAll(".message-wd2 ").children("i").addClass("list-pack");
                        }
                    }
                })
            }
        }else{
            bootDoc.html('展开阅读');
        }
    });
    $(".choose-value span").click(function(){
        $(this).toggleClass("read")
    });
    $("#chk_all").click(function(){
        // 使用prop则完美实现全选和反选
        $("input[name='chk_list[]']").prop("checked", $(this).prop("checked"));

        // 获取所有选中的项并把选中项的文本组成一个字符串
        var str = '';
        $($("input[name='chk_list[]']:checked")).each(function(){
            str += $(this).next().text() + ',';
        });
    });

    /******************************************资金明细*******************************/
    jQuery.divselect = function(divselectid,inputselectid) {
        var inputselect = $(inputselectid);
        $(divselectid+" span").click(function(){
            var ul = $(divselectid+" p");
            $(divselectid+" span i").toggleClass("top-arrow")
            if(ul.css("display")=="none"){
                ul.slideDown("fast");
            }else{
                ul.slideUp("fast");
            }
        });
        $(divselectid+" p a").click(function(){
            var txt = $(this).text();
            $(divselectid+" span em").html(txt);
            var value = $(this).attr("selectid");
            inputselect.val(value);
            $(divselectid+" span i").removeClass("top-arrow")
            $(divselectid+" p").hide();

        });

    };
    $(function(){$.divselect("#divselect","#inputselect");
    });
    /*$('.message-wd6').click(function(){
     $(this).parent().parent().toggleClass('book-lst').siblings().removeClass('book-lst');
     var bootDoc = $(this).parent().find('.message-wd6');
     var bookText = $(this).text();
     var alltext = $('.message-wd6');
     alltext.html('展开阅读')
     if ( bookText == '展开阅读' ){
     bootDoc.html('收起关闭');
     }else{
     bootDoc.html('展开阅读');
     }

     });*/
    if($('#mask').length > 0){
        $('#mask').height(window.outerHeight);
        //var res = document.cookie.substring(5,18);
        //console.log(res);
        //如果没有cookie，执行以下动作
        //if(res != "zhongguocaifu"){
        //显示第一个新手引导
        $('#mask,#searchTip,#searchTip div:eq(0)').show();
        //点击下一步时隐藏本新手引导并显示下一个新手引导
        $('#searchTip div a').click(function(){
            $(this).parent().hide();
            $(this).parent().next().show();;
        })
        //点击关闭和到最后一个新手引导时隐藏蒙板和整个新手引导
        $('#searchTip div span,#searchTip div a:last').click(function(){
            $('#mask,#searchTip').hide();
            $.ajax({
                url:"/user/updateTipsStatus.html",
                type:"POST"
            })
        })

        //添加cookie
        //var oDate=new Date();
        //var cval = "zhongguocaifu";
        //oDate.setDate(oDate.getDate()+30);
        //document.cookie="name="+cval+";expires="+oDate;
        //}
    }
    $('.focus-my span').hover(function(){
        $(this).children('img').show();
    },function(){
        $(this).children('img').hide();
    });
    /******************************************资金明细*******************************/
    /*********注册页面start*******/
    $("#password").focus(function () {
        $(".password1-text").show();
        $(".password-lenth").show();
    })
    $("#code").focus(function(){
        $(".get-yzm").show();
    })
    /*********注册页面end*******/
});
