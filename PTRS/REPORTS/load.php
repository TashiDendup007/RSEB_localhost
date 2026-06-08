<?php
  include("../FILES/session_file.php");
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
elseif(!empty($_POST["symbol"])) 
{
    $symbol=$_POST['symbol'];
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
elseif(!empty($_POST["symbolTV"])) 
{
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
elseif (!empty($_POST["individual"])) 
{
    $cid = $_POST['individual'];

    $wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, a.name 
            FROM client_account c
            JOIN adm_institution a ON c.institution_id = a.institution_id 
            JOIN cds_holding h ON c.cd_code = h.cd_code
            WHERE (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) > 0 
            AND c.ID = :cid
            ORDER BY c.client_id DESC LIMIT 1
        ");
    $wc->bindParam(':cid',$cid);
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
              <input type="text" class="form-control" style="color:red;" value="Invalid CID." readonly>
            </div>';
    }
}
elseif (!empty($_POST["symbolNo"])) {
    $symbol = $_POST['symbolNo'];

    $wc = $dbh->prepare("SELECT symbol, name FROM symbol WHERE symbol=:symbol AND status = 1");
    $wc->bindParam(':symbol',$symbol);
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
elseif(!empty($_POST["symbolGSholdList"])) {
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

    $wc = $dbh->prepare("SELECT pledge_name FROM cds_pledge_contract WHERE pledge_contract=:contrCode");
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
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["net_report"])) 
{
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    echo'
    <div class="col-lg-12">
        <div class="box-body">';
            $query= $dbh->prepare("SELECT DISTINCT participant_code FROM executed_orders WHERE order_date BETWEEN :fdate AND :tdate");
            $query->bindParam(':fdate',$fromDate);
            $query->bindParam(':tdate', $toDate);
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            echo 'Netting Position for trade <br> From : '.$fromDate.' - To : '.$toDate;
            foreach ($rows as $res) {
                echo "<br/><br/><b>MEMBER : ".$res['participant_code']."</b><br>";

                $executed_orders= $dbh->prepare("SELECT DISTINCT a.symbol_id, b.symbol 
                    FROM executed_orders a
                    JOIN symbol b ON a.symbol_id = b.symbol_id
                    WHERE a.status = 0  and a.participant_code = :pc
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
                    foreach($executed_orders as $res1){
                        echo "
                        <tr>
                            <td>SYMBOL :".$res1['symbol']."</td>
                        </tr>";
                        $list_ord = $dbh->prepare("SELECT sum(lot_size_execute) AS totlot, cast(avg(order_exe_price) AS decimal(13,2)) AS avgp 
                            FROM executed_orders  
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

                        $list_ord = $dbh->prepare("SELECT sum(lot_size_execute) AS totlots , cast(avg(order_exe_price) AS decimal(13,2)) AS avgps 
                            FROM executed_orders 
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
        &emsp;&emsp;<a href="loadReportPrint.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&detailNetting=detailNetting" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
    </div>
    <br>';
}
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["trade_details"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    echo'
    <div class="col-lg-12">
      <div class="box-body">
        Summary of Trade<br> From : '.$fromDate.' - To : '.$toDate;
        $query = $dbh->prepare('SELECT DISTINCT participant_code FROM executed_orders WHERE order_date BETWEEN :fdate AND :tdate');
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
                $executed_orders= $dbh->prepare('SELECT DISTINCT a.symbol_id, b.symbol 
                    FROM executed_orders a 
                    JOIN symbol b ON a.symbol_id = b.symbol_id
                    WHERE a.participant_code=:pc and  order_date BETWEEN :fdate AND :tdate
                ');
                $executed_orders->bindParam(':pc', $res['participant_code']);
                $executed_orders->bindParam(':fdate', $fromDate);
                $executed_orders->bindParam(':tdate', $toDate);
                $executed_orders->execute();
                foreach($executed_orders as $res1){
                    $list_ord= $dbh->prepare('SELECT * FROM executed_orders 
                        WHERE participant_code=:pc AND symbol_id=:syid  AND order_date BETWEEN :fdate AND :tdate');
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
                    $list_ord = $dbh->prepare('SELECT sum(lot_size_execute) as totlot , cast(avg(order_exe_price) as decimal(13,2)) as avgp 
                    from executed_orders  where participant_code=:pc AND symbol_id=:syid AND side="B" AND  order_date BETWEEN :fdate AND :tdate');
                    $list_ord->bindParam(':pc',$res['participant_code']);
                    $list_ord->bindParam(':syid',$res1['symbol_id']);
                    $list_ord->bindParam(':fdate',$fromDate);
                    $list_ord->bindParam(':tdate',$toDate);
                    $list_ord->execute();
                    $res2 = $list_ord->fetch();
                    $totbuyamt = $res2['avgp'] * $res2['totlot'];

                    $list_ord= $dbh->prepare('SELECT sum(lot_size_execute) as totlots , cast(avg(order_exe_price) as decimal(13,2)) as avgps 
                    FROM executed_orders WHERE participant_code=:pc and symbol_id=:syid and side="S" and  order_date BETWEEN :fdate AND :tdate');
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
                        <td> Buy Vol : '.number_format($res2['totlot'],0,".",",").'</td>
                        <td> Sell Vol : '.number_format($res4['totlots'],0,".",",").'</td>
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
        &emsp;&emsp;<a href="loadReportPrint.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&tradeDetails=tradeDetails" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
    </div>
    <br>';
}
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["trade_detailss"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $symbol_id = !empty($_POST['symbol_id']) ? $_POST['symbol_id'] : 0;
    echo"
    <div class='col-xs-12'>
        <div class='box-body'>
            <b>Summary of Trade<br> From : ".$fromDate." - To : ".$toDate."</b>
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
                    FROM executed_orders e 
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
        &emsp;&emsp;<a href="loadReport.php?zge_export=zge_export&fromDate='.$fromDate.'&toDate='.$toDate.'&symbol_id='.$symbol_id.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
    </div>
    <br>';
}
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["Clearing"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    echo'
    <div class="col-lg-12">
        <div class="box-body">';
        echo 'Clearing Instruction for Trade <br> From : '.$fromDate.' - To : '.$toDate;
        $i = 1;
        $query = $dbh->prepare("SELECT DISTINCT a.participant_code, b.clearing_account 
            FROM executed_orders  a 
            JOIN adm_participants b ON a.participant_code = b.participant_code
            WHERE order_date BETWEEN :fdate AND :tdate
        ");
        $query->bindParam(':fdate', $fromDate);
        $query->bindParam(':tdate', $toDate);
        $query->execute();
        foreach ($query as $res) {
            $totalb = 0;
            $totals = 0;
            echo"
            <br/><br/><b>MEMBER : ".$res['participant_code']."</b><br>
            <table class='table table'>
                <thead>
                    <tr style='background-color:#333;color:#fff'>
                        <th>SN</th><th>REMARKS</th>
                        <th></th><th>AMOUNT</th>
                    </tr>
                </thead>
                <tbody>";
                $list_ord = $dbh->prepare("SELECT * FROM executed_orders 
                    WHERE status = 0 AND participant_code = :pc AND side = 'B' AND order_date BETWEEN :fdate AND :tdate
                ");
                $list_ord->bindParam(':pc', $res['participant_code']);
                $list_ord->bindParam(':fdate', $fromDate);
                $list_ord->bindParam(':tdate', $toDate);
                $list_ord->execute();
                $results = $list_ord->fetchAll(PDO::FETCH_ASSOC);
                foreach($results as $res2){
                    $totalbuy = $res2['lot_size_execute'] * $res2['order_exe_price'];
                    $totalb = $totalb + $totalbuy;
                }
                echo'
                <tr>
                    <td>'.$i++.'</td>
                    <td>Total buy amount</td>
                    <td></td>
                    <td>Nu. ('.number_format($totalb, 2, ".", ",").')</td>
                </tr>';
                $list_ord= $dbh->prepare("SELECT * FROM executed_orders 
                    WHERE status = 0 AND participant_code = :pc AND side = 'S' AND order_date BETWEEN :fdate AND :tdate
                ");
                $list_ord->bindParam(':pc', $res['participant_code']);
                $list_ord->bindParam(':fdate', $fromDate);
                $list_ord->bindParam(':tdate', $toDate);
                $list_ord->execute();
                foreach($list_ord as $res3) {
                    $totalsell = $res3['lot_size_execute'] * $res3['order_exe_price'];
                    $totals = $totals + $totalsell;
                }   
                echo'
                <tr>
                    <td>'.$i++.'</td>
                    <td>Total sell amount</td>
                    <td></td>
                    <td>Nu. '.number_format($totals,2,".",",").'</td>
                </tr>';

                $diff = $totals - $totalb;

                if ($diff > 0) { 
                    $rm = "<span style='color:green;'>CREDIT (Pay)</span>";
                    echo"
                    <tr>
                        <td><b>Instruction : <b>".$rm."</b></td>
                        <td><b> Account # : ".$res['clearing_account']."</b></td>
                        <td></td>
                        <td><b>Nu. (".number_format($diff, 2, ".", ",").")</b></td>
                    </tr>";
                } elseif ($diff < 0) {
                    $rm = "<span style='color:red;'>DEBIT(Collect)</span>";
                    $diff = $diff * -1;
                    echo"
                    <tr>
                        <td><b>Instruction : <b>".$rm."</b></td>
                        <td><b> Account # : ".$res['clearing_account']."</b></td>
                        <td></td>
                        <td><b>Nu. ".number_format($diff,2,".",",")."</b></td>
                    </tr>";
                } elseif ($diff == 0) { 
                    $rm = "None";
                    echo"
                    <tr>
                        <td><b>Instruction : <b>".$rm."</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>";
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
        &emsp;&emsp;<a href="loadReportPrint.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&Clearing=Clearing" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
    </div>
    <br>';
}
elseif(!empty($_POST["entel_load_report"])) {
    $symbol_id = $_POST['entel_load_report'];
    $current_date = date("Y-m-d");

    $wc= $dbh->prepare("SELECT a.corp_announcement_id, a.announcement_type, a.record_date, a.ex_date, a.announcement_date, a.rate, a.type, b.symbol, b.symbol_id 
        FROM corporate_announcement a 
        JOIN symbol b ON a.symbol_id = b.symbol_id
        where a.symbol_id=:symbol_id AND a.status = 0 AND a.record_date <= :cur_date");
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
    die();
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
