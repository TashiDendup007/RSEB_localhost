<?php  
elseif(isset($_POST['bond_trade_settlement'])) {
    $tdate = $_POST['date'];

    $q1 = $dbh->prepare("SELECT * FROM bond_executed_orders WHERE status = ? AND order_date LIKE ?");
    $q1->execute([0, "%{$tdate}%"]);
    $all_exec_ords = $q1->fetchAll(PDO::FETCH_ASSOC);
    if($all_exec_ords) {
      try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        foreach ($all_exec_ords as $row) {
          $finance_flag_id = $row['flag_id'];
          $id = $row['id'];
          $cd_code = $row['cd_code'];
          $p_code = $row['participant_code'];
          $order_exe_price = $row['order_exe_price'];
          $lot_size_execute = $row['lot_size_execute'];
          $status = $row['status'];
          $symbol_id = $row['symbol_id'];
          $member_broker = $row['member_broker'];
          $username = $member_broker;
          $side = $row['side'];

          $list = ins_id($username);
          $institution_id = $list[0];
          $st = 1;

          if ($side == 'B') {
            $q2 = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
            $q2->execute([
              ':cd_code' => $cd_code, ':symbol_id' => $symbol_id,
            ]);

            foreach ($q2 as $row) {
              $q3 = $dbh->prepare("SELECT lot_check FROM bond_executed_orders WHERE id = :id AND status = 0");
              $q3->bindParam(':id', $id);
              $q3->execute();
              $val = $q3->fetch();

              $l_check = $val['lot_check'];
              $cd_code = $row['cd_code'];
              $existing_vol = $row['volume'];
              $pending_in_vol = $row['pending_in_vol'];
              $cds_holding_id = $row['cds_holding_id'];
              
              if ($l_check > 0 && $pending_in_vol > 0) {
                if ($l_check == $pending_in_vol) {
                  $pending_in_vol_new = 0;
                  $vol_new = $existing_vol + $pending_in_vol;
                  $status = 1;
                  $l_check = 0;
                }
                elseif ($l_check < $pending_in_vol) {
                  $pending_in_vol_new = $pending_in_vol - $l_check;
                  $vol_new = $existing_vol + $l_check;
                  $status = 1;
                  $l_check = 0;
                }
                elseif ($l_check > $pending_in_vol && $pending_in_vol > 0 ) {
                  $pending_in_vol_new = 0;
                  $vol_new = $existing_vol + $pending_in_vol;
                  $status = 0;
                  $l_check = $l_check - $pending_in_vol;
                }

                $q4 = $dbh->prepare("UPDATE cds_holding SET pending_in_vol = :pending_in_vol_new, volume = :vol_new WHERE cd_code = :cd_code AND symbol_id = :symbol_id AND cds_holding_id = :ccid");
                $q4->execute([
                  ':pending_in_vol_new' => $pending_in_vol_new, 
                  ':vol_new' => $vol_new, 
                  ':cd_code' => $cd_code, 
                  ':ccid' => $cds_holding_id, 
                  ':symbol_id' => $symbol_id, 
                ]);

                $q5 = $dbh->prepare("UPDATE executed_orders SET status = :st, lot_check = :lc WHERE id = :id");
                $q5->execute([
                  ':st' => $status, 
                  ':lc' => $l_check, 
                  ':id' => $id, 
                ]);

                $cds_dep_wit = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cdCode, :sym_id, :volume, :mem_bro, :inst_id, :remark, :side)");
                $cds_dep_wit->execute([
                  ':cdCode' => $cd_code, 
                  ':sym_id' => $symbol_id, 
                  ':volume' => $lot_size_execute, 
                  ':mem_bro' => $member_broker, 
                  ':inst_id' => $institution_id, 
                  ':remark' => 'Bought from Trade', 
                  ':side' => $side,
                ]);
              
              } else {
                echo"some error on lot check 1";
              }
            }
          }
          elseif ($side == 'S') {
            $q6 = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
            $q6->bindParam(':cd_code',$cd_code); 
            $q6->bindParam(':symbol_id',$symbol_id);
            $q6->execute();
            foreach ($q6 as $row) {
              $q7 = $dbh->prepare("SELECT lot_check FROM bond_executed_orders WHERE id = :id and status = 0");
              $q7->bindParam(':id', $id);
              $q7->execute();
              $val = $q7->fetch();

              $l_check = $val['lot_check'];
              $cd_code = $row['cd_code'];
              $existing_vol = $row['volume'];
              $pending_out_vol = $row['pending_out_vol'];
              $cds_holding_id = $row['cds_holding_id'];

              if ($l_check > 0 && $pending_out_vol > 0) {
                if($l_check == $pending_out_vol) {
                  $pending_out_vol_new = 0;
                  $vol_new = $existing_vol - $pending_out_vol;
                  $status = 1;
                  $l_check = 0;
                }
                elseif ($l_check < $pending_out_vol) {
                  $pending_out_vol_new = $pending_out_vol - $l_check;
                  $vol_new = $existing_vol - $l_check;
                  $status = 1;
                  $l_check = 0;
                }
                elseif ($l_check > $pending_out_vol && $pending_out_vol > 0) {
                  $pending_out_vol_new = 0;
                  $vol_new = $existing_vol - $pending_out_vol;
                  $status = 0;
                  $l_check = $l_check - $pending_out_vol;
                }

                $q8 = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = :pending_out_vol_new WHERE cd_code = :cd_code AND symbol_id = :symbol_id AND cds_holding_id = :ccid");
                $q8->execute([
                  ':pending_out_vol_new' => $pending_out_vol_new, 
                  ':cd_code' => $cd_code, 
                  ':ccid' => $cds_holding_id, 
                  ':symbol_id' => $symbol_id
                ]);

                $q9 = $dbh->prepare("UPDATE executed_orders SET status = :st, lot_check = :lc WHERE id = :id");
                $q9->execute([
                  ':st' => $status,
                  ':lc' => $l_check,
                  ':id' => $id, 
                ]);

                $sell_cds_rmk = "Sold from Trade";
                $cds_dep_wit = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cdCode, :sym_id, -:volume, :mem_bro, :inst_id, :remark, :side)");
                $cds_dep_wit->execute([
                  ':cdCode' => $cd_code, 
                  ':sym_id' => $symbol_id, 
                  ':volume' => $lot_size_execute, 
                  ':mem_bro' => $member_broker, 
                  ':inst_id' => $institution_id, 
                  ':remark' => 'Sold from Trade', 
                  ':side' => $side
                ]);

                $finance = $dbh->prepare("UPDATE bbo_finance SET status = 1 WHERE flag_id = :id and status = 0");
                $finance->bindParam(':id', $finance_flag_id);
                $finance->execute();
              } else {
                echo"error while checking the lot of seller";
              }
            }
          } // end of side sell 
        } // end of for each

        $dbh->commit();

        // Back Up Settlement Trade Details
        // Fetch Summary of trade vol and value
        // $sql = $dbh->prepare("
        //           SELECT b.symbol, SUM(a.lot_size_execute) AS total_lot_exe , SUM(a.order_exe_price * a.lot_size_execute) AS total_value 
        //           FROM bond_executed_orders a
        //           INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
        //           WHERE a.side = 'B' AND DATE(a.order_date) = :order_date 
        //           GROUP BY a.symbol_id 
        //           ORDER BY a.symbol_id ASC 
        //   ");
        //   $sql->bindValue(':order_date', $tdate);
        //   $sql->execute();
        //   $exe_orders = $sql->fetchAll(PDO::FETCH_ASSOC);

        //   $columnHeader = "\t\tSummary of Traded Volume\t\t\n"; 
        //   $columnHeader .= "Sl\t Symbol\t volume Traded\t Value\t"; 
        //   $setData = '';
        //   $replace   = array("\n", "\r\n", "\r");
        //   $search  = array('', '', ''); 
        //   $i = 1;

        //   $totalVolumeTraded = 0;
        //   $totalValue = 0.0;

        //   foreach ($exe_orders as $key => $value) {
        //     $rowData = '';
        //     $rowData .= str_replace($search, $replace, $i) . "\t";
        //     $rowData .= str_replace($search, $replace, $value['symbol']) . "\t";
        //     $rowData .= str_replace($search, $replace, number_format($value['total_lot_exe'])) . "\t";
        //     $rowData .= str_replace($search, $replace, number_format($value['total_value'], 2)) . "\t"; 
        //     $setData .= trim($rowData) . "\n"; 

        //     $totalVolumeTraded += $value['total_lot_exe'];
        //     $totalValue += $value['total_value'];

        //     $i++;
        //   }

        //   // Add total row
        //   $totalRow = "Total\t\t".number_format($totalVolumeTraded)."\t".number_format($totalValue, 2)."\t";
        //   $setData .= trim($totalRow) . "\n";

        //   // Fetch all Trade Details
        //   $j = 1;
        //   $query = $dbh->prepare("SELECT a.cd_code, a.side, a.lot_size_execute, a.order_exe_price, b.symbol, a.status, a.order_date, a.dirty_price, a.accur_rate, a.ytm, a.order_type  
        //     FROM bond_executed_orders a
        //     INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
        //     WHERE DATE(a.order_date) = :ord__date 
        //     ORDER BY a.symbol_id ASC, a.order_date ASC
        //   ");
        //   $query->bindValue(':ord__date', $tdate);
        //   $query->execute();
        //   $all_exe_orders = $query->fetchAll(PDO::FETCH_ASSOC);

        //   $columnHeader_1 = "\n\t\t All Trade Details \t\t\n"; 
        //   $columnHeader_1 .= "Sl\t CD Code\t Symbol\t Side\t volume\t Price\t Dirty Price\t Accured Interest\t YTM\t Order Type\t Trade Date\t"; 
        //   $setData_1 = '';
        //   $replace   = array("\n", "\r\n", "\r");
        //   $search  = array('', '', ''); 
        //   $i = 1;

        //   foreach ($all_exe_orders as $key => $row) {
        //     $rowData = '';
        //     $rowData .= str_replace($search, $replace, $j) . "\t";
        //     $rowData .= str_replace($search, $replace, $row['cd_code']) . "\t";
        //     $rowData .= str_replace($search, $replace, $row['symbol']) . "\t";
        //     $rowData .= str_replace($search, $replace, $row['side']) . "\t";
        //     $rowData .= str_replace($search, $replace, number_format($row['lot_size_execute'])) . "\t";
        //     $rowData .= str_replace($search, $replace, number_format($row['order_exe_price'], 2)) . "\t"; 
        //     $rowData .= str_replace($search, $replace, number_format($row['dirty_price'], 2)) . "\t"; 
        //     $rowData .= str_replace($search, $replace, number_format($row['accur_rate'], 2)) . "\t"; 
        //     $rowData .= str_replace($search, $replace, number_format($row['ytm'], 2)) . "\t"; 
        //     $rowData .= str_replace($search, $replace, $row['order_type']) . "\t"; 
        //     $rowData .= str_replace($search, $replace, $row['order_date']) . "\t";
        //     $setData_1 .= trim($rowData) . "\n"; 
            
        //     $j++;
        //   }

        //   // Securely write to the file
        //   $filePath = '../../Settlement_Backup/Bond/Bond_Clearing_data_' . $tdate . '.xls';
        //   file_put_contents($filePath, ucwords($columnHeader) . "\n" . $setData . "\n" . ucwords($columnHeader_1) . "\n" . $setData_1 . "\n" . PHP_EOL, FILE_APPEND);

        echo '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Clearing  Successfully Completed.</div>';
      } catch(PDOException $e) {
        $dbh->rollBack();
        error_log("Error ==> " .$e->getMessage() . ", Line ==> " . $e->getLine());
        echo '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"> </i> Exception occurred. Contact RSEB support.</div>';
      }
      
    } else {
      echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> No Trades to be Settled.</div></div></div>';
    }
    exit;
}

?>