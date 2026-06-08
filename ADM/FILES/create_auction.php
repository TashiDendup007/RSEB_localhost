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
          <li><a href="#">Auction</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Create Auction</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="POST" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-6 col-md-6">
                  <label for="symbol_id">Symbol <span style="color:red;">*</span></label>
                  <select class="form-control" name="symbol_id" id="symbol_id" required>
                    <option value="">--Select Symbol--</option>
                  <?php 
                    $getSymbol = $dbh->prepare("SELECT 
                            s.symbol_id, s.symbol
                            FROM symbol s 
                            WHERE s.status = 1 AND s.security_type IN ('OS')
                            ORDER BY s.symbol ASC");
                    $getSymbol->execute();
                    $rows = $getSymbol->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $key) {
                      echo'<option value="'.$key['symbol_id'].'">'.$key['symbol'].'</option>';
                    }
                  ?>
                  </select>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label for="offer_vol">Offer Volume <span style="color:red;">*</span></label>
                  <input type="number" name="offer_vol" id="offer_vol" step="0" class="form-control" required>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label for="min_price">Min Price<span style="color:red;">*</span></label>
                  <input type="number" name="min_price" id="min_price" step="0.01" class="form-control" required>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label for="max_price">Max Price<span style="color:red;">*</span></label>
                  <input type="number" name="max_price" id="max_price" step="0.01" class="form-control" required>
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
                  <label>Status</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="Y">Active</option>
                    <option value="N">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" name="save_auction_offer" id="save_auction_offer"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Auction Symbol List</h4>
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
    var op = 'get_auction_symbol_list';
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_auction_symbol_list='+op,
      cache: false,
      success: function(response){
        hideloading();
        $('#tableList').html(response);
      }
    });
  });

  $("#save_auction_offer").click(function(event) { 
    event.preventDefault();
    showLoading();

    var symIdField = $("#symbol_id").val();
    var offerVolField = $("#offer_vol").val();
    var minPriceField = $("#min_price").val();
    var maxPriceField = $("#max_price").val();
    var strDateField = $("#start_date").val();
    var endDateField = $("#end_date").val();
    var statusField = $("#status").val();
    var operation = "save_auction_offer";
    
    var data = {
      symbol_id: symIdField,
      offer_vol: offerVolField,
      min_price: minPriceField,
      max_price: maxPriceField,
      start_date: strDateField,
      end_date: endDateField,
      status: statusField,
      save_auction_offer: operation
    };

    if(symIdField === '' || offerVolField === '' || minPriceField === '' || maxPriceField === '' || strDateField === '' || endDateField === '' || statusField === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#message").html(response);
          showMessage();

          $("#symbol_id").val('');
          $("#offer_vol").val('');
          $("#min_price").val('');
          $("#max_price").val('');
          $("#start_date").val('');
          $("#end_date").val('');
          $("#status").val('');

          $.ajax({
              url: "load.php",
              data: { get_auction_symbol_list: "get_auction_symbol_list" },
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
  });

  function editAuctionOffer(id) {
    $.ajax({
      type: "POST",
      url: "edit.php",
      data:{ edit_share_auction: id },
      success: function(data) {
        $("#myModal").html(data);
        $("#myModal").modal();
      }
    });
  }

  function deleteAuctionOffer(id, symbolId, symbol) {
    showLoading();
    if (confirm("Are you sure you want to delete auction of "+symbol+" ?")) {
      const operation = "delete_auction_offer";
      const data = { id: id, delete_auction_offer: operation };

      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: $.param(data),
        dataType: "html",
        success: function (response) {
          hideloading();
          var data = JSON.parse(response);

          $("#message").html(data.message);
          showMessage();

          if (data.status == 200) {
            $(`tr[data-id="${id}"]`).remove();
          }
        },
        error: function () {
          hideloading();

          $("#message").html(data.message);
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