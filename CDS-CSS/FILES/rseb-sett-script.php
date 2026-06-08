<?php
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');

if(isset($_POST['SETT'])) {
  $tdate = $_POST['date'];

  $q1 = $dbh->prepare('SELECT * FROM executed_orders WHERE status = 0 AND order_date LIKE "%'.$tdate.'%"');
  $q1->execute();
  if($q1->rowCount() > 0) {
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      foreach ($q1 as $row) {
        $finance_flag_id = $row['order_id'];
        $id = $row['exe_id'];
        $cd_code = $row['cd_code'];
        $p_code = $row['participant_code'];
        $order_exe_price = $row['order_exe_price'];
        $lot_size_execute = $row['lot_size_execute'];
        $status = $row['status'];
        $symbol_id = $row['symbol_id'];
        $member_broker = $row['member_broker'];
        $username = $member_broker;
        $buy = 'B';
        $sell = 'S';
        $side = $row['side'];

        $list = ins_id($username);
        $institution_id = $list[0];
        $st = 1;

        //buy orders
        if ($side == $buy) {
          $q2 = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
          $q2->bindParam(':cd_code', $cd_code); 
          $q2->bindParam(':symbol_id', $symbol_id);
          $q2->execute();

          foreach ($q2 as $row) {
            $q3 = $dbh->prepare("SELECT lot_check FROM executed_orders WHERE exe_id = :id AND status = 0");
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
              $q4->bindParam(':pending_in_vol_new', $pending_in_vol_new);
              $q4->bindParam(':vol_new', $vol_new);
              $q4->bindParam(':cd_code', $cd_code);
              $q4->bindParam(':ccid', $cds_holding_id);
              $q4->bindParam(':symbol_id', $symbol_id);
              $q4->execute();

              $q5 = $dbh->prepare("UPDATE executed_orders SET status = :st, lot_check = :lc WHERE exe_id = :id");
              $q5->bindParam(':id', $id);
              $q5->bindParam(':lc', $l_check);
              $q5->bindParam(':st', $status);
              $q5->execute();

              $cds_rmk = "Bought from Trade";
              $cds_dep_wit = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cdCode, :sym_id, :volume, :mem_bro, :inst_id, :remark, :side)");
              $cds_dep_wit->bindParam(":cdCode", $cd_code);
              $cds_dep_wit->bindParam(":sym_id", $symbol_id);
              $cds_dep_wit->bindParam(":volume", $lot_size_execute);
              $cds_dep_wit->bindParam(":mem_bro", $member_broker);
              $cds_dep_wit->bindParam(":inst_id", $institution_id);
              $cds_dep_wit->bindParam(":remark", $cds_rmk);
              $cds_dep_wit->bindParam(":side", $side);
              $cds_dep_wit->execute();
            
            } else {
              echo"some error on lot check 1";
            }
          }
        }
        //sell orders
        elseif ($side == $sell) {
          $q6 = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
          $q6->bindParam(':cd_code',$cd_code); 
          $q6->bindParam(':symbol_id',$symbol_id);
          $q6->execute();
          foreach ($q6 as $row) {
            $q7 = $dbh->prepare("SELECT lot_check from executed_orders WHERE exe_id = :id and status = 0");
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
              $q8->bindParam(':pending_out_vol_new', $pending_out_vol_new);
              $q8->bindParam(':cd_code', $cd_code);
              $q8->bindParam(':ccid', $cds_holding_id);
              $q8->bindParam(':symbol_id', $symbol_id);
              $q8->execute();

              $q9 = $dbh->prepare("UPDATE executed_orders SET status = :st, lot_check = :lc where exe_id = :id");
              $q9->bindParam(':id', $id);
              $q9->bindParam(':lc', $l_check);
              $q9->bindParam(':st', $status);
              $q9->execute();

              $sell_cds_rmk = "Sold from Trade";
              $cds_dep_wit = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cdCode, :sym_id, -:volume, :mem_bro, :inst_id, :remark, :side)");
              $cds_dep_wit->bindParam(":cdCode", $cd_code);
              $cds_dep_wit->bindParam(":sym_id", $symbol_id);
              $cds_dep_wit->bindParam(":volume", $lot_size_execute);
              $cds_dep_wit->bindParam(":mem_bro", $member_broker);
              $cds_dep_wit->bindParam(":inst_id", $institution_id);
              $cds_dep_wit->bindParam(":remark", $sell_cds_rmk);
              $cds_dep_wit->bindParam(":side", $side);
              $cds_dep_wit->execute();

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
      // $dbh = null;

      // Back Up Settlement Trade Details
      // Fetch Summary of trade vol and value
      $sql = $dbh->prepare("SELECT b.symbol, SUM(a.lot_size_execute) AS total_lot_exe , SUM(a.order_exe_price * a.lot_size_execute) AS total_value 
                FROM executed_orders a
                INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
                WHERE a.side = 'B' AND DATE(a.order_date) = :order_date 
                GROUP BY a.symbol_id 
                ORDER BY a.symbol_id ASC 
        ");
        $sql->bindValue(':order_date', $tdate);
        $sql->execute();
        $exe_orders = $sql->fetchAll(PDO::FETCH_ASSOC);

        $columnHeader = "\t\tSummary of Traded Volume\t\t\n"; 
        $columnHeader .= "Sl\t Symbol\t volume Traded\t Value\t"; 
        $setData = '';
        $replace   = array("\n", "\r\n", "\r");
        $search  = array('', '', ''); 
        $i = 1;

        $totalVolumeTraded = 0;
        $totalValue = 0.0;

        foreach ($exe_orders as $key => $value) {
          $rowData = '';
          $rowData .= str_replace($search, $replace, $i) . "\t";
          $rowData .= str_replace($search, $replace, $value['symbol']) . "\t";
          $rowData .= str_replace($search, $replace, number_format($value['total_lot_exe'])) . "\t";
          $rowData .= str_replace($search, $replace, number_format($value['total_value'], 2)) . "\t"; 
          $setData .= trim($rowData) . "\n"; 

          $totalVolumeTraded += $value['total_lot_exe'];
          $totalValue += $value['total_value'];

          $i++;
        }

        // Add total row
        $totalRow = "Total\t\t".number_format($totalVolumeTraded)."\t".number_format($totalValue, 2)."\t";
        $setData .= trim($totalRow) . "\n";

        // Fetch all Trade Details
        $j = 1;
        $query = $dbh->prepare("SELECT a.cd_code, a.side, a.lot_size_execute, a.order_exe_price, (a.lot_size_execute * a.order_exe_price) AS exe_value, b.symbol, a.status, a.order_date  
            FROM executed_orders a
            INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
            WHERE DATE(a.order_date) = :ord__date 
            ORDER BY a.symbol_id ASC, a.order_date ASC
        ");
        $query->bindValue(':ord__date', $tdate);
        $query->execute();
        $all_exe_orders = $query->fetchAll(PDO::FETCH_ASSOC);

        $columnHeader_1 = "\n\t\t All Trade Details \t\t\n"; 
        $columnHeader_1 .= "Sl\t CD Code\t Symbol\t Side\t volume\t Price\t Amount\t Trade Date\t"; 
        $setData_1 = '';
        $replace   = array("\n", "\r\n", "\r");
        $search  = array('', '', ''); 
        $i = 1;

        foreach ($all_exe_orders as $key => $row) {
          $rowData = '';
          $rowData .= str_replace($search, $replace, $j) . "\t";
          $rowData .= str_replace($search, $replace, $row['cd_code']) . "\t";
          $rowData .= str_replace($search, $replace, $row['symbol']) . "\t";
          $rowData .= str_replace($search, $replace, $row['side']) . "\t";
          $rowData .= str_replace($search, $replace, number_format($row['lot_size_execute'])) . "\t";
          $rowData .= str_replace($search, $replace, number_format($row['order_exe_price'], 2)) . "/share\t"; 
          $rowData .= str_replace($search, $replace, number_format($row['exe_value'], 2)) . "\t"; 
          $rowData .= str_replace($search, $replace, $row['order_date']) . "\t";
          $setData_1 .= trim($rowData) . "\n"; 
          
          $j++;
        }

        // Securely write to the file
        $filePath = '../../Settlement_Backup/Clearing_data_' . $tdate . '.xls';
        file_put_contents($filePath, ucwords($columnHeader) . "\n" . $setData . "\n" . ucwords($columnHeader_1) . "\n" . $setData_1 . "\n" . PHP_EOL, FILE_APPEND);

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
elseif(isset($_POST['load_clearing_data']))
{
    $tdate = $_POST['load_clearing_data'];

    $executed_orders = $dbh->prepare('SELECT DISTINCT a.symbol_id,b.symbol 
        FROM executed_orders a 
        JOIN symbol b ON b.symbol_id = a.symbol_id 
        WHERE a.status = 0  
          AND a.order_date LIKE "%'.$tdate.'%"
      ');
    $executed_orders->execute();
    if ($row = $executed_orders->rowCount() > 0) {
    $i = 1;
    
    foreach ($executed_orders as $res) {
      $symbol_id = $res['symbol_id'];
      $executed_orders1= $dbh->prepare('SELECT sum(a.lot_size_execute) as tv,
                                        cast(avg(a.order_exe_price) as decimal(13,2)) as ap, b.symbol 
                                        FROM executed_orders a
                                        JOIN symbol b ON a.symbol_id = b.symbol_id 
                                        WHERE a.symbol_id = :sym_id 
                                          AND a.status = 0 
                                          AND a.side = "B"
                                          AND a.order_date LIKE "%'.$tdate.'%"');
        $executed_orders1->bindParam(':sym_id', $symbol_id);
        $executed_orders1->execute();
        $res1 = $executed_orders1->fetch();
        echo $i.'  TRADE  : <br>
        ------------------------------------------------------------------------------------------------- <br>
        <b> '.$res['symbol']. ' : </b>  TOTAL VOL  : <b style="color:#3498DB;">'.number_format($res1['tv'], 2, ".", ",").' </b>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
        TOTAL VALUE  : <b style="color:#3498DB;">'.number_format($res1['ap'] * $res1['tv'], 2, ".", ",").'</b><br>
        ------------------------------------------------------------------------------------------------- <br>';
        $bt = $res1['tv'];
        $bv = $res1['ap'] * $res1['tv'];   
        $i++;                
      }
      echo'
      <div class="box-footer">
        <div class="col-xs-4">
          <button type="submit" class="btn btn-primary" id="sett" value="SETT" onclick="sett_process();">Process Settlement</button>
        </div>
      </div>

      <div class="box">
        <div class="box-body">
          <div class="box-body">
            <div class="row">
               <table id="example1" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                      <td><b>CD CODE</b></td>     
                      <td><b>SYMBOL</b></td> 
                      <td><b>VOL</b></td>
                      <td><b>TRADE DATE</b></td>
                      <td><b>PRICE</b></td>
                      <td><b>AMOUNT</b></td>
                     </tr>
                  </thead>   
                  <tbody >';
                    $executed_orders = $dbh->prepare('SELECT a.cd_code, a.lot_size_execute, a.order_exe_price, a.order_date, b.symbol 
                      FROM executed_orders a 
                      INNER JOIN symbol b ON a.symbol_id = b.symbol_id
                      WHERE a.status = 0 
                        AND a.order_date LIKE "%'.$tdate.'%"
                    ');
                    $executed_orders->execute();
                    while ($result = $executed_orders->fetch(PDO::FETCH_ASSOC)) {
                      $amt = $result['lot_size_execute'] * $result['order_exe_price'];
                      echo'
                      <tr>
                        <td> '.$result['cd_code'].'</td>
                        <td> '.$result['symbol'].'</td>
                        <td> '.$result['lot_size_execute'].'</td>
                        <td> '.$result['order_date'].'</td>
                        <td> '.number_format($result['order_exe_price'], 2, ".", ",").'/share</td>
                        <td> '.number_format($amt, 2, ".", ",").'</td>
                      </tr>';
                    }        
                  echo'
                  </tbody>
                </table>
              </div>
            </div>
        </div>
      </div>';
    }
    else{
      echo"<div class='row'>
            <div class='col-lg-4'>
              <span style='font-size: 20px; color: red;'>No Trades On this day</span>
            </div>
          </div>";
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get__trade__details'])) {
    $exe__order__date = $_POST['exe__date'];
    echo'
    <div class="modal-dialog" style="width: 1160px;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title text-center"> <strong>Trade Details</strong></h4>
        </div>        
        <div class="modal-body">
          <div class="box-body">

            <div class="table-responsive">
              <table id="table__id" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                      <th>Sl</th>
                      <th>CD CODE</th>
                      <th>Symbol</th> 
                      <th>Side</th> 
                      <th>Vol</th>
                      <th>Price</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Trade Date</th>
                   </tr>
                </thead>
                <tbody>';
                  $j = 1;
                  $query = $dbh->prepare("SELECT a.cd_code, a.side, a.lot_size_execute, a.order_exe_price, (a.lot_size_execute * a.order_exe_price) AS exe_value, b.symbol, a.status, a.order_date 
                        FROM executed_orders a
                        INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
                        WHERE DATE(a.order_date) = :ord__date 
                        ORDER BY a.symbol_id ASC, a.order_date ASC
                  ");
                  $query->bindValue(':ord__date', $exe__order__date);
                  $query->execute();
                  $results = $query->fetchAll(PDO::FETCH_ASSOC);

                  foreach ($results as $val) {
                      $status = ($val['status'] == 1) ? 'Settled' : 'Unsettled';
                      echo '
                      <tr>
                          <td>' . $j . '</td>
                          <td>' . $val['cd_code'] . '</td>
                          <td>' . $val['symbol'] . '</td>
                          <td>' . $val['side'] . '</td>
                          <td>' . $val['lot_size_execute'] . '</td>
                          <td>' . number_format($val['order_exe_price'], 2, ".", ",") . '/share</td>
                          <td>' . number_format($val['exe_value'], 2, ".", ",") . '</td>
                          <td>' . $status . '</td>
                          <td>' . $val['order_date'] . '</td>
                      </tr>';
                      $j++;
                  }
                  echo'
                </tbody>
              </table>
            </div>
            <script>
              $(function () {
                  $("#table__id").DataTable();
              });
            </script>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
      </div>
    </div>';
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bond__trade__details'])) {
    $exe__order__date = $_POST['exe__date'];
    echo'
    <div class="modal-dialog" style="width: 1160px;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title text-center"> <strong>Trade Details</strong></h4>
        </div>        
        <div class="modal-body">
          <div class="box-body">

            <div class="table-responsive">
              <table id="table__id" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                      <th>Sl</th>
                      <th>CD CODE</th>
                      <th>Symbol</th> 
                      <th>Side</th> 
                      <th>Vol</th>
                      <th>Price</th>
                      <th>Dirty Price</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Trade Date</th>
                   </tr>
                </thead>
                <tbody>';
                  $j = 1;
                  $query = $dbh->prepare("
                      SELECT a.cd_code, a.side, a.lot_size_execute, a.order_exe_price, (a.lot_size_execute * a.dirty_price) AS exe_value, b.symbol, a.status, a.order_date, a.dirty_price 
                      FROM bond_executed_orders a
                      INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
                      WHERE DATE(a.order_date) = :ord__date 
                      ORDER BY a.symbol_id ASC, a.order_date ASC
                  ");
                  $query->bindValue(':ord__date', $exe__order__date);
                  $query->execute();
                  $results = $query->fetchAll(PDO::FETCH_ASSOC);

                  foreach ($results as $val) {
                      $status = ($val['status'] == 1) ? 'Settled' : 'Unsettled';
                      echo '
                      <tr>
                          <td>' . $j . '</td>
                          <td>' . $val['cd_code'] . '</td>
                          <td>' . $val['symbol'] . '</td>
                          <td>' . $val['side'] . '</td>
                          <td>' . $val['lot_size_execute'] . '</td>
                          <td>' . number_format($val['order_exe_price'], 2, ".", ",") . '</td>
                          <td>' . number_format($val['dirty_price'], 2, ".", ",") . '</td>
                          <td>' . number_format($val['exe_value'], 2, ".", ",") . '</td>
                          <td>' . $status . '</td>
                          <td>' . $val['order_date'] . '</td>
                      </tr>';
                      $j++;
                  }
                  echo'
                </tbody>
              </table>
            </div>
            <script>
              $(function () {
                  $("#table__id").DataTable();
              });
            </script>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
      </div>
    </div>';
}
elseif(isset($_POST['bond_trade_settlement'])) {
    /**
     * Bond Order Settlement
     *
     * Processes executed bond orders for a given date, updating CDS holdings and logging deposit/withdrawal records.
     *
     */
    $tdate = $_POST['date'] ?? '';

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tdate)) {
            // http_response_code(400);
          throw new Exception("Invalid date format {$tdate}");
        }

        $q1 = $dbh->prepare("SELECT * FROM bond_executed_orders WHERE status = 0 AND order_date LIKE ?");
        $q1->execute(["%{$tdate}%"]);
        $all_exec_ords = $q1->fetchAll(PDO::FETCH_ASSOC);

        if (!$all_exec_ords) {
            throw new Exception("No Trades to be Settled on the given date");
        }

        foreach ($all_exec_ords as $order) {
            $id               = (int)$order['id'];
            $finance_flag_id  = $order['flag_id'];
            $cd_code          = $order['cd_code'];
            $symbol_id        = $order['symbol_id'];
            $member_broker    = $order['member_broker'];
            $lot_size_execute = (int)$order['lot_size_execute'];
            $side             = $order['side'];   // 'B' or 'S'

            $ins              = ins_id($member_broker);
            $institution_id   = $ins[0];

            // Fetch all CDS holding rows for this cd_code + symbol_id
            $qHolding = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
            $qHolding->execute([':cd_code' => $cd_code, ':symbol_id' => $symbol_id]);
            $holdings = $qHolding->fetchAll(PDO::FETCH_ASSOC);

            foreach ($holdings as $holding) {
                $l_check = fetchLotCheck($dbh, $id);

                if ($l_check === false) {
                    // Order already settled or missing — skip
                    throw new Exception("Issue with Lot check. cd code = {$cd_code}, symbol id => {$symbol_id }, id = {$id}");
                    // continue;
                }

                $cds_holding_id = $holding['cds_holding_id'];
                $existing_vol   = (int)$holding['volume'];

                if ($side === 'B') {
                    $pending_vol = (int)$holding['pending_in_vol'];

                    if ($l_check <= 0 || $pending_vol <= 0) {
                        throw new Exception("lot check error (buy) for order id={$id}");
                        // continue;
                    }

                    $s = computeSettlement($l_check, $pending_vol);

                    $dbh->prepare(
                        "UPDATE cds_holding
                            SET pending_in_vol = :pending_new,
                                volume         = :vol_new
                          WHERE cd_code        = :cd_code
                            AND symbol_id      = :symbol_id
                            AND cds_holding_id = :ccid"
                    )->execute([
                        ':pending_new' => $s['pending_new'],
                        ':vol_new'     => $existing_vol + $s['vol_delta'],
                        ':cd_code'     => $cd_code,
                        ':symbol_id'   => $symbol_id,
                        ':ccid'        => $cds_holding_id,
                    ]);

                } elseif ($side === 'S') {
                    $pending_vol = (int)$holding['pending_out_vol'];

                    if ($l_check <= 0 || $pending_vol <= 0) {
                        throw new Exception("lot check error (sell) for order id={$id}");
                        // continue;
                    }

                    $s = computeSettlement($l_check, $pending_vol);

                    // Only clear pending_out_vol — volume already deducted at order placement
                    $dbh->prepare(
                        "UPDATE cds_holding
                            SET pending_out_vol = :pending_new
                          WHERE cd_code         = :cd_code
                            AND symbol_id       = :symbol_id
                            AND cds_holding_id  = :ccid"
                    )->execute([
                        ':pending_new' => $s['pending_new'],
                        ':cd_code'     => $cd_code,
                        ':symbol_id'   => $symbol_id,
                        ':ccid'        => $cds_holding_id,
                    ]);

                    // Update finance flag only for sell orders
                    $dbh->prepare(
                        "UPDATE bbo_finance SET status = 1 WHERE flag_id = :flag_id AND status = 0"
                    )->execute([':flag_id' => $finance_flag_id]);
                }

                // Update the executed order status + remaining lot_check
                // FIX: original sell branch used 'exe_id'; column is 'id' throughout
                $dbh->prepare(
                    "UPDATE bond_executed_orders
                        SET status = :status, lot_check = :l_check
                      WHERE id = :id"
                )->execute([
                    ':status'  => $s['status'],
                    ':l_check' => $s['l_check_remaining'],
                    ':id'      => $id,
                ]);

                // Log the deposit/withdrawal record
                // FIX: sell volume is stored as a negative using a signed integer, not the '-:volume' parameter trick (which is invalid in PDO)
                $signed_volume = $side === 'S' ? -$lot_size_execute : $lot_size_execute;
                $remark        = $side === 'S' ? 'Sold from Trade' : 'Bought from Trade';

                $dbh->prepare(
                    "INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type)
                     VALUES(:cdCode, :sym_id, :volume, :mem_bro, :inst_id, :remark, :side)"
                )->execute([
                    ':cdCode'  => $cd_code,
                    ':sym_id'  => $symbol_id,
                    ':volume'  => $signed_volume,
                    ':mem_bro' => $member_broker,
                    ':inst_id' => $institution_id,
                    ':remark'  => $remark,
                    ':side'    => $side,
                ]);
                /**
                 * for the log information purpose
                 */
                error_log("cdcode => {$cd_code}, symbolid => {$symbol_id}, pending_vol => {$pending_vol}, l_check => {$l_check}, side => {$side}");
            }
        }

        $dbh->commit();

        /**
         * Back Up Settlement Trade Details
         * Fetch Summary of trade vol and value
         * **/
        $sql = $dbh->prepare("
            SELECT b.symbol, SUM(a.lot_size_execute) AS total_lot_exe , SUM(a.order_exe_price * a.lot_size_execute) AS total_value 
            FROM bond_executed_orders a
            INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
            WHERE a.side = 'B' AND DATE(a.order_date) = :order_date 
            GROUP BY a.symbol_id 
            ORDER BY a.symbol_id ASC 
        ");
        $sql->bindValue(':order_date', $tdate);
        $sql->execute();
        $exe_orders = $sql->fetchAll(PDO::FETCH_ASSOC);

        $columnHeader = "\t\tSummary of Traded Volume\t\t\n"; 
        $columnHeader .= "Sl\t Symbol\t volume Traded\t Value\t"; 
        $setData = '';
        $replace   = array("\n", "\r\n", "\r");
        $search  = array('', '', ''); 
        $i = 1;

        $totalVolumeTraded = 0;
        $totalValue = 0.0;

        foreach ($exe_orders as $key => $value) {
          $rowData = '';
          $rowData .= str_replace($search, $replace, $i) . "\t";
          $rowData .= str_replace($search, $replace, $value['symbol']) . "\t";
          $rowData .= str_replace($search, $replace, number_format($value['total_lot_exe'])) . "\t";
          $rowData .= str_replace($search, $replace, number_format($value['total_value'], 2)) . "\t"; 
          $setData .= trim($rowData) . "\n"; 

          $totalVolumeTraded += $value['total_lot_exe'];
          $totalValue += $value['total_value'];

          $i++;
        }

        // Add total row
        $totalRow = "Total\t\t".number_format($totalVolumeTraded)."\t".number_format($totalValue, 2)."\t";
        $setData .= trim($totalRow) . "\n";

        // Fetch all Trade Details
        $j = 1;
        $query = $dbh->prepare("SELECT a.cd_code, a.side, a.lot_size_execute, a.order_exe_price, b.symbol, a.status, a.order_date, a.dirty_price, a.accur_rate, a.ytm, a.order_type  
          FROM bond_executed_orders a
          INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
          WHERE DATE(a.order_date) = :ord__date 
          ORDER BY a.symbol_id ASC, a.order_date ASC
        ");
        $query->bindValue(':ord__date', $tdate);
        $query->execute();
        $all_exe_orders = $query->fetchAll(PDO::FETCH_ASSOC);

        $columnHeader_1 = "\n\t\t All Trade Details \t\t\n"; 
        $columnHeader_1 .= "Sl\t CD Code\t Symbol\t Side\t volume\t Price\t Dirty Price\t Accured Interest\t YTM\t Order Type\t Trade Date\t"; 
        $setData_1 = '';
        $replace   = array("\n", "\r\n", "\r");
        $search  = array('', '', ''); 
        $i = 1;

        foreach ($all_exe_orders as $key => $row) {
          $rowData = '';
          $rowData .= str_replace($search, $replace, $j) . "\t";
          $rowData .= str_replace($search, $replace, $row['cd_code']) . "\t";
          $rowData .= str_replace($search, $replace, $row['symbol']) . "\t";
          $rowData .= str_replace($search, $replace, $row['side']) . "\t";
          $rowData .= str_replace($search, $replace, number_format($row['lot_size_execute'])) . "\t";
          $rowData .= str_replace($search, $replace, number_format($row['order_exe_price'], 2)) . "\t"; 
          $rowData .= str_replace($search, $replace, number_format($row['dirty_price'], 2)) . "\t"; 
          $rowData .= str_replace($search, $replace, number_format($row['accur_rate'], 2)) . "\t"; 
          $rowData .= str_replace($search, $replace, number_format($row['ytm'], 2)) . "\t"; 
          $rowData .= str_replace($search, $replace, $row['order_type']) . "\t"; 
          $rowData .= str_replace($search, $replace, $row['order_date']) . "\t";
          $setData_1 .= trim($rowData) . "\n"; 
          
          $j++;
        }

        // Securely write to the file
        $filePath = '../../Settlement_Backup/Bond/Bond_Clearing_data_' . $tdate . '.xls';
        file_put_contents($filePath, ucwords($columnHeader) . "\n" . $setData . "\n" . ucwords($columnHeader_1) . "\n" . $setData_1 . "\n" . PHP_EOL, FILE_APPEND);
        
        echo '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Clearing  Successfully Completed.</div>';

    } catch (Exception $e) {
        $dbh->rollBack();
        // http_response_code(500);
        error_log("Bond settlement failed ==> " .$e->getMessage() . ", Line ==> " . $e->getLine());
        echo '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"> </i> Exception occurred. Contact RSEB support.</div>';
    }
}

// ---------------------------------------------------------------------------
// Helper: fetch lot_check for a pending order
// ---------------------------------------------------------------------------
function fetchLotCheck(PDO $dbh, int $id): int|false
{
    $stmt = $dbh->prepare("SELECT lot_check FROM bond_executed_orders WHERE id = :id AND status = 0");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['lot_check'] : false;
}

// ---------------------------------------------------------------------------
// Helper: compute new volumes and status after matching lots
//
// Returns ['vol_delta'        => int,   // amount actually settled
//          'pending_new'      => int,   // remaining pending_vol
//          'status'           => int,   // 1 = fully settled, 0 = partial
//          'l_check_remaining'=> int]
// ---------------------------------------------------------------------------
function computeSettlement(int $l_check, int $pending_vol): array
{
    if ($l_check >= $pending_vol) {
        // All pending lots matched (or more than enough)
        return [
            'vol_delta'         => $pending_vol,
            'pending_new'       => 0,
            'status'            => $l_check === $pending_vol ? 1 : 0,
            'l_check_remaining' => $l_check - $pending_vol,
        ];
    }

    // l_check < pending_vol: partial match
    return [
        'vol_delta'         => $l_check,
        'pending_new'       => $pending_vol - $l_check,
        'status'            => 1,
        'l_check_remaining' => 0,
    ];
}
?>