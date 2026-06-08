<?php 
    include('sessionStartFile_cdscss.php');
    include ('../../CONNECTIONS/db.php');
    date_default_timezone_set("Asia/Thimphu");
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
          <form action="../PROCESS/process.php" method="post">
            <div class="box-body">
              <div class="row">
                <div class="col-lg-6 col-md-6">
                  <label>Select Level</label>
                  <select name="level" id="levelId"  class="form-control">
                    <option value="0">--select--</option>
                    <option value="1">First Level(One Month)</option>
                    <option value="2">Second Level (One Year)</option>
                  </select>
                  <span id="levelError" class="text-danger"></span>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" onclick="generate();"> Generate</button>
              </div>
            </div>
            
            <div id="details"></div>

            <div id="exportButtonId" style="display: none;">
              &emsp;&ensp;
              <a href="#" onClick ="$('#tableId').tableExport({type:'excel',escape:'false',fileName:'PriceAdjustmentReport'});">
                <button class="btn btn-success" type="button"> Excel</button></a>&nbsp;
                <button type="button" class="btn btn-danger" id="exportPDF"> &nbsp;&nbsp;Pdf&nbsp;&nbsp;</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?>  
</body>
<script type="text/javascript">
  $('#levelId').click(function(){
    $('#levelError').html('');
  });
  
  function generate(){
    var returnVal = true;
    var level = document.getElementById("levelId").value;
    var op = "priceAdjustment";

    if(level == "0"){
      $('#levelError').html('Select Level');
      returnVal=false;
    }
    
    if(returnVal == true){
      $.ajax({
        type: "POST",
        url: "load.php",
        data: 'level='+level+'&priceAdjustment='+op,
        success: function(data){
          hideloading();
          $("#details").show();
          $("#details").show().html(data);
          $("#exportButtonId").show();
          $('#message').fadeOut(20000);
        }
      });
    }
}

  $('#exportPDF').click(function(){
    var levelId = $('#levelId').val();
    var untraded = null;
    
    if(levelId == "1"){
      untraded = 'One Month';
    }else{
      untraded = 'One Year';
    }
    
    var doc = new jsPDF('p', 'pt');

    var elem = document.getElementById("tableId");
    var res = doc.autoTableHtmlToJson(elem);
    
    doc.autoTable(res.columns, res.data,{
    margin:{top:85},
    styles: {fontSize: 8},
        drawHeaderCell: function (cell, data) {
            //cell.styles.textColor = 255;
            cell.styles.fontSize = 8;
        },
        createdCell: function (cell, data) {
           cell.styles.fontSize= 10;
           //cell.styles.textColor = [255,0,0];
        },
    });

    var img = new Image();
    img.src ='../../dist/img/Logo.png';
    doc.addImage(img, 'PNG', 50, 3, 40, 50);

    doc.text(130, 25, 'ROYAL SECURITIES EXCHANGE OF BHUTAN');
    doc.setFontSize(10);
    doc.text(240, 43, 'Price Adjustment Report');
    doc.setFontSize(10);
    doc.text(50, 75, 'Untraded For: '+untraded);

    doc.save('PriceAdjustmentReport.pdf');
  });
</script>
</html>

