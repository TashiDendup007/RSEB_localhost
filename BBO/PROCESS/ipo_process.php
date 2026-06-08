<?php 
  include ('../../CONNECTIONS/db.php');
  include ('../../CONNECTIONS/function-sanitize.php');
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="2") {
    header('Location: ../../access.php?err=2');
    die();
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); 
      die();
    }
  } 
  include('../../Functions/f.php'); 
  $_SESSION['timeout'] = time();
  $username = $_SESSION['sess_username'];

  $check= $dbh->prepare('SELECT a.institution_id from adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id = a.institution_id and c.username = :un');
  $check->bindParam(':un', $username);
  $check->execute();
  $res=$check->fetch();
  $institution_id=$res['institution_id'];
  
  //Saving Record
  if (isset($_POST['save_ipo'])) {
    $cid = $_POST['cid'];
    $symbol_id = $_POST['symbol_id'];
    $bidVol = $_POST['volume'];
    $faceValue = $_POST['face_value'];
    $status = 0;
    
    $cdcode = $_POST['cdcode'];
    $order = $bidVol;
    $bidPrice=$_POST['bidprice'];
    $totalAmount = $bidPrice * $order;
    $type = 'IPO';
    $message = '';

    $check= $dbh->prepare('SELECT COUNT(*) FROM ipo WHERE cd_code = :cd');
    $check->bindParam(':cd', $cdcode);
    $check->execute();
    $rowCount = $check->fetchColumn();
    if($rowCount <= 0) {
      try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $save = $dbh->prepare("INSERT INTO ipo(type, cd_code, order_size, symbol_id, bid_price, buy_vol, face_value, total_amount, user_name, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $save->bindParam(1, $type);
        $save->bindParam(2, $cdcode);
        $save->bindParam(3, $order);
        $save->bindParam(4, $symbol_id);
        $save->bindParam(5, $bidPrice);
        $save->bindParam(6, $order);
        $save->bindParam(7, $faceValue);
        $save->bindParam(8, $totalAmount);
        $save->bindParam(9, $username);
        $save->bindParam(10, $status);
        $save->execute();

        $dbh->commit();

        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
        
      } catch(PDOException $e) {
        $dbh->rollBack();
        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
      }
    } else {
      $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! You have already subscribed in this symbol.</div></div></div>';
    }
    $dbh = null;
    echo $message;
    exit();
  }
  elseif (isset($_POST['deb'])) { 
    $cd_code = $_POST['cdcode'];
    $amt = $_POST['amt'];
    $rm = $_POST['rm'];
    $flag_debit = 0;
    $status = 0;
    $message = '';

    $check = $dbh->prepare("SELECT sum(amount) as amount FROM ipo_finance WHERE cd_code = :cd");
    $check->bindParam(':cd',$cd_code);
    $check->execute();
    $res = $check->fetch();
    if($res['amount'] >= $amt) {
      try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $amount = -$amt;

        $save = $dbh->prepare("INSERT INTO ipo_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
        $save->bindParam(1, $cd_code);
        $save->bindParam(2, $amount);
        $save->bindParam(3, $rm);
        $save->bindParam(4, $flag_debit);
        $save->bindParam(5, $flag_debit);
        $save->bindParam(6, $username);
        $save->bindParam(7, $institution_id);
        $save->bindParam(8, $status);
        
        $save->execute();

        $dbh->commit();
        
        $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';

      } catch(PDOException $e) {
        $dbh->rollBack();
        $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
      }
    } else {
      $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Oops Sorry! Your Balance is '.$res['amount'].' , insufficient fund.</div></div></div>';
    } 
    $dbh = null;
    echo $message;
    exit();
  }
  elseif (isset($_POST['cre'])) { 
    $cd_code = $_POST['cdcode'];
    $amt = $_POST['amt'];
    $rm = $_POST['rm'];
    $flag_debit = 1;
    $status = 0;
    $message = '';
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $save = $dbh->prepare("INSERT into ipo_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
      $save->bindParam(1, $cd_code);
      $save->bindParam(2, $amt);
      $save->bindParam(3, $rm);
      $save->bindParam(4, $flag_debit);
      $save->bindParam(5, $flag_debit);
      $save->bindParam(6, $username);
      $save->bindParam(7, $institution_id);
      $save->bindParam(8, $status);
      $save->execute();

      $dbh->commit();

      $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
      
    } catch(PDOException $e) {
      $dbh->rollBack();
      $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }

    $dbh = null;
    echo $message;
    exit();
   
  }
  elseif(!empty($_POST["change_id"])) {
    $id = $_POST["change_id"];
    $ex_vol = $_POST["v"];
    $e_v = $_POST["e_v"];
    $e_p = $_POST["e_p"];
    $side = $_POST["side"];
    $cd_code = $_POST["cd_code"];
    $sy_id = $_POST["sy_id"];

    $tot = cash_total_ipo($cd_code, $username);

    $new_amt = $e_v * $e_p;
    $message = '';
    if($tot >= $new_amt){
      try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $ord_up=$dbh->prepare("UPDATE ipo 
          SET order_size = :new_buy_vol, buy_vol = :new_buy_vol, bid_price = :new_price, total_amount = :new_amt 
          WHERE order_id = :id
        ");
        $ord_up->bindParam(':id', $id);
        $ord_up->bindParam(':new_price', $e_p);
        $ord_up->bindParam(':new_buy_vol', $e_v);
        $ord_up->bindParam(':new_amt', $new_amt);
        $ord_up->execute();

        $dbh->commit();
        $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
            </button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Buy Order Updated.</div></div>';
        
      } catch(PDOException $e) {
        $dbh->rollBack();
        $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
          </button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! Order not updted.</div></div>';
      }
    } else {
      $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
      </button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! Not enough CASH.</div></div>';
    }
    $dbh = null;
    echo $message;
    exit();
  }
  elseif (isset($_POST['search_cd_code_for_ipo'])) {
    $cid_no = $_POST['search_cd_code_for_ipo'];
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
            GROUP BY a.cd_code ORDER BY a.client_id DESC LIMIT 1
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
  else
  {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    die();
  }
?>