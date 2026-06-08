<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if ($role != "6") {
    header('Location: ../../access.php?err=2');
  }

  $inactive = 1500;
  if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) { 
      header("Location: ../../Authentication/Logout.php"); 
    }
  }
  $_SESSION['timeout'] = time();
  
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); 
  date_default_timezone_set("Asia/Thimphu"); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <div class="login-box-body" id='loadingover' style='display: none;'>
    <div id='loadingmsg' style='display: none; color:#fff;'></div>
  </div>

  <div class="wrapper">
    <?php include('../NAV/navigation.php') ?>
    
    <div class="content-wrapper">
      <div class="box-body" id="message" style='display: none;'></div>
      
      <section class="content-header">
        <h1>Sector Index Update</h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Sector Index Update</a></li>      
        </ol>

        <?php 
          $errors = array(1=>"Operation Successfully Completed.",2=>"Oops Sorry! There was an error while operation.");
          $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
          if ($error_id > 0) { 
            echo '<div class="row">
                    <div class="col-lg-12 col-xs-12">
                      <div class="alert alert-'.($error_id == 1 ? "success" : "danger").' alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="icon fa '.($error_id == 1 ? "fa-check" : "fa-ban").'"></i> 
                        Message! '.$errors[$error_id].'
                      </div>
                    </div>
                  </div>';
          }
        ?>
      </section>

      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title"><strong>Sector Index</strong></h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
          </div>

          <form action="../PROCESS/process.php" method="post" class="form-horizontal">
            <div class="box-body">
              <div class="row">
                <div class="col-xs-12">
                  
                  <!-- Sector Type Selection -->
                  <div class="col-xs-2">
                    <label>Sector Type:<font color="red">*</font></label>
                  </div>
                  <div class="col-xs-8">
                    <select id="sectorType" name="sectorType" class="form-control" required onchange="fetchBaseDivisor()">
                      <option value="">-- Select --</option>
                      <?php
                      $query = $dbh->prepare("SELECT DISTINCT sector_type FROM sector_index");
                      $query->execute();
                      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $row['sector_type'] . '">' . strtoupper($row['sector_type']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>

                  <!-- Base Divisor -->
                  <div class="col-xs-12">&nbsp;</div>
                  <div class="col-xs-2">
                    <label>Base Divisor:</label>
                  </div>
                  <div class="col-xs-8">
                    <div class="form-inline" id="divisorDiv">
                      <input type="number" step="any" id="divisor" name="divisor" class="form-control" readonly="true" style="width:430px;">
                      <input type="button" class="btn btn-primary form-control" value="Click here to enter new Divisor Base" onclick="showEntry();">
                    </div>
                    <div class="form-inline" style="display: none;" id="divisorNewDiv">
                      <input type="number" step="any" id="divisorNew" name="divisorNew" class="form-control" style="width:600px;">
                      <input type="button" class="btn btn-warning form-control" value="Cancel" onclick="cancelEntry();">
                    </div>
                  </div>

                  <!-- Action Selection -->
                  <div class="col-xs-12">&nbsp;</div>
                  <div class="col-xs-2">
                    <label>Action:<font color="red">*</font></label>
                  </div>
                  <div class="col-xs-8">
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

                  <!-- Remarks -->
                  <div class="col-xs-12">&nbsp;</div>
                  <div class="col-xs-2">
                    <label>Remarks:<font color="red">*</font></label>
                  </div>
                  <div class="col-xs-8">
                    <textarea id="remarks" name="remarks" class="form-control" required></textarea>
                  </div>
                </div>
              </div>

              <br>
              <div class="box-footer">
                <button type="submit" name="corpActionSector" value="corpAction" class="btn btn-success">Submit</button>
              </div>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>

  <?php include('../NAV/footer.php') ?>  



  <script type="text/javascript">
    function fetchBaseDivisor() {
      var sectorType = $("#sectorType").val();

      if (sectorType) {
        $.ajax({
          url: "fetch_divisor.php",  // 🔹 Separate PHP file for fetching divisor
          type: "POST",
          data: { sectorType: sectorType },
          success: function(response) {
            if (response.trim() !== '') {
              $("#divisor").val(response);
            } else {
              $("#divisor").val(''); // Clear if no data found
            }
          },
          error: function() {
            alert("Error fetching base divisor. Please try again.");
          }
        });
      } else {
        $("#divisor").val('');  // Clear if no sector is selected
      }
    }
  </script>

  <script type="text/javascript">
    function showLoading() {
      document.getElementById('loadingmsg').style.display = 'block';
      document.getElementById('loadingover').style.display = 'block';
    }
    function hideloading() {
      document.getElementById('loadingmsg').style.display = 'none';
      document.getElementById('loadingover').style.display = 'none';
    }
  </script>
  <script type="text/javascript">
    function showEntry(){
      $('#divisorDiv').hide();
      $('#divisorNewDiv').show();
      $('#divisorNew').attr('required', '');
    }
    function cancelEntry(){
      $('#divisorDiv').show();
      $('#divisorNewDiv').hide();
      $('#divisorNew').removeAttr('required');
      $('#divisorNew').val('');
    }
  </script>


</body>
</html>
