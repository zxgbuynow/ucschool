<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"/data/httpd/daguan/public/../application/counsellor/view/counsellor/plus.html";i:1522720413;}*/ ?>
<!DOCTYPE html>
<html>

  <head>
    <meta charset="utf-8">
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <link rel="stylesheet" href="__C_CSS__/style.css">
    <link rel="stylesheet" type="text/css" href="__C_CSS__/mui.picker.min.css" />
  </head>

  <body>
    <header class="page-header">
      <i class="header-left icon-func bbc-icon bbc-icon-back mui-action-back"></i>
      <div class="header-title">添加日程</div>
    </header>
    <section class="container">
      <form class="form-container">
        <section class="mui-input-group">
         <div class="mui-input-row">
            <label>关联订单号：</label>
            <select id="tid" name="tid" class="mui-input-clear mui-input trade-list" >
				 <option value="" selected="selected">请选择</option>
			   </select>
          </div>
          <div class="mui-input-row ">
            <label>开始时间：</label>
      		<input id="start_time" type="datetime"  data-options='{"beginYear":2018,"endYear":2025}' class="mui-input-clear pickTime" placeholder="开始时间">
          </div>
          <div class="mui-input-row ">
            <label>结束时间：</label>
      		<input id="end_time" type="datetime" data-options='{"beginYear":2018,"endYear":2025}' class="mui-input-clear pickTime" placeholder="结束时间">
          </div>
        </section>
        <div class="content-padded font-gray-20 fontS">选择需关联的订单，添加相应日程，时间间距参考订单购买时长</div>
        <section class="mui-content-padded form-op-section">
          <button id="save" type="button" class="mui-btn mui-btn-block mui-btn-warning bbc-btn-warning">保存</button>
        </section>
      </form>
    </section>
    <script src="__C_JS__/zepto.js"></script>
    <script src="__C_JS__/mui.min.js"></script>
    <script src="__C_JS__/template.min.js"></script>
    <script src="__C_JS__/config.js"></script>
    <script src="__C_JS__/app.js"></script>
    <script src="__C_JS__/mui.picker.min.js"></script>
	  <script type="text/html" id="trade_list">
    		<% for(var i in list) { %>	
      		<option value="<%= list[i].id %>"><%= list[i].title %></option>
      	<% } %>
    </script>

    <script>
  		var state = app.getState();
      //TODO 过滤已经按排过的
      var param = {
	      'method': config.apimethod.income,
	      'account': state.token,
	      'source':config.source
	    }  
		//机构列表
	    $.dataRequest(param, function (rs) { 
			var _html = template('trade_list', rs);
			$('#tid').append(_html);
			//选择
	    });
        $('#save').on('tap', function() {
          var stime = $('#start_time').val();
    		  var etime = $('#end_time').val();
    		  var tid = $('#tid').val();
          if(!stime) {
            mui.toast('开始时间不能为空');
            return
          }
          if(!etime) {
            mui.toast('结束时间不能为空');
            return
          }
         
          if(!tid) {
            mui.toast('订单号不能为空');
            return
          }
          var param = {
              'method': config.apimethod.calendaadd,
              'source':config.source,
              'account': state.token,
              'start_time': stime,
              'end_time': etime,
              'tid': tid
           }
          $.dataRequest(param, function(rs) {
           	mui.toast('添加成功');
			       history.go(-1);
          });
        });
        
        //时间选择
        var btns = mui('.pickTime');
        btns.each(function(i, btn) {
          btn.addEventListener('tap', function() {
            var optionsJson = this.getAttribute('data-options') || '{}';
            var options = JSON.parse(optionsJson);
            var that = this;
            var picker = new mui.DtPicker(options);
            picker.show(function(rs) {
              that.value = rs.text
              // result.innerText = '选择结果: ' + rs.text;
              
              picker.dispose();
            });
          }, false);
        });

       //  	document.getElementById("start_time").addEventListener('tap', function() {
    			// 	var dTime = new Date();
    			// 	dTime.setHours(6, 0);
    			// 	plus.nativeUI.pickTime(function(e) {
    			// 		var d = e.date;
    			// 		$('#start_time').val( d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate()+' '+d.getHours() + ":" + d.getMinutes());
    			// 	}, function(e) {
    			// 		mui.toast('请选择时间')
    			// 	}, {
    			// 		title: "请选择时间",
    			// 		is24Hour: true,
    			// 		time: dTime
    			// 	});
    			// });
      	// 	document.getElementById("end_time").addEventListener('tap', function() {
      	// 			var dTime = new Date();
      	// 			dTime.setHours(6, 0);
      	// 			plus.nativeUI.pickTime(function(e) {
      	// 				var d = e.date;
      	// 				$('#end_time').val(d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate()+' '+d.getHours() + ":" + d.getMinutes());
      	// 			}, function(e) {
      	// 				mui.toast('请选择时间')
      	// 			}, {
      	// 				title: "请选择时间",
      	// 				is24Hour: true,
      	// 				time: dTime
      	// 			});
      	// 		});	
        
	
      


      
    </script>
  </body>

</html>
