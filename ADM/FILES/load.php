<?php
include('sessionStartFile_admin.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');

$user_name = $_SESSION['sess_username'];
$list = ins_id($user_name);
$ins_id = $list[0];
$p_code = $list[1];

if(!empty($_POST["pcode_link_user"])) {
  $cid = $_POST['pcode_link_user'];

  $sql = $dbh->prepare("
    SELECT a.username, a.participant_code, b.cd_code, b.user_name
    FROM users a
    JOIN client_account b ON a.cid = b.ID AND a.participant_code=SUBSTRING(b.user_name,1,7)
    WHERE a.cid = :cid
  ");
  $sql->bindParam(':cid', $cid);
  $sql->execute();
  $res = $sql->fetch();

  if ($sql->rowCount() > 0) {
    echo '
      <div class="col-lg-4 col-md-4">
        <label>User Name</label>
        <select name="un" id="un" class="form-control" readonly>
          <option value="'.$res['username'].'">'.$res['username'].'</option>
        </select>
      </div>
      <div class="col-lg-4 col-md-4">
        <label>CD CODE</label>
        <select name="ct" id="ct" class="form-control" readonly>
          <option value="'.$res['cd_code'].'">'.$res['cd_code'].'</option>
        </select>
      </div>
      <div class="col-lg-4 col-md-4">
        <label>Participant Code</label>
        <select name="pcode" id="pcode" class="form-control" readonly>
          <option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>
        </select>
      </div>
      <div class="col-lg-4 col-md-4">
        <label>Broker User</label>
        <select name="bun" id="bun" class="form-control" readonly>';
    echo '<option value="'.$res['user_name'].'">'.$res['user_name'].'</option>';
    echo '</select>
      </div>';
  } else {
    echo '
      <div class="col-lg-4 col-md-4">
        <label>User Name</label>
        <input type="text" class="form-control" value="NO DATA" disabled>
        <input type="hidden" class="form-control" id="ct" name="ct" value="">
        <input type="hidden" class="form-control" id="pcode" name="pcode" value="">
      </div>';
  }
} 
elseif(!empty($_POST["load_brokers"])) {
  $pcode = $_POST['load_brokers'];
  $wc = $dbh->prepare("SELECT username, participant_code FROM users WHERE participant_code=:pcode AND status = 1 AND role_id IN (2, 8)");
  $wc->bindParam(':pcode', $pcode);
  $wc->execute();
  $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-4 col-md-4">
    <label>Broker</label>
    <select name="broker" id="broker" class="form-control">
    <option value="">--Select Broker--</option>';
    foreach ($rows as $res) {
      echo '<option value="'.$res['username'].'">'.$res['username'].'</option>';
    }
    echo '</select>
  </div>';
}
elseif(!empty($_POST["load_rate"])) {
  $type = $_POST['load_rate'];

  $symbol_query = "SELECT symbol, symbol_id FROM symbol WHERE status = 1 AND ";
  if ($type == 'RIGHTS') {
    $symbol_query .= "security_type = 'OS'";
  } elseif ($type == 'IPO') {
    $symbol_query .= "security_type = 'OS'";
  } else {
    $symbol_query .= "(security_type = 'GB' OR security_type = 'CB')";
  }

  echo'
  <div class="col-lg-4 col-md-4" name="symbol" id="symbol">
    <label>Symbol</label>';
    $wc= $dbh->prepare($symbol_query);
    $wc->execute();
    echo '<select name="sy" id="sy" class="form-control"';
    echo '<option value=""> Select Symbol </option>';
    echo '<option value="-Select symbol-" selected>-Select symbol-</option>';
     while($res = $wc->fetch())
    {
      echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
    }
    echo'</select>
  </div>
  <div class="col-lg-4 col-md-4">
    <label>Rate</label>
    <input type="text" class="form-control"  id="rate" name="rate" value="">
  </div>';
  die();
}
elseif(!empty($_POST["getCID"])) {
  $cdCod=$_POST['cdCod'];

  $sql=$dbh->prepare("SELECT * FROM client_account WHERE cd_code=:cd");
  $sql->bindParam(':cd', $cdCod);
  $sql->execute();
  $res=$sql->fetch();
  echo'
  <div class="modal fade" id="cidModalTarget" role="dialog">
    <div class="modal-dialog" role="document">
      <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Update CID Number </h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="row">
              <div class="col-xs-6">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" value="'.$res['f_name'].' '.$res['l_name'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="cdCode">CD Code</label>
                <input type="text" class="form-control" name="cdCode" id="cdCode" value="'.$res['cd_code'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="cid">CID No</label>
                <input type="text" class="form-control" name="cid" id="cid" value="'.$res['ID'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="newcid">New CID no to be updated</label>
                <input type="text" class="form-control" name="newcid" id="newcid" maxlength="11">
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="button" class="btn btn-primary" name="update_cid" id="update_cid"  value="'.$cdCod.'">UPDATE</button>  
            </div>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </form>
    </div>
  </div>
  <script type="text/javascript"> 
    $("#update_cid").click(function(){
      showLoading();
      var cidNo = $("#newcid").val();
      var cdCode = $("#cdCode").val();
      if(cidNo==""){
        alert("Please enter new cid number");
        hideloading();
      }else{
        if (confirm("Are you sure you want to update?"))
        {
          var op="update_cid";
          $.ajax({ 
            type: "POST", 
            url: "../PROCESS/process.php", 
            data: "cidNo="+cidNo+"&update_cid="+op+"&cdCode="+cdCode, 
              success: function(data){  
              hideloading();
              $(".statusMsg").show();
              $(".statusMsg").html(data);
              $(".statusMsg").fadeOut(5000);
              //location.reload();
              $("#cidModalTarget").modal("hide");
              $(".modal-backdrop").remove();
              hideloading();
            } 
          });
        }else{ 
          $("#cidModalTarget").modal("hide");
          //$("body").removeClass("modal-open");
          $(".modal-backdrop").remove();
          hideloading();
          return false;
        }
      }
    });
  </script>';
}
elseif(!empty($_POST["getCDCodeDetls"])) {
  $cd = $_POST['getCDCodeDetls'];
  
  $wc = $dbh->prepare("SELECT * FROM client_account WHERE cd_code=:cd");
  $wc->bindParam(':cd',$cd);
  $wc->execute();
  $res=$wc->fetch();
  if($wc->rowCount() > 0){
    echo'       
    <div class="col-lg-6 col-md-6">
      <label for="name">Name:</label>
      <input type="text" class="form-control" name="name" id="name" value="'.$res['f_name'].' '.$res['l_name'].'" readonly>
    </div>
    <div class="col-lg-6 col-md-6">
      <label for="phone">Phone:</label>
      <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" readonly>
    </div>
    <div class="col-lg-6 col-md-6">
      <label for="cid">Old CID No:</label>
      <input type="text" class="form-control" name="cid" id="cid" value="'.$res['ID'].'" readonly>
    </div>
    <div class="col-lg-6 col-md-6">
      <label for="newcid">New CID No:<font color="red">*</font></label>
      <input type="text" class="form-control" name="newcid" id="newcid" required>
      <span id="cidError" style="color: red;"></span>
    </div>
    <div class="col-lg-12 col-md-12">
      <label for="remark">Remark:<font color="red">*</font></label>
      <textarea name="remark" id="remark" class="form-control" required></textarea>
      <span id="remarkError" style="color: red;"></span>
    </div>
    
    <script type="text/javascript">
      $("#newcid").click( function() {
        $("#cidError").html("");
      });
      $("#remark").click( function() {
        $("#remarkError").html("");
      });
    </script>';
  }else{
    echo'
    <div class="col-lg-6 col-md-6">
      <label>Name</label>
      <input type="text" class="form-control" value="NO DATA" disabled>
    </div>';
  }
}
elseif(!empty($_POST["loadShareAucDtls"])) {
  $cidNo=$_POST['loadShareAucDtls'];

  $wc= $dbh->prepare("SELECT p.bfs_orderid, p.dateentry, p.bfs_code, p.cd_code, p.symbol_id, p.amount, p.payment_status, p.type, 
    p.name, p.email, p.phone, p.vol_applied, p.price, p.details FROM rights_issue_online_temp p WHERE p.name=:cidNo");
  $wc->bindParam(':cidNo',$cidNo);
  $wc->execute();
  if($wc->rowCount() > 0)
  {
    echo'
    <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">bfs orderId</th>
          <th scope="col">Date</th>
          <th scope="col">bfs Code</th>
          <th scope="col">CD code</th>
          <th scope="col">Symbol Id</th>
          <th scope="col">Amount</th>
          <th scope="col">Payment Status</th>
          <th scope="col">Type</th>
          <th scope="col">Name</th>
          <th scope="col">Email</th>
          <th scope="col">Phone</th>
          <th scope="col">Volume</th>
          <th scope="col">Price</th>
          <th scope="col">Details</th>
        </tr>
      </thead>
      <tbody>';
    $i=1;
    foreach($wc as $res) {
      echo'
      <tr>
        <td>'.$i.'</td>
        <td>'.$res['bfs_orderid'].'</td>
        <td>'.$res['dateentry'].'</td>
        <td>'.$res['bfs_code'].'</td>
        <td>'.$res['cd_code'].'</td>
        <td>'.$res['symbol_id'].'</td>
        <td>'.$res['amount'].'</td>
        <td>'.$res['payment_status'].'</td>
        <td>'.$res['type'].'</td>
        <td>'.$res['name'].'</td>
        <td>'.$res['email'].'</td>
        <td>'.$res['phone'].'</td>
        <td>'.$res['vol_applied'].'</td>
        <td>'.$res['price'].'</td>
        <td>'.$res['details'].'</td>
      </tr>';
      $i++;
    }
    echo'
    </tbody>
  </table>
  <script type="text/javascript">
    $(document).ready(function() {
        $("#listTableId").DataTable();
    });
  </script>';
  }else{
    echo'
    <div class="col-xs-12">
      <label>Details</label>
      <input type="text" class="form-control" value="NO DATA" disabled>
    </div>';
  }
} 
elseif(isset($_POST['get_circuitBreaker_list'])) {
  $select = $dbh->prepare("SELECT * FROM circuit_breaker");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-responsive">
      <table id="roleTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Circuit Name</th>
            <th scope="col">Margin</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i=1;
        foreach ($result as $row) {
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td><input type="text" class="form-control" name="name'.$row['id'].'" id="name'.$row['id'].'" value="'.$row['name'].'"></td>
            <td><input type="text" class="form-control" name="margin'.$row['id'].'" id="margin'.$row['id'].'" value="'.$row['margin'].'"></td>
            <td>
              <select class="form-control" name="status'.$row['id'].'" id="status'.$row['id'].'" required>
                <option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'>Active</option>
                <option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'>InActive</option>
              </select>
            </td>
            <td>
              <button type="button" class="btn btn-primary" onclick="updateCircuitBreaker('.$row['id'].')" data-toggle="tooltip" data-placement="top" title="Click here to update"><i class="fa fa-edit"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#roleTableId").DataTable();
    });
  </script>';
  die();
} 
elseif(!empty($_POST["loadAucDtlsFromMail"])) {
  $email = $_POST['loadAucDtlsFromMail'];

  $wc = $dbh->prepare("SELECT p.bfs_orderid, p.dateentry, p.bfs_code, p.cd_code, p.symbol_id, p.amount, p.payment_status, p.type, 
    p.name, p.email, p.phone, p.vol_applied, p.price, p.details FROM rights_issue_online_temp p WHERE p.email=:email");
  $wc->bindParam(':email',$email);
  $wc->execute();
  if($wc->rowCount() > 0) {
    echo'
    <table id="listTableId2" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">bfs orderId</th>
          <th scope="col">Date</th>
          <th scope="col">bfs Code</th>
          <th scope="col">CD code</th>
          <th scope="col">Symbol Id</th>
          <th scope="col">Amount</th>
          <th scope="col">Payment Status</th>
          <th scope="col">Type</th>
          <th scope="col">Name</th>
          <th scope="col">Email</th>
          <th scope="col">Phone</th>
          <th scope="col">Volume</th>
          <th scope="col">Price</th>
          <th scope="col">Details</th>
        </tr>
      </thead>
      <tbody>';
    $i=1;
    foreach($wc as $res){
      echo'
      <tr>
        <td>'.$i.'</td>
        <td>'.$res['bfs_orderid'].'</td>
        <td>'.$res['dateentry'].'</td>
        <td>'.$res['bfs_code'].'</td>
        <td>'.$res['cd_code'].'</td>
        <td>'.$res['symbol_id'].'</td>
        <td>'.$res['amount'].'</td>
        <td>'.$res['payment_status'].'</td>
        <td>'.$res['type'].'</td>
        <td>'.$res['name'].'</td>
        <td>'.$res['email'].'</td>
        <td>'.$res['phone'].'</td>
        <td>'.$res['vol_applied'].'</td>
        <td>'.$res['price'].'</td>
        <td>'.$res['details'].'</td>
      </tr>';
      $i++;
    }
    echo'
    </tbody>
  </table>
  <script type="text/javascript">
    $(document).ready(function() {
        $("#listTableId2").DataTable();
    });
  </script>';
  } else {
    echo'
    <div class="col-xs-12">
      <label>Details</label>
      <input type="text" class="form-control" value="NO DATA" disabled>
    </div>';
  }
} 
elseif(isset($_POST['get_occupation_list'])) {
  $select = $dbh->prepare("SELECT * FROM occupation");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-responsive">
      <table id="roleTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Occupation Name</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i=1;
        foreach ($result as $row) {
          $staus_name = ($row['status'] == 1) ? 'Active' : 'In-Active';
          echo'
          <tr data-id="'.$row['occupation'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['occupation_name'].'</td>
            <td>'.$staus_name.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editOccupation('.$row['occupation'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteOccupation('.$row['occupation'].', \''.$row['occupation_name'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#roleTableId").DataTable();
    });
  </script>';
  die();
} 
elseif(isset($_POST['get_role_list'])) {
  $select = $dbh->prepare("SELECT * FROM role_masters");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-response">
      <table id="roleTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">Sl No</th>
            <th scope="col">Role Name</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($result as $row) {
          $staus_name = ($row['status'] == 1) ? 'Active' : 'In-Active';
          echo'
          <tr id="del_row'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['role_name'].'</td>
            <td>'.$staus_name.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editRole('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteRole('.$row['id'].', \''.$row['role_name'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#roleTableId").DataTable();
    });
  </script>';
  die();
} 
elseif(isset($_POST['get_sector_list'])) {
  $select = $dbh->prepare("SELECT * FROM sector_masters");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-response">
      <table id="roleTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">Sl No</th>
            <th scope="col">Sector Name</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i=1;
        foreach ($result as $row) {
          $status = ($row['status'] == 1) ? 'Active' : 'In-Active';
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['name'].'</td>
            <td>'.$status.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editSector('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteSector('.$row['id'].', \''.$row['name'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#roleTableId").DataTable();
    });
  </script>';
  die();
} 
elseif(isset($_POST['get_corporate_list'])) {
  $select = $dbh->prepare("SELECT * FROM corporate_action_masters");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-response">
      <table id="roleTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">Sl No</th>
            <th scope="col">Corporate Name</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i=1;
        foreach ($result as $row) {
          $staus_name = ($row['status'] == 1) ? 'Active' : 'In-Active';
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['corporate_name'].'</td>
            <td>'.$staus_name.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editCorporate('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteCorporate('.$row['id'].', \''.$row['corporate_name'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#roleTableId").DataTable();
    });
  </script>';
  die();
} 
elseif(isset($_POST['get_rights_offer_list'])) {
  $select = $dbh->prepare("SELECT 
        r.id, r.symbol_id, s.symbol, r.start_at, r.end_at, r.corp_announcement_id, r.announcement_type, c.corporate_name,
        DATE_FORMAT(r.start_at, '%d/%m/%Y %h:%i %p') start_at_format,
        DATE_FORMAT(r.end_at, '%d/%m/%Y %h:%i %p') end_at_format
        FROM rights_offers r
        INNER JOIN corporate_action_masters c ON r.announcement_type = c.id
        INNER JOIN symbol s ON r.symbol_id = s.symbol_id 
        -- WHERE r.status=1
  ");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-responsive">
      <table id="rightsOfferTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Symbol</th>
            <th scope="col">Announcement Type</th>
            <th scope="col">Start Date</th>
            <th scope="col">End Date</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($result as $row) {
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['symbol'].'</td>
            <td>'.$row['corporate_name'].'</td>
            <td>'.$row['start_at_format'].'</td>
            <td>'.$row['end_at_format'].'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editRightsOffer('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteRightsOffer('.$row['id'].', \''.$row['corporate_name'].'\', \''.$row['symbol'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#rightsOfferTableId").DataTable();
    });
  </script>';
  die();
} 
elseif(isset($_POST['get_auction_symbol_list'])) {
  $select = $dbh->prepare("SELECT 
          a.id, a.symbol_id, a.symbol, a.offer_volume, a.auction_date, a.end_date, a.start_price, a.max_price, a.status,
          DATE_FORMAT(a.auction_date, '%d/%m/%Y %h:%i %p') start_at_format,
          DATE_FORMAT(a.end_date, '%d/%m/%Y %h:%i %p') end_at_format
        FROM share_auctions a 
        -- WHERE a.status='Y'");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-responsive">
      <table id="rightsOfferTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Symbol</th>
            <th scope="col">Offer Vol</th>
            <th scope="col">Start/Min Price</th>
            <th scope="col">Max Price</th>
            <th scope="col">Start Date</th>
            <th scope="col">End Date</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($result as $row) {
          $status = $row['status'] == 'Y' ? 'Active' : 'InActive';
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['symbol'].'</td>
            <td>'.$row['start_price'].'</td>
            <td>'.$row['max_price'].'</td>
            <td>'.$row['offer_volume'].'</td>
            <td>'.$row['start_at_format'].'</td>
            <td>'.$row['end_at_format'].'</td>
            <td>'.$status.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editAuctionOffer('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteAuctionOffer('.$row['id'].', \''.$row['symbol_id'].'\', \''.$row['symbol'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#rightsOfferTableId").DataTable();
    });
  </script>';
  die();
} 
elseif (isset($_POST['get_symbol_name'])) {
  $symbol_id = $_POST['id'];

  $get = $dbh->prepare("SELECT name FROM symbol WHERE symbol_id = ?");
  $get->bindParam(1, $symbol_id);
  $get->execute();
  $row = $get->fetch();
  
  echo $row['name'];
  
  $get = null;
  $dbh = null;
  die();
} 
elseif(isset($_POST['get_bond_offer_list'])) {
  $select = $dbh->prepare("SELECT 
        r.id, r.symbol_id, s.symbol, s.name, r.start_bond_at, r.end_bond_at, r.status, r.created_at,
        DATE_FORMAT(r.start_bond_at, '%d/%m/%Y %h:%i %p') start_at_format,
        DATE_FORMAT(r.end_bond_at, '%d/%m/%Y %h:%i %p') end_at_format
        FROM bond_offers r
        INNER JOIN symbol s ON r.symbol_id = s.symbol_id
        -- WHERE r.status = 1
  ");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-responsive">
      <table id="bondOfferTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Symbol</th>
            <th scope="col">Name</th>
            <th scope="col">Start Date</th>
            <th scope="col">End Date</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($result as $row) {
          $status = ($row['status'] == 1) ? 'Active' : 'In-Active';
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['symbol'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['start_at_format'].'</td>
            <td>'.$row['end_at_format'].'</td>
            <td>'.$status.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editBondOffer('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteBondOffer('.$row['id'].', \''.$row['symbol'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#bondOfferTableId").DataTable();
    });
  </script>';
  die();
}
elseif(isset($_POST['get_ipo_offer_list'])) {
  $select = $dbh->prepare("SELECT 
      r.id, r.symbol_id, s.symbol, s.name, r.start_at, r.end_at, r.status, r.created_at,
      DATE_FORMAT(r.start_at, '%d/%m/%Y %h:%i %p') start_at_format,
      DATE_FORMAT(r.end_at, '%d/%m/%Y %h:%i %p') end_at_format
      FROM ipo_offers r
      INNER JOIN symbol s ON r.symbol_id = s.symbol_id
      ORDER BY r.id DESC
  ");
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-responsive">
      <table id="ipoOfferTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Symbol</th>
            <th scope="col">Name</th>
            <th scope="col">Start Date</th>
            <th scope="col">End Date</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($result as $row) {
          $status = ($row['status'] == 1) ? 'Active' : 'In-Active';
          echo'
          <tr data-id="'.$row['id'].'">
            <th scope="row">'.$i.'</th>
            <td>'.$row['symbol'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['start_at_format'].'</td>
            <td>'.$row['end_at_format'].'</td>
            <td>'.$status.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="editIPO_Offer('.$row['id'].')"><i class="fa fa-edit"></i></button>
              <button type="button" class="btn btn-danger" onclick="deleteIPO_Offer('.$row['id'].', \''.$row['symbol'].'\')"><i class="fa fa-trash-o"></i></button>
            </td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#ipoOfferTableId").DataTable();
    });
  </script>';
  die();
}
?>