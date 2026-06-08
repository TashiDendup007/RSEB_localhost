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
          <li><a href="#">List of your online Bidders</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>CD CODE</th>
                    <th>Phone</th>
                    <th>PaymentStatus</th>
                    <th>Applied On</th>
                    <th>Rights Finance</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php 
                  $un = substr($username, 0, 7);

                  $wc = $dbh->prepare("SELECT c.user_name, a.* 
                    FROM cms_rights_bid_registration a 
                    JOIN client_account c on c.cd_code = a.cd_code 
                    WHERE substr(c.user_name,1,7) = :un 
                      AND a.payment_status != 'NONE'
                  ");
                  $wc->bindParam(':un',$un);
                  $wc->execute();
                  $i=1;
                  foreach($wc as $res){
                    $bg = ($res['payment_status'] == '00') ? 'lightgreen' : 'red';
                    echo'
                    <tr style="background-color:#dce2e9;">
                      <td>'.$res['name'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td>'.$res['phone'].'</td>
                      <td style="background:'.$bg.'">'.$res['payment_status'].'</td>
                      <td>'.$res['date'].'</td>
                      <td>';
                      $sql = "SELECT sum(amount) as amount FROM rights_finance WHERE cd_code = :cdCode AND status = 0";
                      $amount= $dbh->prepare($sql);
                      $amount->bindParam(':cdCode', $res['cd_code']);
                      $amount->execute();
                      $amt = $amount->fetch();

                      $CashAvailable = $amt['amount'];
                      echo $CashAvailable;
                      if ($CashAvailable != 0 OR $CashAvailable != 'null') { 
                        $CashAvailable = 0; 
                      } else { 
                        $CashAvailable = $amt['amount']; 
                      }
                      echo $CashAvailable;
                      echo'
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
    <?php include('../NAV/footer.php') ?> 
  </div>
</body>
<script>
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

  function fun(i) {
    var val = document.getElementById('chg_or'+i).value;
    var val1 = document.getElementById('sy'+i).value;
    var cd_code= document.getElementById('cd_code'+i).value;
    var vol= document.getElementById('v'+i).value;
    var side= document.getElementById('side'+i).value;
    var e_p= document.getElementById('e_p'+i).value;
    var e_v= document.getElementById('e_v'+i).value;
    var sy_id= document.getElementById('sy_id'+i).value;
     if (confirm("Are you sure you want to Change this order of "+ cd_code + ', of '+val1+'?')){
      $.ajax({
        type: "POST",
        url: "../PROCESS/right_issue_process.php",
        data:'change_id='+val+'&v='+vol+'&side='+side+'&cd_code='+cd_code+'&e_p='+e_p+'&e_v='+e_v+'&sy_id='+sy_id,
        success: function(data){
          $("#message").show().html(data).fadeOut(5000);
        }
      });
    }
    else{
      return false;
    }
  }
</script>
</html>
