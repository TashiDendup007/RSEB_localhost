<?php 
    include('sessionStartFile_cdscss.php');
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
         <li><a href="#">Symbol</a></li>
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Symbol Creation</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="" method="post" onsubmit="showLoading();">
          <div class="box-body">
            <div class="row">
              <div class="col-xs-4">
                <label for="exampleInputEmail1">ISIN</label>
                <input type="text" class="form-control" name="isin" id="isin" required>
              </div>
              <div class="col-xs-4">
                <label for="exampleInputEmail1">Symbol</label>
                <input type="text" class="form-control" name="sy" id="sy" required>
              </div>
              <div class="col-xs-4">
                <label for="exampleInputEmail1">Name</label>
                <input type="text" class="form-control" name="name" id="name" required>
              </div>
              <div class="col-xs-4">
                <label>Sector</label>
                <select class="form-control">
                  <option>CDS & CSS</option>
                  <option>BackOffice & Order Mgmt.</option>
                  <option>Administrator</option>
                </select>
              </div>
              <div class="col-xs-4">
                <label for="exampleInputEmail1">Face Value</label>
                <input type="number" min="1" class="form-control" name="fv" id="fv" required="">
              </div>
              <div class="col-xs-4">
                <label for="exampleInputEmail1">Premium Value</label>
                <input type="number" min="1" class="form-control" name="pv" id="pv" >
              </div>
              <div class="col-xs-4">
                <label>Security Type</label>
                <select class="form-control">
                  <option>Ordinary Shares</option>
                  <option>Corporate Bonds</option>
                  <option>Government Bonds</option>
                </select>
              </div>
              <div class="col-xs-4">
                <label>Status</label>
                <select class="form-control">
                  <option>Active</option>
                  <option>InActive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
<?php include('../NAV/footer.php'); ?>  
</body>
</html>
