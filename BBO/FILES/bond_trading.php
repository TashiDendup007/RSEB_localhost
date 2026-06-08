<?php 
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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Bond New Order</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
            <div class="box-header with-border text-center">
              <h4 class="box-title"><strong>Bond New Order</strong></h4>
            </div>
            <div class="box-body">
              <form action="" method="POST">
                <div class="row form-horizontal">
                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>CD CODE<font color="red">*</font></label>
                    <input type="text" name="cd_code" id="cd_code" class="form-control" maxlength="10" onChange="get_client_dtls(this.value);" required>
                    <input type="hidden" name="order_type" id="order_type" class="form-control" value="OTC">
                  </div>

                  <div class="col-lg-8 col-md-8 col-sm-12">
                    <label>Client Details</label>
                    <input type="hidden" name="acc_type" id="acc_type" class="form-control" readonly>
                    <input type="text" name="cd_dtls" id="cd_dtls" class="form-control" readonly>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Security Type<font color="red">*</font></label>
                    <select name="sec_type" id="sec_type" class="form-control" onChange="get_symbols_list(this.value);">
                      <option value="">-Security Type-</option>';
                      <?php 
                        $stmt = $dbh->prepare("SELECT s.id, s.security_type, s.precise_name FROM security_type_masters s WHERE s.`status` = 1 AND s.precise_name != 'OS'");
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($rows as $key => $value) {
                          echo'<option value="' . $value['precise_name'] . '">' . $value['security_type'] . '</option>';
                        }
                      ?>
                    </select>
                  </div>

                  <div id="symbol_div_id"></div>
                  <div id="bond_details_id"></div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Order Side<font color="red">*</font></label>
                    <select name="side" id="side" class="form-control" onchange="get_vol_fun(this.value);" required>
                      <option value="">-Select Side-</option>
                      <option value="S" style="color:red;">SELL</option>
                      <option value="B" style="color:blue;">BUY</option>
                    </select>
                  </div>

                  <div id="holding_vol_div"></div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Volume<font color="red">*</font></label>
                    <input type="number" name="volume" id="volume" class="form-control" step="10" min="10" required>
                    <small id="vol_error" style="color:red; display:none;"></small>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Price<font color="red">*</font></label>
                    <input type="number" name="price" id="price" class="form-control" required>
                    <small id="price_error" style="color:red; display:none;"></small>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Accured Interest</label>
                    <input type="number" name="accur_int" id="accur_int" class="form-control" readonly>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Dirty Price (Payable/Receivable Price Per Share)</label>
                    <input type="number" name="dirty_price" id="dirty_price" class="form-control" readonly>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Yield To Maturity (YTM)</label>
                    <input type="number" name="ytm_id" id="ytm_id" class="form-control" readonly>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Broker Commission</label>
                    <input type="number" name="bro_commission" id="bro_commission" class="form-control" readonly>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12" id="avl_amt_div">
                    <label>Available Amount</label>
                    <input type="number" name="avl_amount" id="avl_amount" class="form-control" readonly>
                  </div>

                </div>
              </form>
            </div>
            <div class="box-footer text-center">
              <button type="button" class="btn btn-primary" id="submit_bond_order"><i class="fa fa-save"></i> Submit</button>
              <button type="button" class="btn btn-warning" id="submit_reset_btn"><i class="fa fa-times"></i> Cancel</button>
            </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript" src="../js/bond_script.js"></script>
</html>
