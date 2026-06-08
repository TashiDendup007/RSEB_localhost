<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-purple sidebar-mini">
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
          <li><a href="#">Bond</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bond Offer</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="" method="POST">
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-6 col-md-6">
                  <label for="symbol_id">Symbol <span style="color:red;">*</span></label>
                  <select class="form-control" name="symbol_id" id="symbol_id" onchange="getSymbolName(this.value)" required>
                    <option value="">--Select Symbol--</option>
                  <?php 
                    $getSymbol = $dbh->prepare("SELECT DISTINCT s.symbol_id, s.symbol
                      FROM symbol s 
                      WHERE s.status = 1 AND s.security_type IN ('GB', 'CB')
                      ORDER BY s.symbol_id DESC
                    ");
                    $getSymbol->execute();
                    $rows = $getSymbol->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $key) {
                      echo'<option value="'.$key['symbol_id'].'">'.$key['symbol'].'</option>';
                    }
                  ?>
                  </select>
                </div>
                
                <div class="col-lg-6 col-md-6" id="name_dtls" style="display: none;">
                  <label>Bond Name</label>
                  <input type="text" class="form-control" name="company_name" id="company_name" readonly>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label>Start Date <font color="red">*</font></label>
                  <input type="datetime-local" class="form-control" name="start_date" id="start_date" required>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label>End Date <font color="red">*</font></label>
                  <input type="datetime-local" class="form-control" name="end_date" id="end_date" required>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label>Bond Type</label>
                  <select class="form-control" name="bond_type" id="bond_type" required>
                    <option value="">-- Select Bond Type --</option>
                    <option value="BLA">BLA (Bhutanese Living Abroad)</option>
                    <option value="BLR">BLR (Bhutanese Living in Residence)</option>
                    <option value="ALL">ALL (BOTH)</option>
                  </select>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="1">Active</option>
                    <option value="0">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" name="save_bond_offer" id="save_bond_offer"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Bond Offer List</h4>
              </div>
              <div class="box-body">
                <div id="tableList"></div>
              </div>
              <div class="box-footer"></div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  $(document).ready(function() {
    showLoading();
    var op = 'get_bond_offer_list';
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_bond_offer_list='+op,
      cache: false,
      success: function(response){
        hideloading();
        $('#tableList').html(response);
      }
    });
  });

  function getSymbolName(id) {
    var op = 'get_symbol_name';
    if (id === '') {
      $('#company_name').val('');
      $('#name_dtls').hide();
    } else {
      $.ajax({
        type: "POST",
        url: "load.php",
        data:'get_symbol_name=' + op + '&id=' + id,
        cache: false,
        success: function(response) {
          hideloading();
          $('#name_dtls').show();
          $('#company_name').val(response);
        }
      });
    }
  }

  $("#save_bond_offer").click(function(event) { 
    event.preventDefault();
    showLoading();

    var symIdField = $("#symbol_id").val();
    var strDateField = $("#start_date").val();
    var endDateField = $("#end_date").val();
    var statusField = $("#status").val();
    var typeField = $("#bond_type").val();
    var operation = "save_bond_offer";
    
    var data = {
      symbol_id: symIdField,
      start_date: strDateField,
      end_date: endDateField,
      status: statusField,
      bond_type: typeField,
      save_bond_offer: operation
    };

    if (confirm("Do you want to continue?")) {
      if(symIdField === '' || strDateField === '' || endDateField === '' || statusField === '' || typeField === '') {
        $('#message').html('<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Please Fill All Mandatory Fields</div></div>');
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
            // reload the list of the bond offer
            $.ajax({
                url: "load.php",
                data: { get_bond_offer_list: "get_bond_offer_list" },
                type: "POST",
                dataType: "html",
              success: function(data) {
                $("#tableList").html(data);
              },
            });
          },
          error: function(xhr, status, error) {
            hideloading();
            console.log(error);
          }
        });
      }
    } else {
      hideloading();
      return false;
    }

    
  });

  function editBondOffer(id) {
    $.ajax({
      type: "POST",
      url: "edit.php",
      data:{ edit_bond_offer: id },
      success: function(data) {
        $("#myModal").html(data);
        $("#myModal").modal();
      }
    });
  }

  function deleteBondOffer(id, symbol) {
    showLoading();
    if (confirm("Do you want to delete the bond offer of "+symbol+" ?")) {
      const operation = "delete_bond_offer";
      const data = { id: id, delete_bond_offer: operation };
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

          if (data.status == 200) {
            $(`tr[data-id="${id}"]`).remove();
          }
        },
        error: function () {
          hideloading();
          const statusMsg = $('<div>').addClass('alert alert-danger alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
            $('<i>').addClass('icon fa fa-check'),
            ' Oops sorry! There was an error while operation.'
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
</script>
</html>