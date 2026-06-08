<?php 
  include('sessionStartFile_admin.php');
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
    <?php include('../NAV/navigation.php'); include('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">NegavtiveChek</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Negative Check</h4>
              </div>
              <div class="box-body">
                <?php 
                  $sql = $dbh->prepare("SELECT * FROM cds_holding h WHERE h.pending_in_vol < 0 OR h.pending_out_vol < 0");
                  $sql->execute();
                  echo'
                  <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">CDS Holding Id</th>
                        <th scope="col">CD code</th>
                        <th scope="col">Symbol Id</th>
                        <th class="text-center" scope="col">Volume</th>
                        <th scope="col">Pending In Vol</th>
                        <th scope="col">Pending Out Vol</th>
                        <th scope="col">Flag</th>
                      </tr>
                    </thead>
                    <tbody>';
                  $i=1;
                  foreach($sql as $res){
                    echo'
                    <tr>
                      <td>'.$i.'</td>
                      <td>'.$res['cds_holding_id'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td>'.$res['symbol_id'].'</td>
                      <td>'.$res['volume'].'</td>
                      <td>'.$res['pending_in_vol'].'</td>
                      <td>'.$res['pending_out_vol'].'</td>
                      <td>'.$res['flag'].'</td>
                    </tr>';
                    $i++;
                  }
                  echo'
                  </tbody>
                </table>';
                ?>
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
  $(document).ready(function() {
      $('#listTableId').DataTable();
  });
</script>
</html>
