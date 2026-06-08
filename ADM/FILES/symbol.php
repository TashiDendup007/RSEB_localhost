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
    <div id="message"></div>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">symbol</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Symbol Creation</h4>
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
                <div class="col-lg-3 col-md-3">
                  <label for="isin">ISIN <span style="color:red;">*</span></label>
                  <input type="number" onKeyPress="if(this.value.length==11) return false;" class="form-control" name="isin" id="isin" required>
                  <span id="errISIN" style="color:red;display:none;">*Please enter only numbers</span>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="sy">Symbol <span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="sy" id="sy" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="name">Company Name <span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="name" id="name" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Sector <font color="red">*</font></label>
                  <?php 
                    $stmt = $dbh->prepare("SELECT m.id, m.name FROM sector_masters m WHERE m.status ORDER BY m.name ASC");
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo'
                    <select class="form-control" name="sector" id="sector" required>
                      <option value="">--Select Symbol--</option>';
                      foreach($rows as $row){
                        echo'
                        <option value="'.$row['name'].'">'.$row['name'].'</option>';
                      }
                      echo'
                    </select>';
                    ?>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="fv">Face Value <span style="color:red;">*</span></label>
                  <input type="number" min="1" class="form-control" name="fv" id="fv" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="pv">Premium Value</label>
                  <input type="text" placeholder="NIL" class="form-control" name="pv" id="pv">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="bl">Board Lot <span style="color:red;">*</span></label>
                  <input type="number" min="1" class="form-control" name="bl" id="bl" required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="pus">Paid up Shares</label>
                  <input type="number" min="1" class="form-control" name="pus" id="pus">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="doe">Date of Establishment</label>
                  <input type="date" class="form-control" name="doe" id="doe">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="dol">Date of Listing</label>
                  <input type="date" min="1" class="form-control" name="dol" id="dol">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Security Type <font color="red">*</font></label>
                  <select class="form-control" name="stype" id="stype" onChange="getState2(this.value);">
                    <option value="">--Select Security Type--</option>
                    <?php  
                      $stmt = $dbh->prepare("SELECT m.security_type, m.precise_name FROM security_type_masters m WHERE m.status = 1 ORDER BY m.security_type ASC");
                      $stmt->execute();
                      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach ($rows as $key => $row) {
                        echo'<option value="'.$row['precise_name'].'">'.$row['security_type'].'</option>';
                      }
                    ?>
                  </select>
                </div>
                <div class="col-lg-3 col-md-3" style='display: none;' name="matperiod" id="matperiod">
                  <label for="matperiod1">Maturity Period [Days for CP] [Year For GB & CB]</label>
                  <input type="number" class="form-control" name="matperiod1" id="matperiod1" onKeyPress="if(this.value.length==3) return false;">
                </div>
                <div class="col-lg-3 col-md-3" style='display: none;' id="issueDateDiv">
                  <label for="issueDate">Date of Issue</label>
                  <input type="date" class="form-control" name="issueDate" id="issueDate">
                </div>
                <div class="col-lg-3 col-md-3" style='display: none;' name="matDate" id="matDate">
                  <label for="matDate1">Maturity Date</label>
                  <input type="date" class="form-control" name="matDate1" id="matDate1">
                </div>
                <!-- <div class="col-lg-3 col-md-3" style='display: none;' id="amountDivId">
                  <label for="amount">Amount Issue</label>
                  <input type="number" class="form-control" name="amount" id="amount">
                </div> -->
                <div class="col-lg-3 col-md-3" style='display: none;' id="couponRateDiv">
                  <label for="couponRate">Coupon Rate</label>
                  <input type="number" class="form-control" name="couponRate" id="couponRate">
                </div>
                <div class="col-lg-3 col-md-3" style='display: none;' id="couponPayableDiv">
                  <label for="couponPayable">Coupon Payable</label>
                  <select class="form-control" name="couponPayable" id="couponPayable">
                    <option value="0">--Select--</option>
                    <option value="1">Annually</option>
                    <option value="2">Semi-annually</option>
                    <option value="3">Quarterly</option>
                  </select>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="1">Active</option>
                    <option value="2">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" id="save_symbol"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Search Symbol</h4>
              </div>
              <div class="box-body">
                <form action="" method="POST">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-lg-6 col-md-6">
                        <label for="searchSymId">Symbol Id</label>
                        <input type="text" class="form-control" name="searchSymId" id="searchSymId">
                      </div>
                      <div class="col-lg-6 col-md-6">
                        <label for="searchSymName">Symbol Name</label>
                        <input type="text" class="form-control" name="searchSymName" id="searchSymName">
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
              <div class="box-body" style="display: none;" id="symbolDtlsId">
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
  function getState2(val) {
    if (["CB", "GB", "CP"].includes(val)) {
      $("#matperiod").show();
      $("#matDate").show();
      $("#couponRateDiv").show();
      $("#couponPayableDiv").show();
      $("#issueDateDiv").show();
      //$("#amountDivId").show();
    }
    else {
      $("#matperiod").hide();
      $("#matDate").hide();
      $("#couponRateDiv").hide();
      $("#couponPayableDiv").hide();
      $("#issueDateDiv").hide();
      //$("#amountDivId").hide();
    }
  }

  $("#save_symbol").click(function(){
    showLoading();
    var isinFld = $("#isin").val();
    var symbolFld = $("#sy").val();
    var nameFld = $("#name").val();
    var sectorFld = $("#sector").val();
    var faceValueFld = $("#fv").val();
    var premiumValueFld = $("#pv").val();
    var boardLotFld = $("#bl").val();
    var paidUpSharesFld = $("#pus").val();
    var dateOfEstFld = $("#doe").val();
    var dateOfListFld = $("#dol").val();
    var securityTypeFld = $("#stype").val();
    var matPeriodFld = $("#matperiod1").val();
    var matDateFld = $("#matDate1").val();
    var statusFld = $("#status").val();
    var cpnRateFld = $("#couponRate").val();
    var cpnPayableFld = $("#couponPayable").val();
    var issueDateFld = $("#issueDate").val();
    //var amountFld = $("#amount").val();
    var operation = "save_symbol";

    var data = {
      isin: isinFld,
      sy: symbolFld,
      name: nameFld,
      sector: sectorFld,
      fv: faceValueFld,
      pv: premiumValueFld,
      bl: boardLotFld,
      pus: paidUpSharesFld,
      doe: dateOfEstFld,
      dol: dateOfListFld,
      stype: securityTypeFld,
      matPeriod: matPeriodFld,
      matDate: matDateFld,
      status: statusFld,
      cpnRate: cpnRateFld,
      cpnPayable: cpnPayableFld,
      issueDate: issueDateFld,
      save_symbol: operation,
    };

    // Validate required fields
    if (!['OS', 'CS'].includes(securityTypeFld) && !matPeriodFld) {
      hideloading();
      alert("Required Maturity Period");
      return false;
    }

    if(isinFld == '' || symbolFld == '' || nameFld =='' || faceValueFld==''|| boardLotFld == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
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

  $("#search_id").on("click", function () {
    showLoading();
    var $symIdField = $("#searchSymId");
    var $symNameField = $("#searchSymName");
    
    var data = {
      symbol_id: $symIdField.val(),
      symbol_name: $symNameField.val(),
      search_symbols: "search_symbols"
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php",
      data: data , 
      dataType: 'html',
      success: function(data) { 
        hideloading(); 
        $("#symbolDtlsId").show();
        $('#search_details').html(data); 
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Error: "+textStatus+' ,'+errorThrown);
      }
    });
  });

  function getStateSymbol(val) {
    $.ajax({
      type: "POST",
      url: "bbo-adm.php",
      data:'edit_symbols='+val,
      dataType: 'html',
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }

  function fun(io) {
    var val= document.getElementById('delete_symbol'+io).value;
     if (confirm("Are you sure you want to delete record Id # "+ val + '?'))
     {
        return true;
     }else{
        return false;
     }
  }

  $("#phone").keyup('input', function() {
    var phoneLength = $("#phone").val();
    if(phoneLength.length > 8 )
    {
      $("#errln").show();
      $("#phone").addClass("errorClass");
    }
    else
    {
      $("#errln").hide(10);
      $("#phone").removeClass("errorClass");
    }
  });
  $("#accno").keyup('input', function() {     
    var accountNumber = $("#accno").val();
    if(accountNumber.length > 10 )
    {
      $("#errAcno").show();
      $("#accno").addClass("errorClass");
    }
    else
    {
      $("#errAcno").hide(10);
      $("#accno").removeClass("errorClass");
    }
  });
  $("#isin").keyup('input', function() {     
    var cid = $("#isin").val();
    var flag=/^[0-9]+$/.test(cid);
    if(!flag)
    {              
      $("#errISIN").show();
      $("#isin").addClass("errorClass");
    }
    else
    {
      $("#errISIN").hide(10);
      $("#isin").removeClass("errorClass");
    }
  });
</script>
<style type="text/css">
  .errorClass { background:  #FADBD8  ; }
</style>
</html>
