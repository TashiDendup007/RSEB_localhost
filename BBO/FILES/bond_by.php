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
         <li><a href="#">BOND</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">BOND</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <?php 
          $dateselect = date("Y-m-d H:i:s");

          $stmt = $dbh->prepare("SELECT b.id, b.symbol_id, b.start_bond_at, b.end_bond_at, b.status, 
              DATE_FORMAT(b.start_bond_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
              DATE_FORMAT(b.end_bond_at, '%W %M %e, %Y %h:%i %p') AS end_at_format
              FROM bond_offers b
              WHERE b.status = 1 
              ORDER BY b.id DESC
          ");
          $stmt->execute();
          $result = $stmt->fetch();
          if ($stmt->rowcount() < 1) {
            echo'<div class="box-body"><h3>Currently, there are no active bond offers.</h3></div>'; 
            die();
          }

          if ($result['start_bond_at'] > $dateselect) {
            echo"<div class='box-body'><h3>The Bid for the Bond will open on : <b>".$result['start_at_format']."</b></h3></div>";
            die();
          } elseif($result['end_bond_at'] < $dateselect) {
            echo "<div class='box-body'><h3>The Bid for the Bond has ended on <b>".$result['end_at_format']."</b></h3></div>";
            die();
          }

          /*if (substr($username, 0, 7) == 'MEMDKLT') {
            
          } else {
            die("<div class='box-body'><h3>The Bid for the Bond has ended.</b></h3></div>");
          }*/
        ?>
        <form action="" method="post" onsubmit="showLoading();">
          <div class="box-body">
            <div class="row">
               <div class="col-lg-3 col-md-3 col-sm-12">
                  <label>CD Code<font color="red">*</font></label>
                  <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="cdepCode(this.value);" required>
                </div>
                <div id="cd"></div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-4 col-md-4">
                <button type="button" class="btn btn-primary" style='display: none;' name="bondSave" id="bondSave"><i class="fa fa-save"></i> Save</button>
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


      <div class="row">
        <div class="col-lg-12">
          <div class="box">
            <div class="box-header"></div>
            <div class="box-body">
              <div class="col-lg-6 col-md-6">
                <label>From Date<font color="red">*</font></label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                </div>
                <span id="f_dateErr" style="color: red;"></span>
              </div>
              <div class="col-lg-6 col-md-6">
                <label>To Date<font color="red">*</font></label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                </div>
                <span id="t_dateErr" style="color: red;"></span>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" id="rightsI" name="rightsI" value=""><i class="fa fa-list"></i> List </button>
              </div>
            </div>
          </div>
          <div class="box" width="100%" style="display: none;" id="tableId">
            <div class="box-body">
              <div id="details"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
  <?php include('../NAV/footer.php'); ?>
</body>
<script type="text/javascript">
  function cdepCode(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'bondCD='+val,
      dataType: 'html',
      success: function(response){ 
        $("#cd").html(response);
      } 
    });
  }
 
  function checkDate() {
    var f = document.getElementById("record_date").value;
    var from = new Date(f);
    var t = document.getElementById("announcement_date").value;
    var to = new Date(t);
     if (from < to)
     {
         alert("Record date should be greater than Announcement date ");
         return false;
     }
     else
     {
         return true;
     }
  }

  $("#bondSave").click( function() { 
    showLoading();
    var cidFld = $("#cid").val();
    var cdFld = $("#cdcode").val();
    var symbol_idFld = $("#sy").val();
    var face_valueFld = $("#face_value").val();
    var bidpriceFld = $("#bidPrice").val();
    var volumeFld = $("#volume").val();
    var operation = "save_bond";


    if (volumeFld < 10) {
      hideloading();
      alert("Volume should not be less than 10.");
      return false;
    }

    if (volumeFld % 10 != 0) {
      hideloading();
      alert("Volume must be multiple of 10.");
      return false;
    }

    var data = {
      cdcode: cdFld,
      cid: cidFld,
      symbol_id: symbol_idFld,
      face_value: face_valueFld,
      bidprice: bidpriceFld,
      volume: volumeFld,
      save_bond: operation,
    };

    if(cidFld === '' || cdFld === '' || symbol_idFld === '' || face_valueFld === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
      type: "POST",
      url: "../PROCESS/bond_process.php",
      data: data,
      dataType: 'html',
      success: function(response){
        hideloading();
        $("#message").html(response);
        showMessage();
        $("#cdcode").val('');
        $("#sy").val('');
      }
      });
    }
    return false;
  });

  $('#rightsI').click( function() {
    showLoading();
    var fromDateFld = $("#from_date").val();
    var toDateFld = $("#to_date").val();
    var operation = 'viewbond';

    if(fromDateFld == '') {
      hideloading();
      $("#f_dateErr").html("Select From date");
      return false;
    }

    if(toDateFld == '') {
      hideloading();
      $("#t_dateErr").html("Select To date");
      return false;
    }

    var data = {
      fromDate: fromDateFld,
      toDate: toDateFld,
      viewbond: operation,
    };

    $.ajax({
      type: "POST",
      url: "load.php",
      data: data,
      dataType: 'html',
      success: function(response){
        hideloading();
        $("#tableId").show();
        $("#details").html(response);
      }
    });
  });

  $("#search_CD_Code").click( function () {
    var cid_no = $("#search_cid_no").val();
    $.ajax({
      type: "POST",
      url: "../PROCESS/bond_process.php",
      data: { search_cd_code_for_ipo: cid_no },
      dataType: 'html',
      success: function(data) {
        $("#cd_code_dtls_id").show();
        $("#cd_code_dtls_id").html(data);
      }
    });
  });

  $('#from_date').click( function() {
    $("#f_dateErr").html("");
  });

  $('#to_date').click( function() {
    $("#t_dateErr").html("");
  });
</script>
</html>
