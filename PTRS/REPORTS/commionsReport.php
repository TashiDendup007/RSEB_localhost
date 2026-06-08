<?php 
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
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Commision Report</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Commision Report</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="col-lg-4 col-md-4">
            <label>Broker <span style="color: blue;">[If no broker is selected, it will generate overall]</span></label>
            <select name="broker" id="brokerId" class="form-control" required>
              <option value="0"> -- Select-- </option>
              <?php
                $wc = $dbh->prepare("SELECT p.participant_code, a.name 
                      FROM adm_institution a 
                      LEFT JOIN adm_participants p ON a.institution_id = p.institution_id 
                      WHERE a.institution_id != '1'
                ");
                $wc->execute();
                while ($res= $wc->fetch()) {
                  echo '<option value="'.$res['participant_code'].'">'.$res['name'].'</option>';
                }
              ?>
              </select>
          </div>

          <div class="col-lg-4 col-md-4">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date" id="from_date">
            </div>
          </div>

          <div class="col-lg-4 col-md-4">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date" id="to_date">
            </div>
          </div>
        </div>

        <div class="box-footer">
          <div class="col-lg-4 col-md-4">          
            <button type="button" class="btn btn-success" id="generateId"><i class="fa fa-list"></i> Generate </button>
          </div>
        </div> 
        <div id="details"></div>
        <div id="buttonId" style="display: none;">
          &emsp;&ensp;
          <a href="#" class="" onClick ="$('#tableId').tableExport({type:'excel',escape:'false',fileName:'commisionReport'});">
            <button class="btn btn-success" type="button"> Excel</button></a>&nbsp;
            <button type="button" class="btn btn-danger" id="exportPDF"> &nbsp;&nbsp;Pdf&nbsp;&nbsp;</button>
        </div>

        <br style="clear: both;"> 
        <br>

      </div>
    </section>
  </div>
  <?php include('../NAV/footer.php'); ?>
</div>
</body>
<script type="text/javascript">
  $('#generateId').click(function(){
    showLoading();
    var brokerId = $('#brokerId').val();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    if(toDate == ""){
      toDate = '0';
    }
    if(fromDate == ""){
      fromDate = '0';
    }
    var op = 'commisionReport';
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&commisionReport='+ op +'&brokerId='+brokerId,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").show().html(data);
        $("#buttonId").show();
      }
    });
  });

  /*function loadsymbol(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'entel_load_report='+val, 
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }*/

  function gefun(i){
    if (confirm("Are you sure you want to generate ?")){
      return true;
    }
    else{
      return false;
    }
  }

  /*function checkDate() {
    var f = document.getElementById("to_date").value;
    var from = new Date(f);
    var t = document.getElementById("from_date").value;
    var to = new Date(t);
     if (from < to) {
         alert("To date should be greater than From date ");
         return false;
     } else {
         return true;
     }
  }*/

  function doExport(selector, params){
    var options={
      tableName: 'tableId',
      worksheetName: 'commisionReport',
      fileName: 'commisionReport'
    };
    $.extend(true, options, params);
    $(selector).tableExport(options);
  }

  $('#exportPDF').click( function() {
    var brokerId = $('#brokerId').val();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    if(brokerId == "0"){
      brokerId = 'ALL';
    }
    if(toDate == ""){
      toDate = '0';
    }
    if(fromDate == ""){
      fromDate = '0';
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

    /*img.onload = function(){
      doc.addImage(img, 'PNG', 50, 3, 40, 50);
    };*/
    //doc.setFontType("bold");
    doc.text(130, 25, 'ROYAL SECURITIES EXCHANGE OF BHUTAN');
    doc.setFontSize(10);
    doc.text(240, 43, 'Commision Report');
    doc.setFontSize(10);
    doc.text(50, 75, 'Broker: '+brokerId+',   From: '+fromDate+'  To: '+toDate);

    doc.save('CommionsReport.pdf');
  });
</script>
</html>