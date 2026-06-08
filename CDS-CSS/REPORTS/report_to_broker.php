<?php 
include('../FILES/sessionStartFile_cdscss.php');
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
        <li><a href="#">Reports</a></li>
        <li class="active">Netted Summary</li>
      </ol>
    </section>

    <section class="content">
      <div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Generate Netted Summary</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
          </div>
        </div>
        
        <div class="box-body">
          <div class="col-lg-6 col-md-6">
            <label>Broker <span style="color: red;">[*]</span></label>
            <select name="broker" id="brokerSelect" class="form-control">
              <option value="0">Broker</option>
              <?php
              $brokerQuery = $dbh->prepare("
                    SELECT p.participant_code, a.name 
                    FROM adm_institution a 
                    LEFT JOIN adm_participants p ON a.institution_id = p.institution_id 
                    WHERE a.institution_id != '1'
              ");
              $brokerQuery->execute();
              while ($broker = $brokerQuery->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="'.$broker['participant_code'].'">'.$broker['name'].'</option>';
              }
              ?>
            </select>
          </div>

          <div class="col-lg-6 col-md-6 col-sm-12">
            <label>Security Type<font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-file"></i>
              </div>
              <select name="sec_type" id="sec_type" class="form-control" required>
                <option value="">--Select--</option>
                <option value="OS">Equity</option>
                <option value="CGB">Bond/Debt</option>
              </select>
            </div>
            <span id="sectypeErr" style="color: red;"></span>
          </div>

          <div class="col-lg-6 col-md-6">
            <label>From Date <font color="red">*</font></label>
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control" name="fromDate" id="fromDate" required>
            </div>
          </div>

          <div class="col-lg-6 col-md-6">
            <label>To Date <font color="red">*</font></label>
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control" name="toDate" id="toDate" required>
            </div>
          </div>
        </div>

        <div class="box-footer">
          <div class="col-lg-12 text-center">
            <button type="button" class="btn btn-success" id="generateNet">
              <i class="fa fa-calculator"></i> Generate Report
            </button>
          </div>
        </div>

        <div id="reportResults" class="box-body"></div>
        
        <div id="exportButtons" class="box-footer" style="display: none;">
          <div class="col-lg-6">
            <button class="btn btn-primary" onclick="printReport()">
              <i class="fa fa-print"></i> Print Report
            </button>
          </div>
          <!-- <div class="col-lg-6 text-right">
            <button class="btn btn-danger" id="exportPDF">
              <i class="fa fa-file-pdf-o"></i> Export PDF
            </button>
            <button class="btn btn-success" onclick="exportExcel()">
              <i class="fa fa-file-excel-o"></i> Export Excel
            </button>
          </div> -->
        </div>
      </div>
    </section>
  </div>
  <?php include('../NAV/footer.php'); ?>
</div>
</body>
<script type="text/javascript">
$(document).ready(function() {
  $('#generateNet').click(function() {
      showLoading(); // Show loading indicator
      var toDate = $("#toDate").val(); // Get To Date value
      var fromDate = $("#fromDate").val(); // Get From Date value
      var brokerId = $("#brokerSelect").val(); // Get selected broker ID
      var reportType = 'Net'; // Set report type
      var sec_type = $("#sec_type").val();

      if (sec_type == '') {
        hideloading();
        alert("Select Security Type");
        return false;
      }

      // Validate dates
      if (!toDate || !fromDate) {
          alert("Please select both From Date and To Date.");
          hideloading();
          return false;
      }

      // Send AJAX request
      $.ajax({
          type: "POST",
          url: "load.php", // Backend processing file
          data: {
              toDate: toDate,
              fromDate: fromDate,
              broker: brokerId,
              reportType: reportType,
              sec_type: sec_type
          },
          dataType: "html",
          success: function(data) {
              hideloading(); // Hide loading indicator
              $("#reportResults").html(data); // Display results in the designated div
              $("#exportButtons").show(); // Show export buttons
          },
          error: function(xhr, status, error) {
              hideloading();
              alert("Error generating report: " + error); // Handle errors
          }
      });
  });

    $('#exportPDF').click(function() {
      const brokerId = $('#brokerSelect').val();
      const fromDate = $('#fromDate').val();
      const toDate = $('#toDate').val();
      
      // PDF generation logic similar to previous implementation
      // Would need to implement jsPDF or server-side PDF generation
    });
  });

  function printReport() {
    const printContent = $('#reportResults').html();
    const originalContent = $('body').html();
    $('body').html(printContent);
    window.print();
    $('body').html(originalContent);
  }

  function exportExcel() {
    const table = $('#reportTable').clone();
    table.find('th, td').css('border', '1px solid black');
    $('#reportResults').tableExport({
      type: 'excel',
      fileName: 'Netted_Summary_' + new Date().toISOString().split('T')[0]
    });
  }

  $('#sec_type').click(function() {
    $("#sectypeErr").html("");
  });
</script>
</html>