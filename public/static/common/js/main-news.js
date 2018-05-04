//TAB切换setTab($('切换模块'), '切换导航', '切换区域', 'on', null, 'click');
$(function(){
	setTab($('.w870'), '.lczs-tt-nav span', '.kczc-lst', 'on', null, 'click');
//中投百科 moming
    $('.navmore').click(function () {
        $(this).siblings('span').toggleClass('bk-up');
        if ($(this).text() == '[展开]') {
            $(this).text('[收起]')
        } else {
            $(this).text('[展开]')
        }
    })
	 $('.txtmore').click(function () {
        $(this).parent().siblings('div').toggleClass('closedtxt');
        if ($(this).text() == '展开更多') {
            $(this).text('收起全部')
        } else {
            $(this).text('展开更多')
        }
    })
    jQuery(".slideBox1").slide({
        mainCell: ".bnr-lst",
        effect: "fold",
        mouseOverStop: true,
        autoPlay: true,
        scroll: "1",
        prevCell: ".bnr-btnfl",
        nextCell: ".bnr-btnfr",
        titCell: ".if-nbr i",
        interTime: "4000",
        delayTime: 600,
        trigger: "click"
    });
    //资讯幻灯
    jQuery(".ztscroll").slide({mainCell:".bd ul",effect:"left",autoPlay:true,delayTime:500,interTime:5000});



//信托公告 新浪QQ空间分享
    var qqSpace = function (url, title) {//qq空间
            window.open("http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=" + encodeURIComponent(url) + "&title=" + encodeURIComponent(title), "_blank");
        },
        sina = function (url, title) { //新浪
            window.open('http://v.t.sina.com.cn/share/share.php?title=' + encodeURIComponent(title) + "&info=" + '&url=' + encodeURIComponent(url) + '&source=bookmark', "_blank");
        }
    $(document).on("click", ".xtgg-lst-sina,.tj-share", function () {
        var title = $(this).attr("title");
        var href = $(this).attr("id");
        sina(href, title)
    });
    $(document).on("click", ".xtgg-lst-qzong,.tj-qq", function () {
        var title = $(this).attr("title");
        var href = $(this).attr("id");
        qqSpace(href, title)
    });

//信托公告 二维码
    if ($('.xtgg-lst-wx').length > 0) {

        for (i = 0; i < $('.qrcode-xt').length; i++) {
            var onTitle = $('.qrcode-xt').eq(i).attr("title");
            $(".qrcode-xt").eq(i).qrcode({
                //render: "table",
                width: 125,
                height: 125,
                text: onTitle
            });
        }

    }

//生成二维码
    if ($('#qrcode').length > 0) {
        $("#qrcode").qrcode({
            //render: "table",
            width: 95,
            height: 95,
            text: window.location.href
        });
    }




//生成二维码
if($('#qrcode').length>0){
	$("#qrcode").qrcode({ 
		//render: "table",
		width: 95,
		height:95,
		text:window.location.href
	}); 
}
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?39b5ed0bf4174a1437be8378251e78d0";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })()
})