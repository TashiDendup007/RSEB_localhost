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
         <li><a href="#">Occupation</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Occupation</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="" method="post"  onsubmit="showLoading();">
          <div class="box-body">
            <div class="row">
            <div class="col-xs-12">
               <label>Occupation Type</label>
                <input type="text" class="form-control" name="occ_name" id="occ_name"  required>
              </div>   
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
                <button type="button" class="btn btn-primary" value="<?php echo $_SESSION['sess_username'];?>" name="save_occ" id="save_occ"><i class="fa fa-database"></i> Submit</button>
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header" style="font:8px;">
              <h4 class="box-title">List of Occupation</h4>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Sl.No</th>
                      <th>Name</th>
                      <th>Edit</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $query = $dbh->prepare('SELECT * FROM occupation');
                    $query->execute();
                    $io = 1;
                    while($result = $query->fetch(PDO::FETCH_ASSOC)) {
                      echo '
                      <tr>
                        <td> '.$io++.'</td>
                        <td> 
                          <input type="text" class="col-xs-8" class="form-control" value="'.$result['occupation_name'].'" name="occ" id="occ'.$result['occupation'].'">
                        </td>
                        <td>
                          <button type="button" class="btn btn-success btnpress" data-toggle="modal" data-target="#myModal" name="edit_occ"   id="'.$result['occupation'].'" ><i class="fa fa-edit"></i> Update</button>
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
</div>
<?php include('../NAV/footer.php'); ?> 
</body>
<script language=JavaScript>
  $(".btnpress").click( function(event) { 
    var id = $(this).attr('id');
    var occ = $("#occ"+id).val();
    if(confirm('Do you want to update as, ' +occ+ '?')) {
      $.post("e-cds-css.php", {
        occ_id:id, 
        occ:occ,
      },
      function(data) {
        $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Saved Successfully.</div></div></div>");
        showMessage();
      });
    } else {
      return false;
    }
  });

  $("#save_occ").click( function() { 
    showLoading();
    var bankName = $("#occ_name").val();
    var operation = "save_occ";
    var dataString = 'occ_name='+ bankName + '&save_occ='+ operation;
    if(bankName === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
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
