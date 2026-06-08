<?php  
elseif (isset($_POST['execute_offer_rfq'])) {
	// This block can be implemented similarly to the order execution logic, ensuring to handle all necessary checks and updates for both buyer and seller.
	$seller_cdcode = $_POST['seller_cdcode'];
	$buyer_cdcode = $_POST['buyer_cdcode'];
	$symbol_id = $_POST['symbol_id'];
	$flag_id = $_POST['flag_id'];
	$sell_vol = $_POST['sell_vol'];
	
	header('Content-Type: application/json');
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();
		
		if ($sell_vol % 10 != 0) {
			throw new Exception('Order should be multiple of 10');
		}

		if (empty($buyer_cdcode) || empty($seller_cdcode)) {
			throw new Exception('Both the buyer and seller CD code are required.');
		}

		// checks orders of buyer
		$stmt = $dbh->prepare("
			SELECT e.id, e.flag_id, e.buy_vol, e.order_size, e.price, e.dirty_price, e.acc_intrt, e.ytm, e.order_type, e.order_entry, e.member_broker, e.order_date, e.participant_code, e.side 
			FROM bond_orders e 
			WHERE e.cd_code = ? AND e.symbol_id = ? AND e.side = 'B'
		");
		$stmt->execute([$buyer_cdcode, $symbol_id]);
		$buy_res = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$buy_res) {
			throw new Exception('No BUY order found for execution.');
		}

		// check orders of seller
		$stmt = $dbh->prepare("
			SELECT e.id, e.flag_id, e.buy_vol, e.order_size, e.price, e.dirty_price, e.acc_intrt, e.ytm, e.order_type, e.order_entry, e.member_broker, e.order_date, e.participant_code, e.side  
			FROM bond_orders e 
			WHERE e.cd_code = ? AND e.symbol_id = ? AND e.flag_id = ? AND e.side = 'S'
		");
		$stmt->execute([$seller_cdcode, $symbol_id, $flag_id]);
		$sell_res = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$sell_res) {
			throw new Exception('No SELL order found for execution.');
		}

		// keep value ready for execution logic
		$buy_ord_id = $buy_res['id'];
		$buy_flag_id = $buy_res['flag_id'];
		$buy_price = $buy_res['price'];
		$buy_order_size = $buy_res['order_size'];
		$buy_order_entry = $buy_res['order_entry'];
		$buy_member_broker = $buy_res['member_broker'];
		$buy_order_date = $buy_res['order_date'];
		$buy_part_code = $buy_res['participant_code'];
		$buy_side = $buy_res['side'];
		$buy_balance = 0;
		
		$sell_ord_id = $sell_res['id'];
		$sell_flag_id = $sell_res['flag_id'];
		$sell_price = $sell_res['price'];
		$sell_order_size = $sell_res['order_size'];
		$sell_order_entry = $sell_res['order_entry'];
		$sell_member_broker = $sell_res['member_broker'];
		$sell_order_date = $sell_res['order_date'];
		$sell_part_code = $sell_res['participant_code'];
		$sell_side = $sell_res['side'];

		$sell_acc_intrt = $sell_res['acc_intrt'];
		$sell_ytm = $sell_res['ytm'];
		$sell_dirty_price = $sell_res['dirty_price'];
		$sell_balance = 0;

		// Implement execution logic here:
		$exec_vol = 0;
		$exec_price = 0;
		$exec_dirty_price = 0;
		$exec_ytm = 0;
		$exec_acc_int = 0;

		$buy_ord_status = '';
		$sell_ord_status = '';

		// execution conditions
		if ($buy_order_size == $sell_order_size) {
			$exec_vol = $sell_order_size;
			$buy_balance = 0;
			$sell_balance = 0;
			$buy_ord_status = 'EXECUTED';
			$sell_ord_status = 'EXECUTED';
		}
		elseif ($buy_order_size > $sell_order_size) {
			$exec_vol = $sell_order_size;
			$buy_balance = $buy_order_size - $sell_order_size;
			$buy_ord_status = 'PENDING';
			$sell_ord_status = 'EXECUTED';
		}
		elseif ($buy_order_size < $sell_order_size) {
			$exec_vol = $buy_order_size;
			$sell_balance = $sell_order_size - $buy_order_size;
			$buy_ord_status = 'EXECUTED';
			$sell_ord_status = 'PENDING';
		}

		$exec_price = $sell_price;
		$exec_dirty_price = $sell_dirty_price;
		$exec_ytm = $sell_ytm;
		$exec_acc_int = $sell_acc_intrt;

		// updates bond orders
		$upd_buy_ord = $dbh->prepare("UPDATE bond_orders SET order_size = ?, buy_vol = ?, status = ?, exe_vol = ?, exe_price = ?, lot_check = ? WHERE symbol_id = ? AND cd_code = ? AND flag_id = ?");
		$result = $upd_buy_ord([$buy_balance, $buy_balance, $buy_ord_status, $symbol_id, $exec_vol, $exec_price, $exec_vol, $buyer_cdcode, $buy_flag_id]);
		if (!$result) {
			throw new Exception("Order couldn't execute. Please try again later.");
		}

		// insert into order audits
		$buy_ord_audit = $dbh->prepare("
				INSERT INTO bond_order_audits(bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, flag_id, price, commis_amt, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status)
				SELECT b.id, b.symbol_id, b.cd_code, b.participant_code, b.member_broker, b.order_size, b.order_entry, b.buy_vol, b.flag_id, b.price, b.commis_amt, b.exe_vol, b.exe_price, b.lot_check, b.acc_intrt, b.dirty_price, b.ytm, b.order_type, b.quoted_to, b.order_date, b.status 
				FROM bond_orders b WHERE b.id = ?
		");
		$buy_ord_audit->execute([$buy_ord_id]);

		$upd_sell_ord = $dbh->prepare("UPDATE bond_orders SET order_size = ?, sell_vol = ?, status = ?, exe_vol = ?, exe_price = ?, lot_check = ? WHERE symbol_id = ? AND cd_code = ? AND flag_id = ?");
		$result = $upd_sell_ord([$sell_balance, $sell_balance, $sell_ord_status, $exec_vol, $exec_price, $exec_vol,$symbol_id,  $seller_cdcode, $sell_flag_id]);
		if (!$result) {
			throw new Exception("Order couldn't execute. Please try again later.");
		}

		// sell order audit
		$sell_ord_audit = $dbh->prepare("
				INSERT INTO bond_order_audits(bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, flag_id, sell_vol, price, commis_amt, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status)
				SELECT b.id, b.symbol_id, b.cd_code, b.participant_code, b.member_broker, b.order_size, b.order_entry, b.flag_id, b.sell_vol, b.price, b.commis_amt, b.exe_vol, b.exe_price, b.lot_check, b.acc_intrt, b.dirty_price, b.ytm, b.order_type, b.quoted_to, b.order_date, b.status
				FROM bond_orders b WHERE b.id = ?
		");
		$sell_ord_audit->execute([$sell_ord_id]);

		// insert into bond execution
		$lot_status = 0;
		$exec_query = $dbh->prepare("
		    INSERT INTO bond_executed_orders (cd_code, participant_code, sub_user, member_broker, order_date, symbol_id, order_exe_price, lot_size_execute, status, side, lot_check, flag_id, dirty_price, accur_rate, ytm) VALUES (:cd_code, :participant_code, :sub_user, :member_broker, :order_date, :symbol_id, :order_exe_price, :lot_size_execute, :status, :side, :lot_check, :flag_id, :dirty_price, :accur_rate, :ytm
		    )
		");

		$commonData = [
		    ':symbol_id'        => $symbol_id,
		    ':order_exe_price'  => $exec_price,
		    ':lot_size_execute' => $exec_vol,
		    ':status'           => $lot_status,
		    ':lot_check'        => $exec_vol,
		    ':dirty_price'      => $exec_dirty_price,
		    ':accur_rate'       => $exec_acc_int,
		    ':ytm'              => $exec_ytm
		];

		$buy_exec_res = $exec_query->execute(array_merge($commonData, [
		    ':cd_code'          => $buyer_cdcode,
		    ':participant_code' => $buy_part_code,
		    ':sub_user'         => $buy_order_entry,
		    ':member_broker'    => $buy_member_broker,
		    ':order_date'       => $buy_order_date,
		    ':side'             => $buy_side,
		    ':flag_id'          => $buy_flag_id
		]));

		$sell_exec_res = $exec_query->execute(array_merge($commonData, [
		    ':cd_code'          => $seller_cdcode,
		    ':participant_code' => $sell_part_code,
		    ':sub_user'         => $sell_order_entry,
		    ':member_broker'    => $sell_member_broker,
		    ':order_date'       => $sell_order_date,
		    ':side'             => $sell_side,
		    ':flag_id'          => $sell_flag_id
		]));

		if (!$buy_exec_res || !$sell_exec_res) {
	      throw new Exception("Order couldn't execute. Please try again later.");
		}

		// update finance
		$gst_register = 'Y';
		$broker_comm = 1;

		$exec_amount = $exec_price * $exec_vol;
		$commis_fee = $exec_amount * $broker_comm / 100;
		$gst_fee = ($gst_register == 'Y') ? ($commis_fee * 0.05) : 0;
		// $total_amount = round($exec_amount + $commis_fee + $gst_fee, 2);

		// buy bbo finance
		$flag_buy = 3;
    $remarks_buy = "Amount for buying " . $exec_vol . " share @ Nu." . $exec_price;
		$flag = 4;
    $buy_com_remarks = "Commission for the trade of " . $exec_vol . " share @ Nu. " . $exec_price;
    $gst_flag = 5;
    $gst_remarks = "GST fee for the trade of " . $exec_vol . " share @ Nu. " . $exec_price;

    $buy_fin = $dbh->prepare("INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id) VALUES(?, ?, ?, ?, ?, ?, ?)");
    $buy_fin->execute([$buyer_cdcode, $exec_amount, $buy_member_broker, $remarks_buy, $flag_buy, $institution_id, $buy_flag_id]);

    $buy_com_gst = "INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id)
        			VALUES (:cd_code1, :amount1, :user_name1, :remarks1, :flag1, :institution_id1, :flag_id1)";

    $params = [
        ':cd_code1'        => $buyer_cdcode,
        ':amount1'         => -abs($commis_fee),
        ':user_name1'      => $buy_member_broker,
        ':remarks1'        => $buy_com_remarks,
        ':flag1'           => $flag,
        ':institution_id1' => $institution_id,
        ':flag_id1'        => $buy_flag_id
    ];

    if ($gst_register === 'Y') {
        $buy_com_gst .= ",
            (:cd_code2, :amount2, :user_name2, :remarks2, :flag2, :institution_id2, :flag_id2)
        ";
        $params += [
            ':cd_code2'        => $buyer_cdcode,
            ':amount2'         => -abs($gst_fee),
            ':user_name2'      => $buy_member_broker,
            ':remarks2'        => $gst_remarks,
            ':flag2'           => $gst_flag,
            ':institution_id2' => $institution_id,
            ':flag_id2'        => $buy_flag_id
        ];
    }

    // sell bbo finance
    $flag_sell = 2;
    $remarks_sell = "Amount for selling " . $exec_vol . " share @ Nu." . $exec_price;
    $flag = 4;
    $sell_com_remarks = "Commission for the trade of " . $exec_vol . " share @ Nu. " . $exec_price;
    $gst_flag = 5;
    $sell_gst_remarks = "GST fee for the trade of " . $exec_vol . " share @ Nu. " . $exec_price;

    $b_fin = $dbh->prepare("
        INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id)
        VALUES (:cd_code, :amount, :user_name, :remarks, :flag, :institution_id, :flag_id)
    ");
    $b_fin->execute([
        ':cd_code'         => $seller_cdcode,
        ':amount'          => $exec_amount,
        ':user_name'       => $sell_member_broker,
        ':remarks'         => $remarks_sell,
        ':flag'            => $flag_sell,
        ':institution_id'  => $institution_id,
        ':flag_id'         => $sell_flag_id,
    ]);


    $finsellstatuscomm = 0;
    $sql = "
        INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id)
        VALUES (:cd_code1, :amount1, :user_name1, :remarks1, :flag1, :institution_id1, :flag_id1)
    ";

    $params = [
        ':cd_code1'        => $seller_cdcode,
        ':amount1'         => -abs($commis_fee),
        ':user_name1'      => $sell_member_broker,
        ':remarks1'        => $sell_com_remarks,
        ':flag1'           => $flag_sell,
        ':institution_id1' => $institution_id,
        ':flag_id1'        => $sell_flag_id,
    ];

    if ($gst_register === 'Y') {
        $sql .= ",
            (:cd_code2, :amount2, :user_name2, :remarks2, :flag2, :institution_id2, :flag_id2)
        ";

        $params += [
            ':cd_code2'        => $seller_cdcode,
            ':amount2'         => -abs($gst_fee),
            ':user_name2'      => $member_broker,
            ':remarks2'        => $sell_gst_remarks,
            ':flag2'           => $gst_flag,
            ':institution_id2' => $institution_id,
            ':flag_id2'        => $sell_flag_id,
        ];
    }

    // if buy order size fully is executed, delete buy order and buy finance
    if ($buy_balance == 0) {
    	$del_buy_order = $dbh->prepare("DELETE FROM bond_orders WHERE id = ?");
    	$del_buy_order->execute([$buy_ord_id]);

    	$del_buy_fin = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = ?");
    	$del_buy_fin->execute([$buy_flag_id]);
    }
    // if sell order size is fully executed, delete sell orders and sell finance
    if ($sell_balance == 0) {
    	$del_sell_order = $dbh->prepare("DELETE FROM bond_orders WHERE id = ?");
    	$del_sell_order->execute([$sell_ord_id]);

    	$del_sell_fin = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = ?");
    	$del_sell_fin->execute([$sell_flag_id]);
    }
    // if buy order size is partially executed, update buy finance since buy order already been updated at top
    if ($buy_balance > 0) {
    	
    }

    // if sell order is partially executed, update sell finance
    if ($sell_balance > 0) {
    	$new_amout = $sell_balance * $sell_price;
    	$new_comm = $new_amout * $broker_comm / 100;
    	$total_new_amt = round($new_amout + $new_comm, 2);

    	$upd_sell_fin = $dbh->prepare("UPDATE bbo_finance SET amount = ? WHERE flag_id = ?");
    	$upd_sell_fin->execute([$sell_flag_id]);
    }

		$dbh->commit();
		echo json_encode([
			"success" => true,
			"message" => "Offer executed successfully."
		]);
	} catch (\Exception $e) {
		if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }

		http_response_code(400);
		echo json_encode([
			"success" => false,
		    "message" => htmlspecialchars($e->getMessage()),
		]);
	}
	exit();
}

?>