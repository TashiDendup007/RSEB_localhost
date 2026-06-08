<?php 
date_default_timezone_set('Asia/Thimphu');
include ('../FILES/session_start_file.php');
include('../../Functions/f.php'); 
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');

$check = $dbh->prepare("SELECT a.institution_id FROM adm_institution a, adm_participants b, users c WHERE c.participant_code = b.participant_code AND b.institution_id = a.institution_id AND c.username = :un");
$check->execute([':un' => $username]);
$res = $check->fetch();
$institution_id = $res['institution_id'];

//Saving Record
if (isset($_POST['save_bond'])) {
  $cid = $_POST['cid'];
  $symbol_id = $_POST['symbol_id'];
  $bidVol = $_POST['volume'];
  $faceValue = $_POST['face_value'];
  $status = 0;

  // error_log("save ==> ".$cid);
  
  $cdcode = $_POST['cdcode'];
  $order = $bidVol;
  $bidPrice=$_POST['bidprice'];
  $totalAmount = $bidPrice*$order;
  $type = 'BOND';

  if(isset($_POST['bondMechanism']) && $_POST['bondMechanism'] == 'YR') {
    $totalAmount = $bidVol;
    $order = $bidVol / 1000;
  }

  if(isset($_POST['bondMechanism']) && $_POST['bondMechanism'] == 'YRO') {
    $totalAmount = $bidVol;
    $order = $bidVol / 1000;
    
    $save = $dbh->prepare('UPDATE bond_application_temp SET Approved="Y" WHERE cd_code=:cd');
    $save->bindParam(':cd',$cdcode);
    $save->execute();
  }

  // $check= $dbh->prepare("SELECT * FROM bond WHERE cd_code=:cd AND symbol_id=:sy");
  $check= $dbh->prepare("SELECT * FROM bond WHERE cid_no = :cd AND symbol_id = :sy");
  $check->bindParam(':cd', $cid);
  $check->bindParam(':sy', $symbol_id);
  $check->execute();
  if($check->rowCount() <= 0) {
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $save = $dbh->prepare("INSERT INTO bond(type, cd_code, order_size, symbol_id, bid_price, buy_vol, face_value, total_amount, user_name, status, cid_no) 
          VALUES(:type, :cdcode, :order, :symbol_id, :bidPrice, :order, :faceValue, :totalAmount, :username, :status, :cid_no)");
      $save->bindParam(":type", $type);
      $save->bindParam(":cdcode", $cdcode);
      $save->bindParam(":order", $order);
      $save->bindParam(":symbol_id", $symbol_id);
      $save->bindParam(":bidPrice", $bidPrice);
      $save->bindParam(":faceValue", $faceValue);
      $save->bindParam(":totalAmount", $totalAmount);
      $save->bindParam(":username", $username);
      $save->bindParam(":status", $status);
      $save->bindParam(":cid_no", $cid);
      $save->execute();

      $order_id = $dbh->lastInsertId();

      $remarks = 'Purchase of ' . $order . ' units of ' . $symbol_id . ' Bond.';
      $insert = $dbh->prepare("INSERT INTO bond_finance(remarks, cd_code, amount, flag, flag_id, user_name, institution_id, status) VALUES(?, ?, ?, 1, ?, ?, ?, 0)");
      $insert->execute([$remarks, $cdcode, -$totalAmount, $order_id, $username, $institution_id]);

      $dbh->commit();
      $dbh = null;

      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i>  Operation Successfully Completed.</div></div></div>';
    } catch(PDOException $e) {
      $dbh->rollBack();
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i>Sorry! There was an error => '.$e->getMessage().'</div></div></div>';
    }
  } else {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i>You have already subscribed.</div></div></div>';
  }
  die();
}
elseif (isset($_POST['deb'])) {
  $cd_code = $_POST['cdcode'];
  $amt = $_POST['amt'];
  $rm = $_POST['rm'];
  $flag_debit = 0;
  $status = 0;

  $check= $dbh->prepare("SELECT sum(amount) as amount FROM bond_finance WHERE cd_code=:cd");
  $check->bindParam(':cd',$cd_code);
  $check->execute();
  $res = $check->fetch();

  if($res['amount'] >= $amt) {
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $amount = -$amt;

      $save = $dbh->prepare("INSERT into bond_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id, status) 
        VALUES(:cdCode, :amount, :remarks, :flag_debit, :flag_debit, :usr, :instId, :stas)");
      $save->bindParam(':cdCode', $cd_code);
      $save->bindParam(':amount', $amount);
      $save->bindParam(':remarks', $rm);
      $save->bindParam(':flag_debit', $flag_debit);
      $save->bindParam(':usr', $username);
      $save->bindParam(':instId', $institution_id);
      $save->bindParam(':stas', $status);
      $save->execute();

      $dbh->commit();
      $dbh = null;

      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
    } catch(PDOException $e) {
      $dbh->rollBack();
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> There was an error => '.$e->getMessage().'</div></div></div>';
    }
  } else {
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Your Balance is '.$res['amount'].' , insufficient fund.</div></div></div>';
  }
  die();
}
elseif (isset($_POST['cre'])) { 
  $cd_code = $_POST['cdcode'];
  $amt = $_POST['amt'];
  $rm = $_POST['rm'];
  $flag_debit = 1;
  $status = 0;

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("INSERT INTO bond_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id, status) 
        VALUES(:cdCode, :amount, :remarks, :flag_debit, :flag_debit, :usr, :instId, :stas)");
    $save->bindParam(':cdCode', $cd_code);
    $save->bindParam(':amount', $amt);
    $save->bindParam(':remarks', $rm);
    $save->bindParam(':flag_debit', $flag_debit);
    $save->bindParam(':usr', $username);
    $save->bindParam(':instId', $institution_id);
    $save->bindParam(':stas', $status);
    $save->execute();

    $dbh->commit();
    $dbh = null;

    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> There was an error while operation.</div></div></div>';
  }
  die();
} 
elseif(!empty($_POST["change_id"])) {
  $id = $_POST["change_id"];
  $ex_vol = $_POST["v"];
  $e_v = $_POST["e_v"];
  $e_p = $_POST["e_p"];
  $side = $_POST["side"];
  $cd_code = $_POST["cd_code"];
  $sy_id = $_POST["sy_id"];

  // error_log("update ==> ".$cd_code);

  if ($e_v < 10) {
      echo'<div class="alert alert-danger alert-dismissible">Volume should not be less than 10</div>';
      exit;
  }

  if ($e_v % 10 != 0) {
      echo'<div class="alert alert-danger alert-dismissible">Volume must be a multiple of 10</div>';
      exit;
  }

  // get actual exiting order
  $stmt = $dbh->prepare("SELECT order_size FROM bond WHERE order_id = ?");
  $stmt->execute([$id]);
  $ex_vol = $stmt->fetchColumn();

  $old_amount = $ex_vol * $e_p;

  // get amount from bbo finance
  $tot = cash_total_bond($cd_code, $username);
  $new_amt = (int)$e_v * (int)$e_p;

  $diff_amt =  $new_amt - $old_amount;

  // if ($tot >= $new_amt) {
  if ($tot >= $diff_amt) {
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $ord_up=$dbh->prepare("UPDATE bond SET order_size = :new_buy_vol, buy_vol = :new_buy_vol, bid_price=:new_price, total_amount=:new_amt WHERE order_id=:id");
      $ord_up->bindParam(':new_buy_vol', $e_v);
      $ord_up->bindParam(':new_price', $e_p);
      $ord_up->bindParam(':new_amt', $new_amt);
      $ord_up->bindParam(':id', $id);
      $ord_up->execute();

      $remarks = 'Update of ' . $e_v . ' units of ' . $sy_id . ' Bond.';
      $insert = $dbh->prepare("INSERT INTO bond_finance(remarks, cd_code, amount, flag, flag_id, user_name, institution_id, status) VALUES(?, ?, ?, 1, ?, ?, ?, 0)");
      $insert->execute([$remarks, $cd_code, -$diff_amt, $id, $username, $institution_id]);

      $dbh->commit();
      $dbh = null;

      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
      </button><i class="icon fa fa-check"> </i> Buy Order Updated.</div></div>';

    } catch(PDOException $e) {
      $dbh->rollBack();
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
      </button><i class="icon fa fa-times"> </i> Order not updted.</div></div>';
    }
  } else {
    echo'
    <div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
      </button><i class="icon fa fa-times"> </i> Not enough CASH.</div></div>';
  }
  die();
} 
else if (isset($_POST['cancle_id'])) {
  $order_id = $_POST['cancle_id'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("DELETE FROM bond WHERE order_id=:orid");
    $save->bindParam(':orid', $order_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;

    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Order deleted successfully.'
    );
  } catch(PDOException $e) {
    $dbh->rollBack();
    $data = array(
        'status' => 400, 'success' => false, 'message' => $e->getMessage()
    );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();  
}
elseif (isset($_POST['search_cd_code_for_ipo'])) {
  $cid_no = $_POST['search_cd_code_for_ipo'];
  $part_code = $_SESSION['sess_part_code'];
  $mem_code = substr($part_code, 0, 7);

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
          -- AND SUBSTRING(a.user_name, 1, 7) = ?
          GROUP BY a.cd_code 
          ORDER BY a.client_id DESC 
          -- LIMIT 1
      ");
      $stmt->bindParam(1, $cid_no);
      // $stmt->bindParam(2, $mem_code);
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
else
{
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
  die();
}

function cash_total_bond($cd_code, $username) {
  include ('../../CONNECTIONS/db.php');
  $wc = $dbh->prepare("SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID
      FROM bond_finance a 
      JOIN client_account b ON a.cd_code = b.cd_code
      WHERE a.cd_code=:ac and a.user_name=:un");
  $wc->bindParam(':ac',$cd_code);
  $wc->bindParam(':un',$username);
  $wc ->execute();
  $value = $wc->fetch();
  $tot = $value['tot'];
  return $tot;
}
?>