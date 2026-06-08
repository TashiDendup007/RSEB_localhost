<?php 
    include('sessionStartFile_cdscss.php');
    include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
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
         <li><a href="#">Bank Branch</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Bank Branch</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="" method="POST" onsubmit="showLoading();">
          <div class="box-body">
            <div class="row">
             <div class="col-lg-6 col-md-6">
                <label>Bank Name</label>
                <select name="bank_id" id="bank_id"  class="form-control">
                  <option value=""> Select Bank </option>
                <?php
                  $wc = $dbh->prepare("SELECT bank_name, bank_id FROM banks");
                  $wc->execute();
                  while ($res = $wc->fetch()) {
                    echo'<option value="'.$res['bank_id'].'">'.$res['bank_name'].'</option>';
                  }
                ?>
                </select>
              </div>  
              <div class="col-lg-6 col-md-6">
               <label>Branch Name</label>
                <input type="text" class="form-control" name="branch_name" id="branch_name"  required>
              </div> 
              <div class="col-lg-12">
               <label>Branch Address</label>
                <input type="text" class="form-control" name="branch_address" id="branch_address"  required>
              </div> 
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" value="<?php echo $_SESSION['sess_username'];?>" name="save_branch" id="save_branch"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header" style="font:8px;">
              <h4 class="box-title">List of Bank Branch</h4>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sl.No</th>
                    <th>Bank Name</th>
                    <th>Branch Name</th>
                    <th>Edit</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $query = $dbh->prepare('SELECT a.BRANCH_ID, a.BRANCH_NAME, a.BRANCH_ADDRESS, b.bank_name 
                      FROM bank_branch a 
                      JOIN banks b ON a.bank_id = b.bank_id');
                    $query->execute();
                    $io = 1;
                    while ($result=$query->fetch(PDO::FETCH_ASSOC)) {
                      echo '
                      <tr>
                        <td> '.$io++.'</td>
                        <td> '.$result['bank_name'].'</td>
                        <td> 
                          <input type="text" class="col-xs-8" class="form-control" value="'.$result['BRANCH_NAME'].'" name="braname" id="braname'.$result['BRANCH_ID'].'">
                        </td>
                        <td>
                          <button type="button" class="btn btn-success btnpress" data-toggle="modal" data-target="#myModal" name="edit_branch" id="'.$result['BRANCH_ID'].'" ><i class="fa fa-edit"></i> Update</button>
                      </td>
                    </tr>';
                    }
                  $query->closeCursor();
                  ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
<?php include('../NAV/footer.php'); ?>  
</body>
<script language=JavaScript>
$(".btnpress").click( function(event) {
  // $('#loading').fadeIn(2000);
   var id = $(this).attr('id');
   var bra_name = $("#braname"+id).val();
   if(confirm('Do you want to updated, Branch name as' +bra_name +'?')) {
      $.post("e-cds-css.php", {
          bra_id:id, 
          bra_name:bra_name,
      },
      function(data) {
        $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Saved Successfully.</div></div></div>");
        showMessage();
      });
    } else {
      return false;
    }
  });

$("#save_branch").click( function() {
  showLoading();
  var bankId = $("#bank_id").val();
  var branchName = $("#branch_name").val();
  var branchAddress = $("#branch_address").val();
  var operation = "save_branch";
  var dataString = 'bank_id='+ bankId + '&branch_name='+ branchName +'&branch_address='+ branchAddress + '&save_branch='+ operation;
  
  if(bankId === '' || branchName === '' || branchAddress === '') {
    alert("Please Fill All Mandatory Fields");
    hideloading();
  } else {
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString ,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#message").html(data);
        showMessage();
        // location.reload();
      }
    });
  }
  return false;
});
</script>
</html>
