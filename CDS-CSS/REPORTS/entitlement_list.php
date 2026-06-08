<?php 
  include('../FILES/sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
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
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Reports</a></li>      
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Entitlement List</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
              <label>Symbol</label>
              <select name="sy" id="sy" class="form-control" onChange="loadsymbol(this.value);">
                <option value=""> Select Symbol </option>
                <?php
                  $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE status = 1 AND security_type IN ('OS') ORDER BY symbol ASC");
                  $wc->execute();
                  while ($res = $wc->fetch()) {
                    echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                  }
                ?>
              </select>
            </div> 
            <div id="cd"></div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php') ?> 
  </div>
</body>
<script type="text/javascript">
  function loadsymbol(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'entel_load_report='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }
  
  function gefun(i) {
   if (confirm("Are you sure you want to generate ?")) {
      return true;
    } else {
        return false;
    }
  }
</script>
</html>