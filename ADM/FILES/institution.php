<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-purple sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div id="message"></div>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Institute</a></li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Institution Creation</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="POST" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row">
                
                <div class="col-lg-6 col-md-6">
                  <label for="ins_name">Institution Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="ins_name" id="ins_name" required>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label for="gst_register">GST Registered?<span style="color:red;">*</span></label>
                  <select class="form-control" name="gst_register" id="gst_register" required>
                    <option value="">-- Select --</option>
                    <option value="Y">YES</option>
                    <option value="N">NO</option>
                  </select>
                </div>

                <!-- <div class="col-lg-6 col-md-6">
                  <label for="C_Person">Contact Person<span style="color:red;">*</span></label>
                  <input type="text" class="form-control"  name="C_Person" id="C_Person" required>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label for="Phone">Phone No.<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" onKeyPress="if(this.value.length==8) return false;" name="Phone" id="Phone" required>
                  <span id="errln" style="color:red;display:none;">*Please enter numbers only</span>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label for="ca">Clearing Account</label>
                  <input type="text" class="form-control"  name="ca" id="ca" required>
                </div> -->

                <div class="col-lg-12 col-md-12">
                  <label for="Address">Address<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="Address" id="Address" required>
                </div>
              </div>
              <span>NOTE: Fields marked (<font color="red">*</font>) are mendatory</span>
            </div>
            <div class="box-footer">
              <div class="col-lg-4 col-md-4">
                <button class="btn btn-primary" id="save_ins" type="button"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Search Institution</h4>
              </div>
              <form action="" method="POST">
                <div class="box-body">
                  <div class="row">
                    <div class="col-lg-4 col-md-4">
                      <label for="searchname">Institute Name</label>
                      <input type="text" class="form-control" name="searchname" id="searchname">
                    </div>
                    <!-- <div class="col-lg-4 col-md-4">
                      <label for="searchConPerson">Contact Person</label>
                      <input type="text" class="form-control" name="searchConPerson" id="searchConPerson">
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <label for="searchphone">Phone No</label>
                      <input type="number" class="form-control" name="searchphone" id="searchphone">
                    </div> -->
                    <div class="col-lg-8 col-md-8">
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
              <div class="box-body">
                <div id="search_details"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>
</body>
<script type="text/javascript">
  $("#save_ins").click(function(event){ 
    event.preventDefault();
    showLoading();

    var insNameField = $("#ins_name");
    var addressField = $("#Address");
    var gstField = $("#gst_register");
    var operation = "save_institute";

    var data = {
      Ins_Name: insNameField.val(),
      Address: addressField.val(),
      gst_reg: gstField.val(),
      save_institute: operation,
    };

    if(insNameField.val() === '' || addressField.val() === '' || gstField.val() === '') {
      $("#message").html('<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Please Fill All Mandatory Fields</div></div>');
      showMessage();
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#ins_name").val('');
          $("#Address").val('');
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
    var $orgNameField = $("#searchname");
    var $addressField = $("#searchaddress");
    
    var data = {
      inst_name: $orgNameField.val(),
      address: $addressField.val(),
      search_institution: "search_institution"
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
      data:{ edit_inst: val },
      dataType: 'html',
      success: function(response){
        $("#myModal").html(response);
      }
    });
  }

  function delete_inst(val) {
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      showLoading();
      var data = {
        delete_institute: 'delete_institute',
        institute_id: val,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data ,
        dataType: 'json', 
        success: function(response) {
          hideloading();
          // var data = JSON.parse(response);
          var statusMsg = '<div class="col-lg-12 col-md-12"><div class="alert alert-info alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message! '+response.message+'.</div></div>';
          if(response.status == 200){
            $(`tr[data-id="${val}"]`).remove();
          }
          $("#message").html(statusMsg);
          showMessage();
        }
      });
     }else{
        return false;
     }
   }

  $("#Phone").keyup('input', function() {
    var phoneLength = $("#Phone").val();
    var flag=/^[0-9]+$/.test(phoneLength);
    if(!flag)
    {
      $("#errln").show();
      $("#Phone").addClass("errorClass");
    }
    else
     {
      $("#errln").hide(10);
      $("#Phone").removeClass("errorClass");
    }        
  });
</script>
</html>
