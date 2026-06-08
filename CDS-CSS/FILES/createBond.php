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
        <form action="" method="POST" onsubmit="showLoading();">
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
                <label for="matperiod1">Maturity Period [Days for CP] [Year For GB & CB]<span style="color:red;">*</span></label>
                <input type="number" class="form-control" name="matperiod1" id="matperiod1" onKeyPress="if(this.value.length==3) return false;">
              </div>
              <div class="col-lg-3 col-md-3" style='display: none;' id="issueDateDiv">
                <label for="issueDate">Date of Issue<span style="color:red;">*</span></label>
                <input type="date" class="form-control" name="issueDate" id="issueDate">
              </div>
              <div class="col-lg-3 col-md-3" style='display: none;' name="matDate" id="matDate">
                <label for="matDate1">Maturity Date<span style="color:red;">*</span></label>
                <input type="date" class="form-control" name="matDate1" id="matDate1">
              </div>
              <!-- <div class="col-lg-3 col-md-3" style='display: none;' id="amountDivId">
                <label for="amount">Amount Issue</label>
                <input type="number" class="form-control" name="amount" id="amount">
              </div> -->
              <div class="col-lg-3 col-md-3" style='display: none;' id="couponRateDiv">
                <label for="couponRate">Coupon Rate<span style="color:red;">*</span></label>
                <input type="number" class="form-control" name="couponRate" id="couponRate">
              </div>
              <div class="col-lg-3 col-md-3" style='display: none;' id="couponPayableDiv">
                <label for="couponPayable">Coupon Payable<span style="color:red;">*</span></label>
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
            <br>NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="button" class="btn btn-primary" id="save_symbol"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="box">
            <div class="box-header">
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
              <hr>
            </div>
            <div class="box-body" id="dtls_id" style="display: none;">
              <div id="search_details"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border" style="font:8px;">
              <h4 class="box-title">Search Symbol</h4>
            </div>
            <div class="box-body" onLoad="document.forms.search.part.focus()">
              <form class="form-horizontal" name="search" role="form" method="POST" onkeypress="return event.keyCode != 13;">
                  <div class="input-group col-sm-3">
                    <input id="searchItem" name="searchItem" type="text" class="form-control" placeholder="Search key..." autocomplete="off"/>
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-default btnSearch">
                        <span class="glyphicon glyphicon-search"> </span>
                      </button> </span>
                  </div>
                </form>
                <div class="row mt">
                <div class="col-lg-12">
                  <div class="content-panel tablesearch">
                    <section id="unseen">
                      <table id="resultTable" class="table table-bordered table-striped">
                        <thead>
                          <tr>
                            <th>Id</th>
                            <th>Symbol</th>
                            <th>Sector</th>
                            <th>Face Value</th>
                            <th>Security Type</th>
                            <th>Edit</th>
                          </tr>
                        </thead>                  
                        <tbody></tbody>
                      </table>
                    </section>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> -->

    </section>
  </div>
</div>
<?php include('../NAV/footer.php') ?> 
</body>
<script type="text/javascript">
  function getState2(val)  {
    if (["CB", "GB", "CP"].includes(val)) {
      $("#matperiod").show();
      $("#matDate").show();
      $("#couponRateDiv").show();
      $("#couponPayableDiv").show();
      $("#issueDateDiv").show();
      //$("#amountDivId").show();
    } else {
      $("#matperiod").hide();
      $("#matDate").hide();
      $("#couponRateDiv").hide();
      $("#couponPayableDiv").hide();
      $("#issueDateDiv").hide();
      //$("#amountDivId").hide();
    }
  }

  $("#search_id").on("click", function() {
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
      url: "../../ADM/FILES/searchItem.php",
      data: data , 
      dataType: 'html',
      success: function(data){ 
        hideloading(); 
        $("#dtls_id").show();
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
      url: "../../ADM/FILES/bbo-adm.php",
      data:'edit_symbols='+val,
      dataType: 'html',
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }

  /*$113(document).ready(function(){
    $113(".tablesearch").hide();
    function search(){
      var query_value = $113("input#searchItem").val();
      var operation = "search_symbols";
      var dataString = 'query='+ query_value +'&search_symbols='+ operation;
      if(query_value !== ''){
        $113.ajax({ 
          type: "POST",
          url: "../../ADM/FILES/searchItem.php",
          data:  dataString ,
          cache: false,
          success: function(html){
            $113("table#resultTable tbody").html(html);
          }
        });
      }return false;    
    }
    $113("input#searchItem").live("keyup", function(e) {
      clearTimeout($113.data(this, 'timer'));
      var search_string = $113(this).val(); 
      if (search_string == '') {
        $113(".tablesearch").fadeOut(300);
      }else{
        $113(".tablesearch").fadeIn(300);
        $113(this).data('timer', setTimeout(search, 100));
      };
    });
  });*/

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "edit_symbol.php",
      data:'edit_symbols='+val,
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }

 function fun(io) {
    var val= document.getElementById('delete_symbol'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
    } else {
      return false;
    }
 }
</script>
<style type="text/css">
  .errorClass { background:  #FADBD8  ; }
</style>
<script type="text/javascript">
  $("#phone").keyup('input', function() {
    var phoneLength = $("#phone").val();
    if(phoneLength.length > 8 ) {
      $("#errln").show();
      $("#phone").addClass("errorClass");
    } else {
      $("#errln").hide(10);
      $("#phone").removeClass("errorClass");
    }
  });
  
  $("#accno").keyup('input', function() {   
    var accountNumber = $("#accno").val();
    if(accountNumber.length > 10 ) {
      $("#errAcno").show();
      $("#accno").addClass("errorClass");
    } else {
      $("#errAcno").hide(10);
      $("#accno").removeClass("errorClass");
    }
  });

  $("#isin").keyup('input', function() {     
    var cid = $("#isin").val();
    var flag=/^[0-9]+$/.test(cid);
    if(!flag) {              
      $("#errISIN").show();
      $("#isin").addClass("errorClass");
    } else {
      $("#errISIN").hide(10);
      $("#isin").removeClass("errorClass");
    }
  });

  $("#save_symbol").click(function () {
      showLoading();

      var isin = $("#isin").val();
      var symbol = $("#sy").val();
      var name = $("#name").val();
      var sector = $("#sector").val();
      var faceValue = $("#fv").val();
      var premiumValue = $("#pv").val();
      var boardLot = $("#bl").val();
      var paidUpShares = $("#pus").val();
      var dateOfEst = $("#doe").val();
      var dateOfList = $("#dol").val();
      var securityType = $("#stype").val();
      var matPeriod = $("#matperiod1").val();
      var matDate = $("#matDate1").val();
      var status = $("#status").val();
      
      var cpnRate = $("#couponRate").val();
      var cpnPayable = $("#couponPayable").val();
      var issueDate = $("#issueDate").val();
      //var amount = $("#amount").val();
      var operation = "save_symbol";

      // Validate required fields
      if (!['OS', 'CS'].includes(securityType) && !matPeriod) {
        hideloading();
        alert("Required Maturity Period");
        return false;
      }

      if (!isin || !symbol || !name || !faceValue || !boardLot || !securityType) {
        hideloading();
        alert("Please Fill All Mandatory Fields");
        return false;
      }

      var dataString = 'isin='+ isin +'&sy='+ symbol + '&name='+ name +'&sector='+ sector + '&fv='+ faceValue + '&pv='+ premiumValue +'&bl='+ boardLot + '&pus='+ paidUpShares + '&doe='+ dateOfEst + '&dol='+ dateOfList + '&stype='+ securityType + '&matPeriod='+ matPeriod +'&matDate='+ matDate + '&status='+ status + '&save_symbol='+ operation+'&cpnRate='+cpnRate+'&cpnPayable='+cpnPayable+'&issueDate='+issueDate;

      $.ajax({
        type: "POST",
        url: "../../ADM/PROCESS/process.php",
        data: dataString ,
        success: function(data){
          hideloading();
          $("#message").show().html(data);
          showMessage();
        }
      });
  });
</script>
</html>
