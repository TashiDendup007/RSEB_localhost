<?php 
  include('../FILES/session_file.php');
  include ('../../CONNECTIONS/db.php');
  date_default_timezone_set("Asia/Thimphu");
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
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
          <li><a href="ptrs_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">BSI Update</a></li>      
        </ol>
        <?php 
          $errors = array(1=>"Operation Successfully Completed.",2=>"Oops Sorry! There was an error while operation.");
          $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
          if ($error_id == 1) 
          { 
          echo'<div class="row"><div class="col-lg-12 col-lg-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          else if ($error_id == 2) 
          {
          echo'<div class="row"><div class="col-lg-12 col-lg-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
        ?>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title"><strong>Bhutan Stock Index</strong></h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="post" class="form-horizontal">
            <div class="box-body">
              <div class="row">
                <div class="col-lg-12">
                  <div class="col-lg-2 col-md-2">
                    <label>Base Divisor:</label>
                  </div>
                  <div class="col-lg-10 col-md-10">
                    <?php
                    $save = $dbh->prepare("SELECT m.base, m.created_date FROM market_index m ORDER BY m.id DESC LIMIT 1");
                    $save->execute();
                    $res = $save->fetch(PDO::FETCH_ASSOC);
                    $base = isset($res['base']) ? $res['base'] : 0;
                    echo'
                    <div class="form-inline" id="divisorDiv">
                      <input type="number" step="any" id="divisor" name="divisor" value="'.$base.'" class="form-control" readonly="true" style="width:430px;">
                      <input type="button" class="btn btn-primary form-control" value="Click here to enter new Divisor Base" onclick="showEntry();">
                    </div>
                    ';
                    ?>
                    <div class="form-inline" style="display: none;" id="divisorNewDiv">
                      <input type="number" step="any" id="divisorNew" name="divisorNew" class="form-control" style="width:600px;">
                      <input type="button" class="btn btn-warning form-control" value="Cancel" onclick="cancelEntry();">
                    </div>
                  </div>
                  <div class="col-lg-12">&nbsp;</div>
                  <div class="col-lg-2 col-md-2">
                    <label id="cp">Action:<font color="red">*</font></label>
                  </div>
                  <div class="col-lg-10 col-md-10">
                    <select id="cp" name="cp" class="form-control" required>
                      <option value="">-- Select --</option>
                      <option value="Bonus">Bonus</option>
                      <option value="Dividend">Dividend</option>
                      <option value="Rights">Rights</option>
                      <option value="BuyBack">BuyBack</option>
                      <option value="Delist">Delist</option>
                      <option value="New Listing">New Listing</option>
                      <option value="Price Change">Price Change</option>
                    </select>
                  </div>
                  <div class="col-lg-12">&nbsp;</div>
                  <div class="col-lg-2 col-md-2">
                    <label for="remarks">Remarks:<font color="red">*</font></label>
                  </div>
                  <div class="col-lg-10 col-md-10">
                    <textarea id="remarks" name="remarks" class="form-control" required></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="submit" id="submitId" name="bsiUpdate" value="bsiUpdate" class="btn btn-success"><i class="fa fa-tasks"></i> Submit</button>
                <button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>
              </div>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  function showEntry() {
    showLoading();
    $('#divisorDiv').hide();
    $('#divisorNewDiv').show();
    $('#divisorNew').attr('required', '');

    // get new divisor
    $.ajax({
      type: "POST",
      url: "loadPriceUpdate.php",
      data: 'loadNewDivisor=loadNewDivisor',
      dataType: "html",
      success: function(response){
        hideloading();
        var number = parseFloat(response);
        $("#divisorNew").val(number.toFixed(2));
      }
    });
  }

  function cancelEntry(){
    $('#divisorDiv').show();
    $('#divisorNewDiv').hide();
    $('#divisorNew').removeAttr('required');
    $('#divisorNew').val('');
  }
</script>
</html>