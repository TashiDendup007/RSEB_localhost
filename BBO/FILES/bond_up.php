<?php 
  date_default_timezone_set('Asia/Thimphu');
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  
  $check = $dbh->prepare('SELECT a.institution_id,c.participant_code from adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un');
  $check->bindParam(':un',$username);
  $check->execute();
  $res=$check->fetch();
  $institution_id = $res['institution_id'];
  $participant_code = $res['participant_code'];
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
        <li><a href="#">Update Order</a></li>
      </ol>
    </section>

    <?php 
      $dateselect = date("Y-m-d H:i:s");

      $stmt = $dbh->prepare("SELECT b.id, b.symbol_id, b.start_bond_at, b.end_bond_at, b.status, 
          DATE_FORMAT(b.start_bond_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
          DATE_FORMAT(b.end_bond_at, '%W %M %e, %Y %h:%i %p') AS end_at_format
          FROM bond_offers b
          WHERE b.status = 1 
          ORDER BY b.id DESC
      ");
      $stmt->execute();
      $result = $stmt->fetch();
      if ($stmt->rowcount() < 1) {
        echo'<div class="box"><div class="box-body"><h3>Currently, there are no active bond offers.</h3></div></div>'; 
        die();
      }

      if ($result['start_bond_at'] > $dateselect) {
        echo"<div class='box'><div class='box-body'><h3>The Bid for the Bond will open on : <b>".$result['start_at_format']."</b></h3></div></div>";
        die();
      } elseif($result['end_bond_at'] < $dateselect) {
        echo "<div class='box'><div class='box-body'><h3>The Bid for the Bond has ended on <b>".$result['end_at_format']."</b></h3></div></div>";
        die();
      }

      /*if (substr($username, 0, 7) == 'MEMDKLT') {
            
      } else {
        die("<div class='box-body'><h3>The Bid for the Bond has ended.</b></h3></div>");
      }*/
    ?>

    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="box">
            <div class="box-body table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>SYMBOL</th>
                    <th>CD CODE</th>
                    <th>PRICE</th>
                    <th>VOLUME</th>
                    <th>SIDE</th>
                    <th>TIME</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                  //$un = substr($username, 0, 7);
                  $wc = $dbh->prepare("SELECT s.symbol, b.symbol_id, b.cd_code, b.order_size, b.type, b.bid_price, b.order_date, b.order_id
                    FROM bond b
                    JOIN symbol s ON b.symbol_id = s.symbol_id 
                    WHERE b.user_name = :un 
                      AND b.status = 0 
                      ORDER BY b.order_Date DESC
                  ");
                  $wc->bindParam(':un', $username);
                  $wc->execute();
                  $i = 1;
                  foreach($wc as $res){
                    echo'
                    <tr style="background-color:#dce2e9;">
                      <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
                      <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
                      <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
                      <input type="hidden" value="'.$res['order_size'].'" id="v'.$i.'">
                      <input type="hidden" value="'.$res['type'].'" id="side'.$i.'">
                      <td>'.$i.'</td>
                      <td>'.$res['symbol'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td>
                        <input type="text" class="form-control" size="8" value="'.$res['bid_price'].'" id="e_p'.$i.'" readonly>
                      </td>
                      <td>
                        <input type="text" class="form-control" size="8" value="'.$res['order_size'].'" id="e_v'.$i.'">
                      </td>
                      <td>BUY</td>
                      <td>'.$res['order_date'].'</td>
                      <td>
                        <button type="button" class="btn btn-primary" name="chg_or" id="chg_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');" data-toggle="tooltip" data-placement="top" title="Click Here to Change For '.$res['cd_code'].'"><i class="fa fa-refresh"></i> Change</button>
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
<script>
  $(function () {
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

  function fun(i) {
    var val = document.getElementById('chg_or'+i).value;
    var val1 = document.getElementById('sy'+i).value;
    var cd_code = document.getElementById('cd_code'+i).value;
    var vol = document.getElementById('v'+i).value;
    var side = document.getElementById('side'+i).value;
    var e_p = document.getElementById('e_p'+i).value;
    var e_v = document.getElementById('e_v'+i).value;
    var sy_id = document.getElementById('sy_id'+i).value;

    if (vol < 10) {
      alert("Volume should be less than 10.");
      return false;
    }

    if (vol % 10 != 0) {
      alert("Volume should be multiple of 10.");
      return false;
    }

    if (confirm("Are you sure you want to Change this order of "+ cd_code + ', of '+val1+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_process.php",
        data:{ change_id: val, v: vol, side: side, cd_code: cd_code, e_p: e_p, e_v: e_v, sy_id: sy_id },
        dataType: "html",
        success: function(data){
          $("#message").html(data);
          showMessage();
          // document.location.reload();
        }
      });
    }
    else{
      return false;
    }
 }
</script>
</body>
</html>
