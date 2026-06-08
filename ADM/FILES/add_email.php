<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Add Email</a></li>
        </ol>
      </section>
      
      <div class="box-body" id="message"></div>

      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Add Broker Member Email</h4>
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
                <div class="col-lg-3">
                  <label for="email">Email: <span style="color:red;">*</span></label>
                  <input type="email" class="form-control" name="email" id="email" required>
                </div>

                <div class="col-lg-6">
                  <label for="institute_id">Institute:<span style="color:red;">*</span></label>
                  <select class="form-control" name="institute_id" id="institute_id" required>
                    <option value="">--Select Institute--</option>
                    <?php 
                    $stmt = $dbh->prepare("SELECT a.institution_id, a.name FROM adm_institution a ORDER BY a.name ASC");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($result as $key => $value) {
                      echo'<option value='.$value['institution_id'].'>'.$value['name'].'</option>';
                    }
                    ?>
                  </select>
                </div>

                <div class="col-lg-3">
                  <label for="member">Member Code:<span style="color:red;">*</span></label>
                  <select class="form-control" name="member" id="member" required>
                    <option value="">--Select Member Code--</option>
                    <?php 
                    $stmt = $dbh->prepare("SELECT p.participant_code FROM adm_participants p ORDER BY p.participant_code ASC ");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($result as $key => $value) {
                      echo'<option value='.$value['participant_code'].'>'.substr($value['participant_code'], 0, 3).'_'.substr($value['participant_code'], 3, 7).'</option>';
                    }
                    ?>
                  </select>
                </div>

                <div class="col-lg-3">
                  <label for="purpose">Purpose:<span style="color:red;">*</span></label>
                  <select class="form-control" name="purpose" id="purpose" required>
                    <option value="">--Select--</option>
                    <option value="trade_confirmation">Trade Confirmation</option>
                    <option value="clearing_detail">Clearing Detail</option>
                  </select>
                </div>

                <div class="col-lg-3">
                  <label for="e_type">Recipient Type:<span style="color:red;">*</span></label>
                  <select class="form-control" name="e_type" id="e_type" required>
                    <option value="">--Select--</option>
                    <option value="to">To</option>
                    <option value="cc">Cc</option>
                    <option value="bcc">Bcc</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-4">
                <button type="button" id="save_email" class="btn btn-primary"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>
        </div>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <div class="box-title">Broker Email List</div>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table id="table_email_id" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $stmt = $dbh->prepare("SELECT * FROM email_confirmation");
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $i = 1;
                    // error_log(print_r($rows, true));

                    foreach ($rows as $key => $value) {
                      echo'
                      <tr data-id="'.$value['id'].'">
                        <td>'.$i.'</td>
                        <td>'.substr($value['mem_code'], 0, 3).'___'.substr($value['mem_code'], 3, 7).'</td>
                        <td>'.$value['email_add'].'</td>
                        <td>
                          <button type="button" class="btn btn-danger" onclick="deleteEmail('.$value['id'].')" style="width: 100%; height: 100%; margin: 0;"> Delete</button>
                        </td>
                      </tr>';
                      $i++;
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  $( function () {
    $("#table_email_id").DataTable();
  });

  function deleteEmail(id) {
    const e_id = id;
    const op = 'deleteEmailConfirmation';
    if (confirm("Are you sure want to delete ?")) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: { deleteEmailConfirmation: op, id: e_id},
          dataType : 'html',
          success: function(response) {
            $("#message").html(response);
            showMessage();
            $(`tr[data-id="${e_id}"]`).fadeOut('slow');
          }
        });
    } else {
      return false;
    }
    
  }

  $("#save_email").click( function () {
    showLoading();
    var email = $("#email").val();
    var institute_id = $("#institute_id").val();
    var member = $("#member").val();
    var purpose = $("#purpose").val();
    var e_type = $("#e_type").val();
    var operation = "save_email_confirmation";

    var dataString = 'email='+ email +'&institute_id='+ institute_id + '&member='+ member +'&purpose='+ purpose + '&e_type='+ e_type + '&save_email_confirmation='+ operation;
    if(email === ''|| institute_id === ''|| member === '' || purpose === ''|| e_type === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString ,
        success: function(data){
          hideloading();
          $("#message").html(data);
          showMessage();
        }
      });
    }
    return false;
  });
</script>
</html>
