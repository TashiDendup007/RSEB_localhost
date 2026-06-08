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
    <div class="box-body" id="message" style='display: none;'></div>
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Pledgee</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Pledgee</h4>
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
                <label>Pledgee Name</label>
                <input type="text" class="form-control" name="pledgee_name" id="pledgee_name"  required>
              </div>              
              <div class="col-lg-6 col-md-6">
                <label>Address</label>
                <input type="text" class="form-control" name="address" id="address" required>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-primary" value="<?php echo $_SESSION['sess_username'];?>" name="save_pledge" id="save_pledge"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header" style="font:8px;">
              <h4 class="box-title">List of Pledgee</h4>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Sl.No</th>
                      <th>PLEDGEE NAME</th>
                      <th>ADDRESS</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $query = $dbh->prepare("SELECT * FROM cds_pledgee");
                    $query->execute();
                    $io = 1;
                    while ($result=$query->fetch(PDO::FETCH_ASSOC)) {
                      echo'
                      <tr>
                        <td> '.$io++.' </td>
                        <td>
                          <input type="text" class="col-xs-8" class="form-control" value="'.$result['pledgee'].'" name="pname" id="pname'.$result['pledgee_id'].'">
                        </td>
                        <td>
                          <input type="text" class="col-xs-8" class="form-control" value="'.$result['address'].'" name="padd" id="padd'.$result['pledgee_id'].'">
                        </td>
                        <td>
                          <button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-success btnpress" name="edit_pledgee" id="'.$result['pledgee_id'].'"><i class="fa fa-edit"></i> </button>
                          
                          <button type="button" class="btn btn-danger" name="edit_pledgee" id="edit_pledgee" onclick="deletePlg('.$result['pledgee_id'].')"><i class="fa fa-trash"></i> </button>

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
  function deletePlg(id) {
    if (confirm("Are you sure you want to delete?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: { delete_pledegee: id },
        dataType: "html",
        success: function (response) {
          $("#message").html(response);
          showMessage();
          // location.reload(); 
        }
      });
    } else {
      return false;
    }
  }

  $(".btnpress").click(function(event){  
    var id = $(this).attr('id');
    var pname = $("#pname"+id).val();
    var padd = $("#padd"+id).val();

    if(confirm('Update it as,'+pname+'?')) {
      $.post("e-cds-css.php", {
          pid:id, 
          pname:pname,
          padd:padd,
       },
      function(data) {
        $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Saved Successfully.</div></div></div>");
        showMessage();
      }
    );
   } else {
      return false;
   }
});

$("#save_pledge").click(function(){
  showLoading();  
  var pledgee_name = $("#pledgee_name").val();
  var address = $("#address").val();
  var operation = "save_pledge";
  var dataString = 'pledgee_name='+ pledgee_name + '&address='+ address +'&save_pledge='+ operation;
  
  if(pledgee_name=='' || address=='') {
    alert("Please Fill All Mandatory Fields");
    hideloading();
  } else {
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString,
      dataType: "html",
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
