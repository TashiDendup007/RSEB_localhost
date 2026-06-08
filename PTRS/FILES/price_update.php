<?php 
  date_default_timezone_set("Asia/Thimphu");
  include('../FILES/session_file.php');
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
          <li><a href="ptrs_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Price Adjustment</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Price Adjustment</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="post" class="form-horizontal">
          <div class="box-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="form-group">
                  <label class="col-lg-2 col-md-2">Price Adjustment For:</label>
                  <div class="col-lg-10 col-md-10">
                    <select class="form-control" name="pAdjustment" id="pAdjustment" onchange="showFields(this.value)" required>
                      <option value="">--Select--</option>
                      <option value="1">Bonus</option>
                      <option value="2">Rights Issue</option>
                      <option value="3">Dividend</option>
                      <option value="4">Untraded for Three Month</option>
                      <option value="5">Untraded for One Year</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-lg-2 col-md-2">Select Company:</label>
                  <div class="col-lg-10 col-md-10">
                    <div id="symbolDivId"></div>
                    <span id="symbolIdError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="curDivId">
                  <label class="col-lg-2 col-md-2">Current Market Price:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="text" name="curMarPrice" id="curMarPrice" class="form-control" placeholder="Current Market Price" readonly="true">
                    <span id="curPriceError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="BonusDivId" style="display: none;">
                  <label class="col-lg-2 col-md-2">Bonus %:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="number" name="bosPercentage" id="bosPercentage" class="form-control" placeholder="Bonus Percentage" onChange=getNewMaPrice(this.value);>
                    <span id="bonusPerError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="subcPriceDivId" style="display: none;">
                  <label class="col-lg-2 col-md-2">Subscription Price:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="number" name="subcPrice" id="subcPrice" class="form-control" placeholder="Subcription Price">
                    <span id="subPriError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="unitDivId" style="display: none;">
                  <label class="col-lg-2 col-md-2">Unit:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="number" name="unit" id="unit" class="form-control" placeholder="Unit" value="1" readonly>
                    <span id="unitError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="RightsDivId" style="display: none;">
                  <label class="col-lg-2 col-md-2">Rights Issue %:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="number" name="rigPercentage" id="rigPercentage" class="form-control" placeholder="Rights Percentage" onChange=getNewMaPrice(this.value);>
                    <span id="rigsPerError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="ShareDivId" style="display: none;">
                  <label class="col-lg-2 col-md-2">Dividend/Share:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="number" name="share" id="share" class="form-control" placeholder="Number of Shares" onChange=getNewMaPrice(this.value);>
                    <span id="shareError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="BookValDivId" style="display: none;">
                  <label class="col-lg-2 col-md-2">Book Value:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="number" name="bookValue" id="bookValueId" class="form-control" placeholder="Book Value" onChange=getNewMaPrice(this.value);>
                    <span id="bookValueError" class="text-danger"></span>
                  </div>
                </div>
                <div class="form-group" id="newPriDivId">
                  <label class="col-lg-2 col-md-2">New Market Price:</label>
                  <div class="col-lg-10 col-md-10">
                    <input type="text" name="newMarketPrice" id="newMarketPriceId" class="form-control" placeholder="New Market Price" readonly="true">
                    <span id="newPriceError" class="text-danger"></span>
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                  <button type="button" class="btn btn-success" onclick="updatePrice();"><i class="fa fa-check"></i> Update</button>
                  <button type="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>
              </div>
            </div>
            <div id="details"></div>
          </div>
          </form>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?>  
</body>
<script type="text/javascript">
  function showFields(id) {
    if (id == 1) {
      $('#BonusDivId').show(); 
      $('#newMarketPriceId').val('');
      $('#subcPrice').val(''); 
      $('#subcPriceDivId').hide();
      $('#rigPercentage').val(''); 
      $('#RightsDivId').hide();
      $('#unit').val(''); 
      $('#unitDivId').hide();
      $('#share').val(''); 
      $('#ShareDivId').hide();
      $('#bookValueId').val(''); 
      $('#BookValDivId').hide();
    } else if(id == 2) {
      $('#subcPriceDivId').show(); 
      $('#RightsDivId').show(); 
      $('#unitDivId').show();
      $('#bosPercentage').val(''); 
      $('#BonusDivId').hide();
      $('#share').val(''); 
      $('#ShareDivId').hide();
      $('#bookValueId').val(''); 
      $('#BookValDivId').hide();
      $('#newMarketPriceId').val('');
    } else if(id == 3) {
      $('#ShareDivId').show();
      $('#bosPercentage').val(''); 
      $('#BonusDivId').hide();
      $('#subcPrice').val('');  
      $('#subcPriceDivId').hide();
      $('#rigPercentage').val(''); 
      $('#RightsDivId').hide();
      $('#unit').val('');  
      $('#unitDivId').hide();
      $('#bookValueId').val(''); 
      $('#BookValDivId').hide();
      $('#newMarketPriceId').val('');
    } else if(id == 4 || id == 5) {
      $('#BookValDivId').show();
      $('#bosPercentage').val(''); 
      $('#BonusDivId').hide();
      $('#subcPrice').val('');  
      $('#subcPriceDivId').hide();
      $('#rigPercentage').val(''); 
      $('#RightsDivId').hide();
      $('#unit').val('');  
      $('#unitDivId').hide();
      $('#share').val(''); 
      $('#ShareDivId').hide();
      $('#newMarketPriceId').val('');
    } else {
      $('#BonusDivId').hide();
      $('#subcPriceDivId').hide();
      $('#unitDivId').hide();
      $('#RightsDivId').hide();
      $('#ShareDivId').hide();
      $('#BookValDivId').hide();
    }
    selectSymbol(id);
  }

  function selectSymbol(id){
    if (id == '') {
      $("#symbolDivId").html("");
    } else {
      $.ajax({
        type: "POST",
        url: "loadPriceUpdate.php",
        data: 'id='+id+'&getSymbols=getSymbols',
        dataType: "html",
        success: function(data){
          hideloading();
          $("#symbolDivId").html(data);
        }
      });
    }
  }

  function getNewMaPrice(val) {
    var priceAdjustId = $('#pAdjustment').val();
    var curPrice = $('#curMarPrice').val();
    var newPrice='';
    if(priceAdjustId == 1){
      var bonus = val;
      newPrice = Number(curPrice)/Number(1+(Number(bonus)/100));
    }
    else if(priceAdjustId == 2) {
      var subcriptPri = $('#subcPrice').val();
      var rightsPct = val;
      var unit = $('#unit').val();

      if(subcriptPri  ==  ''){
        $('#subPriError').html('Please enter Subscription Price');
      }
      if(rightsPct  ==  ''){
        $('#rigsPerError').html('Please enter Rights Percentage');
      }
      if(unit  ==  ''){
        $('#unitError').html('Please enter unit');
      }
      if(subcriptPri != '' && rightsPct != '' && unit != '') {
        newPrice = (Number(curPrice)+((Number(subcriptPri)/Number(unit))*(Number(rightsPct)/100)))/(1+(Number(rightsPct)/100));
      }
    }
    else if(priceAdjustId == 3){
      var divPerShare = val;

      if(divPerShare  ==  '') {
        $('#shareError').html('Please enter Share');
      }
      if(divPerShare != ''){
        newPrice = (Number(curPrice)-Number(divPerShare));
      }
    }
    else if(priceAdjustId == 4){
      var bookVal = val;
      newPrice = (Number(curPrice) + Number(bookVal))/2;
    }
    else if(priceAdjustId == 5){
      var bookVal = val;
      newPrice = Number(bookVal);
    }
    newPrice = newPrice.toFixed(2);
    $('#newMarketPriceId').val(newPrice);
  }

  function updatePrice() {
    var returnVal = true;
    var reason = $("#pAdjustment").val();
    var symbolId = $("#symbolId").val();
    var currentPrice = $("#curMarPrice").val();
    var newPrice = $("#newMarketPriceId").val();
    var op = "updateMarketPrice";
    
    var bonusPer = '';
    var subcPrice = '';
    var unit = '';
    var rightsPerc = '';
    var dvdPerShare = '';
    var bookValueId = '';

    if (reason === '1') {
      bonusPer = $("#bosPercentage").val();
    } else if (reason === '2') {
      subcPrice = $("#subcPrice").val();
      unit = $("#unit").val();
      rightsPerc = $("#rigPercentage").val();
    } else if (reason === '3') {
      dvdPerShare = $("#share").val();
    } else if (reason === '4' || reason === '5') {
      bookValueId = $("#bookValueId").val();
    } 

    if(currentPrice  ==  "") {
      $('#curPriceError').html('Enter current price');
      returnVal = false;
    }

    if (newPrice  ==  "") {
      $('#newPriceError').html('New Price cannot be NULL');
      returnVal = false;
    }

    if (returnVal  ==  true) {
      $.ajax({
        type: "POST",
        url: "loadPriceUpdate.php",
        data: 'symbolId=' + symbolId + '&currentPrice=' + currentPrice + '&updateMarketPrice=' + op + '&newPrice=' + newPrice + '&reason=' + reason + '&bonusPer=' + bonusPer + '&subcPrice=' + subcPrice + '&unit=' + unit + '&rightsPerc=' + rightsPerc + '&dvdPerShare=' + dvdPerShare + '&bookVal=' + bookValueId,
        dataType: "html",
        success: function(response){
          var result = $.trim(response);
          hideloading();
          if(result == "success") {
            $("#pAdjustment").val("");
            $("#curMarPrice").val("");
            $("#newMarketPriceId").val("");
            $("#message").html("<span style='color: #01FE90; font-weight: bold;'>New Market Price Successfully Updated</span>");
          } else {
            $("#message").html("<span style='color: #EC1616; font-weight: bold;'>Error in operation. Please try again</span>");
          }
          showMessage();
        }
      });
    }
  }

  function getCurMarketPrice(id) {
    if (id  ==  '0') {
      $("#curMarPrice").val('');
    } else {
      $.ajax({
        type: "POST",
        url: "loadPriceUpdate.php",
        data: 'id=' + id + '&getCurrentMarketPrice=getCurrentMarketPrice',
        dataType: "html",
        success: function(data){
          hideloading();
          $("#curMarPrice").val(data);
        }
      });
    }
  }

  $('#curMarPrice').click(function(){
    $('#curPriceError').html('');
  });

  $('#bookValueId').click(function(){
    $('#newPriceError').html('');
  });

  $('#unit').click(function(){
    $('#unitError').html('');
  });

  $('#subcPrice').click(function(){
    $('#subPriError').html('');
  });

  $('#rigPercentage').click(function(){
    $('#rigsPerError').html('');
  });

  $('#newMarketPriceId').click(function(){
    $('#newPriceError').html('');
  });

  $('#share').click(function(){
    $('#shareError').html('');
  });

  function clearMessage(){
    $('#curPriceError').html('');
  }
</script>
</html>

