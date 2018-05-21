<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:67:"/data/httpd/bullqt/public/../application/index/view/index/info.html";i:1526886006;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <title>公牛赠品核销问卷调查</title>
  <link rel="stylesheet" type="text/css" href="__HOME_CSS__/style.css">
  <link rel="stylesheet" href="__HOME_CSS__/citypicker.min.css">
  <link rel="stylesheet" type="text/css" href="__HOME_CSS__/main.css">

</head>

<body>
  <form class="list" id="info_form" method="post" action="<?php echo url('Index/info'); ?>">
    <div class="container page-question">
      <h3>完善门店信息</h3>
      <div class="step">
        <span class="top">
          <b>2</b>
          <i>/</i>2</span>
        <span>STEPS</span>
      </div>
      <div class="input-wrap">
        <div class="item-input shop">
          <input type="text" id="shopname" name="shopname" datatype="*" nullmsg="请填写店铺名称" placeholder="店铺名称" />
        </div>
        <div class="item-input name">
          <input type="text" placeholder="姓       名" datatype="*" id="username" name="username" nullmsg="请填写姓名"/>
        </div>
        <div class="item-input mobile">
          <input type="tel" id="phone" name="phone" datatype="m" nullmsg="请填写联系电话" placeholder="联系电话" errormsg="联系电话格式不对" />
        </div>
        <div class="item-input location select">
          <input type="text" placeholder="店铺地址" datatype="*" id="location" name="location" readonly="" nullmsg="请填写店铺地址"/>
          <a class="select_box">
            <label class="arr"></label>
          </a>
        </div>
        <div class="item-input market">
          <input type="text" name="market" id="market" placeholder="所在市场" datatype="*" nullmsg="请填写所在市场"/>
        </div>
        <a href="javascript:;" class="btn" id="submit-btn">提 交</a>
      </div>
    </div>
  </form>
  <script type="text/javascript" src="__HOME_JS__/jquery.min.js"></script>
  <script type="text/javascript" src="__HOME_JS__/rem.js"></script>
  <script type="text/javascript" src="__HOME_JS__/Validform_v5.3.2.js"></script>
  <script src="__HOME_JS__/framework7.min.js"></script>
  <script src="__HOME_JS__/regionsObject2.js"></script>
  <script src="__HOME_JS__/cityPicker.js"></script>
  <script type="text/javascript">
      
  $("#info_form").Validform({
    btnSubmit:"#submit-btn", 
    tiptype:1, 
    ignoreHidden:false,
    dragonfly:false,
    tipSweep:true,
    label:".label",
    showAllError:false,
    postonce:true,
    ajaxPost:true,
    callback:function(data){
      console.log(data);
      if (data.status=='y') {
        setTimeout(function (argument) {
          window.location.href = "<?php echo url('Index/end'); ?>";
        },2000)
        
      }
    }
  });

  </script>
</body>

</html>