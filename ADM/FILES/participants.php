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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Participants</li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <form action="" method="POST" onsubmit="showLoading();">
          <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">Participants Registration</h4>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                  <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                  <i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-3 col-md-3">
                  <label>Participant Type<span style="color:red;">*</span></label>
                  <select class="form-control" ng-model="cn" name="Type" id="Type">
                    <option value="">--Select Participant Type--</option>
                    <option value="MEMBER">MEMBER</option>
                    <option value="COMPANIES">COMPANIES</option>
                    <option value="GOVT">GOVT</option>
                    <option value="EMPLOYEE">EMPLOYEE</option>
                    <option value="CLIENT">CLIENT</option>
                  </select>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="Pcode">Participant Code<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="Pcode" id="Pcode" value="{{cn | limitTo: 3}}{{org | limitTo: 4}}" readonly="">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Institution Name<span style="color:red;">*</span></label>
                  <?php  
                    $stmt = $dbh->prepare("SELECT institution_id, name FROM adm_institution");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo'
                    <select name="Ins" id="Ins" class="form-control">
                    <option value="">--Select Institution--</option>';
                    foreach ($results as $row) {
                        $id = htmlentities($row['institution_id'], ENT_QUOTES);
                        $name = htmlentities($row['name'], ENT_QUOTES);
                        echo '<option value="'.$id.'">'.$name.'</option>';
                    }
                    echo'</select>';
                    $dbh = null;
                  ?>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="cp">Contact Person<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="cp" id="cp" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="phone">Mob/Tel Phone<span style="color:red;">*</span></label>
                  <input type="number" class="form-control"  name="phone" id="phone" onKeyPress="if(this.value.length==8) return false;" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="email">Email<font color="red">*</font></label>
                  <input type="email" class="form-control" name="email" id="email">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="org">Organization<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" ng-model="org" name="org" id="org" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="ca">Clearing Account<span style="color:red;">*</span></label>
                  <input type="text" class="form-control"  name="ca" id="ca" required>
                </div>
                <div class="col-lg-12 col-md-12">
                  <label for="add">Address<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="add" id="add" required>
                </div>
              </div>
            </div>
            <span>NOTE: Fields marked (<font color="red">*</font>) are mendatory</span>
            <div class="box-footer">
              <div class="col-xs-4">
                <button class="btn btn-primary" id="save_participant" type="button"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </div>
         </form> 

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Search Participant</h4>
              </div>
              <div class="box-body">
                <form action="" method="POST">
                  <div class="box-body">
                    <div class="row" ng-app="">
                      <div class="col-lg-3 col-md-3">
                        <label for="searchPCode">Participant code</label>
                        <input type="text" class="form-control" name="searchPCode" id="searchPCode">
                      </div>
                      <div class="col-lg-3 col-md-3">
                        <label for="searchname">Organization Name</label>
                        <input type="text" class="form-control" name="searchname" id="searchname">
                      </div>
                      <div class="col-lg-3 col-md-3">
                        <label for="searchConPerson">Contact Person</label>
                        <input type="text" class="form-control" name="searchConPerson" id="searchConPerson">
                      </div>
                      <div class="col-lg-3 col-md-3">
                        <label for="searchphone">Phone No</label>
                        <input type="number" class="form-control" name="searchphone" id="searchphone">
                      </div>
                      <div class="col-lg-12 col-md-12">
                        <label for="searchaddress">Address</label>
                        <input type="text" class="form-control" name="searchaddress" id="searchaddress">
                      </div>
                    </div>
                  </div>
                  <div class="box-footer">
                    <div class="col-lg-6 col-md-6">
                      <button type="button" class="btn btn-primary" id="search_id"><i class="fa fa-search"></i> Search</button>
                    </div>
                  </div>
                </form>
              </div>
              <div class="box-body">
                <div id="search_details"></div>
              </div>

            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?>
</body>
<script type="text/javascript">
  $("#save_participant").click( function(event) { 
    event.preventDefault();
    showLoading();

    var participantType = $("#Type").val();
    var participantCode = $("#Pcode").val();
    var institution = $("#Ins").val();
    var contactPerson = $("#cp").val();
    var phone = $("#phone").val();
    var email = $("#email").val();
    var organisation = $("#org").val();
    var address = $("#add").val();
    var ca = $("#ca").val();
    var operation = "save_participant";
    
    var data = {
      Type: participantType,
      Pcode: participantCode,
      Ins: institution,
      cp: contactPerson,
      phone: phone,
      email: email,
      org: organisation,
      add: address,
      save_participant: operation,
      ca: ca
    };

    if(participantType === '' || participantCode == '' || institution == '' || contactPerson === '' || email === '' || phone === '' || organisation === '' || address === '' || ca === '') {
      $("#message").html('<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Please Fill All Mandatory Fields</div></div>');
      showMessage();
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        },
        error: function(xhr, status, error) {
          hideloading();
          console.log(error);
        }
      });
    }
  });

  $("#search_id").on("click", function() {
    var $pCodeField = $("#searchPCode");
    var $orgNameField = $("#searchname");
    var $conPersonField = $("#searchConPerson");
    var $phoneField = $("#searchphone");
    var $addressField = $("#searchaddress");
    
    var data = {
      participant_code: $pCodeField.val(),
      org_name: $orgNameField.val(),
      contact_person: $conPersonField.val(),
      phone: $phoneField.val(),
      address: $addressField.val(),
      search_participant: "search_participant"
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php",
      data: data , 
      dataType: 'html',
      success: function(data){ 
        hideloading(); 
        $('#search_details').html(data); 
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Error: "+textStatus+' ,'+errorThrown);
      }
    });
  });

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "bbo-adm.php",
      data:{ edit_part: val },
      dataType: 'html',
      success: function(data){
        $("#myModal").html(data);
        $("#myModal").modal();
      }
    });
  }

  function deleteParticipant(val) {
    showLoading();
    if (confirm("Are you sure you want to delete record Id # " + val + "?")) {
      const operation = "delete_participant";
      const data = { delete_id: val, delete_participant: operation };
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: $.param(data),
        dataType: "html",
        success: function (response) {
          hideloading();
          var data = JSON.parse(response);
          const statusMsg = $('<div>').addClass('alert alert-success alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
              $('<i>').addClass('icon fa fa-check'), data.message
          );

          $("#message").html(statusMsg);
          showMessage();

          if(data.status == 200){
            $(`tr[data-id="${val}"]`).remove();
          }
        },
        error: function () {
          hideloading();
          const statusMsg = $('<div>').addClass('alert alert-danger alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
            $('<i>').addClass('icon fa fa-check'),
            ' Message! Oops sorry! There was an error while operation.'
          );

          $("#message").html(statusMsg);
          showMessage();
        }
      });
    } else {
      hideloading();
      return false;
    }
  }

  // Cache jQuery objects
  var $type = $("#Type");
  var $pcode = $("#Pcode");
  var $institution = $("#Ins");
  var $contactPerson = $("#cp");
  var $phone = $("#phone");
  var $email = $("#email");
  var $organisation = $("#org");
  var $address = $("#add");
  var $ca = $("#ca");
</script>
</html>
