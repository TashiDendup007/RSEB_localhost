<?php 
include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');
include('../../Functions/f.php'); 
date_default_timezone_set("Asia/Thimphu");

$check = $dbh->prepare('SELECT a.institution_id from adm_institution a, adm_participants b,users c WHERE c.participant_code=b.participant_code and b.institution_id = a.institution_id AND c.username=:un');
$check->bindParam(':un', $username);
$check->execute();
$res = $check->fetch();
$institution_id = $res['institution_id'];
$sys_date_time = date("Y-m-d H:i:s"); 

//Saving Record
if (isset($_POST['save_right_issue'])) {
  $cid = $_POST['cid'];
  $symbol_id = $_POST['symbol_id'];
  $type = $_POST['options'];
  $subscribe = $_POST['subscribe1'];
  $renounce1 = $_POST['renounce1'];
  $bidVol = $_POST['bidVol'];
  $faceValue = $_POST['face_value'];
  $rencd = $_POST['rencd'];
  $rights = $_POST['rights'];
  $announcement_id = $_POST['announcement_id'];
  $status = 0;

  // check subcribed and renounce total
  if ($type === 'S' || $type === 'R') {
    $check = $dbh->prepare("SELECT SUM(r.order_size) AS order_total, s.ribon_volume - SUM(r.order_size) AS available_rights
      FROM rights_issue r
      JOIN client_account a ON r.cd_code = a.cd_code 
      JOIN spot_date_holding s ON a.client_id = s.client_id 
      WHERE r.type IN ('S', 'R') 
        AND r.cd_code = ? 
        AND r.status = 0 
        AND r.symbol_id = ? 
        AND s.corp_announcement_id = ?
      GROUP BY s.ribon_volume
    ");
    $check->bindParam(1, $_POST['cdcode']);
    $check->bindParam(2, $symbol_id);
    $check->bindParam(3, $announcement_id);
    $check->execute();
  }

  if ($type == "S") {
    $availableVolume = $_POST['availableVolume'];
    $cdcode = $_POST['cdcode'];
    $order = $subscribe;
    $bidPrice = 0;
    $totalAmount = $faceValue * $order;
    $availableRights = $rights - $order;

  } else if($type == "R") {
    $availableVolume = $_POST['availableVolume'];
    $cdcode = $_POST['cdcode'];
    $order = $renounce1;
    $bidPrice = 0;
    $totalAmount = $faceValue * $order;
    $availableRights = $rights - $order;

  } else if($type == "O") {
    $rencd = 0;
    $availableVolume = $_POST['availableVolume'];
    $cdcode = $_POST['cdcode'];
    $order = $bidVol;
    $bidPrice= 0;
    $totalAmount = $bidPrice * $order;
    $faceValue = 0;
    $availableRights = $rights - $order;

  } else {
    $rencd = 0;
    $rights = 0;
    $availableVolume = 0;
    $cdcode = $_POST['cdcode'];
    $order = $bidVol;
    $bidPrice = $_POST['bidPrice'];
    $totalAmount = $bidPrice * $order;
    $faceValue = 0;
    $availableRights = 0;
    // $symbol_id = $_POST['sy'];
  }
  $flag = 5;

  if ($type === 'S' || $type === 'R') {
    if ($rowAvailable = $check->fetch()) {
      $available_rights = $rowAvailable['available_rights'];
      if ($order > $available_rights) {
        echo '
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fa fa-check"></i> Insufficient Volume, only: '.$available_rights.' available
                </div>
            </div>
        </div>';
        die();
      }
    }
  }

  if ($type == 'R') {
     $check= $dbh->prepare("SELECT order_size FROM rights_issue WHERE type='R' AND cd_code = :cd AND renounce_cd_code = :r_cd AND status = 0");
     $check->bindParam(':cd', $cdcode);
     $check->bindParam(':r_cd', $rencd);
     $check->execute();
  }
  elseif ($type == 'S') {
     $check= $dbh->prepare("SELECT order_size FROM rights_issue WHERE type = :type AND cd_code = :cd AND status = 0");
     $check->bindParam(':type', $type);
     $check->bindParam(':cd', $cdcode);
     $check->execute();
  } else {
    $check = $dbh->prepare("SELECT r.order_size 
        FROM rights_issue r
        INNER JOIN client_account c ON r.cd_code=c.cd_code 
        WHERE r.type=:type AND c.ID=:cid AND r.status=0 AND r.order_size != 0 AND r.symbol_id=:symId");
     $check->bindParam(':type', $type);
     $check->bindParam(':cid', $cid);
     $check->bindParam(':symId', $symbol_id);
     $check->execute();
  }
  $state = $check->fetch();
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    if ($check->rowCount() > 0) {
      $updateVol = $state['order_size'] + $order;
      $total_amount = $updateVol * $faceValue;
      
      if ($type == 'R') {
        $update = $dbh->prepare("UPDATE rights_issue SET order_size=:os, bid_price=:bp, total_amount=:ta WHERE cd_code=:cd AND type=:type AND renounce_cd_code=:ren");
        $update->bindParam(':os',$updateVol);
        $update->bindParam(':bp',$bidPrice);
        $update->bindParam(':ta',$total_amount);
        $update->bindParam(':cd',$cdcode);
        $update->bindParam(':type',$type);
        $update->bindParam(':ren',$rencd);
        $update->execute();

        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
      } elseif ($type == 'S') {
        $update = $dbh->prepare("UPDATE rights_issue SET order_size=:os, bid_price=:bp, total_amount=:ta WHERE cd_code=:cd AND type=:type");
        $update->bindParam(':os', $updateVol);
        $update->bindParam(':bp', $bidPrice);
        $update->bindParam(':ta', $total_amount);
        $update->bindParam(':cd', $cdcode);
        $update->bindParam(':type', $type);
        $update->execute();

        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
      } else {
        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> You have already placed the order.</div></div></div>';
      }
    } else {
      $save = $dbh->prepare("INSERT INTO rights_issue(type, cd_code, renounce_cd_code, order_size, symbol_id, rights_issued, face_value, total_amount, bid_price, available_rights, user_name, status, cid_no) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $save->bindParam(1, $type);
      $save->bindParam(2, $cdcode);
      $save->bindParam(3, $rencd);
      $save->bindParam(4, $order);
      $save->bindParam(5, $symbol_id);
      $save->bindParam(6, $rights);
      $save->bindParam(7, $faceValue);
      $save->bindParam(8, $totalAmount);
      $save->bindParam(9, $bidPrice);
      $save->bindParam(10, $availableRights);
      $save->bindParam(11, $username);
      $save->bindParam(12, $status);
      $save->bindParam(13, $cid);
      $save->execute();

      $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
    }

    $dbh->commit();
    $dbh = null;

  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error ==> '.$e->getMessage().'</div></div></div>';
  }
  echo $message;
  die();
} elseif (isset($_POST['deb'])) { 
  $cd_code = $_POST['cdcode'];
  $amt = $_POST['amt'];
  $rm = $_POST['rm'];
  $flag_debit = 0;
  $status = 0;

  $check= $dbh->prepare('SELECT sum(amount) as amount from rights_finance where cd_code=:cd');
  $check->bindParam(':cd',$cd_code);
  $check->execute();
  $res=$check->fetch();
  if ($res['amount'] >= $amt) {
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();
      $amount = -$amt;

      $save = $dbh->prepare("INSERT into rights_finance(cd_code, amount, remarks, flag, flag_id, user_name, institution_id, status) 
                        VALUES(:cd_code, :amount, :remarks, :flag_debit, :flag_id, :username, :institution_id, :status)");
      $save->bindParam(':cd_code', $cd_code);
      $save->bindParam(':amount', $amount);
      $save->bindParam(':remarks', $rm);
      $save->bindParam(':flag_debit', $flag_debit);
      $save->bindParam(':flag_id', $flag_debit);
      $save->bindParam(':username', $username);
      $save->bindParam(':institution_id', $institution_id);
      $save->bindParam(':status', $status);
      $save->execute();

      $dbh->commit();
      $dbh = null;
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div>';
    }catch(PDOException $e){
      $dbh->rollBack();
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div>';
    }
  } else {
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Message!&nbsp;&nbsp Oops Sorry! Your Balance is '.$res['amount'].' ,insufficient fund.</div></div>';
  } 
  die();
} elseif (isset($_POST['cre'])) { 
  $cd_code = $_POST['cdcode'];
  $amt = $_POST['amt'];
  $rm = $_POST['rm'];
  $flag_debit = 1;
  $status = 0;

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("INSERT into rights_finance (cd_code,amount,remarks,flag,flag_id,user_name,institution_id,status) 
      VALUES(:cd_code, :amount, :remarks, :flag_debit, :flag_debit, :username, :isnt_id, :status)");
    $save->bindParam(':cd_code', $cd_code);
    $save->bindParam(':amount', $amt);
    $save->bindParam(':remarks', $rm);
    $save->bindParam(':flag_debit', $flag_debit);
    $save->bindParam(':username', $username);
    $save->bindParam(':isnt_id', $institution_id);
    $save->bindParam(':status', $status);
    $save->execute();

    $dbh->commit();
    $dbh = null;

    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div>';
  }catch(PDOException $e){
    $dbh->rollBack();
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div>';
  }
  die();
} 
elseif(!empty($_POST["change_id"])) {
  $id = $_POST["change_id"];
  $ex_vol = $_POST["v"];
  $e_v = $_POST["e_v"];
  $e_p = number_format($_POST["e_p"], 2);
  $side = $_POST["side"];
  $cd_code = $_POST["cd_code"];
  $sy_id = $_POST["sy_id"];
  $closing_date = $_POST["closing_date"];

  if ($sys_date_time > $closing_date) {
    $message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Rights Auction Has Ended.</div>';
    echo $message;
    exit();
  }

  if ($e_p < 11 || round($e_p * 100) % 5 != 0 || $e_p > 50.27) {
    $message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button> 
      Bid price cannot be less than Nu. 11 <br>
      Bid price cannot be greater than Nu. 50.27 <br>
      Bid price must be multiple of 0.05
    </div>';
    echo $message;
    exit();
  }

  if (($e_v < 100) || ($e_v % 10 != 0 || filter_var($e_v, FILTER_VALIDATE_INT) === false || $e_v <= 0)) {
    $message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
      Volume cannot be less than 100<br>
      Volume must be multiple of 10<br>
      Bid volume must be a natural number (positive integer).
    </div>';
    echo $message;
    exit();
  }

  $tot = cash_total_rights($cd_code, $username);
  $new_amt = ($e_v * $e_p) + ($e_v * $e_p * 0.02);

  if ($tot >= $new_amt) {
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $ord_up = $dbh->prepare("UPDATE rights_issue SET order_size=:new_buy_vol, bid_price=:new_price, total_amount=:new_amt WHERE order_id=:id");
      $ord_up->bindParam(':id', $id);
      $ord_up->bindParam(':new_price', $e_p);
      $ord_up->bindParam(':new_buy_vol', $e_v);
      $ord_up->bindParam(':new_amt', $new_amt);
      $ord_up->execute();

      $dbh->commit();
      $dbh = null;
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
      </button><i class="icon fa fa-check"></i> Order Updated for ' . $cd_code . '</div></div>';
    } catch(PDOException $e) {
      $dbh->rollBack();
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
      </button><i class="icon fa fa-check"></i> Sorry! Order not updted.</div></div>';
    }
  } else {
    echo'
    <div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
    </button><i class="icon fa fa-check"></i> Sorry! Not enough CASH.</div></div>';
  }
  die();
} 
elseif (isset($_POST['delete_rights_order'])) {
  $oder_id = $_POST['order_id'];
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $stmt = $dbh->prepare("DELETE FROM rights_issue WHERE order_id=:id");
    $stmt->bindParam(':id', $oder_id);
    $stmt->execute();

    $dbh->commit();
    $dbh = null;
    $stmt = null;

    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Order deleted successfully.'
    );
  } catch (Exception $e) {
    $dbh->rollBack();
    $data = array(
        'status' => 400, 'success' => false, 'message' => $e->getMessage()
    );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
}
elseif (isset($_POST['delete_rights_order_sub_ren'])) {
  $oder_id = $_POST['order_id'];
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $stmt = $dbh->prepare("DELETE FROM rights_issue WHERE order_id=:id");
    $stmt->bindParam(':id', $oder_id);
    $stmt->execute();

    $dbh->commit();
    $dbh = null;
    $stmt = null;

    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Order deleted successfully.'
    );
  } catch (Exception $e) {
    $dbh->rollBack();
    $data = array(
        'status' => 400, 'success' => false, 'message' => $e->getMessage()
    );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif (isset($_POST['search_cd_code'])) {
  $cid_no = $_POST['search_cd_code'];
  
  echo'
  <div class="table-responsive">
    <table class="table table-striped table-bordered" width="100%">
      <thead>
        <tr>
          <th>Symbol</th>
          <th>CD Code</th>
          <th>Name</th>
          <th>CID/DISN</th>
        </tr>
      </thead>
      <tbody>';
      /*$stmt = $dbh->prepare("SELECT a.cd_code, a.ID, a.acc_type, a.f_name, a.l_name, a.phone, a.email, s.symbol, h.volume 
          FROM client_account a 
          JOIN cds_holding h ON a.cd_code = h.cd_code 
          JOIN symbol s ON h.symbol_id = s.symbol_id 
          WHERE a.ID = ?
          AND h.symbol_id = 63 
          AND sh.corp_announcement_id = 66
          GROUP BY a.cd_code
      ");*/
      $stmt = $dbh->prepare("SELECT a.cd_code, a.ID, a.acc_type, a.f_name, a.l_name, a.phone, a.email, s.symbol, h.ribon_volume, h.volume 
              FROM client_account a 
              JOIN spot_date_holding h ON a.client_id = h.client_id
              JOIN symbol s ON h.symbol_id = s.symbol_id 
              WHERE a.ID = ? 
              AND h.announcement_type = 1 
              AND h.ribon_volume > 0
              AND h.sdh_id = (SELECT MAX(sdh_id) FROM spot_date_holding WHERE client_id = a.client_id)
      ");
      $stmt->bindParam(1, $cid_no);
      $stmt->execute();
      foreach ($stmt as $key) {
        $name = ($key['acc_type'] == 'I') ? $key['f_name'].' '.$key['l_name'] : $key['f_name'];
        echo'
        <tr>
          <td>'.$key['symbol'].'</td>
          <td>'.$key['cd_code'].'</td>
          <td>'.$name.'</td>
          <td>'.$key['ID'].'</td>
        </tr>';
      }
      echo'
      </tbody>
    </table>
  </div>';
  die();
}
elseif (isset($_POST['search_cd_code_for_renounce'])) {
  $cid_no = $_POST['search_cd_code_for_renounce'];
  if ($cid_no == '') {
    echo'<span style="color: red;">Required CID Number</span>';
    die();
  }
  echo'
  <div class="table-responsive">
    <table class="table table-striped table-bordered" width="100%">
      <thead>
        <tr>
          <th>CD Code</th>
          <th>Name</th>
          <th>CID/DISN</th>
        </tr>
      </thead>
      <tbody>';
      $stmt = $dbh->prepare("SELECT a.cd_code, a.ID, a.acc_type, a.f_name, a.l_name, a.phone, a.email 
          FROM client_account a 
          WHERE a.ID = ?
          GROUP BY a.cd_code ORDER BY a.client_id DESC -- LIMIT 1
      ");
      $stmt->bindParam(1, $cid_no);
      $stmt->execute();
      foreach ($stmt as $key) {
        $name = ($key['acc_type'] == 'I') ? $key['f_name'].' '.$key['l_name'] : $key['f_name'];
        echo'
        <tr>
          <td>'.$key['cd_code'].'</td>
          <td>'.$name.'</td>
          <td>'.$key['ID'].'</td>
        </tr>';
      }
      echo'
      </tbody>
    </table>
  </div>';
  die();
}
else {
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
  exit();
}

function cash_total_rights($cd_code, $username) {
  include ('../../CONNECTIONS/db.php');
  $wc = $dbh->prepare("SELECT a.cd_code, sum(a.amount) as tot, b.cd_code, b.ID
      FROM rights_finance a 
      LEFT JOIN client_account b ON a.cd_code=b.cd_code 
      WHERE a.cd_code = :ac 
      -- and a.user_name = :un
  ");
  $wc->bindParam(':ac',$cd_code);
  // $wc->bindParam(':un',$username);
  $wc->execute();
  $value = $wc->fetch();
  $tot = $value['tot'];
  return $tot;

}
?>