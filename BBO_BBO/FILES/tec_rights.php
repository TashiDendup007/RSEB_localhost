<?php 
  date_default_timezone_set('Asia/Thimphu');
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Rights</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Rights Issue Update</h4>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
                </div>
              </div>
              <?php 
                $dateselect = date("Y-m-d H:i:s");

                /*$checkActive = $dbh->prepare("SELECT r.id, r.symbol_id, r.start_at, r.end_at, r.status,
                    DATE_FORMAT(r.start_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
                    DATE_FORMAT(r.end_at, '%W %M %e, %Y %h:%i %p') AS end_at_format 
                  FROM rights_offers r 
                  WHERE r.status = 1 
                    AND r.id = (SELECT MAX(id) FROM rights_offers WHERE status = 1)");
                $checkActive->execute();
                $result = $checkActive->fetch();
                if ($checkActive->rowcount() < 1) {
                  echo'<div class="box-body"><h3>There is no active Rights offering</h3></div>'; 
                  die();
                } 
                if ($result['start_at'] > $dateselect) {
                  echo"<div class='box-body'><h3>The bid for the Rights Offer will open on : <b>".$result['start_at_format']."</b></h3></div>";
                  die();
                } elseif($result['end_at'] < $dateselect) {
                  echo "<div class='box-body'><h3>The Bid for the Rights Offer has ended on <b>".$result['end_at_format']."</b></h3></div>";
                  die();
                }*/
                if ('2025-07-02 09:00:00' > $dateselect) {
                  echo"<div class='box-body'><h3>The bid for the Rights Offer will open on : <b>2nd July 2025 09:00 AM</b></h3></div>";
                  die();
                } elseif('2025-07-04 18:00:00' < $dateselect) {
                  echo "<div class='box-body'><h3>The Bid for the Rights Offer has ended on <b>4th July 2025 05:00 PM</b></h3></div>";
                  die();
                }
              ?>
              <div class="box-body table-responsive">
                <input type="hidden" name="statrt_date" id="statrt_date" value="2025-07-02 09:00:00"> <?php // echo $result['start_at']; ?>
                <input type="hidden" name="end_date" id="end_date" value="2025-07-04 18:00:00"> <?php // echo $result['end_at']; ?>
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>SYMBOL</th>
                      <th>CD CODE</th>
                      <th>PRICE</th>
                      <th>VOLUME</th>
                      <th>TYPE</th>
                      <th>TIME</th>
                      <th>AGENT</th>
                      <th>ACTION</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                  $un = substr($username, 0, 7);
                  $stmt = $dbh->prepare("SELECT s.symbol, ri.symbol_id, ri.cd_code, ri.order_size, ri.type, ri.bid_price, ri.user_name, ri.order_id, ri.order_date 
                    FROM client_account ca
                    JOIN rights_issue ri ON ca.cd_code = ri.cd_code
                    JOIN symbol s ON ri.symbol_id = s.symbol_id
                    WHERE ri.user_name LIKE :username 
                      AND ri.status = 0 
                      AND ri.type = 'B'
                  ");
                  $stmt->bindValue(':username', $un . '%', PDO::PARAM_STR);
                  $stmt->execute();
                  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  $i = 1;
                  foreach ($results as $res) {
                    $type = ($res['type'] == 'B') ? 'BID' : 'SHARE AUCTION';
                    echo'
                    <tr data-id="'.$i.'" style="background-color:#dce2e9;">
                      <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
                      <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
                      <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
                      <input type="hidden" value="'.$res['order_size'].'" id="v'.$i.'">
                      <input type="hidden" value="'.$res['type'].'" id="side'.$i.'">
                      <td>'.$i.'</td>
                      <td>'.$res['symbol'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td><input type="number" class="form-control" size="5" value="'.$res['bid_price'].'" id="e_p'.$i.'" min="11" max="50.27" step="0.05" onChange="priceFix('.$i.')"></td>
                      <td><input type="number" class="form-control" size="8" value="'.$res['order_size'].'" id="e_v'.$i.'" min="100" step="10"></td>
                      <td>'.$type.'</td>
                      <td>'.$res['order_date'].'</td>
                      <td>'.$res['user_name'].'</td>
                      <td>
                        <button type="button" class="btn btn-primary" name="chg_or" id="chg_or'.$i.'" value="'.$res['order_id'].'" onclick="return update('.$i.');" data-toggle="tooltip" data-placement="top" title="Click Here To Change Order for '.$res['cd_code'].'"><i class="fa fa-check"></i></button>

                        <button type="button" class="btn btn-danger" name="del_ord" id="del_ord'.$i.'" value="'.$res['order_id'].'" onclick="return deleteOrd('.$i.');" data-toggle="tooltip" data-placement="top" title="Click Here To Delete Order for '.$res['cd_code'].'"><i class="fa fa-trash"></i></button>
                      </td>
                    </tr>';  
                    $i++; 
                    }
                  ?>
                  </tbody>
                </table>
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
  $( function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });

  function priceFix(i) {
    document.getElementById("e_p" + i).addEventListener("blur", function () {
        let value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(2);
        }
    });
  }

  function update(i) {
    showLoading();
    var val = $('#chg_or'+i).val();
    var val1 = $('#sy'+i).val();
    var cd_code = $('#cd_code'+i).val();
    var vol = $('#v'+i).val();
    var side = $('#side'+i).val();
    var e_p = $('#e_p'+i).val();
    var e_v = $('#e_v'+i).val();
    var sy_id = $('#sy_id'+i).val();

    var presentdate = "<?php echo date("Y-m-d H:i:s"); ?>";
    var closedate = $("#end_date").val();

    if (presentdate > closedate) {
      hideloading();
      $("#message").html("<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Market is closed</div>");
      showMessage();
      return false;
    }

    if (e_p > 50.27) {
      hideloading();
      $("#message").html("<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid Price cannot be greater than 50.27</div>");
      showMessage();
      return false;
    }

    if (e_p < 11) {
      hideloading();
      $("#message").html("<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid Price cannot be less than 11</div>");
      showMessage();
      return false;
    }

    if (isNaN(e_p) || Math.round(e_p * 100) % 5 !== 0) {
      hideloading();
      $("#message").html("<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid price must be a multiple of 0.05</div>");
      showMessage();
      return false;
    }

    /*if (!(/^\d+(\.\d{0,1})?$/.test(e_p))) {
      hideloading();
      $("#message").html("<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Bid price should be a whole number or a decimal number with an increment of 0.05</div>");
      showMessage();
      return false;
    }*/

    if (e_v <= 99) {
      hideloading();
      $("#message").html("<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Volume cannot be less than 100</div>");
      showMessage();
      return false;
    }

    if (e_v % 10 != 0) {
      hideloading();
      $("#message").html("<div class='alert alert-warning alert-dismissible'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><i class='icon fa fa-times'> </i> Volume should be a multiple of 10</div>");
      showMessage();
      return false;
    }

    if(confirm("Are you sure you want to Change this order of "+ cd_code + ', of '+val1+'?')) {
      $.ajax({
          type: "POST",
          url: "../PROCESS/right_issue_process.php",
          data: { change_id: val, v: vol, side: side, cd_code: cd_code, e_p: e_p, e_v: e_v, sy_id: sy_id, closing_date: closedate },
          dataType: "html",
          success: function(response) {
            hideloading();
            $("#message").html(response);
            showMessage();
          },
          error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            hideloading();
            $("#message").html("<div class='alert alert-danger'>An error occurred: " + error + "</div>");
            showMessage();
          }
      });
    }else{
      hideloading();
      return false;
    }
  }

  function deleteOrd(val) {
    showLoading();
    var $delOrd = $("#del_ord" + val);
    var $cd_code_id = $("#cd_code" + val);

    var id = $delOrd.val(); 
    var cd_code = $cd_code_id.val(); 

    if (confirm("Are you sure want to delete order of "+cd_code+"?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/right_issue_process.php",
        data: { delete_rights_order: "delete_rights_order", order_id: id },
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
            $(`tr[data-id="${val}"]`).remove();
          }
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
</script>
</html>