<?php 
  include ('../FILES/session_start_file.php');
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
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Report</a></li>      
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">BOND Allocation summary</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-lg-6">
                <label>Symbol <font color="red">*</font></label>
                <select class="form-control" name="symbol_id" id="symbol_id" required>
                  <option value="">--Select Symbol--</option>
                  <?php
                    $getSymbol = $dbh->prepare("SELECT DISTINCT b.symbol_id,  s.symbol
                        FROM bond b 
                        JOIN symbol s ON b.symbol_id = s.symbol_id 
                        LEFT JOIN assign_broker a ON s.symbol_id = a.symbol
                        WHERE a.username = ?
                        ORDER BY s.symbol ASC
                    ");
                    $getSymbol->execute([$username]);
                    $rows = $getSymbol->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $key) {
                      echo'<option value="'.$key['symbol_id'].'">'.$key['symbol'].'</option>';
                    }
                  ?>
                </select>
                <span id="symbol_err" style="color: red;"></span>
              </div>
              <div class="col-lg-12">
                <?php
                  /*echo"
                  <table class='table table-striped'>
                    <td><b>CD CODE</b></td>             
                    <td><b>SYMBOL</b></td>             
                    <td><b>NAME</b></td>             
                    <td><b>BID VOL</b></td>
                    <td><b>RATE</b></td>
                    <td><b>AMT</b></td>
                    <td><b>ALLOCATED UNIT(s)</b></td>";
                  $save = $dbh->prepare('SELECT b.cd_code,b.bid_price,b.order_size,b.allocated_size,s.symbol,s.name,s.face_value*b.allocated_size as amt from bond b,symbol s where s.symbol_id=73 and s.symbol_id=b.symbol_id order by bid_price ASC');
                  $save->execute();
                  foreach($save as $price){
                   // $amt=$price['bid_price']*1000;
                   echo"<tr class='blink' style=' background-color: #c8c8c8 ;'>";
                   echo"<td >".$price['cd_code']."</b></td>";
                   echo"<td >".$price['symbol']."</b></td>";
                   echo"<td >".$price['name']."</b></td>";
                   echo"<td >".number_format($price['order_size'])."</td>";
                   echo"<td >".$price['bid_price']."</td>";                   
                   echo"<td >".number_format($price['amt'])."</td>";                   
                   echo"<td >".number_format($price['allocated_size'])."</td>";                   
                   }
                  echo"</table>";*/
                ?>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6">
              <button type="button" class="btn btn-success" name="generateAllocation" id="generateAllocation"><i class="fa fa-list"></i> Generate</button>
            </div>
          </div>
          <div id="summary_dtls"></div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>
</body>
<script type="text/javascript">
  $("#generateAllocation").click( function() {
    var symbolFld = $("#symbol_id");
    var operation = "bond_allocation_summary";

    if (symbolFld.val() == "") {
      $("#symbol_err").html("Select Symbol");
      return false;
    }
    var data = {
      symbol_id: symbolFld.val(),
      bond_allocation_summary: operation
    };
    showLoading();
    $.ajax({
      type: "POST",
      url: "bond_load.php",
      data: data,
      dataType: "html",
      success: function(response) {
        console.log(response);
        hideloading();
        $("#summary_dtls").html(response);
      }
    });

  });

  $("#symbol_id").click( function (){
    $("#symbol_err").html("");
  });
</script>
</html>