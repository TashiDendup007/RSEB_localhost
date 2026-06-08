<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if($role!="7")
  {
    header('Location: ../../access.php?err=2');
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); 
    }
  }
  $_SESSION['timeout'] = time();
  include ('../../CONNECTIONS/db.php');

  $symbol = "GSL";
  //$str_length = 7;
  $getLastCdCode = $dbh->prepare("SELECT c.cd_code FROM custodial_account c ORDER BY c.client_Id DESC LIMIT 1");
  $getLastCdCode->execute();
  $result = $getLastCdCode->fetch();
  $lastNumber = substr($result["cd_code"], 3);
  $newNumber = $lastNumber+1;
  //$str = substr("0000000{$newCdCode}", -$str_length);
  $number = sprintf('%07d', $newNumber);
  $newCdCode = $symbol.$number;
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-yellow sidebar-mini"><div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <div class="content-wrapper">
    <div class="box-body" id="message" style="display: none;"></div>
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Account</a></li>      
      </ol>
      <?php 
          $errors = array(1=>"Operation Successfully Completed.",2=>"Oops Sorry! There was an error while operation.",3=>"Record Updated Successfully.",4=>"Record Already Exists.",5=>"Record Deleted Successfully.");
          $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
          if ($error_id == 1) 
          { 
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          else if ($error_id == 2) 
          {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          else if ($error_id == 3) 
          {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          else if ($error_id == 4) 
          {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          else if ($error_id == 5) 
          {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
      ?>
    </section>
    <section class="content">
      <div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Volume Entry</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
          <div class="box-body"><br>
            <div class="row">  
              <div class="col-xs-4">
                <label>Cd Code/ CID No<font color="red">*</font></label>
                <input type="text" class="form-control" name="cdCode" id="cdCode" maxlength="10" onChange="getDetails(this.value);" required>
              </div>
              <div id="dtlsId"></div>
              <div class="col-xs-4" id="symbolId">
                <label>Symbol<font color="red">*</font></label>
                <select id="symbol" name="symbol" class="form-control" required>
                  <option value="">--Select--</option>
                  <?php
                  $q=$dbh->prepare("SELECT s.symbol_id, s.symbol, s.name FROM symbol s WHERE s.security_type='CS' ORDER BY s.symbol ASC");
                  $q->execute();
                  foreach($q as $state)
                  {
                    echo '<option value="'.$state['symbol_id'].'">'.$state['name'].'</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-xs-4" id="volumeId">
                <label>Volume <font color="red">*</font></label>
                <input type="number" class="form-control" name="volume" id="volume" min="1" step="1" required>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
                <button type="submit" class="btn btn-primary" style="" id="save_custodial_cds" name="save_custodial_cds" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-align-justify"></i> SAVE</button>
            </div>
          </div>
        </form>
        <div class="row">
          <div class="col-xs-12">
            <!-- <div class="box"><br>
              <table id="tableId" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr>
                    <th>Sl.No</th>
                    <th>Name</th>
                    <th>Cd Code</th>
                    <th>CID No</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <button type="button" class="btn btn-info" onclick="editDtls('')"></button>
                  <?php
                  /*$sql = $dbh->prepare("SELECT * FROM custodial_account ORDER BY cd_code DESC");
                  $sql->execute();
                  $i=1;
                  foreach ($sql as $res) {
                    echo'
                    <tr>
                      <td>'.$i.'</td>
                      <td>'.$res["f_name"].' '.$res["l_name"].'</td>
                      <td>'.$res["cd_code"].'</td>
                      <td>'.$res["ID"].'</td>
                      <td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-info" onclick="editDtls('.$res["client_Id"].')"><i class="fa fa-edit"></i></button></td>
                    </tr>';
                    $i++;
                  }*/
                  ?>
                </tbody>
              </table>
            </div> -->
          </div>
        </div>
      </div>
    </section>
  </div>
<?php include('../NAV/footer.php') ?>  
</body>
<script type="text/javascript">
  function getDetails(cdOrCidNo){
    var op = "getIndiDtls";
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'getIndiDtls='+op+'&id='+cdOrCidNo, 
      success: function(data){ 
        $("#dtlsId").html(data);
      } 
    });
  }
</script>
<script type="text/javascript">
  function editDtls(id){
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data:'edit_custodial_dtls='+id, 
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }
</script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#tableId').DataTable({
        "order": [[ 0, "asc" ]]
    });
   $('select[name=example_length]').append($("<option></option>").attr("value","-1").text("All"));
  });
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
</html>
