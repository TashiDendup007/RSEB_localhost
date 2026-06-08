<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
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
          <li class="active">Price</li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <form action="" method="POST">
          <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">Price Update</h4>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                  <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                  <i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="row" >
                <div class="col-lg-6 col-md-6">
                  <label>Symbol</label>
                  <?php
                  $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol");
                  $wc->execute();
                  $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
                  echo'<select name="sy" id="sy"  class="form-control">
                  <option value=""> Select Symbol </option>';
                  foreach($rows as $row){
                    echo'
                    <option value="'.$row['symbol_id'].'">'.$row['symbol'].'</option>';
                  }
                  echo'</select>';
                  ?>
                </div> 
                <div class="col-lg-6 col-md-6">
                  <label for="exampleInputEmail1">Price</label>
                  <input type="text" class="form-control" name="price" id="price" required>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" id="save_price"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </div>
         </form> 

        <div class="row">
          <div class="col-lg-12 col-md-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Price List</h4>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Sl.No</th>
                        <th>Symbol.</th>
                        <th>Price</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php 
                      $query = $dbh->prepare('SELECT a.*,b.symbol from market_price a,symbol b where a.symbol_id = b.symbol_id');
                      $query->execute();
                      $io = 1;
                      while($result = $query->fetch(PDO::FETCH_ASSOC)) 
                      {
                        echo'
                        <tr>
                          <td> '.$io++.'</td>
                          <td>'.$result['symbol'].'
                            <input type="hidden" value="'.$result['symbol'].'" name="sy" id="sy'.$result['id'].'">
                          </td>
                          <td>
                            <input type="text" class="form-control" value="'.$result['market_price'].'" name="mp" id="mp'.$result['id'].'">
                          </td>
                          <td>
                            <button type="button" class="btnpress btn btn-info" name="edit_price" name="edit_price" id="'.$result['id'].'""><i class="fa fa-edit"></i> Update</button>
                          </td>
                        </tr>';
                      }
                      $query->closeCursor();
                    ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?>  
</body>
<script type="text/javascript">
  $("#save_price").click(function(){
    var sy = $("#sy").val();
    var price = $("#price").val();
    if(price!='' ){
     if (confirm("Are you sure you want to Enter price of  # "+ sy + ' as '+price+'?')) {
          showLoading();
          const operation = "save_price";
          const data = { save_price: operation, sy: sy, price: price };
          $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: $.param(data),
            dataType: 'html',
            success: function(response){
              hideloading();
              $("#message").html(response);
              showMessage();
            }
          });
      } else {
        return false;
      }
    } else {
      alert('Please enter all Fields');
      return false;
    }
  });

  $(".btnpress").click(function() {
    var id = $(this).attr('id');
    var sy = $("#sy"+id).val();
    var mp = $("#mp"+id).val();
    if(mp != '' ){
      if (confirm("Are you sure you want to Update price of  # "+ sy + ' as '+mp+'?')) {
        showLoading();
        const operation = "update_price";
        const data = { update_price: operation, id: id, mp: mp };
        
        $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: $.param(data),
          dataType: 'html',
          success: function(response){
            hideloading();
            $("#message").html(response);
            showMessage();
          }
        });
      } else {
       return false;
      }
    } else {
        alert('Price cannot be left blank!');
       return false;
    }
});
</script>
</html>
