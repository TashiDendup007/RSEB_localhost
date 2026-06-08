<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>RSEB | CapitalMarketSolution</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  
  <style type="text/css">
  .button {
    background-color: #5D3FD3;
    border: none;
    color: white;
    padding: 10px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    box-shadow: 5px 2px 10px #301934;
  }
  
  .button1 { border-radius: 15px; }

  #loadingmsg
  {
    top:40%; left: 45%; height: 130px; width:120px; background-image: url("dist/img/default.gif"); background-repeat:no-repeat; position: fixed; z-index: 100; margin: auto;
  }
  #loadingover
  {
    background: black; z-index: 99; width: 100%; height: 100%; position: fixed; top: 0; left: 0; -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)"; filter: alpha(opacity=80);
    -moz-opacity: 0.8; -khtml-opacity: 0.8; opacity: 0.8;
  }
</style>
</head>
<body class="hold-transition login-page" style="color:white;background: radial-gradient(#5D3FD3, #301934);"><div class="login-box-body" id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none; color:#fff;'></div></div>
<div class="login-box">
  <div class="login-logo">
    <a><b style="color:white;">RSEB-CaMS-</b><span style="color:magenta;">Access</span></a>
  </div>
  <div class="login-box-body button1">
    <div class="row">
      <img src="img/rseb_logo2.png" class="profile-user-img img-responsive " />
    </div>

    <p class="login-box-msg">Sign in to start your session</p>
    <form action="Authentication/verification.php" method="POST" onsubmit="showLoading();">
    <div class="form-group has-feedback">
      <input type="text" class="form-control button1" placeholder="UserName" name="username" autofocus required>
      <span class="glyphicon glyphicon-user form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="password" class="form-control button1" placeholder="Password" name="password" required>
      <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>
    <div class="row">
      <div class="col-lg-12 text-center">
        <button type="submit" class="button button1"> &nbsp;&nbsp;&nbsp; Login &nbsp;&nbsp;&nbsp;</button>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-xs-8 left">
        <a href="#" class="btn"> <i class="fa fa-fw fa-question"></i>  Forgot Password</a>
      </div>
      <div class="col-xs-4 right">
        <a href="https://rsebl.org.bt/#/OnlineRenew/1" class="btn"><i class="fa fa-fw fa-refresh"></i> Renew</a>
      </div>
    </div>
    <p class="message">
      <?php
        $errors = array(
            1=>"Invalid Credentials, Try again",
            2=>"Please login to access this area",
            3=>"Password Changed Successfully",
            4=>"Your account has expired. Kindly renew."
          );
        $error_id = isset($_GET['err']) ? (int)$_GET['err'] : 0;
        if ($error_id == 1 || $error_id == 2 || $error_id == 3 || $error_id == 4) {
          echo '<div class="alert alert-' . ($error_id == 1 ? 'error' : ($error_id == 4 ? 'warning' : 'success')) . '">' . $errors[$error_id] . '</div>';
        }
       ?>
    </p>
  </form>
  </div>
</div>
<script type="text/javascript">
  function showLoading() {
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
  }
</script>
</body>
</html>