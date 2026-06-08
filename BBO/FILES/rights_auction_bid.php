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
           <li><a href="#">Right Auction</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Rights Auction</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
            <?php 
              $dateselect = date("Y-m-d H:i:s");

              if ('2025-07-02 09:00:00' > $dateselect) {
                echo'
                <div class="box-body">
                  <div class="alert alert-info" role="alert">
                    <h3>The Rights Auction Bid will open on : 2nd July 2025 09:00 AM</h3>
                  </div>
                </div>';
                die();
              } elseif('2025-07-04 18:00:00' < $dateselect) {
                echo'
                <div class="box-body">
                  <div class="alert alert-info" role="alert">
                    <h3>The Rights Auction Bid has ended on : 4th July 2025 05:00 PM</h3>
                  </div>
                </div>';
                die();
              }
            ?>
            <form action="" method="post" onsubmit="showLoading();">
              <div class="box-body">
                <div class="row">
                    <input type="hidden" name="start_at" id="start_at" value="2025-07-02 09:00:00"> <?php // echo $result['start_at']; ?>
                    <input type="hidden" name="end_at" id="end_at" value="2025-07-04 18:00:00"> <?php // echo $result['end_at']; ?>
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
            <div class="box-header with-border">
              <strong>Search CD Code</strong>
            </div>
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
      url: "load_auction_bid.php", 
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
    var cid = $("#cid_no").val();
    var symbol_id = $("#rights_symbol_id").val();
    var bidPrice = Number($("#bidPrice").val());
    var totalVol = Number($("#volume").val());
    var operation = "rights__auction__process";

    var closedate = $("#end_date").val();

    if (bidPrice < 11) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid Price cannot be less than 11</div>");
      showMessage();
      return false;
    }

    if (bidPrice > 50.27) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid Price cannot be greater than 50.47</div>");
      showMessage();
      return false;
    }

    if (Math.round(bidPrice * 100) % 5 !== 0) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid Price must be multiple of 0.05</div>");
      showMessage();
      return false;
    }

    if (totalVol < 100) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Volume cannot be less than 100</div>");
      showMessage();
      return false;
    }

    if (!Number.isInteger(totalVol) || totalVol <= 0) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Volume must be a natural number.</div>");
      showMessage();
      return false;
    }

    if (totalVol % 10 != 0) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Volume must be multiple of 10.</div>");
      showMessage();
      return false;
    }

    var total_bid_amount = (Number(bidPrice) * Number(totalVol)) + (Number(bidPrice) * Number(totalVol) * 0.02);

    var dataString = 'cdcode=' + cd + '&cid=' + cid + '&symbol_id=' + symbol_id + '&options='+ type + '&bidPrice='+ bidPrice + '&bidVol='+ totalVol + '&total_bid_amount='+ total_bid_amount + '&save__rights__auction='+ operation + '&closing_date=' + closedate;

    if(cd == ''|| type == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      var presentdate = "<?php echo date("Y-m-d H:i:s");?>";

      if (presentdate > closedate) {
        $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Market is closed</div>");
        showMessage();
        hideloading();
      } else {

        if (confirm("Total Amount is Nu. " + total_bid_amount + ". Do you want to Continue?")) {
          showLoading();
          $.ajax({
            type: "POST",
            url: "../PROCESS/rights__auction__process.php",
            data: dataString,
            dataType: "html",
            success: function(response) {
              hideloading();
              $("#message").html(response);
              showMessage();
            }
          });
        } else {
          hideloading();
          return false;
        }
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
