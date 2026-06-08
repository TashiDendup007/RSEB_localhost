<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>RSEB | CapitalMarketSolution</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/font-awesome.min.css">
  <link rel="stylesheet" href="vendor/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  
  <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
  <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body class="hold-transition login-page" style="color:white;background: radial-gradient(#5D3FD3, #301934);">
  <div class="login-box">
    <div class="login-logo">
      <a><b style="color:white;">RSEB-CaMS-</b><span style="color:magenta;">Access</span></a>
    </div>
  </div>
  <div class="row">
  	<div class="col-lg-2"></div>
  	<div class="col-lg-8 text-center">
  		<div class="alert alert-success" role="alert">
		  <p style="font-size: 30px;">The system is currently down for migration to a new system.</p>
		  <hr>
		  <p class="mb-0"></p>
		</div>
  	</div>
  	<div class="col-lg-2"></div>
  </div>
  <?php include('GifLoader/gif.php'); ?>
</body>
<script type="text/javascript">
    document.addEventListener('contextmenu', event => event.preventDefault());
    	document.onkeydown = function(e) {
        if(event.keyCode == 123) {
            return false;
        }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
            return false;
        }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
            return false;
        }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
            return false;
        }
        if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
            return false;
        }
    }
</script>
</html>