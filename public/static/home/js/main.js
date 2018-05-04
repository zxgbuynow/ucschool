var base = document.getElementById('x-base') ? document.getElementById('x-base').value : 'scredit-web';
var resRoot = document.getElementById('x-resRoot') ? document.getElementById('x-resRoot').value : '..';
require.config({
	baseUrl : './js',
	paths : {
		jquery : 'jquery',
		common : 'common',	
		jquerySuperSlide:'jquery.SuperSlide.2.1.1',
        highstock:'highstock',
        placeholder : 'placeholder'
	},
	shim : {
		'common' : {
			deps : [ 'jquery']
		},			
		'jqueryScroll' : {
			deps : [ 'jquery']
		},			
		'jquerySuperSlide' : {
			deps : [ 'jquery']
		},
        'highstock' : {
            deps : [ 'jquery']
        },
        'placeholder' : {
            deps : [ 'jquery']
        }
    }
});


require([ 'jquery','common','jquerySuperSlide','highstock','placeholder'], function($) {
//moming
jQuery(".slideBox1").slide({ mainCell:".bnr-lst",effect:"fold",mouseOverStop:true, autoPlay:true,scroll:"1",prevCell:".bnr-btnfl",nextCell:".bnr-btnfr",titCell:".bnr-nbr i",interTime:"4000", delayTime:600, trigger:"click"});
	jQuery(".slideBox1 .bnr-lst,.bnr-btn").hover(function(){ $('.bnr-btnfl,.bnr-btnfr').css({ visibility:'visible' }); },function(){ $('.bnr-btnfl,.bnr-btnfr').css({ visibility:'hidden' }); })
	$(window).resize(function(){ $('.bnr-lst,.bnr-lst li').width( $(window).width() ) });
	jQuery(".slideBox2").slide({ mainCell:".new-topic",effect:"fold", autoPlay:true,scroll:"1",titCell:".rec-ciecle i",interTime:"4000", delayTime:600, trigger:"click"});
    jQuery(".ztscroll").slide({mainCell:".bd ul",effect:"left",autoPlay:true,delayTime:500,interTime:6000});
//placeholder
    if($('input').length > 0 && typeof $('input').placeholder == 'function'){
        $(function(){ $('input').placeholder();});
    }

    $(window).resize(function(){
            $('.that-open').css({
             position:'fixed',
             left: ($(window).width() - $('.that-open').outerWidth())/2,
             top: ($(window).height() - $('.that-open').outerHeight())/2
            });
        });// 最初运行函数
    $(document).ready(function(){
        if($('.login-pop:visible').length > 0){
            setTimeout(function(){
                $('.login-pop').addClass('disnone');
                $('.login-pop-bg').hide();
            },8000);
            if(Cookies('sfKnew')==1){
                $('.login-pop').addClass('disnone');
                $('.login-pop-bg').hide();
            }
            //$('.agreed-to.login').on('click',function(oEvent){
            //    var sfKnewFlag = 1;
            //    Cookies('sfKnew',sfKnewFlag);
            //});
        }
        $('.close-investment').on('click',function(){
            var sfKnewFlag = 1;
            Cookies('sfKnew',sfKnewFlag);
            $('.login-pop').addClass('disnone');
            $('.login-pop-bg').hide();
        });
        // if($('.that-open').length > 0 && $('.that-open:visible').length == 0){
        //
        // }
    });

    $(window).resize();

//meixiu
// setTab($('.xt-knowl-box'), '.xt-knowl-tab span', '.xt-knowl-tabmnc', 'on', null, 'click');
// setTab($('.sgt-lstbox'), '.xt-knowl-tab span', '.sgt-lstmnc', 'on', null, 'click');
// setTab($('.search-box'), '.search-tab span', '.search-tabmnc', 'on', null, 'click');
// setTab($('.search-box2'), '.search-tab span', '.search-tabmnc2', 'on', null, 'click');
// setTab($('.search-box3'), '.search-tab span', '.search-tabmnc3', 'on', null, 'click');
// setTab($('.searchjx-box'), '.searchjx-tab span', '.searchjx-tabmnc', 'on', null, 'click');
//
// //jing
// setTab($('.l-achivtbbox'), '.l-achivtb span', '.l-achivtbmn', 'on', null, 'click');
// setTab($('.l-yg-tabbox'), '.l-yg-tab span', '.l-yg-tabmn', 'on', null, 'click');


//信托列表 (开发时请删除 !!!)
	$('.on-lst a').click(function(){
			$(this).toggleClass('on');
		});

//保留n位小数不四舍五入	仅处理数字不考虑单位，返回字符串类型 使用参考：parseFloat(2.325598).retainDecimals(4)
    Number.prototype.retainDecimals = function(n) {
        if (n <= 0) {
            return parseInt(this)
        };
        var thisNum = parseFloat(this).toFixed(n + 1);
        return thisNum.substr(0, thisNum.length - 1);
    };
$(document).ready(function(){
    if( $('.jz-zs').length>0 )
    {
        $('.jz-zs').hover(function() {
                // alert('sss');
                $(this).find('.nw-tr').show();

                //获取净值走势数据
                if($(this).hasClass('jz-temp')){
                    var contain=$(this).find('.container');
                    var proName=$(this).attr('data-name');
                    var proId=$(this).attr('data-proid');
                    // alert('ssss');
                    $.ajax({
                        url:'/simu/getJzDataByDateAjax',
                        data:{proId:proId},
                        type:"post",
                        success:function(data){
                            var list_ajax = data;
                            if(typeof list_ajax.netRevenues == 'string'){
                                list_ajax.netRevenues = JSON.parse(list_ajax.netRevenues);
                            }
                            if(typeof list_ajax.hs == 'string'){
                                list_ajax.hs = JSON.parse(list_ajax.hs);
                            }
                            var dataTrend=[{
                                name: proName,
                                data:list_ajax.netRevenues,color:"#e33338"}
                                , {
                                    name: '沪深300',
                                    data:list_ajax.hs,color:"#fabc3d"

                                }]
                            drawChart(dataTrend,{renderTarget:contain[0],'px':2});
                        }
                    });
                }
                $(this).removeClass('jz-temp')
                //获取净值走势数据end

            },
            function() {
                $('.nw-tr').hide();
                $('.nw-tr').parent('.jz-zs').addClass('jz-temp');
            })
    }


    if($('.chart-jnyl5').length > 0 ) {
        //有净值的才需要Ajax获取数据
        var contain=$('.chart-jnyl5');
        var proName=contain.attr('data-name');
        var proId=contain.attr('data-proid');
        var startDate=contain.attr('start-date');
        $.ajax({
            url:'/simu/getJzDataByDateAjax',
            data:{proId:proId,startDate:startDate},
            type:"post",
            success:function(data){
                var list_ajax = data;
                if(typeof list_ajax.netRevenues == 'string'){
                    list_ajax.netRevenues = JSON.parse(list_ajax.netRevenues);
                }
                if(typeof list_ajax.hs == 'string'){
                    list_ajax.hs = JSON.parse(list_ajax.hs);
                }
                var dataTrend=[{
                    name: proName,
                    data:list_ajax.netRevenues,color:"#e33338"}
                    , {
                        name: '沪深300',
                        data:list_ajax.hs,color:"#fabc3d"
                    }];
                drawChart(dataTrend,{'renderTarget':contain[0],'navigator': {enabled: false},'imageX':120,'imageY':80,'text':"暂时无数据",'px':2,'width':460,'height':200});
            }
        });
    }

//收益走势图
    if( $('.ind-img').length >0 && netRevenueData){
        drawChart(netRevenueData,{'renderTarget':$('.ind-img')[0],'imageX':270,'text':'暂时无数据','px':2,'height':310});
    }
    if( $('.trd-img').length>0 )
    {
        var oNavigator = {
            enabled: false
        };
        var oPlotOptions = {
            column : {
                borderWidth: 0
            }
        };
        drawChart(monthDrawDown,{
            'renderTarget':$('.trd-img')[0],
            'type':'area',
            'navigator':oNavigator,
            'plotOptions':oPlotOptions
        });
    }

    //度月回撤图
    if( $('.mon-img').length>0 )
    {
        var oNavigator = {
            enabled: false
        };
        var oPlotOptions = {
            column : {
                borderWidth: 0
            }
        };
        var fnCallback = function(oChart){
            if(oChart.series.length > 0 && oChart.series[0].data){
                $.each(oChart.series[0].data,function(itemIndex,oItem){
                    if(oItem.y > 0){
                        oItem.update({
                            color: '#e33338'
                        });
                    } else{
                        oItem.update({
                            color: '#72c352'
                        });
                    }
                });
            }
        }
        drawChart(monthReturn,{
            'renderTarget':$('.mon-img')[0],
            'type':'column',
            'navigator':oNavigator,
            'plotOptions':oPlotOptions,
            'fnCallback' : fnCallback
        });
    }
})


    //绘制图表
    function drawChart(chartData,drawOptions){
        if(chartData[0] &&chartData[0] && chartData[0].data &&chartData[0].data.length == 0){
            //无数据时应显示固定的图片
            var text = drawOptions['text'] || '暂时无数据';
            var renderTarget = drawOptions['renderTarget'];
            if(!($(renderTarget).find('.sf_no_chart_wrap').length > 0)){
                $(renderTarget).append("<div class=\"sf_no_chart_wrap\"><div class=\"no_chart_ico\"><img src=\"./images/no_chart_ico.png\"></div> <div class=\"no_chart_txt\">" + text + "</div> </div>");
            }
        }else{
            var chartType = drawOptions['type'] || 'spline';
            var imageX = drawOptions['imageX'] || 100,
                imageY = drawOptions['imageY'] || 100,
                px = drawOptions['px'] || 1;
            var iWidth = drawOptions['width'] || null,
                iHeight = drawOptions['height'] || null;
            var oDefaultNavigator = {
                maskFill: 'rgba(76,180,247,0.75)',
                maskInside: false,
                handles: {
                    backgroundColor: 'rgba(190,248,224,0.5)',
                    borderColor: '#77b6d1'
                },
                xAxis: {
                    labels: {
                        enabled: false
                    }
                }
            };
            var oDefaultPlotOptions = {
                series: {
                    lineWidth: px,
                    animation: false
                },
                spline: {
                    marker: {
                        radius: 1
                    }
                }
            };
            var navigatorOptions = drawOptions['navigator'] || oDefaultNavigator;
            var plotOptions = drawOptions['plotOptions'] || oDefaultPlotOptions;
            var fnCallback = drawOptions['fnCallback'] || null;
            var chart = new Highcharts.StockChart({
                    global: {
                        useUTC: false
                    },
                    chart: {
                        type: chartType,
                        renderTo: drawOptions.renderTarget,
                        backgroundColor: 'rgba(255,255,255,0)',
                        width : iWidth,
                        height: iHeight
                    },
                    scrollbar: {
                        enabled: false
                    },
                    navigator: navigatorOptions,
                    title: {
                        text: ''
                    },
                    legend: {
                        verticalAlign: 'top',
                        enabled: true
                    },
                    rangeSelector: {
                        enabled: false
                    },
                    xAxis: {
                        type: 'datetime',
                        gridLineDashStyle: 'longdash',
                        showLastLabel: true,
                        gridLineWidth: 1,
                        gridLineDashStyle: 'dash',
                        tickLength: 2,
                        labels: {
                            style: {
                                font: '11px Arial',
                                align: 'right'
                            },
                            formatter: function() {
                                if(!chartData[0]['flag']) {
                                    return Highcharts.dateFormat('%Y-%m-%d', this.value);
                                }else{
                                    return Highcharts.dateFormat('%Y-%m', this.value);
                                }
                            },
                            staggerLines: 2
                        },
                        lineColor: '#C0D0E0',
                        showEmpty: true
                    },
                    yAxis: {
                        title: {
                            text: ''
                        },
                        opposite: false,
                        lineWidth: 1,
                        showEmpty: true,

                        labels: {
                            useHTML: true,
                            formatter: function() {
                                var yLabelcol;
                                if (this.value < 0) {
                                    yLabelcol = "#24990a"
                                } else if (this.value >= 1) {
                                    yLabelcol = "#ea413c"
                                } else {
                                    yLabelcol = "#666666"
                                }

                                //if (dataSource.name.indexOf("净值")>0){
                                //	return this.value.retainDecimals(2);
                                //		          		}else{
                                return "<em style=color:" + yLabelcol + ">" + this.value.retainDecimals(0) + "%</em>";
                                //		          		}
                            }
                        }
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
//			dateTimeLabelFormats: {
//            day: '%Y-%m-%d'
//      		  }
                        formatter: function () {
                            var date = new Date(this.x);
                            if(!chartData[0]['flag']) {
                                var header = '<b>时间: ' + date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日' + '</b>';
                            }else{
                                var header = '<b>时间: ' + date.getFullYear() + '年' + (date.getMonth() + 1) + '月'
                                    + '</b>';
                            }
                            $.each(this.points, function (i, point) {
                                if(!!this.y){
                                    header += '<br/><span style="color:' + this.point.series.color + '">' + this.point.series.name + ':' + this.y.toFixed(2) + '%</span>';
                                }
                            });
                            return header;
                        }
                    },
                    credits:{
                        enabled:false
                    },
                    plotOptions: plotOptions,
                    series: chartData
                },function(oChart){
                    //TODO 加入背景图片
                    oChart.renderer.image('./images/highcharts-logo.png', imageX, imageY, 248, 55).add();
                    if(fnCallback !== null && typeof fnCallback == 'function'){
                        fnCallback(oChart);
                    }
                }
            );
        }

}
    $(document).ready(function(){
        //经理详情页
        if($('.chart-manager-detail').length > 0 && simu_trend){
            drawChart(simu_trend,{'renderTarget':$('.chart-manager-detail')[0]});
        }
        //公司详情页
        if($('.chart-sfcompany-details').length > 0 && simuCompanyTrend){
            drawChart(simuCompanyTrend,{'renderTarget':$('.chart-sfcompany-details')[0]});
        }
    });






});