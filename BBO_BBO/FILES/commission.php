<?php
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');

  $check = $dbh->prepare('SELECT a.institution_id,c.participant_code FROM adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un');
  $check->bindParam(':un', $username);
  $check->execute();
  $res = $check->fetch();
  $institution_id = $res['institution_id'];
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
        <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
          <h1>
            <small></small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
             <li><a href="#">Commission</a></li>      
          </ol>
          <?php 
          $errors = array(1=>"Operation Successfully Completed.",2=>"There was an error while operation.",3=>"Record Updated Successfully.");
          $error_id = isset($_GET['msg']) ? (int)$_GET['msg'] : 0;
          if ($error_id == 1) {
          echo'
          <div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> '.$errors[$error_id].'
          </div></div></div>';
          } else if ($error_id == 2) {
            echo'
            <div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> '.$errors[$error_id].'
            </div>';
          }
          ?>
        </section>
        <section class="content">
          <?php include('../NAV/orderNav.php'); ?>
          <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">Commission</h4>
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
                  <div class="col-lg-6 col-md-6 col-sm-12">
                    <label>Commission Name<font color="red">*</font></label>
                    <input type="text" class="form-control" name="commission_name" id="commission_name" required>
                  </div>              
                  <div class="col-lg-6 col-md-6 col-sm-12">
                    <label>Rate<font color="red">*</font></label>
                    <input type="text" class="form-control" name="rate" id="rate" required>
                  </div>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-lg-6">
                  <button type="button" class="btn btn-primary" value="<?php echo $_SESSION['sess_username'];?>" name="save_commission" id="save_commission"><i class="fa fa-save"></i> Save</button>
                </div>
              </div>
            </form>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <div class="box">
                <div class="box-header with-border" style="font:8px;">
                  <h4 class="box-title">List of Commission</h4>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>Sl.No</th>
                          <th>Commission Name</th>
                          <th>Rate</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php 
                        $query= $dbh->prepare("SELECT * FROM bbo_commission WHERE institution_id=:inid");
                        $query->bindParam(':inid', $institution_id);
                        $query->execute();
                        $io = 1;
                        while ($result=$query->fetch(PDO::FETCH_ASSOC)) {
                          echo'
                          <tr>
                            <td>'.$io++.'</td>
                            <td><input type="text" class="form-control" value="'.$result['commission_name'].'" name="cname" id="cname'.$result['bro_comm_id'].'"></td>
                            <td><input type="text" size="8" class="form-control" value="'.$result['rate'].'" name="crate" id="crate'.$result['bro_comm_id'].'"></td>
                            <td>
                              <ul class="list-inline">
                                <li>
                                  <button class="btnpress btn btn-info" name="edit_commission" id="'.$result['bro_comm_id'].'" data-toggle="tooltip" data-placement="top" title="Edit Commission"><i class="fa fa-edit"></i></button>
                                </li>
                                <li>
                                  <form action="b-edit.php" method="post" onsubmit="confirmation();">
                                    <input type="hidden" class="form-control" name="commission_id" id="commission_id" value="'.$result['bro_comm_id'].'">
                                    <button type="submit" class="btn btn-danger" name="delete_commission" id="delete_commission"  data-toggle="tooltip" data-placement="top" title="Delete Commission"><i class="fa fa-trash-o"></i></button>
                                  </form>
                                </li>
                              </ul>
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
    <?php include('../NAV/footer.php') ?>
  </div>
</body>
<script language=JavaScript>
  $(".btnpress").click(function(event){
    var id = $(this).attr('id');
    var c_name = $("#cname"+id).val();
    var c_rate = $("#crate"+id).val();
    var dataString = 'c_name='+c_name+'&c_rate='+c_rate+'&id='+id;
    if (confirm('Update Commission Name as '+c_name+' and Rate as '+c_rate+'?')) {
      showLoading();
      $.ajax({
        type: "POST",
        url: "b-edit.php",
        data: dataString,
        dataType: 'html',
        success: function(data){
          hideloading();
          $("#message").html(data);
          showMessage();
        }
      });
    }else{
      event.preventDefault();
      return false;
    }
  });

  function confirmation(){
    if(confirm("Are you sure want to delete commission?")){
      showLoading();
      return true;
    }else{
      event.preventDefault();
      return false;
    }
  }

  $("#save_commission").click(function(){
    showLoading();
    var commission_name = $("#commission_name").val();
    var rate = $("#rate").val();
    var operation = "save_commission";
    var dataString = 'commission_name='+ commission_name + '&rate='+ rate +'&save_commission='+ operation;
    if (commission_name=='' || rate=='') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType: 'html',
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }
    return false;
  });
</script>
</html>
