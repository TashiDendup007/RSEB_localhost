<?php 
  date_default_timezone_set('Asia/Thimphu');
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
  
  $list = ins_id($username);
  $ins_id = $list[0];
  $p_code = $list[1];
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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">IPO</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">IPO</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <?php 
            $dateselect = date("Y-m-d H:i:s");

            $stmt = $dbh->prepare("SELECT b.id, b.symbol_id, b.start_at, b.end_at, b.status, 
                DATE_FORMAT(b.start_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
                DATE_FORMAT(b.end_at, '%W %M %e, %Y %h:%i %p') AS end_at_format
                FROM ipo_offers b
                WHERE b.status = 1 
                ORDER BY b.id DESC
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($stmt->rowcount() < 1) {
              echo'<div class="box-body"><h3>There are no active IPO offer</h3></div>'; 
              die();
            }

            if ($result['start_at'] > $dateselect) {
              echo"<div class='box-body'><h3>The Bid for the IPO will open on : <b>".$result['start_at_format']."</b></h3></div>";
              die();
            } elseif($result['end_at'] < $dateselect) {
              echo "<div class='box-body'><h3>The Bid for the IPO has ended on <b>".$result['end_at_format']."</b></h3></div>";
              die();
            }
          ?>
          <form action="" method="post" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row">
                 <div class="col-lg-3 col-md-3">
                    <label>CD Code</label>
                    <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="cdepCode(this.value);" required>
                  </div>
                  <div id="cd"></div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" style='display: none;' name="ipoSave" id="ipoSave"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>

        <div class="box">
          <div class="box-header with-border"><strong>Search CD Code</strong></div>
          <div class="box-body">
            <form action="" method="POST">
              <div class="input-group col-lg-6 col-md-6">
                <input type="text" name="search_cid_no" id="search_cid_no" class="form-control" placeholder="Enter CID Number">
                <span class="input-group-btn">
                  <button class="btn btn-primary" type="button" id="search_CD_Code" name="search_CD_Code">Search</button>
                </span>
                <div id="search_erro_msg" style="color: red;"></div>
              </div>
            </form>
            <div id="cd_code_dtls_id" style="display: none;"></div>
          </div>
        </div>

        <div class="box">
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
            </div>
          </div>
          <div class="col-lg-6 col-md-6">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
            </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">          
              <button type="button" class="btn btn-success" id="generate_list" name="generate_list"><i class="fa fa-list"></i>  List </button>
            </div>
          </div>
          <div id="details"></div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
<script type="text/javascript">
  function cdepCode(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'ipoCD='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  $("#ipoSave").click( function() {
    showLoading();
    var cid = $("#cid").val();
    var cd = $("#cdcode").val();
    var symbol_id = $("#sy").val();
    var face_value = $("#face_value").val();
    var bidprice = $("#bidPrice").val();
    var volume = $("#volume").val();
    var operation = "save_ipo";
    var dataString = 'cdcode='+ cd + '&cid='+ cid + '&symbol_id=' + symbol_id+ '&face_value=' + face_value+ '&bidprice=' + bidprice+ '&volume='+ volume + '&save_ipo='+ operation;
    if(cd == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
      type: "POST",
      url: "../PROCESS/ipo_process.php",
      data: dataString,
      dataType: "html",
      success: function(response){
        hideloading();
        $("#message").html(response);
        showMessage();
      }
      });
    }
    return false;
  });

  $('#generate_list').click( function() {
    showLoading();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    var op = 'view_ipo_dtls';
    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&view_ipo_dtls='+ op,
      dataType: "html",
      success: function(response){
        hideloading();
        $("#details").html(response);
      }
    });
  });

  $("#search_CD_Code").click( function () {
    var cid_no = $("#search_cid_no").val();
    $.ajax({
      type: "POST",
      url: "../PROCESS/ipo_process.php",
      data: { search_cd_code_for_ipo: cid_no },
      dataType: 'html',
      success: function(data) {
        $("#cd_code_dtls_id").show();
        $("#cd_code_dtls_id").html(data);
      }
    });
  });
</script>
</html>
