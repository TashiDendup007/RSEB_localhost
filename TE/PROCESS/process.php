<?php
include ('../FILES/sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/trading_hours.php');
include ('../../CONNECTIONS/function-sanitize.php');
date_default_timezone_set("Asia/Thimphu");
include('../../Functions/f.php');
// $username = $_SESSION['sess_username'];

$list = ins_id($username);
$institution_id = $list[0];
$p_code = $list[1];
$cdcode = find_link_user_cd_code($username);
$broker_user_name = broker_user_name($username);

  //Saving Record
  if (isset($_POST['side_for_order'])) {
    if(empty($_POST['vol']) || $_POST['vol'] == 0 || $_POST['vol'] == '' || $_POST['price'] == 0 || $_POST['price'] == '') {
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Please Fill All Mandatory Fields.</div></div>';
      die();
    }
    //variable declaration
    $cdcode = clean($_POST['cdcode']);
    $cdcode = strtoupper($cdcode);
    $p_codeo = clean($_POST['p_codeo']);
    $p_code = clean($_POST['p_code']);
    $u_name = clean($_POST['u_name']);
    $vol = clean(intval($_POST['vol']));
    $avl_vol = clean(intval($_POST['avl_vol']));
    $pov = clean(intval($_POST['pov']));
    $piv = clean(intval($_POST['piv']));
    $sy_id = clean($_POST['sy_id']);
    // $price = clean(intval($_POST['price']));
    $price = clean($_POST['price']);

    $side = clean($_POST['side_for_order']);
    $b_commis = clean(floatval($_POST['b_commis']));

    $n_pov = $pov + $vol;
    $n_piv = $piv + $vol;
    $new_vol_cds = $avl_vol - $vol;

    //$price = substr($price,0,5);
    $price = number_format((float)$price, 2, '.', '');
    $commis_amt = round($vol * $price * $b_commis * 0.01, 2);
    $gst_amt = round($commis_amt * 0.05, 2);
    // $amt = ($vol * $price) + $commis_amt + $gst_amt;

    // checks whethere GST registered or not
    $stmt = $dbh->prepare("SELECT p.gst_register
            FROM client_account a 
            LEFT JOIN adm_institution p ON a.institution_id = p.institution_id
            WHERE a.cd_code = ?
    ");
    $stmt->execute([$cdcode]);
    $gst_register = $stmt->fetchColumn();

    // for seller minus commission and gst (if any) and vice versa for buyer
    $sign = ($side === 'S') ? -1 : 1;
    $gstAmt = ($gst_register === 'Y') ? $gst_amt : 0;

    $amt = round(($vol * $price) + $sign * ($commis_amt + $gstAmt), 2);

    $find_existing_order = check_orders($cdcode, $sy_id, $side, $p_codeo);
    $flag_id = date("ymdhis");
    $financestatus = 0;

    $check = date("H:i:s");
    foreach ($trading_hours as $hour) {
      if ($check > $hour['start'] && $check < $hour['end']) {
        die('<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Market Closed.</div></div>');
      }
    }

    // to check price 
    $cap_name = 'CAP';
    $market_price = market_price($sy_id); 
    $cap = circuit($cap_name);
    $cap_value = cap_compute($market_price,$cap);
    $ceiling_price = $market_price + $cap_value;
    $floor_price = $market_price - $cap_value;

    if ($price > $ceiling_price || $price < $floor_price) {
      die('<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Price should be between <b>'.number_format($floor_price, 2).'</b> and <b>'.number_format($ceiling_price, 2).'</b></div></div>');
    }
      
    $flag = ($side == 'B') ? 3 : 2;
    $t = ($side == 'B') ? 'Buy' : 'Sell';

    $remarks = $t.' Order entry by user '.$u_name.' of member '.$p_codeo.', of volume '.$vol.' @ Nu. '.$price.'/share';
    // check if order already exist
    if($find_existing_order === 1) {
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> '.$t.' Order for this client of same symbol already Placed.</div></div>'; 
      die();
    }
    elseif ($find_existing_order === 0) {
      if ($side == 'S') {
        // check volume available
        $stmt = $dbh->prepare("SELECT h.volume
            FROM cds_holding h 
            WHERE h.cd_code = ?
              AND h.symbol_id = ?
        ");
        $stmt->execute([$cdcode, $sy_id]);
        $avail__holding__vol = $stmt->fetchColumn();
        if ($vol > $avail__holding__vol) {
          die('
            <div class="col-lg-12 col-md-12">
              <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Insufficient Shares. You have only ' .$avail__holding__vol.' shares
              </div>
            </div>
          ');
        }
      
        try {
          $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $dbh->beginTransaction();

          //buy code when no same order
          $order_audit = order_auditTE($cdcode, $p_codeo, $u_name, $vol, $vol, $sy_id, $price, $side, $commis_amt, $flag_id, $p_code);

          $b_order = $dbh->prepare("INSERT INTO orders(cd_code, participant_code, member_broker, order_entry, sell_vol, order_size, symbol_id, price, side, commis_amt, flag_id) VALUES (:cdcode, :p_codeo, :p_code, :u_name, :vol, :vol, :sy_id, :price, :side, :commis_amt, :flag_id)");
          $b_order->bindParam(':cdcode', $cdcode);
          $b_order->bindParam(':p_codeo', $p_codeo);
          $b_order->bindParam(':p_code', $p_code);
          $b_order->bindParam(':u_name', $u_name);
          $b_order->bindParam(':vol', $vol);
          $b_order->bindParam(':sy_id', $sy_id);
          $b_order->bindParam(':price', $price);
          $b_order->bindParam(':side', $side);
          $b_order->bindParam(':commis_amt', $commis_amt);
          $b_order->bindParam(':flag_id', $flag_id);
          $b_order->execute();

          $b_fin = $dbh->prepare("INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id, status) VALUES(:cdcode, :amt, :u_name, :remarks, :flag, :institution_id, :flag_id, :financestatus)");
          $b_fin->bindParam(':cdcode', $cdcode);
          $b_fin->bindParam(':amt', $amt);
          $b_fin->bindParam(':u_name', $u_name);
          $b_fin->bindParam(':remarks', $remarks);
          $b_fin->bindParam(':flag', $flag);
          $b_fin->bindParam(':institution_id', $institution_id);
          $b_fin->bindParam(':flag_id', $flag_id);
          $b_fin->bindParam(':financestatus', $financestatus);
          $b_fin->execute();

          $cds_acc = $dbh->prepare("UPDATE cds_holding SET volume = :new_vol, pending_out_vol = :pov WHERE cd_code = :cdcode and symbol_id = :sy_id");
          $cds_acc->bindParam(':new_vol', $new_vol_cds);
          $cds_acc->bindParam(':pov', $n_pov);
          $cds_acc->bindParam(':cdcode', $cdcode);
          $cds_acc->bindParam(':sy_id', $sy_id);
          $cds_acc->execute();

          $dbh->commit();
          $dbh = null;

          echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> '.$t.' Order Placed Successfully.</div></div>';
          die();

        } catch(PDOException $e) {
          $dbh->rollBack();
          error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
          echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> An error occurred. Please contact RSEB support.</div></div>';
          die();
        }
      } elseif($side === 'B') {

        // check amount available
        $stmt = $dbh->prepare("SELECT SUM(m.amount) AS total_amount FROM bbo_finance m WHERE m.cd_code = ? AND m.status = 1");
        $stmt->execute([$cdcode]);
        $avail__holding__amount = $stmt->fetchColumn();
        if ($amt > $avail__holding__amount) {
          die('
            <div class="col-lg-12 col-md-12">
              <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Insufficient cash. You have only Nu. <b>' .number_format($avail__holding__amount, 2).'</b> available.
              </div>
            </div>
          ');
        }

        try {
          $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $dbh->beginTransaction();

          $order_audit = order_auditTE($cdcode, $p_codeo, $u_name, $vol, $vol, $sy_id, $price, $side, $commis_amt, $flag_id, $p_code);

          $b_order = $dbh->prepare("INSERT INTO orders (cd_code, participant_code, member_broker, order_entry, buy_vol, order_size, symbol_id, price, side, commis_amt, flag_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $b_order->execute([$cdcode, $p_codeo, $p_code, $u_name, $vol, $vol, $sy_id, $price, $side, $commis_amt, $flag_id]);

          $b_fin = $dbh->prepare("INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
          $b_fin->execute([$cdcode, "-$amt", $u_name, $remarks, $flag, $institution_id, $flag_id]);

          $dbh->commit();
          $dbh = null;

          echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> '.$t.' Order Placed Successfully.</div></div>';
        } catch(PDOException $e) {
          $dbh->rollBack();
          error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
          echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> An error occurred. Please contact RSEB support.</div></div>';
        }
        exit();
      }
    }
  }
  elseif(!empty($_POST["cancle_id"])) {
    $id = $_POST["cancle_id"]; // order id
    $fid = $_POST["fid"];
    $v = intval($_POST["v"]);
    $side = $_POST["side"];
    $cd_code = $_POST["cd_code"];
    $sy_id = $_POST["sy_id"];


    header('Content-Type: application/json');
    $data = [];
    // Input validation
    if (!ctype_alnum($id) || !ctype_alnum($fid) || !ctype_alnum($cd_code) || !ctype_alnum($sy_id)) {
      $data = [
          "status" => 2,
          "message" => '
                  <div class="col-lg-12 col-md-12">
                    <div class="alert alert-warning alert-dismissible">
                      <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Invalid Data.
                    </div>
                  </div>'
      ];
      echo json_encode($data);
      die();
    }
    
    // Check if market is closed
    $check = date("H:i:s");
    foreach ($trading_hours as $hour) {
      if ($check > $hour['start'] && $check < $hour['end']) {
        $data = [
            "status" => 2,
            "message" => '
                    <div class="col-lg-12 col-md-12">
                      <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Market Closed. Please try again later.
                      </div>
                    </div>'
        ];
        echo json_encode($data);
        die();
      }
    }
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      if($side == 'S') {
        // get existing order from db
        $stmt = $dbh->prepare("SELECT order_size FROM orders WHERE order_id = ?");
        $stmt->execute([$id]);
        $v = $stmt->fetchColumn();
      
        // To check negative value
        $get_val = $dbh->prepare("SELECT pending_out_vol, volume FROM cds_holding WHERE symbol_id = ? and cd_code = ?");
        $get_val->bindParam(1, $sy_id);
        $get_val->bindParam(2, $cd_code);
        $get_val->execute();
        $row = $get_val->fetch();

        $old_pov = $row['pending_out_vol'];
        $old_volume = $row['volume'];

        $upd_pending_out_vol = $old_pov - $v;
        $upd_volume = $old_volume + $v;

        if ($upd_pending_out_vol < 0) {
          $data = [
              "status" => 2,
              "message" => '
                      <div class="col-lg-12 col-md-12">
                        <div class="alert alert-warning alert-dismissible">
                          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Negative Error. Plase contact RSEB support.
                        </div>
                      </div>'
          ];
          echo json_encode($data);
          die();
        }

        $cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = ?, volume = ? WHERE cd_code = ? AND symbol_id = ?");
        $cds_acc->bindParam(1, $upd_pending_out_vol);
        $cds_acc->bindParam(2, $upd_volume);
        $cds_acc->bindParam(3, $cd_code);
        $cds_acc->bindParam(4, $sy_id);
        $cds_acc->execute();

        // commented due to negative issue
        /*$cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = pending_out_vol-:v, volume = volume + :v WHERE cd_code = :cdcode AND symbol_id = :sy_id");
        $cds_acc->bindParam(':v', $v);
        $cds_acc->bindParam(':cdcode', $cd_code);
        $cds_acc->bindParam(':sy_id', $sy_id);
        $cds_acc->execute();*/
      }
      /*elseif($side == 'B') {
        // no need to update cds_holding for buyers
      }*/

      $order_date1 = $dbh->prepare("SELECT max(order_date) AS od FROM orders_audit WHERE flag_id = :fid");
      $order_date1->bindParam(':fid',$fid);
      $order_date1->execute();
      $of = $order_date1->fetch();
      $o_date =  $of['od'];

      $order_cancle_status = $dbh->prepare("UPDATE orders_audit SET flag = 'C', username = :un WHERE flag_id = :fid AND order_date = :od");
      $order_cancle_status->bindParam(':un', $username);
      $order_cancle_status->bindParam(':fid', $fid);
      $order_cancle_status->bindParam(':od', $o_date);
      $order_cancle_status->execute();

      $order_cancle = $dbh->prepare("DELETE FROM orders WHERE order_id = :id");
      $order_cancle->bindParam(':id', $id);
      $order_cancle->execute();

      $bbo_fin_del=$dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = :fid");
      $bbo_fin_del->bindParam(':fid', $fid);
      $bbo_fin_del->execute();

      $dbh->commit();
      $dbh = null;

      $data = [
          "status" => 1,
          "message" => '
                  <div class="col-lg-12 col-md-12">
                    <div class="alert alert-success alert-dismissible">
                      <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Deleted Order Successfully. 
                    </div>
                  </div>'
      ];
    } catch(PDOException $e) {
      $dbh->rollBack();
      error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
      $data = [
          "status" => 2,
          "message" => '
                  <div class="col-lg-12 col-md-12">
                    <div class="alert alert-danger alert-dismissible">
                      <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> An error occurred. Please contact RSEB support. 
                    </div>
                  </div>'
      ];
    }
    echo json_encode($data);
    die();

  }
  elseif(!empty($_POST["change_id"])) {
      $id = clean($_POST["change_id"]); // order id
      $fid = clean($_POST["fid"]);
      $ex_vol = clean(intval($_POST["v"])); // previous order vol
      $e_v = clean(intval($_POST["e_v"]));  // enly enter order vol
      $e_p = clean(floatval($_POST["e_p"]));
      /*$e_p = substr($e_p,0,5);*/
      $e_p = round($e_p, 2);
      $side = clean($_POST["side"]);
      $cd_code = clean($_POST["cd_code"]);
      $sy_id = clean($_POST["sy_id"]);

      $cap_name = 'CAP';
      $market_price = market_price($sy_id);
      $cap = circuit($cap_name);
      $cap_value = cap_compute($market_price, $cap);

      /*$up = $market_price+$cap_value;
      $dw = $market_price-$cap_value;*/
      $up = round($market_price + $cap_value, 2);
      $dw = round($market_price - $cap_value, 2);

      $broker_user_name =  broker_user_name($username);
      $b_commis = client_commission_te($cd_code, $broker_user_name);
      $finance_type  = 'terminal';
      $tot = cash_total_client($cd_code, $finance_type, $broker_user_name);

      $data = [];
      header('Content-Type: application/json');
      if($e_v == 0 || $e_v == '') {
        $data = [
          "status" => "failure",
          "message" => '<div class="alert alert-warning alert-dismissible"> Invalid Data </div>'
        ];
        echo json_encode($data);
        die();
      }

      // Check if market is closed
      $check = date("H:i:s");
      foreach ($trading_hours as $hour) {
        if ($check > $hour['start'] && $check < $hour['end']) {
          $data = [
            "status" => "failure",
            "message" => '<div class="alert alert-warning alert-dismissible"> Market Closed. Please try again later. </div>'
          ];
          echo json_encode($data);
          die();
        }
      }

      // check price range
      if ($e_p > $up || $e_p < $dw) {
        $data = [
            "status" => "failure",
            "message" => '<div class="alert alert-warning alert-dismissible"> Price Should be between <b>'.$dw.'</b> & <b>'.$up.'</b></div>'
        ];
        echo json_encode($data);
        die();
      } else {
          // Check if order exists
          $order_size_count = $dbh->prepare("SELECT COUNT(*) FROM orders WHERE order_id = :ord_id");
          $order_size_count->execute([':ord_id' => $id]);
          $check_order_exists_or_not = $order_size_count->fetchColumn();

          if (!$check_order_exists_or_not) {
              echo json_encode([
                  "status" => "failure",
                  "message" => '<div class="alert alert-warning alert-dismissible">No order to be found.</b></div>'
              ]);
              exit;
          }

          // get previous order vol from db
          // replace $ex_vol which is pulling from the hidden
          $stmt = $dbh->prepare("SELECT order_size FROM orders WHERE order_id = ?");
          $stmt->execute([$id]);
          $ex_vol = $stmt->fetchColumn();

          try {
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $dbh->beginTransaction();

              // checks whethere GST registered or not
              $stmt = $dbh->prepare("SELECT p.gst_register
                      FROM client_account a 
                      LEFT JOIN adm_institution p ON a.institution_id = p.institution_id
                      WHERE a.cd_code = ?
              ");
              $stmt->execute([$cd_code]);
              $gst_register = $stmt->fetchColumn();

              if ($side == 'S') {
                $list = pending_vol($cd_code, $sy_id);
                $pov = $list[0];
                $piv = $list[1];
                $vol = $list[2];

                $avl_vol_change = $vol + $ex_vol;

                if ($avl_vol_change >= $e_v) {
                  $new_vol = $avl_vol_change - $e_v;
                  $new_pov = ($pov - $ex_vol) + $e_v;

                  $new_commis_amt = $e_v * $e_p * $b_commis * 0.01;
                  $gst_amt = $new_commis_amt * 0.05;
                  // $new_amt = ($e_v * $e_p) + $new_commis_amt;

                  $gstAmt = ($gst_register === 'Y') ? $gst_amt : 0;
                  $new_amt = round(($e_v * $e_p) - ($new_commis_amt + $gstAmt), 2);

                  $cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = :new_pov, volume = :new_vol WHERE cd_code = :cdcode AND symbol_id = :sy_id");
                  $cds_acc->execute([':new_pov' => $new_pov, ':new_vol' => $new_vol, ':cdcode' => $cd_code, ':sy_id' => $sy_id]);
                } else {
                  $data = [
                      "status" => "failure",
                      "message" => '<div class="alert alert-warning alert-dismissible"> Insufficient shares available. </div>'
                  ];
                  echo json_encode($data);
                  die();
                }
              }
              elseif ($side == 'B') {
                  $e_amt = prev_amt_ord($fid);
                  $new_commis_amt = $e_v * $e_p * $b_commis * 0.01;
                  $gst_amt = $new_commis_amt * 0.05;
                  // $new_amt = ($e_v * $e_p) + $new_commis_amt; 

                  $gstAmt = ($gst_register === 'Y') ? $gst_amt : 0;
                  $new_amt = round(($e_v * $e_p) + $new_commis_amt + $gstAmt, 2);

                  $avl_amt = $tot + $e_amt;

                  $ex_comission = $ex_vol * $e_p * $b_commis * 0.01;
                  $ex_gst = $ex_comission * 0.05;
                  // $ex_amount = ($ex_vol * $e_p) + $ex_comission;

                  $ex_gstAmt = ($gst_register === 'Y') ? $ex_gst : 0;
                  $ex_amount = round(($e_v * $e_p) + $ex_comission + $ex_gstAmt, 2);

                  $ex_total_amount = $ex_amount + $tot;

                  if ($ex_total_amount >= $new_amt) {
                    $new_amt = $new_amt * -1;
                  } else {
                    $data = [
                        "status" => "failure",
                        "message" => '<div class="alert alert-warning alert-dismissible"> Insufficient cash available.</div>'
                    ];
                    echo json_encode($data);
                    die();
                  }
              }

              $order_side = ($side == 'S') ? 'SELL' : 'BUY';
              $part_code = substr($username, 0, 7);
              $remarks = $order_side.' Order entry by user '.$username.' of member '.$part_code.', of volume '.$e_v.' @ Nu. '.$e_p.'/share';

              // update bbo_finance table
              $bbo_fin_up = $dbh->prepare("UPDATE bbo_finance SET amount = :new_amt, remarks = :remarkks WHERE flag_id = :fid");
              $bbo_fin_up->execute([':new_amt' => $new_amt, ':remarkks' => $remarks, ':fid' => $fid]);

              // get flag id
              $check = $dbh->prepare("SELECT flag_id FROM orders WHERE order_id = ?");
              $check->execute([$id]);
              $flag_id = $check->fetchColumn();

              // update order audit table
              $order_audit = order_auditTE($cd_code, $p_code, $username, $e_v, $e_v, $sy_id, $e_p, $side, $new_commis_amt, $flag_id, $broker_user_name);

              // update order table
              $ord_up = $dbh->prepare("UPDATE orders 
                          SET 
                              buy_vol = CASE WHEN :side = 'B' THEN :vol ELSE buy_vol END,
                              sell_vol = CASE WHEN :side = 'S' THEN :vol ELSE sell_vol END,
                              order_size = :vol,
                              price = :price,
                              commis_amt = :commis_amt
                          WHERE order_id = :id
              ");
              $ord_up->execute([':side' => $side, ':vol' => $e_v, ':price' => $e_p, ':commis_amt' => $new_commis_amt, ':id' => $id]);

              $dbh->commit();
              $dbh = null;

              $data = [
                  "status" => "success",
                  "message" => '<div class="alert alert-success alert-dismissible"> Successfully Updated Order.</div>'
              ];
              echo json_encode($data);
              die();
          } catch (Exception $e) {
              $dbh->rollBack();
              error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
              $data = [
                  "status" => "failure",
                  "message" => '<div class="alert alert-danger alert-dismissible"> An error occurred. Please contact RSEB support.</div>'
              ];
              echo json_encode($data);
              die();
          }
      }
  }
  else {
    header('location: ../FILES/te-landing.php?ms=2');
    die();
  }
  ?>
