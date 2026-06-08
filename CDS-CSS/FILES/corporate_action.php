<?php
  include ('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <?php include('../../CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
  <?php include('../NAV/navigation.php'); ?>
  <div class="content-wrapper">
    <div id="message"></div>
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Corporate Action</a></li>
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Corporate Creation</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="" method="post" onsubmit="showLoading();">
          <div class="box-body">
            <div class="box-body">
              <div class="row">
               <div class="col-lg-4 col-md-6 col-sm-12">
                  <label>Announcement Type</label>
                  <select name="announcement_type" id="announcement_type" class="form-control" onchange="showFields(this.value)" required>
                    <option value="">--Select Symbol--</option>
                    <?php
                      $wc = $dbh->prepare("SELECT m.id, m.corporate_name FROM corporate_action_masters m WHERE m.status = 1 ORDER BY m.corporate_name ASC ");
                      $wc->execute();
                      while ($res = $wc->fetch()) {
                        echo '<option value="'.$res['id'].'">'.$res['corporate_name'].'</option>';
                      }
                    ?>
                  </select>
                </div>
                <script type="text/javascript">
                  function showFields(val) {
                    if (val == 4) {
                      $("#exDateDivId").hide();
                      $("#buyBack_div_id").show();
                    } else {
                      $("#exDateDivId").show();
                      $("#buyBack_div_id").hide();
                    }
                  }
                </script>
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <label>Symbol</label>
                  <select name="sy" id="sy" class="form-control">
                    <option value="">--Select Symbol--</option>
                    <?php
                      $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE security_type IN ('OS')");
                      $wc->execute();
                      while ($res = $wc->fetch()) {
                        echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                      }
                    ?>
                  </select>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <label>Record Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="record_date" id="record_date" onChange="return checkDate();" required>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12" id="exDateDivId">
                  <label>Ex Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="ex_date" id="ex_date" required>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <label>Announcement Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="announcement_date" id="announcement_date" onChange="return checkDate();" required>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <label>Rate [<span style="font-weight: normal!important;">Rights/ Bonus/ Dividend/ Buy Back %</span>]</label>
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i>%</i>
                    </div>
                    <input type="number" class="form-control" onKeyPress="if(this.value.length==5) return false;" name="rate" id="rate" step="any" min="1" max="100" required>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12" id="buyBack_div_id" style="display: none;">
                  <label>Buy Back Price (Nu.)</label>
                  <input type="number" name="buyBack_price" id="buyBack_price" class="form-control" required>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <label>Type</label>
                  <select class="form-control" name="type" id="type">
                    <option value="Interim">Interim</option>
                    <option value="Final">Final</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="button" class="btn btn-primary" name="save_corporate_announcement" id="save_corporate_announcement"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border" >
              <h4 class="box-title">Search Corporate Announcement</h4>
            </div>
            <div class="box-body">
              <form action="" method="POST">
                <div class="col-lg-6">
                  <label>Announcement Type <font color="red">*</font></label>
                  <select name="announcement_type_srh" id="announcement_type_srh" class="form-control" required>
                    <option value="">--Select Symbol--</option>
                    <?php
                      $wc = $dbh->prepare("SELECT m.id, m.corporate_name FROM corporate_action_masters m WHERE m.status = 1 ORDER BY m.corporate_name ASC ");
                      $wc->execute();
                      while ($res = $wc->fetch()) {
                        echo '<option value="'.$res['id'].'">'.$res['corporate_name'].'</option>';
                      }
                    ?>
                  </select>
                  <span id="annTypeErr" style="color: red;"></span>
                </div>
                <div class="col-lg-6">
                  <label>Symbol <font color="red">*</font></label>
                  <select name="symbol_srh" id="symbol_srh" class="form-control">
                    <option value="">--Select Symbol--</option>
                    <?php
                      $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE security_type IN ('OS')");
                      $wc->execute();
                      while ($res = $wc->fetch()) {
                        echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                      }
                    ?>
                  </select>
                  <span id="symbolError" style="color: red;"></span>
                </div>
                <div class="col-lg-6">
                  <br>
                  <button type="button" class="btn btn-info btn-flat" id="serach_id"><i class="fa fa-search"></i> Search</button>
                </div>
              </form>
            </div>
            <div class="box-footer">
              <div id="detailsId"></div>
            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
</div>
<?php include('../NAV/footer.php'); ?>
<script type="text/javascript">
  $("#serach_id").click(function() {
    var annTypeFld = $("#announcement_type_srh");
    var symbolFld = $("#symbol_srh");
    var operation = "search_corporation_announcement";

    if (annTypeFld.val() == "") {
      $("#annTypeErr").html("Select Announcement Type");
      return false;
    }

    if (symbolFld.val() == "") {
      $("#symbolError").html("Select Symbol");
      return false;
    }

    var data = {
      announce_type: annTypeFld.val(),
      symbol: symbolFld.val(),
      search_corporation_announcement: operation,
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php", 
      data: data, 
      dataType: 'html',
      success: function(response) {
        $("#detailsId").html(response);
      } 
    });
  });

  $('#announcement_type_srh').click(function() {
    $("#annTypeErr").html("");
  });

  $('#symbol_srh').click(function() {
    $("#symbolError").html("");
  });

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

  $("#save_corporate_announcement").click(function() {
    showLoading();
    var announcementType = $("#announcement_type"),
        symbol = $("#sy"),
        announcementDate = $("#announcement_date"),
        recordDate = $("#record_date"),
        rate = $("#rate"),
        type = $("#type");

    var operation = "save_corporate_announcement";

    var exdateVal = '0000-00-00';
    var buyBack_price = 0;
    if (announcementType.val() != 4) {
      exdateVal = $("#ex_date").val();
    } else {
      buyBack_price = $("#buyBack_price").val();
    }
    
    var data = {
      announcement_type: announcementType.val(),
      sy: symbol.val(),
      announcement_date: announcementDate.val(),
      record_date: recordDate.val(),
      exdate: exdateVal,
      rate: rate.val(),
      type: type.val(),
      price: buyBack_price,
      save_corporate_announcement: operation
    };

    // Validate user input
    if (announcementType.val() === '' || symbol.val() === '' || announcementDate.val() === '' || recordDate.val() === '' || rate.val() === '' || type.val() === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(data) {
          hideloading();
          $("#message").html(data);
          showMessage();
        }
      });
    }
    return false;
  });

</script>
</body>
</html>
