<?php 
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
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Vault</a></li>      
      </ol>
    </section>
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Vault</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
        <div class="box-body">
          <div class="box-body">
              <div class="row">  
              <div class="col-xs-3">
                   <label>ID Card</label>
                   <input type="text" class="form-control" name="cid" id="cid" onclick="getState3(this.value);"  required>
                </div>
                <div  id="cd1">
                </div>
                 <!--<div class="col-xs-3">
                  <label>Symbol</label>
                  <?php
                      /*      $wc= $dbh->prepare("select symbol,symbol_id from symbol");
                           $wc->execute();
                            echo '<select name="sy" id="sy"  class="form-control" onChange="getState2(this.value);">';
                            echo '<option value=""> Select Symbol </option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['symbol_id'].'">';
                            echo $res['symbol'];
                            echo'</option>';
                            }
                            echo'</select>'; */
                    ?>
                </div> 
                 <div class="col-xs-3">
                  <label>Volume</label>
                  <input type="number" class="form-control" name="hol" id="hol" min="1">
                </div>--> 
              </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
        <div class="col-xs-4">
            <button type="submit" class="btn btn-success" id="Dep" name="Dep" value="<?php echo $_SESSION['sess_username'];?>" disabled><i class="fa fa-plus"></i>  Deposit </button>
        </div>
        </div>
        </form>
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header" style="font:8px;">
              <h4 class="box-title"><i class="fa fa-edit"></i><i class="fa fa-trash-o"></i></h4>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Id</th>
                    <th>Account</th>
                    <th>Name</th>
                    <th>Holding</th>
                    <th>Date</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $query= $dbh->prepare('SELECT a.* from bbo_vault a where a.user_name=:un');
                    $query->bindParam(':un',$_SESSION['sess_username']);
                    $query->execute();
                    $io=1;
                    while($result=$query->fetch(PDO::FETCH_ASSOC))
                    {
                      echo'
                      <tr>
                        <td> '.$result['vault_id'].'</td>
                        <td> '.$result['acc_code'].'</td>
                        <td> '.$result['f_name'].'</td>';
                        if($result['amount']<0) {
                          echo '<td>( '.$result['bbo_holding'].' )</td>';
                        } else {
                          echo '<td> '.$result['bbo_holding'].'</td>';
                        }
                        echo'<td> '.$result['dep_date'].'</td>';
                        echo'<td><button  data-toggle="modal" data-target="#myModal" name="edit_val" id="edit_val"  value="'.$result['vault_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button></td>
                          <form action="../PROCESS/process.php" method="POST" onsubmit="return fun('.$io.');"><td><button  name="delete_val" id="delete_val'.$io.'" value="'.$result['vault_id'].'"><i class="fa fa-trash-o"></i></button>&nbsp;</td></form>';
                        echo'</tr>';
                        $io=$io+1;
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
      </div>
    </section>
  </div>
</div>
<?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  function getState(val) {
    $.ajax({
      type: "POST",
      url: "b-edit.php",
      data:'edit_val='+val, 
      success: function(data) { 
        $("#myModal").html(data);
      }
    });
  }

 function fun(io) {
    var val= document.getElementById('delete_val'+io).value;
   if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
   } else {
      return false;
   }
 }

function getState3(val) {
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'cid='+val,success: function(data) { 
      $("#cd1").html(data);
    } 
  });
}
</script>
</html>
