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
           <li><a href="#">Bank</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bank</h4>
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
                <div class="col-lg-6 col-md-6">
                  <label>Bank Name</label>
                  <input type="text" class="form-control" name="bank_name" id="bank_name" required>
                </div>   
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" value="<?php echo $_SESSION['sess_username'];?>" name="save_bank" id="save_bank"><i class="fa fa-database"></i> Save</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header" style="font:8px;">
                <h4 class="box-title">List of Bank</h4>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Bank NAME</th>
                        <th>Edit</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php 
                      $query = $dbh->prepare('SELECT * FROM banks');
                      $query->execute();
                      $io = 1;
                      while($result = $query->fetch(PDO::FETCH_ASSOC)) {
                        echo'
                        <tr>
                          <td> '.$io++.'</td>
                          <td>
                            <input type="text" class="col-xs-6" class="form-control" value="'.$result['bank_name'].'" name="bname" id="bname'.$result['bank_id'].'">
                          </td>
                          <td>
                            <button type="button" class="btn btn-success btnpress" data-toggle="modal" data-target="#myModal" name="edit_bank" id="'.$result['bank_id'].'""><i class="fa fa-edit"></i> Update</button>
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
     var b_name = $("#bname"+id).val();
     if(confirm('Update name as,' +b_name+'?')) {
      $.post("e-cds-css.php", {
            bank_id:id, 
            bank_name:b_name,
          },
          function(response) {
            $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Saved Successfully.</div></div></div>");
            showMessage();
          });
      } else {
        return false;
      }
  });

  $("#save_bank").click( function () { 
    showLoading();
    var bankName = $("#bank_name").val();
    var operation = "save_bank";
    var dataString = 'bank_name='+ bankName + '&save_bank='+ operation;
    if(bankName === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType : "html",
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
          // location.reload();
        }
      });
    }
    return false;
  });
</script>
</html>
