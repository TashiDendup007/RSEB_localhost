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
    <div id="message"></div>
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Holiday</a></li>      
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Holidays</h4>
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
              <label>Holiday Name</label>
              <input type="text" class="form-control" name="hol_name" id="hol_name" required>
            </div>         
            <div class="col-lg-6 col-md-6">
              <label>Holiday Date</label>
              <input type="date" class="form-control" name="hol_date" id="hol_date" required>
            </div> 
          </div>
        </div>
        <div class="box-footer">
        <div class="col-lg-6 col-md-6">
        <button type="button" class="btn btn-success" id="save_hol" name="save_hol" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Save </button> 
        </div>
        </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border" style="font:8px;">
              <h4 class="box-title">List of Holiday</h4>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Sl</th>
                      <th>Holiday Name</th>
                      <th>Holiday Date (YYYY-MM-DD)</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $query = $dbh->prepare('SELECT * FROM holiday');
                    $query->execute();
                    $i = 1;
                    while ($result=$query->fetch(PDO::FETCH_ASSOC)) {
                      $status_rmk = ($result['status'] == 1) ? 'Active' : 'Inactive';
                      echo '
                      <tr>
                        <td>' . $i++ . '</td>
                        <td><input type="text"  class="form-control" value="'.$result['hol_name'].'" name="hol_name" id="hol_name'.$result['id'].'"></td>
                        <td><input type="text" size="9" class="form-control" value="'.$result['holiday_date'].'" name="holiday_date" id="holiday_date'.$result['id'].'"></td>
                        <td>' . $status_rmk . '</td>
                        <td>
                          <button type="button" class="btn btn-info btnpress" name="edit_hol" id="'.$result['id'].'" data-toggle="tooltip" data-placement="top" title="Update"><i class="fa fa-edit"></i> </button>
                          <button type="button" class="btn btn-danger btndelete" name="delete_hol" id="'.$result['id'].'" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fa fa-trash-o"></i> </button>
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
  // Update function
  $(".btnpress").click(function(event){
     var id = $(this).attr('id');
     var hol_name = $("#hol_name"+id).val();
     var holiday_date = $("#holiday_date"+id).val();

     if(confirm(hol_name + ' ' + holiday_date + ' --- ' + id)) {
        $.post("e-cds-css.php", {
            hol_id: id,
            hol_name: hol_name, 
            holiday_date: holiday_date,
        },
        function(data) {
          $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Saved Successfully.</div></div></div>");
          showMessage();
        });
     } else {
        return false;
     }
  });

  // Delete Function
  $(".btndelete").click(function(event) {
     var id = $(this).attr('id');
     var hol_name = $("#hol_name" + id).val();
     var holiday_date = $("#holiday_date" + id).val();

     if(confirm(hol_name + ' ' + holiday_date + ' --- ' + id)) {
        $.post("e-cds-css.php", {
            hol_id: id,
            delete_holiday: 'delete_holiday',
        },

        function(data) {
          $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Deleted Successfully.</div></div></div>");
          showMessage();
        });
     } else {
        return false;
     }
  });

  function getState2(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'mat='+val,
      dataType: "html",
      success: function(response){ 
        $("#cd").html(response);
      } 
    });
  }

  $("#save_hol").click(function(){
    showLoading(); 
    var holName = $("#hol_name").val();
    var holDate = $("#hol_date").val();
    var operation = "save_hol";
    var dataString = 'hol_name='+ holName + '&hol_date='+ holDate + '&save_hol='+ operation;

    if(holName == '' || holDate == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } 
    else {
      $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString ,
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
