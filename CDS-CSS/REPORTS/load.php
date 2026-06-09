<?php
include('../FILES/sessionStartFile_cdscss.php');
include ('../../CONNECTIONS/db.php');

if (!empty($_POST["mat"])) {
    $cd = $_POST['mat'];

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, b.name 
        FROM client_account a 
        JOIN adm_institution b ON a.institution_id = b.institution_id 
        WHERE a.cd_code = :cd
    ");
    $wc->bindParam(':cd', $cd);
    $wc->execute();
    $state = $wc->fetch();
    if ($state) {
        echo '
        <div class="col-lg-6 col-md-6">
            <label>Details of Client</label>
            <input type="text" class="form-control" value="'.$state['f_name'].' '.$state['l_name'].' , ID# '.$state['ID'].'" readonly>
        </div>

        <div class="col-lg-6 col-md-6">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
            </div>
        </div>
        <script type="text/javascript">
            $("#accountAct").show();
        </script>';
    } else {
      echo '
        <div class="col-xs-4">
          <label>Details of Client</label>
          <input type="text"  class="form-control" style="color:red;" value="Invalid CD Code." readonly>
        </div>
        <script type="text/javascript">
            $("#details").hide();
            $("#accountAct").hide();
        </script>';
    }
}
elseif(!empty($_POST["symbol"])) {
    $symbol=$_POST['symbol'];
    $wc= $dbh->prepare("select symbol,name from symbol where symbol=:symbol ");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state=$wc->fetch();
    if ($wc->rowCount() > 0) {
    echo '      <div class="col-xs-4">
                  <label>Details of Symbol</label>
                  <input type="text" class="form-control" value="Symbol : '.$state['symbol'].' , Name : '.$state['name'].'" readonly>
                </div>
                <br><br> <br><br>
                <div class="col-xs-3">
                <label>From Date</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                </div>
                </div>
                <div class="col-xs-3">
                <label>To Date</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="to_date" id="to_date"  onChange="return checkDate();" required>
                </div>
                </div>';
      }
      else
      {
      echo '    <div class="col-xs-4">
                  <label>Details of Symbol</label>
                  <input type="text"  class="form-control" style="color:red;" value="Invalid symbol." readonly>
                </div>';
      }
}
elseif (!empty($_POST["symbolTV"])) {
    $symbol = $_POST['symbolTV'];

    $wc = $dbh->prepare("SELECT symbol, name FROM symbol WHERE symbol=:symbol AND status=1 ");
    $wc->bindParam(':symbol', $symbol);
    $wc->execute();
    $state = $wc->fetch();

    if($state) {
        echo'
        <div class="col-lg-4 col-md-4">
          <label>Details of Symbol</label>
          <input type="text" class="form-control" value="Symbol : '.$state['symbol'].' , Name : '.$state['name'].'" readonly>
        </div>
        <div class="col-lg-4 col-md-4">
          <label>Top</label>
          <input type="text" class="form-control" value="" id="top" name="top">
        </div>
        <script type="text/javascript">
            $("#tvl").show();
        </script>';
    } else {
        echo'
        <div class="col-lg-4 col-md-4">
            <label>Details of Symbol</label>
            <input type="text"  class="form-control" style="color:red;" value="Invalid symbol." readonly>
        </div>';
    }
    exit();
}
elseif(!empty($_POST["symbolForAnnouncement"])) 
{
    $symbol=$_POST['symbolForAnnouncement'];
    $wc= $dbh->prepare("select symbol,name from symbol where symbol=:symbol ");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
    echo '      <div class="col-xs-4">
                  <label>Details of Symbol</label>
                  <input type="text" class="form-control" value="Symbol : '.$state['symbol'].' , Name : '.$state['name'].'" readonly>
                </div>
                <br><br> <br><br>
                <div class="col-xs-3">
                <label>From Date</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                </div>
                </div>
                <div class="col-xs-3">
                <label>To Date</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="to_date_announcement" id="to_date_announcement"  onChange="return checkDate();" required>
                </div>
                </div>';
      }
      else
      {
      echo '    <div class="col-xs-4">
                  <label>Details of Symbol</label>
                  <input type="text"  class="form-control" style="color:red;" value="Invalid symbol." readonly>
                </div>';
      }
}
elseif (!empty($_POST["individual"])) {
    $cid = $_POST['individual'];

    $wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, a.name 
            FROM client_account c
            JOIN adm_institution a ON c.institution_id = a.institution_id 
            JOIN cds_holding h ON c.cd_code = h.cd_code
            WHERE (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) > 0 
            AND (c.ID = :cid OR c.cd_code= :cid)
            ORDER BY c.client_id DESC LIMIT 1
        ");
    $wc->bindParam(':cid', $cid);
    $wc->execute();
    $state = $wc->fetch();
    if($state) {
        echo'
            <div class="col-lg-8 col-md-8">
              <label>Client Details</label>
              <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
            </div>
            <script type="text/javascript">
                $("#individual").show();
            </script>
        ';
    } else {
        echo '
            <div class="col-lg-8 col-md-8">
              <label>Client Details</label>
              <input type="text" class="form-control" style="color:red;" value="Invalid CID / No Share Details" readonly>
            </div>';
    }
}
elseif (!empty($_POST["symbolNo"])) {
    $symbol = $_POST['symbolNo'];

    $wc = $dbh->prepare("SELECT symbol, name FROM symbol WHERE symbol=:symbol AND status = 1");
    $wc->bindParam(':symbol', $symbol);
    $wc->execute();
    $state = $wc->fetch();
    if($state) {
        echo'
        <div class="col-lg-6 col-md-6">
          <label>Details of Symbol</label>
          <input type="text" class="form-control" value="Symbol : '.$state['symbol'].' , Name : '.$state['name'].'" readonly>
        </div>
        <script type="text/javascript">
            $("#nos").show();
        </script>';
    } else {
        echo'
        <div class="col-lg-6 col-md-6">
            <label>Details of Symbol</label>
            <input type="text" class="form-control" style="color:red;" value="Invalid symbol." readonly>
        </div>
        <script type="text/javascript">
            $("#nos").hide();
        </script>';
    }
    exit();
}
elseif (!empty($_POST["symbolGSholdList"])) {
    $symbol = $_POST['symbolGSholdList'];

    $wc = $dbh->prepare("SELECT symbol, name FROM symbol WHERE symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    if($state) {
        echo '      
        <div class="col-lg-6 col-md-6">
            <label>Details of Symbol</label>
            <input type="text" class="form-control" value="Symbol : '.$state['symbol'].' , Name : '.$state['name'].'" readonly>
        </div>
        <script type="text/javascript">
            $("#gsh").show();
        </script>';
    } else {
        echo '
        <div class="col-lg-6 col-md-6">
            <label>Details of Symbol</label>
            <input type="text"  class="form-control" style="color:red;" value="Invalid symbol" readonly>
        </div>';
    }
    exit();
}
elseif (!empty($_POST["pledge"])) {
    $plContCode = $_POST['pledge'];

    $wc = $dbh->prepare("SELECT pledge_name from cds_pledge_contract where pledge_contract=:contrCode");
    $wc->bindParam(':contrCode',$plContCode);
    $wc->execute();
    $state = $wc->fetch();
    if($state) {
        echo'
        <div class="col-lg-10 col-md-10">
          <label>Details of Pledge</label>
          <input type="text" class="form-control" value="NAME :'.$state['pledge_name'].'" readonly>
        </div>
        <script type="text/javascript">
            $("#pledge").show();
        </script>';
    } else {
        echo '
        <div class="col-lg-10 col-md-10">
            <label>Details of Client</label>
            <input type="text"  class="form-control" style="color:red;" value="Invalid Pledge Contract Code." readonly>
        </div>';
    }
}
elseif (!empty($_POST["symbolPlType"])) {
    $symbol = $_POST['symbolPlType'];

    if ($symbol == 'S') {
        echo'
        <div class="col-lg-3 col-md-3">
            <label>Symbol</label>
            <input type="text" class="form-control"  name="symbol" id="symbol" onChange="getState2(this.value);" required>
        </div>
        <div id="symbolDetails"></div>';
    } else {
        echo'
        <div class="col-lg-3 col-md-3">
            <label>Pledgee</label>';
            $wc= $dbh->prepare("SELECT pledgee FROM cds_pledgee");
            $wc->execute();
            echo'
            <select name="symbol" id="symbol"  class="form-control"  onChange="getState3(this.value);">
                <option value="">--Select Pledgee--</option>';
                while($res= $wc->fetch()) {
                echo'<option value="'.$res['pledgee'].'">'.$res['pledgee'].'</option>';
            }
            echo'
            </select>
        </div>
        <div id="pledgeDetails"></div>'; 
    }
}
elseif (!empty($_POST["symbolPlDetails"])) {
    $symbol = $_POST['symbolPlDetails'];

    $wc = $dbh->prepare("SELECT symbol, name FROM symbol WHERE symbol = :symbol AND status = 1");
    $wc->bindParam(':symbol', $symbol);
    $wc->execute();
    $state = $wc->fetch();
    if($state) {
        echo'
        <div class="col-lg-6 col-md-6">
            <label>Details of Symbol</label>
            <input type="text" class="form-control" value="Symbol : '.$state['symbol'].' , Name : '.$state['name'].'" readonly>
        </div>
        <script type="text/javascript">
            $("#pldetails").show();
        </script>';
    } else {
        echo'
        <div class="col-lg-6 col-md-6">
            <label>Details of Symbol</label>
            <input type="text"  class="form-control" style="color:red;" value="Invalid symbol." readonly>
        </div>';
    }
}
elseif(!empty($_POST["plSybolDetails"])) 
{
    $pledgee=$_POST['plSybolDetails'];

       $pldgee= $dbh->prepare("select pledgee from cds_pledgee where pledgee=:pl");
       $pldgee->bindParam(':pl',$pledgee);
       $pldgee->execute();
       $state=$pldgee->fetch();
        if($pldgee->rowCount() > 0)
        {
        echo '      <div class="col-xs-6">
                      <label>Details of Pledgee</label>
                      <input type="text" class="form-control" value="Name : '.$state['pledgee'].'" readonly>
                    </div>';?>
                    <script type="text/javascript">
                    $("#pldetails").show();
                    </script>
          <?php
          }
          else
          {
          echo '    <div class="col-xs-4">
                      <label>Details of Pledgee</label>
                      <input type="text"  class="form-control" style="color:red;" value="Invalid Pledgee." readonly>
                    </div>';
          }
}
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["net_report"])) 
{
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $sec_type = $_POST['sec_type'];

    $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';

    echo'
    <div class="col-lg-12">
        <div class="box-body">';
            $query= $dbh->prepare("SELECT DISTINCT participant_code FROM {$table_name} WHERE order_date BETWEEN :fdate AND :tdate");
            $query->bindParam(':fdate', $fromDate);
            $query->bindParam(':tdate', $toDate);
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            echo 'Netting Position for trade <br> From : '.$fromDate.' - To : '.$toDate;
            foreach ($rows as $res) {
                echo "<br><br><b>MEMBER : ".$res['participant_code']."</b><br>";

                $executed_orders= $dbh->prepare("
                    SELECT DISTINCT a.symbol_id, b.symbol 
                    FROM {$table_name}  a
                    JOIN symbol b ON a.symbol_id = b.symbol_id
                    WHERE a.status = 0 AND a.participant_code = :pc
                ");
                $executed_orders->bindParam(':pc', $res['participant_code']);
                $executed_orders->execute();

                echo"
                <table class='table table-bordered'>
                    <thead>
                        <tr style='background-color:#333;color:#fff'>
                            <th>SN</th>
                            <th>VOL BUY</th>
                            <th>VOL SELL</th>
                            <th>AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>";
                    $i = 1;
                    $diff = 0;
                    foreach($executed_orders as $res1){
                        echo "
                        <tr>
                            <td>SYMBOL :".$res1['symbol']."</td>
                        </tr>";
                        $list_ord = $dbh->prepare("
                            SELECT sum(lot_size_execute) AS totlot, cast(avg(order_exe_price) AS decimal(13,2)) AS avgp 
                            FROM {$table_name}
                            WHERE status = 0 AND participant_code = :pc AND symbol_id = :syid AND side = 'B'
                        ");
                        $list_ord->bindParam(':pc', $res['participant_code']);
                        $list_ord->bindParam(':syid', $res1['symbol_id']);
                        $list_ord->execute();
                        $res2 = $list_ord->fetch();
                        $totbuyamt = $res2['avgp'] * $res2['totlot'];
                        echo'
                        <tr>
                            <td>'.$i++.'</td>
                            <td>'.$res2['totlot'].'</td>
                            <td>-</td>
                            <td>Nu. ('.number_format($totbuyamt, 2, ".", ",").')</td>
                        </tr>';

                        $list_ord = $dbh->prepare("
                            SELECT sum(lot_size_execute) AS totlots , cast(avg(order_exe_price) AS decimal(13,2)) AS avgps 
                            FROM {$table_name}  
                            WHERE status = 0 AND participant_code=:pc AND symbol_id = :syid AND side = 'S'
                        ");
                        $list_ord->bindParam(':pc', $res['participant_code']);
                        $list_ord->bindParam(':syid', $res1['symbol_id']);
                        $list_ord->execute();
                        $res2 = $list_ord->fetch(); 
                        $totsellamt = $res2['avgps'] * $res2['totlots'];
                        echo'
                        <tr>
                            <td>'.$i.'</td>
                            <td>-</td>
                            <td>'.$res2['totlots'].'</td>
                            <td>Nu. '.number_format($totsellamt, 2, ".", ",").'</td>
                        </tr>';
                  
                        $diff = -$totbuyamt + $totsellamt;
                    }

                    if($diff > 0){
                        $rm ="Receiveable";
                    } elseif ($diff<0) {
                        $rm = "Payable";
                    } elseif ($diff == 0) {
                        $rm = "None";
                    }
                    echo"
                    <tr>
                        <td><b>Difference<b></td>
                        <td></td>
                        <td><b>".$rm."</b></td>
                        <td><b>Nu. ".number_format($diff, 2, ".", ",")."</b></td>
                    </tr>
                </tbody>
            </table>";
        }
        echo '
        </div>
    </div>
    <div class="row no-print">
        <div class="col-xs-12">
        &emsp;&emsp;<a href="loadReportPrint.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&detailNetting=detailNetting&table_name='.$table_name.'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
    </div>
    <br>';
}
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["trade_details"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $sec_type = $_POST['sec_type'];

    $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';

    echo'
    <div class="col-lg-12">
        <div class="box-body">
            Summary of Trade<br> From : '.$fromDate.' - To : '.$toDate;
            $query = $dbh->prepare("SELECT DISTINCT participant_code FROM {$table_name} WHERE order_date BETWEEN :fdate AND :tdate");
            $query->bindParam(':fdate', $fromDate);
            $query->bindParam(':tdate', $toDate);
            $query->execute();
            foreach($query as $res) {
                echo "
                <br><br><b>MEMBER : ".$res['participant_code']."</b><br>
                <table class='table table-bordered'>
                    <thead>
                        <tr style='background-color:#333;color:#fff'>
                            <th>SN</th>
                            <th>ACCOUNT </th>
                            <th>SIDE/DATE</th>
                            <th>VOLUME</th>
                            <th>PRICE</th>
                            <th>AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>";
                    $i=1;
                    $executed_orders= $dbh->prepare("
                        SELECT DISTINCT a.symbol_id, b.symbol 
                        FROM {$table_name} a 
                        JOIN symbol b ON a.symbol_id = b.symbol_id
                        WHERE a.participant_code=:pc and  order_date BETWEEN :fdate AND :tdate
                    ");
                    $executed_orders->bindParam(':pc', $res['participant_code']);
                    $executed_orders->bindParam(':fdate', $fromDate);
                    $executed_orders->bindParam(':tdate', $toDate);
                    $executed_orders->execute();
                    foreach($executed_orders as $res1){
                        $list_ord = $dbh->prepare("
                            SELECT * FROM {$table_name} 
                            WHERE participant_code=:pc AND symbol_id=:syid  AND order_date BETWEEN :fdate AND :tdate
                        ");
                        $list_ord->bindParam(':pc', $res['participant_code']);
                        $list_ord->bindParam(':syid', $res1['symbol_id']);
                        $list_ord->bindParam(':fdate', $fromDate);
                        $list_ord->bindParam(':tdate', $toDate);
                        $list_ord->execute();
                        foreach($list_ord as $res3){
                            $amt = $res3['lot_size_execute'] * $res3['order_exe_price'];
                            echo'
                            <tr>
                                <td>'.$i++.' .'.$res1['symbol'].'</td>
                                <td>'.$res3['cd_code'].'</td>
                                <td>'.$res3['side'].' - '.$res3['order_date'].'</td>
                                <td>'.number_format($res3['lot_size_execute'],2,".",",").'</td>
                                <td>'.$res3['order_exe_price'].'</td>
                                <td>Nu. '.number_format($amt,2,".",",").'</td>
                            </tr>';
                        }
                        $list_ord = $dbh->prepare("
                            SELECT sum(lot_size_execute) AS totlot, cast(avg(order_exe_price) AS decimal(13,2)) AS avgp 
                            FROM {$table_name} 
                            WHERE participant_code=:pc AND symbol_id=:syid AND side='B' AND order_date BETWEEN :fdate AND :tdate
                        ");
                        $list_ord->bindParam(':pc',$res['participant_code']);
                        $list_ord->bindParam(':syid',$res1['symbol_id']);
                        $list_ord->bindParam(':fdate',$fromDate);
                        $list_ord->bindParam(':tdate',$toDate);
                        $list_ord->execute();
                        $res2 = $list_ord->fetch();
                        $totbuyamt = $res2['avgp'] * $res2['totlot'];

                        $list_ord= $dbh->prepare("
                            SELECT sum(lot_size_execute) as totlots , cast(avg(order_exe_price) as decimal(13,2)) as avgps 
                            FROM {$table_name} 
                            WHERE participant_code=:pc and symbol_id=:syid and side='S' and  order_date BETWEEN :fdate AND :tdate
                        ");
                        $list_ord->bindParam(':pc',$res['participant_code']);
                        $list_ord->bindParam(':syid',$res1['symbol_id']);
                        $list_ord->bindParam(':fdate',$fromDate);
                        $list_ord->bindParam(':tdate',$toDate);
                        $list_ord->execute();
                        $res4 = $list_ord->fetch(); 
                        $totsellamt = $res4['avgps'] * $res4['totlots'];  
                        echo'
                        <tr style="font-weight: bold;">
                            <td>Total :</td>
                            <td> Buy Vol : '.number_format(isset($res2['totlot']) ? $res2['totlot'] : 0, 0, ".", ",").'</td>
                            <td> Sell Vol : '.number_format(isset($res2['totlots']) ? $res2['totlots'] : 0, 0, ".", ",").'</td>
                            <td></td>
                            <td>Total Trade</td>
                            <td>Nu. '.number_format($totsellamt,2,".",",").'</td>
                        </tr>';    
                    }
                    echo"
                    </tbody>
                </table>";
            }
            echo'
            </div>
        </div>
        <div class="row no-print">
            <div class="col-xs-12">
            &emsp;&emsp;<a href="loadReportPrint.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&tradeDetails=tradeDetails&table_name='.$table_name.'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
        </div>
        <br>';
} 
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["trade_detailss"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $symbol_id = !empty($_POST['symbol_id']) ? $_POST['symbol_id'] : 0;
    $sec_type = $_POST['sec_type'];

    $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';
    $trade_type = ($sec_type === 'OS') ? 'Equity' : 'Bond';
    echo'
    <div class="col-xs-12">
        <div class="box-body">
            Summary of '.$trade_type.' Trade<br> From : '.$fromDate.' - To : '.$toDate;
            echo"
            <table class='table table-bordered'>
                <thead>
                    <tr style='background-color:#333;color:#fff'>
                        <th>#</th>
                        <th>MEMBER BROKER</th>
                        <th>TRADING PLATFORM</th>
                        <th>CD CODE</th>
                        <th>BUY</th>
                        <th>SELL</th>
                        <th>ORDER EXECUTED PRICE</th>
                        <th>AMOUNT</th>
                        <th>SYMBOL</th>
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody>";
                $i = 1;
                $query = "SELECT e.member_broker, e.sub_user, e.cd_code, if(e.side = 'B', e.lot_size_execute, 0) as BUY, if(e.side = 'S', e.lot_size_execute, 0) as SELL, e.order_exe_price, e.lot_size_execute * e.order_exe_price as amount, s.symbol, e.order_date 
                    FROM {$table_name} e 
                    JOIN symbol s ON e.symbol_id = s.symbol_id 
                    WHERE order_date BETWEEN :fdate AND :tdate 
                ";
                if ($symbol_id != 0) {
                    $query .= " AND e.symbol_id=:sym_id ";
                }
                $query .= " ORDER BY e.member_broker ASC";

                $executed_orders = $dbh->prepare($query);
                $executed_orders->bindParam(':fdate', $fromDate);
                $executed_orders->bindParam(':tdate', $toDate);
                if ($symbol_id != 0) {
                    $executed_orders->bindParam(':sym_id', $symbol_id);
                }
                $executed_orders->execute();
                foreach ($executed_orders as $res1) {
                    $platform = (strlen($res1['sub_user']) == 18) ? 'MCAMS' : 'BROKER';
                    echo'
                    <tr>
                        <td>'.$i.'</td>
                        <td>'.$res1['member_broker'].'</td>
                        <td>'.$platform.'</td>
                        <td>'.$res1['cd_code'].'</td>
                        <td>'.$res1['BUY'].'</td>
                        <td>'.$res1['SELL'].'</td>
                        <td>'.$res1['order_exe_price'].'</td>
                        <td>'.$res1['amount'].'</td>
                        <td>'.$res1['symbol'].'</td>
                        <td>'.$res1['order_date'].'</td>
                    </tr>';
                    $i++;
                }
                echo'
                </tbody>
            </table>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-xs-12">
        &emsp;&emsp;<a href="loadReport.php?zge_export=zge_export&fromDate='.$fromDate.'&toDate='.$toDate.'&symbol_id='.$symbol_id.'&table_name='.$table_name.'&trade_type='.$trade_type.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
    </div>
    <br>';
}
else if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["Clearing"])) 
{
        $fromDate = $_POST['fromDate1'].' 00:00:00';
        $toDate = $_POST['toDate1'].' 23:59:00';
        $sec_type = $_POST['sec_type'];
        
        $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';
        $text_sym = ($sec_type === 'OS') ? 'Equity' : 'Bond';
        $price_col = ($sec_type === 'OS') ? 'order_exe_price' : 'dirty_price';
        echo'
        <div class="col-lg-12">
            <div class="box-body">';
                $query = $dbh->prepare("
                    SELECT DISTINCT a.participant_code, b.clearing_account
                    FROM {$table_name} a
                    INNER JOIN adm_participants b ON a.participant_code = b.participant_code
                    WHERE a.order_date BETWEEN ? AND ?
                ");
                $query->execute([$fromDate, $toDate]);
                $results = $query->fetchAll(PDO::FETCH_ASSOC);
                echo 'Clearing Instruction For '.$text_sym.' Trade <br> From : '.$fromDate.' - To : '.$toDate;
                $i = 1;
                foreach ($results as $res) {
                    $totalb = 0;
                    $totals = 0;
                    echo"
                    <br><br><b>MEMBER : ".$res['participant_code']."</b><br>
                    <table class='table table'>
                        <thead>
                            <tr style='background-color:#333; color:#fff'>
                                <th>SN</th>
                                <th>REMARKS</th>
                                <th></th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>";

                        $stmt = $dbh->prepare("
                            SELECT SUM(lot_size_execute * {$price_col}) AS total_buy_amt 
                            FROM {$table_name} 
                            WHERE status = 0 AND participant_code = ? AND side = 'B' AND order_date BETWEEN ? AND ?
                        ");
                        $stmt->execute([$res['participant_code'], $fromDate, $toDate]);
                        $total_buy_amt = $stmt->fetchColumn();
                        $totalb = isset($total_buy_amt) ? $total_buy_amt : 0;
                        echo'
                        <tr>
                            <td>'.$i++.'</td>
                            <td>Total buy amount</td>
                            <td></td>
                            <td>Nu. ('.number_format($totalb, 2, ".",",").')</td>
                        </tr>';

                        $stmt1 = $dbh->prepare("
                            SELECT SUM(lot_size_execute * {$price_col}) AS total_sell_amt 
                            FROM {$table_name} 
                            WHERE status = 0 AND participant_code = ? AND side = 'S' AND order_date BETWEEN ? AND ?
                        ");
                        $stmt1->execute([$res['participant_code'], $fromDate, $toDate]);
                        $total_sell_amt = $stmt1->fetchColumn();
                        $totals = isset($total_sell_amt) ? $total_sell_amt : 0;
                        echo'
                        <tr>
                            <td>'.$i++.'</td>
                            <td>Total sell amount</td>
                            <td></td>
                            <td>Nu. '.number_format($totals, 2,".",",").'</td>
                        </tr>';

                        $diff = $totals - $totalb;

                        if ($diff != 0) {
                            $isCredit = $diff > 0;
                            $rm = $isCredit ? "<span style='color:green;'>CREDIT (Pay)</span>" : "<span style='color:red;'>DEBIT(Collect)</span>";
                            $amount = $isCredit ? $diff : -$diff;
                            $formattedAmount = number_format($amount, 2, ".", ",");
                            echo "
                            <tr>
                                <td><b>Instruction : {$rm}</b></td>
                                <td><b> Account # : {$res['clearing_account']}</b></td>
                                <td></td>
                                <td><b>Nu. {$formattedAmount}</b></td>
                            </tr>";
                        } else {
                            echo "
                            <tr>
                                <td><b>Instruction : None</b></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>";
                        }
                        echo"
                        </tbody>
                    </table>";
                }
            echo '
            </div>
        </div>
        <div class="row no-print">
            <div class="col-lg-12">
                <div class="col-lg-6 text-left">
                    &emsp;<a href="loadReportPrint.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&Clearing=Clearing&sec_type='.$sec_type.'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
                </div>
                <div class="col-lg-6 text-right">
                    <button class="btn btn-success" onclick="sendClearingReport(\''.$fromDate.'\', \''.$toDate.'\', \''.$sec_type.'\')"><i class="fa fa-envelope"></i> Send Clearing Detail Report to Brokers</button>
                </div>
            </div>
        </div>
        <br>

        <script type="text/javascript">
          function sendClearingReport(from_date, to_date, sec_type) {
            if (confirm("Do you want to continue?")) {
              showLoading();
              var op = "sendClearingReportViaMail";
              $.ajax({
                type: "POST",
                url: "load.php",
                data: "from_date=" + from_date + "&to_date=" + to_date + "&sendClearingReportViaMail=" + op + "&sec_type=" + sec_type,
                success: function(data){
                hideloading();
                alert(data);
              }
            });
          } else {
            return false;
          }
        }
        </script>';
}
elseif (!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["reportType"])) {
    $brokerId = isset($_POST['broker']) ? $_POST['broker'] : 0;
    $fromDate = $_POST['fromDate'] . ' 00:00:00';
    $toDate = $_POST['toDate'] . ' 23:59:59';
    $reportType = $_POST['reportType'];
    $sec_type = $_POST['sec_type'];

    $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';
    $trade_type = ($sec_type === 'OS') ? 'Equity' : 'Bond';
    $price_col = ($sec_type === 'OS') ? 'order_exe_price' : 'dirty_price';
    
    echo '
    <div class="col-lg-12">
        <div class="box-body">';
        $query = $dbh->prepare("
            SELECT DISTINCT a.participant_code, b.clearing_account
            FROM {$table_name} a
            INNER JOIN adm_participants b ON a.participant_code = b.participant_code
            WHERE a.order_date BETWEEN ? AND ? AND a.participant_code = ?");
        
        if (!$query->execute([$fromDate, $toDate, $brokerId])) {
            error_log("Error executing participant query: " . implode(" ", $query->errorInfo()));
        }

        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        if (empty($results)) {
            error_log("No results found for participant code: " . $brokerId);
        } else {
            echo 'Clearing Instruction For '.$trade_type.' Trade <br> From : ' . $fromDate . ' - To : ' . $toDate;

            $i = 1;
            foreach ($results as $res) {
                $stmt = $dbh->prepare("
                    SELECT SUM(lot_size_execute * {$price_col}) AS total_buy_amt 
                    FROM {$table_name} WHERE status IN (0,1) AND participant_code = ? AND side = 'B' AND order_date BETWEEN ? AND ?
                ");
                
                if (!$stmt->execute([$res['participant_code'], $fromDate, $toDate])) {
                    error_log("Error executing buy query: " . implode(" ", $stmt->errorInfo()));
                }

                $total_buy_amt = $stmt->fetchColumn() ?: 0;

                $stmt1 = $dbh->prepare("
                    SELECT SUM(lot_size_execute * {$price_col}) AS total_sell_amt 
                    FROM {$table_name} WHERE status IN (0,1) AND participant_code = ? AND side = 'S' AND order_date BETWEEN ? AND ?
                ");
                
                if (!$stmt1->execute([$res['participant_code'], $fromDate, $toDate])) {
                    error_log("Error executing sell query: " . implode(" ", $stmt1->errorInfo()));
                }

                $total_sell_amt = $stmt1->fetchColumn() ?: 0;

                $diff = $total_sell_amt - $total_buy_amt;

                $isCredit = $diff > 0;
                $rm = $isCredit ? "<span style='color:green;'>CREDIT (Pay)</span>" : "<span style='color:red;'>DEBIT (Collect)</span>";
                $formattedAmount = number_format(abs($diff), 2, ".", ",");

                echo "<br><br><b>MEMBER : " . $res['participant_code'] . "</b><br>
                <table class='table table'>
                    <thead>
                        <tr style='background-color:#333; color:#fff'>
                            <th>SN</th>
                            <th>REMARKS</th>
                            <th></th>
                            <th>AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>" . $i++ . "</td><td>Total buy amount</td><td></td><td>Nu. (" . number_format($total_buy_amt, 2, ".", ",") . ")</td></tr>
                        <tr><td>" . $i++ . "</td><td>Total sell amount</td><td></td><td>Nu. " . number_format($total_sell_amt, 2, ".", ",") . "</td></tr>
                        <tr>
                            <td><b>Instruction : " . ($diff != 0 ? $rm : "None") . "</b></td>
                            <td><b>Account # : " . $res['clearing_account'] . "</b></td>
                            <td></td>
                            <td><b>Nu. " . ($diff != 0 ? $formattedAmount : "") . "</b></td>
                        </tr>
                    </tbody>
                </table>";
            }
        }
    echo'</div>
    </div>';
    exit();
}
elseif (isset($_POST['sendClearingReportViaMail']) && isset($_POST['from_date']) && isset($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $sec_type = $_POST['sec_type'];

    $trade_date = date("d_M_Y", strtotime($from_date));

    $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';
    $trade_type = ($sec_type === 'OS') ? 'Equity' : 'Bond';
    $price_col = ($sec_type === 'OS') ? 'order_exe_price' : 'dirty_price';

    include('clearing_mail.php');

    die();
}
elseif(!empty($_POST["entel_load_report"])) 
{
    $symbol_id = $_POST['entel_load_report'];
    $current_date = date("Y-m-d");

    $wc= $dbh->prepare("SELECT a.corp_announcement_id, a.announcement_type, a.record_date, a.ex_date, a.announcement_date, a.rate, a.type, b.symbol, b.symbol_id 
        FROM corporate_announcement a 
        JOIN symbol b ON a.symbol_id = b.symbol_id
        where a.symbol_id=:symbol_id AND a.status = 0 AND a.record_date <= :cur_date
    ");
    $wc->bindParam(':symbol_id', $symbol_id);
    $wc->bindParam(':cur_date', $current_date);
    $wc->execute();
    if($wc->rowCount() > 0) {
       $i = 1;
        echo"
        <br>
        <div class='col-lg-12'>
            <table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Symbol</th>
                        <th>Type</th>
                        <th>Record Date</th>
                        <th>Announcement Date</th>
                        <th>Rate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";
                while ($state = $wc->fetch()) {
                    switch ($state['announcement_type']) {
                        case "1":
                            $corporate_name = 'RIGHTS';
                            break;
                        case "2":
                            $corporate_name = 'BONUS';
                            break;
                        case "3":
                            $corporate_name = 'DIVIDEND';
                            break;
                        default:
                            $corporate_name = 'BUYBACK';
                            break;
                    }
                    echo"
                    <tr>
                        <td>".$i."</td>
                        <td>".$state['symbol']."</td>
                        <td>".$state['type'].'-'.$corporate_name."</td>
                        <td>".$state['record_date']."</td>
                        <td>".$state['announcement_date']."</td>
                        <td>".number_format($state['rate'], 2)."</td>
                        <td>
                            <form action='generate_entitelment.php' method='post'>
                                <input type='hidden' id='atype' name='atype' value=".$state['announcement_type'].">
                                <button type='submit' class='btn btn-primary' style='display:block;' onclick='return gefun(".$i.");' name='gen_ent' value=".$state['corp_announcement_id']."> GENERATE
                            </form>
                        </td>
                    </tr>";
                    $i++;
                }
                echo"
                </tbody>
            </table>
        </div>";    
    } else {
        echo '
        <br>
        <div class="col-lg-12 col-xs-12">
            <div class="alert alert-info alert-dismissible"><button type="button" class="close"data-dismiss=" alert"aria-hidden="true">&times;</button><i class="icon fa fa-cross"></i> No Corporate Announcement.
            </div>
        </div>';
    }
}
elseif (!empty($_POST["onlineTerminal_emd_details"])) {
    $fromDate = $_POST['fromDate'].' 00:00:00';
    $toDate = $_POST['toDate'].' 23:59:00';
        echo'
        <div class="col-lg-12">
            <div class="box-body">';
            echo 'Summary of EMD<br> From : <b>'.$fromDate.'</b> - To : <b>'.$toDate.'</b>
            <table class="table table-bordered" id="user_table_id">
                <thead>
                    <tr style="background-color:#333;color:#fff">
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">CID No</th>
                        <th scope="col">CD Code</th>
                        <th scope="col">Phone</th>
                        <th class="text-center" scope="col"> Amount</th>
                        <th class="text-center" scope="col"> GST</th>
                        <th scope="col">Order No</th>
                        <th scope="col">Date</th>
                    </tr>
                    </thead>
                    <tbody>';
                    $i = 1;
                    $sql = $dbh->prepare("SELECT e.order_no, e.name, e.cd_code, e.cid, e.phone, e.fee_status, e.email, e.created_date, e.app_fee, e.gst 
                        FROM emd e 
                        WHERE e.fee_status = 1 AND e.created_date BETWEEN :fdate AND :tdate 
                        GROUP BY e.order_no
                    ");
                    $sql->bindParam(':fdate', $fromDate);
                    $sql->bindParam(':tdate', $toDate);
                    $sql->execute();
                    foreach ($sql as $res) {
                    echo'
                    <tr>
                        <td>'.$i.'</td>
                        <td>'.$res['name'].'</td>
                        <td>'.$res['cid'].'</td>
                        <td>'.$res['cd_code'].'</td>
                        <td>'.$res['phone'].'</td>
                        <td class="text-center">'.$res['app_fee'].'</td>
                        <td class="text-center">'.$res['gst'].'</td>
                        <td>'.$res['order_no'].'</td>
                        <td>'.$res['created_date'].'</td>
                    </tr>';
                    $i++;
                    }
                echo'
                </tbody>
            </table>';
            echo '
        </div>
    </div>
    <div class="row no-print">
        <div class="col-xs-12">
        &emsp;&emsp;<a href="loadReport.php?emd_export=emd_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
    </div>
    <br>
    <script type="text/javascript">
    $( document ).ready(function() {
      $("#user_table_id").DataTable({
         "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]
      });
    });
  </script>';
}
elseif (!empty($_POST["get_dtls_cid"])) {
    $cid = $_POST['get_dtls_cid'];

    $wc = $dbh->prepare("SELECT 
                CASE
                    WHEN a.acc_type = 'I' THEN CONCAT(a.f_name,' ', COALESCE(a.l_name, ''))
                    ELSE a.f_name
                END AS full_name, a.acc_type, a.ID, a.phone, a.email
            FROM client_account a
            WHERE a.ID = ?
            ORDER BY a.client_id DESC LIMIT 1
        ");
    $wc->bindParam(1, $cid);
    $wc->execute();
    $rows = $wc->fetch();
    if($rows) {
        $details = 'Name : '.$rows['full_name'].', CID/DISN : '.$rows['ID'].', Phone : '.$rows['phone'];
        echo'
            <div class="col-lg-6 col-md-6">
              <label>Client Details</label>
              <input type="text" class="form-control" value="'.$details.'" readonly>
            </div>
            <div class="col-lg-6 col-md-6">
              <label>From Date</label>
              <input type="date" class="form-control" name="from_date" id="from_date" required>
            </div>
            <div class="col-lg-6 col-md-6">
              <label>To Date</label>
              <input type="date" class="form-control" name="to_date" id="to_date" required>
            </div>
            
            <script type="text/javascript">
                $("#generate_button").show();
            </script>
        ';
    } else {
        echo '
            <div class="col-lg-8 col-md-8">
              <label>Client Details</label>
              <input type="text" class="form-control" style="color:red;" value="Invalid CID." readonly>
            </div>';
    }
}
elseif (isset($_POST['getRecordDateList'])) {
    $syml_id = $_POST['symbol_id'];

    $stmt = $dbh->prepare("SELECT a.corp_announcement_id, a.record_date
            FROM corporate_announcement a 
            WHERE a.symbol_id = ?
            AND a.announcement_type = 1
            ORDER BY a.corp_announcement_id DESC
    ");
    $stmt->bindParam(1, $syml_id);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo'<option value="">--Select--</option>';
    foreach ($rows as $key => $value) {
        echo'<option value="'.$value['corp_announcement_id'].'">'.$value['record_date'].'</option>';
    }

    die();
}
elseif (isset($_POST['get_pledge_report_rma'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    echo'
    <div class="col-lg-12">
        <h4 class="text-center"><strong>Pledege Report</strong>, Date : <b>'.$from_date.'</b> To <b>'.$to_date.'</b></h4><hr>
        <div class="box-body">';
            echo'
            <table id="table_pledge_id" class="table table-striped table-bordered" width="100%">
              <thead>
                <tr>
                  <th scope="col"></th>
                  <th scope="col">Symbol</th>
                  <th scope="col">CD Code</th>
                  <th scope="col">Pledge Volume</th>
                  <th scope="col">Market Price</th>
                  <th scope="col">Pledgee</th>
                  <th scope="col">Remarks</th>
                  <th scope="col">Value</th>
                  <th scope="col">Fee Collected</th>
                  <th scope="col">Date</th>
                </tr>
                </thead>
                <tbody>';
                $stmt = $dbh->prepare("SELECT s.symbol, c.cd_code, c.pledge_volume, p.market_price, c.pledgee, c.remarks, c.pledge_date, (c.pledge_volume * p.market_price) AS value, (c.pledge_volume * p.market_price * 0.0003) AS fee_collected
                    FROM cds_pledge c 
                    LEFT JOIN symbol s ON c.symbol_id = s.symbol_id
                    LEFT JOIN market_price p ON c.symbol_id = p.symbol_id
                    WHERE 
                        DATE(c.pledge_date) BETWEEN ? AND ?
                        AND c.pledge_volume > 0
                ");
                $stmt->bindParam(1, $from_date);
                $stmt->bindParam(2, $to_date);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $i = 1;
                foreach ($rows as $key => $value) {
                    echo'
                    <tr>
                      <td>'.$i.'</td>
                      <td>'.$value['symbol'].'</td>
                      <td>'.$value['cd_code'].'</td>
                      <td>'.$value['pledge_volume'].'</td>
                      <td>'.$value['market_price'].'</td>
                      <td>'.$value['pledgee'].'</td>
                      <td>'.$value['remarks'].'</td>
                      <td>'.$value['value'].'</td>
                      <td>'.$value['fee_collected'].'</td>
                      <td>'.$value['pledge_date'].'</td>
                    </tr>';
                    $i++;
                }
                echo'
                </tbody>
            </table>
            <div class="col-lg-6 text-left">
                <a href="load.php?generate_pledge_report_excel=generate_pledge_report_excel&from_date='.$from_date.'&to_date='.$to_date.'" class="btn btn-success"><i class="fa fa-save"></i> Generate Excel</a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#table_pledge_id").DataTable();
        });
    </script>';
    die();
}
elseif(!empty($_GET['generate_pledge_report_excel'])) {
    $replace   = array("\n");
    $search  = array('');

    $fromDate = $_GET['from_date'];
    $toDate = $_GET['to_date'];

    $wc = $dbh->prepare("SELECT s.symbol, c.cd_code, c.pledge_volume, p.market_price, c.pledgee, c.remarks, c.pledge_date, (c.pledge_volume * p.market_price) AS value, (c.pledge_volume * p.market_price * 0.0003) AS fee_collected
        FROM cds_pledge c 
        LEFT JOIN symbol s ON c.symbol_id = s.symbol_id
        LEFT JOIN market_price p ON c.symbol_id = p.symbol_id
        WHERE 
            DATE(c.pledge_date) BETWEEN ? AND ?
            AND c.pledge_volume > 0
    ");
    $wc->bindParam(1, $fromDate);
    $wc->bindParam(2, $toDate);
    $wc->execute(); 

    $columnHeader = "SlNo\t Symbol\t CD Code\t Pledge Volume\t Market Price\t Pledgee\t Remarks\t Value\t Fee Collected\t Date\t"; 
    $setData = '';
    $i = 1;
    while ($rec=$wc->fetch()) { 
        if($wc->rowCount() <= 0) 
        {}
        $rowData = '';  
        $value = $i++."\t"
            .str_replace($search,$replace,$rec['symbol'])."\t"
            .str_replace($search,$replace,$rec['cd_code'])."\t"
            .str_replace($search,$replace,$rec['pledge_volume'])."\t"
            .str_replace($search,$replace,$rec['market_price'])."\t"
            .str_replace($search,$replace,$rec['pledgee'])."\t"
            .str_replace($search,$replace,$rec['remarks'])."\t"
            .str_replace($search,$replace,$rec['value'])."\t"
            .str_replace($search,$replace,$rec['fee_collected'])."\t"
            .str_replace($search,$replace,$rec['pledge_date'])."\t"
        ;
        $rowData .= $value;  
        $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=pledge_report.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 

    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
    die();
}
elseif (!empty($_POST["escorReport"])) {
    $company_name = $_POST['company_name'];

    // Use a single query to fetch company name
    $checkQuery = $dbh->prepare('SELECT company_name FROM unclaimed_dividend WHERE (company_name = :com_name OR :com_name = "ALL") LIMIT 1');
    $checkQuery->execute([':com_name' => $company_name]);
    $companyData = $checkQuery->fetch(PDO::FETCH_ASSOC);

    if ($companyData) {
        // Header Display
        echo sprintf('<div class="row">
                        <div class="text-center col-xs-12">
                            <h4><b>Escrow Account Details For %s</b></h4>
                        </div>
                    </div><br><br>', $company_name !== 'ALL' ? ' &nbsp;' . $companyData['company_name'] : '');

        // Main Query - Modified to get distinct CIDs from unclaimed_dividend first
        $mainQuery = $dbh->prepare('SELECT 
                ud.CID,
                ud.cd_code,
                ud.name,
                (SELECT COALESCE(SUM(ud.amount), 0) 
                 FROM uc_payment up 
                 WHERE up.cid_no = ud.CID) AS payable_amount,
                ud.company_name,
                (SELECT COALESCE(SUM(up.amount), 0) 
                 FROM uc_payment up 
                 WHERE up.cid_no = ud.CID) AS paid_amount,
                (SELECT up.account_number 
                 FROM uc_payment up 
                 WHERE up.cid_no = ud.CID 
                 ORDER BY up.payment_date DESC 
                 LIMIT 1) AS account_number,
                (SELECT up.payment_date 
                 FROM uc_payment up 
                 WHERE up.cid_no = ud.CID 
                 ORDER BY up.payment_date DESC 
                 LIMIT 1) AS payment_date
            FROM unclaimed_dividend ud
            WHERE (:com_name = "ALL" OR ud.company_name = :com_name)
            GROUP BY ud.CID');
        $mainQuery->execute([':com_name' => $company_name]);

        // Table Output
        echo '<div class="col-xs-12 table-responsive">
                <table id="example1" class="table">
                    <thead>
                        <tr style="background-color:#333;color:#fff">
                            <th>CD CODE</th>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>ACC. NO</th>
                            <th>PAYABLE AMOUNT</th>
                            <th>PAID AMOUNT</th>
                            <th>BALANCE AMOUNT</th>
                            <th>PAYMENT DATE</th>
                        </tr>
                    </thead>
                    <tbody>';

        $totals = ['payable' => 0, 'paid' => 0, 'balance' => 0];
        
        while ($record = $mainQuery->fetch(PDO::FETCH_ASSOC)) {
            $payable = (float)$record['payable_amount'];
            $paid = (float)$record['paid_amount'];
            $balance = $payable - $paid;

            $totals['payable'] += $payable;
            $totals['paid'] += $paid;
            $totals['balance'] += $balance;

            echo sprintf('<tr>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>Nu. %.2f</td>
                            <td>Nu. %.2f</td>
                            <td>Nu. %.2f</td>
                            <td>%s</td>
                          </tr>',
                          $record['cd_code'], $record['CID'], $record['name'], 
                          $record['account_number'] ? $record['account_number'] : 'N/A',
                          $payable, $paid, $balance, 
                          $record['payment_date'] ? $record['payment_date'] : 'N/A');
        }

        // Totals Display
        echo sprintf('</tbody></table></div>
                      <div class="row">
                          <div class="col-xs-12">
                              <b>TOTAL PAYABLE AMOUNT &nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;&nbsp;Nu. %.2f
                              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              TOTAL PAID AMOUNT &nbsp;&nbsp;&nbsp;&nbsp;: &nbsp;&nbsp;&nbsp;&nbsp;Nu. %.2f
                              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              TOTAL BALANCE AMOUNT &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp; Nu. %.2f</b>
                          </div>
                      </div><br><br>
                      <div class="row no-print">
                          <form action="generate_entitelment.php" method="post">
                              <input type="hidden" name="company_name" value="%s">
                              <button type="submit" class="btn btn-primary" name="download_escrow_csv">EXCEL</button>
                          </form>
                      </div>',
                      $totals['payable'], $totals['paid'], $totals['balance'], htmlspecialchars($company_name, ENT_QUOTES, "UTF-8"));
    } else {
        // No data available
        echo '<div class="alert alert-warning">No data found for the selected criteria</div>';
    }
}
elseif (isset($_POST['get_symbol_name'])) {
    $symbol_id = $_POST['symbol_id'];

    $stmt = $dbh->prepare("SELECT name FROM symbol WHERE symbol_id = ?");
    $stmt->execute([$symbol_id]);
    $name = $stmt->fetchColumn();
    echo'
        <label>Symbol Name</label>
        <input type="text" class="form-control" name="symbol" id="symbol" value="'.$name.'" readonly>
    ';
    exit;
}
elseif (!empty($_POST["generate_mcmas_wallet"])) {
        $fromDate = $_POST['fromDate'].' 00:00:00';
        $toDate = $_POST['toDate'].' 23:59:00';
        $report_type = $_POST['report_ty'];
        $cd_code = ($report_type == 'A') ? $_POST['cd_code'] : '';
        
        echo'
        <div class="col-lg-12">
            <div class="box-body">
            Summary of BLA mCaMS<br> From : <b>'.$fromDate.'</b> - To : <b>'.$toDate.'</b>
            <table class="table table-bordered table-striped" id="cams_wallet_id">
                <thead>
                    <tr style="background-color:#333;color:#fff">
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">CID No</th>
                        <th scope="col">CD Code</th>
                        <th class="text-center" scope="col"> Amount</th>
                        <th scope="col">Type</th>
                        <th scope="col">Flag</th>
                        <th scope="col">Date</th>
                    </tr>
                    </thead>
                    <tbody>';
                    $i = 1;
                    $total_balance = 0;
                    
                    $sql = "SELECT a.f_name, a.l_name, a.ID, m.cd_code, m.cid, m.amount, m.trx_time, m.created_Date, m.`type`, m.paid_to_user 
                                FROM mcams_wallet m 
                                LEFT JOIN client_account a ON m.cd_code = a.cd_code
                                WHERE m.created_Date BETWEEN ? AND ?";
                    $params = [$fromDate, $toDate];

                    if ($report_type == 'A') {
                        $sql .= " AND m.cd_code = ?";
                        $params[] = $cd_code;
                    }
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute($params);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($rows as $res) {
                        $total_balance += $res['amount'];
                        echo'
                        <tr>
                            <td>' . $i . '</td>
                            <td>' . $res['f_name'] . ' ' . $res['l_name'] . '</td>
                            <td>' . $res['ID'] . '</td>
                            <td>' . $res['cd_code'] . '</td>
                            <td class="text-center">' . $res['amount'] . '</td>
                            <td>' . $res['type'] . '</td>
                            <td>' . $res['paid_to_user'] . '</td>
                            <td>' . $res['created_Date'] . '</td>
                        </tr>';
                        $i++;
                    }
                echo'
                </tbody>
            </table>
        </div>
        <div class="col-lg-12">
            <strong>mCaMS Wallet Balance => ' . number_format($total_balance, 2) . '</strong>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-xs-12">
        &emsp;&emsp;<a href="load.php?export_mcams_wallet=export_mcams_wallet&fromDate='.$fromDate.'&toDate='.$toDate.'&report_type='.$report_type.'&cd_code='.$cd_code.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
    </div>
    <br>
    <script type="text/javascript">
    $( document ).ready(function() {
      $("#cams_wallet_id").DataTable({
         "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]
      });
    });
  </script>';
}
elseif(!empty($_GET['export_mcams_wallet'])) {
    $replace   = array("\n");
    $search  = array('');

    $fromDate = $_GET['fromDate'];
    $toDate = $_GET['toDate'];
    $report_type = $_GET['report_type'];
    $cd_code = $_GET['cd_code'];

    $filename = 'mcams_wallet';

    $sql = "SELECT a.f_name, a.l_name, a.ID, m.cd_code, m.cid, m.amount, m.trx_time, m.created_Date, m.`type`, m.paid_to_user 
                FROM mcams_wallet m 
                LEFT JOIN client_account a ON m.cd_code = a.cd_code
                WHERE m.created_Date BETWEEN ? AND ?";
    $params = [$fromDate, $toDate];

    if ($report_type == 'A') {
        $sql .= " AND m.cd_code = ?";
        $params[] = $cd_code;

        $filename = 'mcams_wallet_' . $cd_code;
    }
    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);

    $columnHeader = "SlNo\t Name\t CID\t CD Code\t Amount\t Type\t Flag\t Date\t"; 
    $setData = '';
    $i = 1;
    while ($rec=$stmt->fetch()) { 
        if($stmt->rowCount() <= 0) 
        {}
        $fl_name = $rec['f_name'] . ' ' . $rec['l_name'];
        $rowData = '';
        $value = $i++."\t"
            .str_replace($search,$replace,$fl_name)."\t"
            .str_replace($search,$replace,$rec['ID'])."\t"
            .str_replace($search,$replace,$rec['cd_code'])."\t"
            .str_replace($search,$replace,$rec['amount'])."\t"
            .str_replace($search,$replace,$rec['type'])."\t"
            .str_replace($search,$replace,$rec['paid_to_user'])."\t"
            .str_replace($search,$replace,$rec['created_Date'])."\t"
        ;
        $rowData .= $value;  
        $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=". $filename .".xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 

    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
    die();
}
elseif (!empty($_POST["generate_wallet_balance"])) {
        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];
        
        echo'
        <div class="col-lg-12">
            <div class="box-body">
            Summary of BLA mCaMS Wallet Balance<br> From : <b>'.$fromDate.'</b> - To : <b>'.$toDate.'</b>
            <hr>
            <table class="table table-bordered table-striped" id="cams_balance_id">
                <thead>
                    <tr style="background-color:#333;color:#fff">
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">CID No</th>
                        <th scope="col">CD Code</th>
                        <th class="text-center" scope="col"> Amount</th>
                    </tr>
                    </thead>
                    <tbody>';
                    $i = 1;
                    $total_balance = 0;
                    
                    $stmt = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, m.cd_code, m.`name`, m.amount 
                            FROM mcams_wallet m 
                            LEFT JOIN client_account a ON m.cd_code = a.cd_code
                            WHERE 
                            -- DATE(m.created_Date) BETWEEN ? AND ? 
                            (:fdate = '0' OR DATE(m.created_Date) >= :fdate) AND 
                            (:tdate = '0' OR DATE(m.created_Date) <= :tdate)
                            GROUP BY m.cd_code
                    ");
                    $stmt->bindParam(":fdate", $fromDate);
                    $stmt->bindParam(":tdate", $toDate);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($rows as $res) {
                        $total_balance += $res['amount'];
                        echo'
                        <tr>
                            <td>' . $i . '</td>
                            <td>' . $res['f_name'] . ' ' . $res['l_name'] . '</td>
                            <td>' . $res['ID'] . '</td>
                            <td>' . $res['cd_code'] . '</td>
                            <td class="text-center">' . $res['amount'] . '</td>
                        </tr>';
                        $i++;
                    }
                echo'
                </tbody>
            </table>
        </div>
        <div class="col-lg-12">
            <strong>mCaMS Wallet Balance => ' . number_format($total_balance, 2) . '</strong>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-xs-12">
        &emsp;&emsp;<a href="load.php?export_generated_wallet_bal=export_generated_wallet_bal&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
    </div>
    <br>
    <script type="text/javascript">
    $( document ).ready(function() {
      $("#cams_balance_id").DataTable({
         "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]
      });
    });
  </script>';
}
elseif(!empty($_GET['export_generated_wallet_bal'])) {
    $replace   = array("\n");
    $search  = array('');

    $fromDate = $_GET['fromDate'];
    $toDate = $_GET['toDate'];

    $stmt = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, m.cd_code, m.`name`, m.amount 
            FROM mcams_wallet m 
            LEFT JOIN client_account a ON m.cd_code = a.cd_code
            WHERE 
            -- DATE(m.created_Date) BETWEEN ? AND ? 
            (:fdate = '0' OR DATE(m.created_Date) >= :fdate) AND 
            (:tdate = '0' OR DATE(m.created_Date) <= :tdate)
            GROUP BY m.cd_code
    ");
    $stmt->bindParam(":fdate", $fromDate);
    $stmt->bindParam(":tdate", $toDate);
    $stmt->execute();

    $columnHeader = "SlNo\t Name\t CID\t CD Code\t Amount\t"; 
    $setData = '';
    $i = 1;
    while ($rec=$stmt->fetch()) { 
        if($stmt->rowCount() <= 0) 
        {}
        $fl_name = $rec['f_name'] . ' ' . $rec['l_name'];
        $rowData = '';
        $value = $i++."\t"
            .str_replace($search,$replace,$fl_name)."\t"
            .str_replace($search,$replace,$rec['ID'])."\t"
            .str_replace($search,$replace,$rec['cd_code'])."\t"
            .str_replace($search,$replace,$rec['amount'])."\t"
        ;
        $rowData .= $value;  
        $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=bla_wallet_balance.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 

    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
    die();
}
elseif (isset($_POST['get_symbols_list'])) {
    $secType = $_POST['sec_type'] ?? '';
    $tableName  = '';

    if ($secType === 'OS') {
        $securityTypes = ['OS'];
        $tableName = 'executed_orders';
    } else {
        $securityTypes = ['GB', 'CB'];
        $tableName = 'bond_executed_orders';
    }

    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($securityTypes), '?'));

    $sql = "
        SELECT DISTINCT e.symbol_id, s.symbol
        FROM {$tableName} e
        JOIN symbol s ON e.symbol_id = s.symbol_id
        WHERE s.status = 1 AND s.trsstatus = 1 AND s.security_type IN ($placeholders)
        ORDER BY s.symbol ASC
    ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($securityTypes);
?>
    <div class="col-lg-3 col-md-3">
        <label>Symbol</label>
        <select name="symbol" id="symbol" class="form-control" required>
            <option value="">ALL</option>
            <?php foreach ($stmt as $row): ?>
                <option value="<?= $row['symbol_id']; ?>">
                    <?= htmlspecialchars($row['symbol']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php
    exit();
}
else
{

}
?>
<script>
 function checkDate() {
    var f= document.getElementById("to_date").value;
    var from= new Date(f);
    var t= document.getElementById("from_date").value;
    var to= new Date(t);
     if (from < to)
     {
         alert("To date should be greater than From date ");
         return false;
     }
     else
     {
         return true;
     }
 }
</script>
<script type="text/javascript">
$('#to_date_announcement').change(function(){
            var cdcode = $("#cdcode").val();
            var toDate = $("#to_date_announcement").val();
            var fromDate = $("#from_date").val();
            var op = 'announcement';
            $.ajax({
            type: "POST",
            url: "loadReport.php",
            data: 'toDate='+toDate +'&fromDate='+fromDate +'&cdcode='+cdcode +'&announcement='+ op,
            success: function(data){
              $("#details").html(data);
            }
            });
      });
</script>
