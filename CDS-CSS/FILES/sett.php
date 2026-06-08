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
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Settlement</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Settlement Cycle</h4>
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
                <label>Settlement Name</label>
                <input type="text" class="form-control" name="set_name" id="set_name" required>
              </div>         
               <div class="col-lg-6 col-md-6">
                <label>Type</label>
                <input type="number" max='3' class="form-control" name="set_day" id="set_day" required>
              </div> 
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-success" id="save_sett" name="save_sett" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Save </button> 
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header" style="font:8px;">
              <h4 class="box-title">Settlement Cycle</h4>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>Settlement Name</th>
                    <th>Settlement Day</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $query = $dbh->prepare('SELECT * FROM css_settlement_cycle');
                    $query->execute();
                    $i = 1;
                    while($result=$query->fetch(PDO::FETCH_ASSOC)) {
                      echo'
                      <tr>
                        <td> '.$i++.'</td>
                        <td> 
                          <input type="text"  class="form-control" value="'.$result['name'].'" name="name" id="name'.$result['sett_id'].'">
                        </td>
                        <td>
                          <input type="text" size="9" class="form-control" value="'.$result['days'].'" name="days" id="days'.$result['sett_id'].'">
                        </td>
                        <td>
                          <button type="button" class="btn btn-success btnpress" name="edit_set" id="'.$result['sett_id'].'"><i class="fa fa-edit"></i> Update</button>
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
  $(".btnpress").click(function(event){  
    var id = $(this).attr('id');
    var set_name = $("#name"+id).val();
    var set_day = $("#days"+id).val();

    if(confirm(set_day)) {
      $.post("e-cds-css.php", {
          set_id:id,
          set_name:set_name, 
          set_day:set_day,
      },
      function(response) {
        $("#message").html("<div class='row'><div class='col-lg-12 col-xs-12'><div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-check'> </i> Saved Successfully.</div></div></div>");
        showMessage();
      });
    } else {
      return false;
    }           
  });

$("#save_sett").click(function(){ 
  showLoading();    
  var setName = $("#set_name").val();
  var setDay = $("#set_day").val();
  var operation = "save_sett";
  var dataString = 'set_name='+ setName + '&set_day='+ setDay + '&save_sett='+ operation;           
  
  if (setName === '' || setDay === '') {
    alert("Please Fill All Mandatory Fields");
    hideloading();
  } else {
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString ,
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
