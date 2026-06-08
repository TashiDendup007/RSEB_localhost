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
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Account</a></li>
        </ol>
        <?php 
          $errors = array(1=>"Operation Completed Successfully",2=>"Oops Sorry! There was an error while operation.",3=>"Record Updated Successfully.");
          $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
          $alert_class = ($error_id == 2) ? 'warning' : 'success';

          if ($error_id) {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-'.$alert_class.' alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> '.$errors[$error_id].'</div></div></div>';
          }
        ?>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Account Registration</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="" method="POST">
            <div class="box-body">
              <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label>Account Type</label>
                  <select class="form-control" id="atype" name="atype" onChange="getState2(this.value);" required>
                    <option value="">--Select an account type--</option>
                    <option value="I">Individual</option>
                    <option value="J">Corporate</option>
                    <option value="A">Association</option>
                    <option value="R">Religious</option>
                  </select>
                </div>

                <div id="accType1Container">
                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="cid_no">CID<span style="color:red;">*</span></label>
                    <input type="number" class="form-control" name="cid_no" id="cid_no" onKeyPress="if(this.value.length==11) return false;" required>
                    <span id="errCid" style="color: red;"></span>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="title">Title<span style="color:red;">*</span></label>
                    <select class="form-control" name="title" id="title" required>
                      <option value="">-- Select --</option>
                      <?php 
                        $stmt = $dbh->prepare("SELECT id, title_name FROM title_master WHERE status = 1 ORDER BY title_name ASC");
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($rows as $row) {
                          echo'<option value="'.$row['title_name'].'">'.$row['title_name'].'</option>';
                        }
                      ?>
                    </select>
                  </div> 

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="first_name">First Name<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" name="first_name" id="first_name" autocomplete="off" required>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" name="last_name" id="last_name">
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Gender<span style="color:red;">*</span></label>
                    <select id="gender" name="gender" class="form-control" required>
                      <option value="">-- Select --</option>
                      <?php 
                        $stmt = $dbh->prepare("SELECT id, gender FROM tbl_gender_master WHERE status = 1 ORDER BY gender ASC");
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($rows as $state) {
                          echo '<option value="'.$state['id'].'">'.$state['gender'].'</option>';
                        }
                      ?>
                    </select>
                  </div>

                   <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Marital Status<span style="color:red;">*</span></label>
                    <select id="marital" name="marital" class="form-control" required>
                      <option value="">-- Select --</option>
                      <?php  
                      $stmt = $dbh->prepare("SELECT id, name FROM tbl_marital_status WHERE status = 1 ORDER BY name ASC");
                      $stmt->execute();
                      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach($rows as $state) {
                        echo '<option value="'.$state['id'].'">'.$state['name'].'</option>';
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>DOB<span style="color:red;">*</span></label>
                    <input type="date" class="form-control" name="dob" id="dob" onchange="checkAge(this.value)" required>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="nationality">Nationality<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" name="nationality" id="nationality" required>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="occupation">Occupation<span style="color:red;">*</span></label>
                    <select id="occupation" name="occupation" class="form-control" required>
                      <option value="">-- Select --</option>
                      <?php 
                        $q = $dbh->prepare("SELECT occupation, occupation_name FROM occupation ORDER BY occupation_name ASC");
                        $q->execute();
                        $occupation = $q->fetchAll(PDO::FETCH_ASSOC);
                        foreach($occupation as $state) {
                          echo'<option value="'.$state['occupation'].'">'.$state['occupation_name'].'</option>';
                        }
                      ?>
                    </select>
                  </div>
                </div>

                <div id="accType2Container" style="display: none;">
                  <div class="col-lg-8 col-md-8 col-sm-12">
                    <label for="company_name">Company/Association Name<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" name="company_name" id="company_name" required>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="licenseNo">Registration/License No<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" name="licenseNo" id="licenseNo" required>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="disn_no">DISN<span style="color:red;">*</span></label>
                    <input type="text" maxlength="11" class="form-control" name="disn_no" id="disn_no" onKeyPress="if(this.value.length==11) return false;" required>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label for="contact_person">Contact Person<span style="color:red;">*</span></label>
                    <input type="text" class="form-control" name="contact_person" id="contact_person" required>
                  </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label for="tpn">TPN<span style="color: red; padding: auto;" id="tpnAsterisk">*</span></label>
                  <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn">
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label for="phone">Phone No<span style="color:red;">*</span></label>
                  <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length === 8) return false;" autocomplete="off" required>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="email" autocomplete="off">
                </div>

                <div class="col-lg-4" id="guardian_div" style="display: none;">
                  <label for="guardian_name">Guardian Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="guardian_name" id="guardian_name">
                </div>

                <div class="clearfix"></div>
                <p style="padding-left: 18px; font-weight: bold; margin-top: 10px; color: #09895b;" class="text-center">Permanent Address</p>
                <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label>Dzongkhag<span style="color:red;">*</span></label>
                  <select id="dzongkhag_id" name="dzongkhag_id" class="form-control" onchange="populatelist(this.value, 'gewog_list', '')" required>
                    <option value="">-- Select --</option>
                    <?php  
                    $dzos = $dbh->prepare("SELECT DzongkhagID, DzongkhagName FROM tbldzongkhag ORDER BY DzongkhagName ASC");
                    $dzos->execute();
                    $rows = $dzos->fetchAll(PDO::FETCH_ASSOC);
                    foreach($rows as $state) {
                      echo'<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
                    }
                    ?>
                  </select>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label>Gewog<span style="color:red;">*</span></label>
                  <select id="gewog_id" name="gewog_id" class="form-control" onchange="populatelist(this.value, 'village_list', '')"  required>
                    <option value="">-- Select --</option>
                  </select>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label>Village<span style="color:red;">*</span></label>
                  <select id="village_id" name="village_id" class="form-control" required>
                    <option value="">-- Select --</option>
                  </select>
                </div>

                <div class="clearfix"></div>
                <p style="padding-left: 18px; font-weight: bold; margin-top: 10px; color: #09895b;" class="text-center">Bank Account Details</p>
                <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label for="bank">Bank<span style="color:red;">*</span></label>
                  <select id="bank" name="bank" class="form-control" required>
                    <option value="">-- Select Bank --</option>
                    <?php 
                      $stmt = $dbh->prepare("SELECT bank_id, bank_name FROM banks");
                      $stmt->execute();
                      $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach($banks as $state) {
                        echo'<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
                      }
                    ?>
                  </select>
                  <span id="bankError" style="color: red;"></span>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label for="account_no">Account Number<span style="color:red;">*</span></label>
                  <input type="number" class="form-control" name="account_no" id="account_no" required>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                  <label for="bankAccType">Account Type<font style="color:red;">*</font></label>
                  <select id="bankAccType" name="bankAccType" class="form-control" required>
                    <option value="">--Select Account Type--</option>
                    <option value="Saving Account">Saving Account</option>
                    <option value="Current Account">Current Account</option>
                  </select>
                </div>

                <div class="col-lg-12">
                  <label for="address">Address<font style="color:red;">*</font></label>
                  <textarea class="form-control" name="address" id="address" autocomplete="off" required></textarea>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6 col-sm-12">
                <button type="buton" style="display:none" class="btn btn-primary" id="save_client" name="save_client" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-save"></i> Save </button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Search Account</h4>
              </div>
              <div class="box-body">
                <form action="" method="POST">
                  <div class="col-lg-6 col-md-6">
                    <div class="input-group margin">
                      <input type="text" class="form-control" name="search_cid" id="search_cid" placeholder="Enter CID/ CD Code/ Name">
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-info btn-flat" id="serach_id"><i class="fa fa-search"></i> Search</button>
                        </span>
                    </div>
                    <span id="searchErr" style="color: red;"></span>
                  </div>
                </form>
                <div id="account_detail"></div>
              </div>
            </div>
          </div>

          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border"><h5 class="box-title">Generate List</h5></div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <label>From Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                  </div>
                  <span id="f_dateErr" style="color: red;"></span>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <label>To Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                  </div>
                  <span id="t_dateErr" style="color: red;"></span>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <button type="button" class="btn btn-success" id="accs" name="accs" value=""><i class="fa fa-list"></i>  List </button>
                </div>
              </div>
              <div id="details"></div>
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
    $("#occupation").select2();
    $("#dzongkhag_id").select2();
  });

  $("#save_client").click( function () {
    showLoading();
    var acc_type = $("#atype").val();
    var cid = (acc_type == 'I') ? $("#cid_no").val() : $("#disn_no").val();
    var title = (acc_type == 'I') ? $("#title").val() : '';
    var first_name = (acc_type == 'I') ? $("#first_name").val() : $("#company_name").val();
    var last_name = (acc_type == 'I') ? $("#last_name").val() : $("#contact_person").val();
    var occupation = (acc_type == 'I') ? $("#occupation").val() : 101;
    var nationality = (acc_type == 'I') ? $("#nationality").val() : '';
    var dzongkhag = $("#dzongkhag_id").val();
    var tpn_no = $("#tpn").val();
    var phone_no = $("#phone").val();
    var email = $("#email").val();
    var bank = $("#bank").val();
    var bank_acc_no = $("#account_no").val();
    var bank_acc_type = $("#bankAccType").val();
    var license_no = (acc_type == 'I') ? '' : $("#licenseNo").val();
    var address = $("#address").val();
    var user_name = $("#save_client").val();
    var commisssion = 0;

    var gender = (acc_type == 'I') ? $("#gender").val() : '';
    var marital = (acc_type == 'I') ? $("#marital").val() : '';
    var dob = (acc_type == 'I') ? $("#dob").val() : '1900-01-01';
    var gewog_id = $("#gewog_id").val();
    var village_id = $("#village_id").val();
    var guardian_name = ( acc_type === "I" ) ? $("#guardian_name").val() : '';

    var operation = 'save_client_dtls_new';

    if (acc_type === '' || cid === '' || first_name === '' || dzongkhag === ''|| gewog_id === ''|| village_id === '' || phone_no === '' || bank === '0' || bank_acc_no === '' || bank_acc_type === '' || address === '') {
      $("#message").html('<div class="col-lg-12 col-sm-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Fill all mandatory fields.</div></div>');
      showMessage();
      hideloading();
      return false;
    }

    if ((acc_type === 'I' && (title === '' || occupation === '' || nationality === '' || gender === '' || marital === '' || dob === '')) || (acc_type === 'J' && (license_no === '' || last_name === '' || tpn_no === ''))) {
      $("#message").html('<div class="col-lg-12 col-sm-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Fill all mandatory fields.</div></div>');
      showMessage();
      hideloading();
      return false;
    }

    if (acc_type === 'I' && cid.length < 11) {
      $("#message").html('<div class="col-lg-12 col-sm-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> CID Number should be 11 digits.</div></div>');
      showMessage();
      hideloading();
      return false;
    }

    var data = {
      atype: acc_type,
      title: title,
      fn: first_name,
      ln: last_name,
      occupation: occupation,
      nat: nationality,
      id: cid,
      dz: dzongkhag,
      tpn: tpn_no,
      phone: phone_no,
      email: email,
      bank: bank,
      accno: bank_acc_no,
      bankAccType: bank_acc_type,
      add: address,
      username: user_name,
      commission: commisssion,
      licenseNo: license_no,
      gender: gender,
      marital: marital,
      dob: dob,
      gewog_id: gewog_id,
      village_id: village_id,
      guardian_name: guardian_name,
      save_client_dtls_new: operation,
    };

    $.ajax({
      type: "POST", 
      url: "../PROCESS/process.php", 
      data: data, 
      dataType: 'html',
      success: function(response) {
        hideloading();
        $("#message").html(response);
        showMessage();
      } 
    });
  });

  function getState2 (val) {
    if (val == '') {
      $('#save_client').hide();
    } 
    else if (val == 'I') {
      $('#save_client').show();
      $("#accType1Container").show();
      $("#accType2Container").hide();
      $("#tpnAsterisk").hide();
      $('#tpn').prop('required', false);
    } 
    else if (['J', 'A', 'R'].includes(val)) {
      $('#save_client').show();
      $("#accType1Container").hide();
      $("#accType2Container").show();
      $("#tpnAsterisk").show();
      $('#tpn').prop('required', true);

      $.ajax({ 
        type: "POST", 
        url: "load.php", 
        data: { operation : 'get__DISN__No', acc_type : val }, 
        dataType: "html",
        success: function(response) {
          $("#disn_no").val(response);
        } 
      });
    }
  }

  function populatelist(value, name, serial) {
      var op = "populateList";
      $.ajax({
        type: "POST",
        url: "../PROCESS/load_function.php",
        data:"populateList=" + op + "&id=" + value + "&list_name=" + name,
        success: function(data) {
          if (name == "gewog_list") {
            $("#gewog_id").html(data);
          } else if(name == "village_list") {
            $("#village_id").html(data);
          }
        }
      });
  }

  function checkAge(dob) {
    const age = calculateAge(dob);
    if (age < 18) {
      $("#guardian_div").show();
      $("#guardian_name").attr('required', 'true');
    } else {
      $("#guardian_div").hide();
      $("#guardian_name").removeAttr('required');
    }
  } 

  function calculateAge(dob) {
    // Parse the date of birth string into a Date object
    const dobDate = new Date(dob);

    // Get the current date
    const currentDate = new Date();

    // Calculate the difference in years
    const age = currentDate.getFullYear() - dobDate.getFullYear();

    // Check if the birthday has occurred this year
    if (
      currentDate.getMonth() < dobDate.getMonth() ||
      (currentDate.getMonth() === dobDate.getMonth() &&
        currentDate.getDate() < dobDate.getDate())
    ) {
      // If the birthday hasn't occurred yet, subtract 1 from the age
      return age - 1;
    } else {
      // If the birthday has occurred, return the calculated age
      return age;
    }
  }

  $("#serach_id").click( function () {
    var cidNoField = $("#search_cid");
    var operation = "search_accounts";

    if (cidNoField.val() == "") {
      $("#searchErr").html("Field Required");
      return false;
    }

    var data = {
      cid_number: cidNoField.val(),
      search_accounts: operation,
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php", 
      data: data, 
      dataType: 'html',
      success: function(response) { 
        $("#account_detail").html(response);
      } 
    });
  });

  function getState(val) {
    $.ajax({
      type: "POST", 
      url: "e-cds-css.php",
      data:'edit_cli='+val, 
      success: function(response) { 
        $("#myModal").modal('show');
        $("#myModal").html(response);
      }
    });
  }

  function fun(io) {
    var val = document.getElementById('delete_cli'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
    } else {
      return false;
    }
  }

  $('#accs').click(function(){
    showLoading();
    var fromDateFld = $("#from_date").val();
    var toDateFld = $("#to_date").val();
    var operation = 'accs';

    if(fromDateFld == '') {
      hideloading();
      $("#f_dateErr").html("Select From date");
      return false;
    }
    if(toDateFld == '') {
      hideloading();
      $("#t_dateErr").html("Select To date");
      return false;
    }

    var data = {
      fromDate: fromDateFld,
      toDate: toDateFld,
      accs: operation,
    };

    $.ajax({
      type: "POST",
      url: "load.php",
      data: data,
      dataType: 'html',
      success: function(response) {
        hideloading();
        $("#details").html(response);
      }
    });
  });

  $('#from_date').click( function () {
    $("#f_dateErr").html("");
  });

  $('#to_date').click( function () {
    $("#t_dateErr").html("");
  });

  $("#search_cid").click( function (){
    $("#searchErr").html("");
  });
</script>
</html>
