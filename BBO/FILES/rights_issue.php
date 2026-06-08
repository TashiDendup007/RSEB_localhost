<?php 
  date_default_timezone_set('Asia/Thimphu');
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
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
           <li><a href="#">Right Issue</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Rights Issue</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
            <?php 
              $dateselect = date("Y-m-d H:i:s");

              $checkActive = $dbh->prepare("
                      SELECT r.id, r.symbol_id, r.start_at, r.end_at, r.status,
                        DATE_FORMAT(r.start_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
                        DATE_FORMAT(r.end_at, '%W %M %e, %Y %h:%i %p') AS end_at_format 
                      FROM rights_offers r 
                      WHERE r.status = 1 
                        AND r.id = (SELECT MAX(id) FROM rights_offers WHERE status = 1)
              ");
              $checkActive->execute();
              $result = $checkActive->fetch();
              if ($checkActive->rowcount() < 1) {
                echo'
                <div class="box-body">
                  <div class="alert alert-info" role="alert">
                    <h3>There is no active Rights offering</h3>
                  </div>
                </div>'; 
                die();
              } 

              if ($result['start_at'] > $dateselect) {
                echo'
                <div class="box-body">
                  <div class="alert alert-info" role="alert">
                    <h3>The Rights Issue Subcription will open on : '.$result['start_at_format'].'</h3>
                  </div>
                </div>';
                die();
              } elseif($result['end_at'] < $dateselect) {
                echo'
                <div class="box-body">
                  <div class="alert alert-info" role="alert">
                    <h3>The Rights Issue Subcription has ended on : '.$result['end_at_format'].'</h3>
                  </div>
                </div>';
                die();
              }
            ?>
            <form action="" method="post" onsubmit="showLoading();">
              <div class="box-body">
                <div class="row">
                    <input type="hidden" name="start_at" id="start_at" value="<?php echo $result['start_at']; ?>">
                    <input type="hidden" name="end_at" id="end_at" value="<?php echo $result['end_at']; ?>">
                    <div class="col-lg-3 col-md-3 col-sm-12">
                      <label>CD Code <font color="red">*</font></label>
                      <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="cdepCode(this.value);" required>
                    </div>
                    <div id="cd"></div>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-lg-6">
                  <button type="button" class="btn btn-primary" style="display: none;" name="riSave" id="riSave"><i class="fa fa-save"></i> Save</button>
                </div>
              </div>
            </form>
          </div>

          <div class="box">
            <div class="box-header with-border"><strong>Search Subscribe CD Code</strong></div>
            <div class="box-body">
              <form action="" method="POST">
                <div class="input-group col-lg-6 col-md-6">
                  <input type="text" name="search_cid_no" id="search_cid_no" class="form-control" placeholder="Enter CID Number">
                  <span class="input-group-btn">
                    <button class="btn btn-primary" type="button" id="searchCD_Code" name="searchCD_Code">Search</button>
                  </span>
                  <div id="search_erro_msg" style="color: red;"></div>
                </div>
              </form>
              <div id="dtls_id" style="display: none;"></div>
            </div>
          </div>

          <div class="box">
            <div class="box-header with-border"><strong>Search Renounce CD Code</strong></div>
            <div class="box-body">
              <form action="" method="POST">
                <div class="input-group col-lg-6 col-md-6">
                  <input type="text" name="search_cid_no_renounce" id="search_cid_no_renounce" class="form-control" placeholder="Enter CID Number">
                  <span class="input-group-btn">
                    <button class="btn btn-primary" type="button" id="searchCD_Code_renounce" name="searchCD_Code_renounce">Search</button>
                  </span>
                  <div id="search_erro_msg" style="color: red;"></div>
                </div>
              </form>
              <div id="renouce_cd_dtls_id" style="display: none;"></div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="box">
                <div class="box-header with-border"><strong>Generate List</strong></div>
                <div class="box-body">
                  <div class="col-lg-6 col-md-6 col-sm-12">
                    <label>From Date <font style="color: red;">*</font></label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                    </div>
                    <span id="f_dataErr" style="color: red;"></span>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12">
                    <label>To Date <font style="color: red;">*</font></label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                    </div>
                    <span id="t_dataErr" style="color: red;"></span>
                  </div>
                </div>
                <div class="box-footer">
                  <div class="col-lg-6">          
                    <button type="button" class="btn btn-success" id="generate_rights" name="generate_rights"><i class="fa fa-list"></i> List</button>
                  </div>
                </div>
              </div>
              <div class="box" style="display: none;" id="tableListId">
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
      data: { rightIsueCD: val },
      dataType: 'html',
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  $("#riSave").click( function () {
    showLoading();
    var cd = $("#cdcode").val();
    var type = $("#options").val();
    var avlVol = $("#availableVolume").val();
    var rights = $("#rights_issued").val();
    var cid = $("#cid").val();
    var symbol_id = $("#rights_symbol_id").val();
    var face_value = $("#face_value").val();
    var operation = "save_right_issue";

    //This logic was put in  to accomodate auction
    var availableAmt = $("#availableAmount").val();
    var bidPrice = $("#bidPrice").val();
    var totalVol = $("#volume").val();
    var announcement_id = $("#announcement_id").val();

    if (type == 'B') {
      if (totalVol < 100) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Volume cannot be less than 100</div>");
        showMessage();
        return false;
      }

      if (bidPrice < face_value) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Bid Price cannot be less than "+face_value+"</div>");
        showMessage();
        return false;
      }

      /*if (bidPrice % 1 != 0) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Bid Price can only be whole number</div>");
        showMessage();
        return false;
      }*/

      var totalBidPrice = Number(bidPrice) * Number(totalVol);

      if (availableAmt < totalBidPrice) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Insufficient Amount</div>");
        showMessage();
        return false;
      }
    }

    if (type == "S") {
      var subscribe = $("#subscribe1").val();
      if (subscribe == '' || subscribe == 0) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Subscribe Volume cannot be 0 or empty!</div>"); 
        showMessage();
        return false;
      }
      
      var renounceCd = '';
      var renVol = '';
      var bidVol = '';
      var bidPrice = '';
    } 
    else if(type == "R") {
      var renounceCd = $("#rencdcode").val();
      var renVol = $("#renounce1").val();
      var subscribe = '';
      var bidVol = '';
      var bidPrice = '';

      if (cd == renounceCd) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> The CD code cannot be the same as Renouncer</div>"); 
        showMessage();
        return false;
      }

      if($("#renAvailableAmount").val() == "" || $("#renAvailableAmount").val() == 0) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Required Amount</div>"); 
        showMessage();
        return false;
      }

      if(renVol == "") {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Required Renounce Volume</div>"); 
        showMessage();
        return false;
      }

      if(renounceCd == "") {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Required Cd Code to Renounce</div>"); 
        showMessage();
        return false;
      }
    } 
    else if(type == "O") {
      var renounceCd = '';
      var renVol = '';
      var subscribe = '';
      var bidVol = $("#offerVol1").val();
      var bidPrice = '';

      if(bidVol == "" || bidVol == 0) {
        hideloading();
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Enter Offer Volume</div>"); 
        showMessage();
        return false;
      }
    } 
    else {
      var bidVol = $("#volume").val();
      var bidPrice = $("#bidPrice").val();
      var subscribe = '';
      var renounceCd = '';
      var renVol = '';
    }

    var dataString = 'cdcode=' + cd + '&cid=' + cid + '&availableVolume='+ avlVol + '&rights=' + rights + '&symbol_id=' + symbol_id + '&options='+ type + '&subscribe1='+ subscribe + '&rencd='+ renounceCd +'&face_value=' + face_value + '&renounce1='+ renVol + '&bidPrice='+ bidPrice + '&bidVol='+ bidVol + '&save_right_issue='+ operation + '&announcement_id=' + announcement_id;
    if(cd == ''|| type == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      var closedate = $("#end_at").val();
      var presentdate = "<?php echo date("Y-m-d H:i:s");?>";

      if (presentdate > closedate) {
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Market is closed</div>");
        showMessage();
        hideloading();
      } else {
        hideloading();
        $.ajax({
          type: "POST",
          url: "../PROCESS/right_issue_process.php",
          data: dataString,
          dataType: "html",
          success: function(response) {
            hideloading();
            // location.reload();
            $("#message").html(response);
            showMessage();
          }
        });        
      }
    }
    return false;
  });

  $('#generate_rights').click(function() {
    showLoading();
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();
    var op = 'viewrights';

    if (fromDate == '') {
      $("#f_dataErr").html("Select From Date");
      hideloading();
      return false;
    }

    if (toDate == '') {
      $("#t_dataErr").html("Select To Date");
      hideloading();
      return false;
    }

    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&viewrights='+ op,
      success: function(response){
        hideloading();
        $("#tableListId").show();
        $("#details").html(response);
      }
    });
  });

  $('#from_date').click(function() {
    $("#f_dataErr").html("");
  });

  $('#to_date').click(function() {
    $("#t_dataErr").html("");
  });

  function fun(i) {
    showLoading();
    var id = document.getElementById('delete_rights'+i).value;
    var data = {
      order_id: id,
      delete_rights_order_sub_ren: 'delete_rights_order_sub_ren'
    };
    
    if (confirm("Are you sure you want to delete ?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/right_issue_process.php",
        data: data,
        dataType: 'html',
        success: function(response) {
          hideloading();
          var data = JSON.parse(response);

          const statusMsg = $('<div>').addClass('alert alert-success alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
              $('<i>').addClass('icon fa fa-check'), data.message
          );

          $("#message").html(statusMsg);
          showMessage();

          if (data.status == 200) {
            $(`tr[data-id="${i}"]`).remove();
          }
        }
      });
    } else {
      hideloading();
      return false;
    }
  }

  $("#searchCD_Code").click( function () {
    var cid_no = $("#search_cid_no").val();

    // if (cid_no === '') {
    //   $("#search_erro_msg").html("Enter CID Number");
    //   return false;
    // }

    $.ajax({
      type: "POST",
      url: "../PROCESS/right_issue_process.php",
      data: { search_cd_code: cid_no },
      dataType: 'html',
      success: function(data) {
        $("#dtls_id").show();
        $("#dtls_id").html(data);
      }
    });

    // return false;
  });

  $("#searchCD_Code_renounce").click( function () {
    var cid_no = $("#search_cid_no_renounce").val();
    $.ajax({
      type: "POST",
      url: "../PROCESS/right_issue_process.php",
      data: { search_cd_code_for_renounce: cid_no },
      dataType: 'html',
      success: function(data) {
        $("#renouce_cd_dtls_id").show();
        $("#renouce_cd_dtls_id").html(data);
      }
    });
  });

  $("#searchCD_Code").click( function () {
    $("#search_erro_msg").html("");
  });
</script>
</html>
