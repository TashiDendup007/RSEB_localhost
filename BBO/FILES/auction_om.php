<?php 
  date_default_timezone_set("Asia/Thimphu");
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <h1><small></small></h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Auction Market</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body" style="background-color:#66639f; color:#FFFFFF;">
                <div class="col-sm-12">
                  <div id="dataId12"></div>   
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>  
  </div>
</body>
<script type="text/javascript">
  $(document).ready(function() {
    var autoLoad = setInterval(function() {
      $('#dataId12').load('aution_om_dtls.php').fadeIn("slow");
    }, 5000);
  });
</script>
  
<style>
  /* @group Blink */
  .blink {
    animation-duration: 1s;
    animation-name: blink;
    animation-iteration-count: infinite;
    animation-direction: alternate;
    animation-timing-function: ease-in-out;
  }
  @keyframes blink {
      from {
          opacity: 1;
      }
      to {
          opacity: 0.2;
      }
  }
  /* @end */
</style>
</html>























