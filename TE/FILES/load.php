<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
$username = $_SESSION['sess_username'];
$cdcode = find_link_user_cd_code($username);
$list = ins_id($username);
$ins_id = $list[0];
$p_code = $list[1];
$broker_user_name = broker_user_name($username);

$wc= $dbh->prepare("SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate
  FROM client_account b, bbo_commission c where b.bro_comm_id=c.bro_comm_id and b.cd_code=:ac and b.user_name=:un ");
$wc->bindParam(':ac',$cdcode);
$wc->bindParam(':un',$broker_user_name);
$wc->execute();
$state  =$wc->fetch();

if (!empty($_POST["BUY"])) {
  echo'
  <div class="modal-dialog" >
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#d0e4fe;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">BUY </h4>
        </div>
        <div class="modal-body" style="color:blue;">
          <div id="order_msg"></div>
          <div class="box-body">
            <div class="row" ng-app="">
              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <label for="cid">CD. Code</label>
                <input type="text" class="form-control" name="cid" id="cid" style="text-transform:uppercase;" value="'.$cdcode.'"required readonly>
                <input type="hidden"  name="tp" id="tp" value="B">
                <input type="hidden" class="form-control" name="p_codeo" id="p_codeo" value="'.$p_code.'">
                <input type="hidden" class="form-control" name="p_code" id="p_code" value="'.$broker_user_name.'">
                <input type="hidden" class="form-control" name="u_name" id="u_name" value="'.$username.'">
              </div>';
              if ($wc->rowCount() > 0) {
              echo'
              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <label>Client</label>
                <input type="hidden" id="b_commis"  value="'.$state['rate'].'" >
                <input type="text" class="form-control" value="'.$state['f_name'].' '.$state['l_name'].' " readonly>
              </div>
              <input type="hidden" class="form-control" name="cd_code" id="cd_code"  value="'.$state['cd_code'].'" readonly>
              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="sy_div">
                <label>Symbol</label>';
                $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE security_type IN ('OS', 'GB', 'CB') AND status = 1 AND trsstatus = 1");
                $wc->execute();
                echo'
                <select name="sy" id="sy" class="form-control" onChange="tots3(this.value);">
                  <option value=""> --Select Symbol-- </option>';
                  while ($res = $wc->fetch()) {
                    echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                  }
                echo'</select>
                </div>';
              } else {
                echo 'No records';
              }
              echo'
              <div id="cdd"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary submit" name="buysubmit" id="buysubmit"><i class="fa fa-database"></i> Submit</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          </div>
        </div>
      </div>
    </form>
  </div>';
  } elseif (!empty($_POST["SELL"])) {
    echo'
    <div class="modal-dialog" >
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#ffb3b3;">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">SELL </h4>
          </div>
          <div class="modal-body" style="color:red;">
            <div id="order_msg"></div>
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                  <label for="cid">CD Code</label>
                  <input type="text" class="form-control" name="cid" id="cid" style="text-transform:uppercase;" value="'.$cdcode.'"  required readonly>
                  <input type="hidden"  name="tp" id="tp" value="S">
                  <input type="hidden" class="form-control" name="p_codeo" id="p_codeo" value="'.$p_code.'">
                  <input type="hidden" class="form-control" name="p_code" id="p_code" value="'.$broker_user_name.'">
                  <input type="hidden" class="form-control" name="u_name" id="u_name" value="'.$username.'">
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                  <label>Client</label>
                  <input type="hidden" id="b_commis"  value="'.$state['rate'].'" >
                  <input type="text" class="form-control" value="'.$state['f_name'].' '.$state['l_name'].' " readonly>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  id="sy_div">
                  <label>Symbol</label>';
                  $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE security_type IN ('OS', 'GB', 'CB') AND status = 1 AND trsstatus = 1");
                  $wc->execute();
                  echo'
                  <select name="sy" id="sy" class="form-control" onChange="tots2(this.value);">
                    <option value="" selected>-Select symbol-</option>';
                    while ($res = $wc->fetch()) {
                      echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                    }
                    echo'
                  </select>
                </div>
              <div id="cdd"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary submit" name="sellsubmit" id="sellsubmit"><i class="fa fa-database"></i> Submit</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          </div>
        </form>
      </div>
    </div>';
  }
  elseif(!empty($_POST["getStateWithdraw"])) {
    echo'
    <div class="modal-dialog" >
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#ffb3b3;">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Withdraw</h4>
          </div>
          <div class="modal-body" >
            <div id="cdd"></div>
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-xs-12"></div>
                <div class="col-xs-4">
                  <label for="Withdraw_amt">Withdraw Amount ( Nu.)</label>
                  <input type="number" class="form-control" name="Withdraw_amt" id="Withdraw_amt" required>
                </div>
              </div>
              </div>
            </div>
            <div class=" box-footer">
              <div col-lg-6 col-md-6 col-xs-6>
              <button type="button" class="btn btn-primary submit" name="withdrawAmt" id="withdrawAmt" onClick=CheckWalletBalance();>Withdraw</button>
            </div>
          </div>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>';
  }
  elseif (!empty($_POST["SymbolLoad"])) {
    // Initiate cURL
    $ch = curl_init();

    // Where you want to post data
    $url1 = "https://cms.rsebl.org.bt/RSEB2020/api2/indivclentholding.php";
    $url2 = "https://cms.rsebl.org.bt/RSEB2020/api2/MarketWatch_forcms.php";

    // Define the POST data
    $data1 = array(
        'ListedCompanies' => 'ListedCompanies',
        'symbol' => $_POST['val']
    );
    $data2 = array(
        'OrderForEachSymbol' => 'OrderForEachSymbol',
        'Symbol' => $_POST['val']
    );

    // Set cURL options for the first request
    curl_setopt($ch, CURLOPT_URL, $url1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data1));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // added

    // Execute the first request
    $ListedCompanies = curl_exec($ch);
    $ListedCompanies = json_decode($ListedCompanies, true);

   /* print_r($ListedCompanies);
    die();*/

    // Set cURL options for the second request
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data2));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // added

    // Execute the second request
    $output = curl_exec($ch);

    // Close cURL handle
    curl_close($ch);

    // Process the results
    if ($ListedCompanies[0]['symbol'] == 'No Data') {
        $SymbolDetails = $_POST['val'];
        $PaidUpShares = 'No Data';
    } else {
        $SymbolDetails = $ListedCompanies[0]['name'] . ' (' . $ListedCompanies[0]['sector'] . ')';
        $PaidUpShares = number_format($ListedCompanies[0]['paid_up_shares']);
    }

    // Output the HTML
    echo '
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">' . $SymbolDetails . '</h4>
          <span> Paid up Shares : ' . $PaidUpShares . '</span>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-12 col-sm-12 col-md-12">
              <div id="containerChart"></div>
            </div>
            <div class="col-lg-6 col-sm-12 col-md-12">
              <table id="example1" class="table table-bordered table-striped table-condensed">
                <thead>
                  <tr>
                    <th>Buy Vol</th>
                    <th>Price</th>
                    <th>Sell Vol</th>
                  </tr>
                </thead>
                <tbody>';
                  $values = json_decode($output, true);
                  $maxTrade = 0;
                  if ($values) {
                    foreach ($values as $key) {
                      if ($key['Price'] == $key['Discovered']) {
                          $class = '#17202A';
                          $color = 'white';
                      } else {
                          $class = 'white';
                          $color = 'black';
                      }
                      echo'<tr><td style="color:#5DADE2;background-color:'.$class.'">'.number_format($key['BuyVol']).'</td><td style="color:'.$color.';background-color:'.$class.'">'.$key['Price'].'</td><td style="color:red;background-color:'.$class.'">'.number_format($key['SellVol']).'</td></tr>';
                    }
                  }
            echo
            '</tbody>
          </table>
          </div>
          <div class="col-lg-6 col-sm-12 col-md-12">
              <table  class="table table-bordered table-striped table-condensed">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Rate%</th>
                  </tr>
                </thead>
                <tbody>';
                if ($ListedCompanies) {
                  foreach ($ListedCompanies as $key){
                    echo'
                    <tr>
                      <td>'.$key['announcement_date'].'</td>
                      <td>'.$key['Type'].'</td>
                      <td>'.$key['rate'].'</td>
                    </tr>';
                  }
                }
                echo
                '</tbody>
              </table>
          </div>
        </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>';
  }
  elseif(!empty($_POST["WalletTrxHistory"]))
  {
    // Initiate cURL
    $ch = curl_init();

    // Where you want to post data
    // $url1 = "http://localhost/RSEB2020/api2/indivclentholding.php";
    $url1 = "https://cms.rsebl.org.bt/RSEB2020/api2/indivclentholding.php";

    // Define the POST data
    $data1 = array(
        'WalletTrxHistory' => 'WalletTrxHistory',
        'cd_code' => $_POST['cd_code']
    );

    // Set cURL options for the second request
    curl_setopt($ch, CURLOPT_URL, $url1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data1));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the second request
    $output = curl_exec($ch);
    $values = json_decode($output, true);

    // Close cURL handle
    curl_close($ch);
    // Output the HTML

    echo '
      <thead>
        <tr>
          <th>#</th>
          <th>Trx Time</th>
          <th>Amount</th>
          <th>Type</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>';
      $i=1;
      foreach($values as $key){
        echo'<tr>
        <td>'.$i++.'</td>
        <td>'.$key['trx_time'].'</td>
        <td>'.$key['amount'].'</td>
        <td>'.$key['type'].'</td>
        <td>'.$key['paid_to_user'].'</td>
        </tr>';
      }
      echo'</tbody>';
   
  }
  elseif(!empty($_POST["cd_load_cli"])){
    $cd=$_POST['cd_load_cli'];
    $wc= $dbh->prepare("SELECT  a.*,c.symbol from cds_holding a, symbol c where  a.cd_code=:cd and a.symbol_id=c.symbol_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $i = 1;
    if($wc->rowCount() > 0){
    ?>
      <div>
        <?php
        foreach($wc as $res){
          echo  $i."<code> SYMBOL :</code> ".$res['symbol']. " | <code>Available Volume for Sale : </code>".$res['volume']." <br/>";
          $i++;
        }?>
      </div>
      <?php
    }
    else{
      echo "No Shares.";
    }
    $wc= $dbh->prepare("SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID from bbo_finance a,client_account b
    where  a.cd_code=:cd and b.cd_code=:cd and a.status=1");
    $wc->bindParam(':cd',$cd);
    /*$wc->bindParam(':un',$broker_user_name);*/
    $wc->execute();
    $res=$wc->fetch();
    echo  "<hr><code>Available Cash  : </code> Nu. ".$res['tot']."<br/>";
  } 
  elseif (isset($_POST['get_pending_order_list'])) {
    $username = $_POST['usernmae'];
    echo'
    <table id="example1" class="table table-bordered table-striped" width="100%">
      <thead>
        <tr>
          <th>SYMBOL</th>
          <th>CD CODE</th>
          <th>PRICE</th>
          <th>VOLUME</th>
          <th>SIDE</th>
          <th>TIME</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>';
      $wc = $dbh->prepare("SELECT a.order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id 
        FROM symbol b
        JOIN orders a ON a.symbol_id = b.symbol_id 
        WHERE a.order_entry = :un 
        ORDER BY order_date DESC
      ");
      $wc->bindParam(':un', $username);
      $wc->execute();
      $wc = $wc->fetchALL(PDO::FETCH_ASSOC);
      $i = 1;
      foreach ($wc as $res) {
        $background_color = $res['side'] == 'S' ? '#eca0ab' : '#dce2e9';
        $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
        echo'
        <tr style="background-color:'.$background_color.'">
          <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
          <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
          <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
          <input type="hidden" value="'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'" id="v'.$i.'">
          <input type="hidden" value="'.$res['flag_id'].'" id="fid'.$i.'">
          <input type="hidden" value="'.$res['side'].'" id="side'.$i.'">
          
          <td>'.$res['symbol'].'</td>
          <td>'.$res['cd_code'].'</td>
          <td>
            <input type="number" class="form-control" size="5" value="'.$res['price'].'" id="e_p'.$i.'">
          </td>
          <td>
            <input type="number" class="form-control" step="1" min="1" size="8" value="'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'" id="e_v'.$i.'">
          </td>
          <td>'.$side.'</td>
          <td>'.$res['order_date'].'</td>
          <td>
            <button type="buttton" class="btn btn-primary" name="chg_or" id="chg_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');" data-toggle="tooltip" data-placement="top" title="Change '.$res['symbol'].' order ?"><i class="fa fa-refresh"></i> Change</button>
          </td>
        </tr>';
        $i++;
      }
      echo'
      </tbody>
    </table>';
  }
  else {
    echo "No Data";
  }
  ?>
<script type="text/javascript">
  function tots1(val) {
    var p_c = $("#p_code").val();
    var tp = $("#tp").val();
    $.ajax({ 
      type: "POST", 
      url: "ja.php",
      data:'cid='+val+'&p_c='+p_c+'&tp='+tp,
      success: function(data) {
        var dd =  data.split('|');
        var result = dd[0];
        var ac = dd[1];

        if(result == 0) {
          $(".submit").hide();
          $("#sy_div").hide();
          $("#v_div").hide();
          $("#p_div").hide();
          $("#msg1").show().text(ac+ ' , Does not have a CD account');
        } else {
          $("#cd").html(data);
          $(".submit").show();
          $("#sy_div").show();
          $("#v_div").show();
          $("#p_div").show();
          $("#msg1").hide().text(ac+ ' , Does not have a CD account');
        }
      } 
    });
  }

  function tots2(val) {
    var cd = '<?php echo $cdcode; ?>';
    $.ajax({ 
      type: "POST", 
      url: "ja.php",
      data:'sy='+val+'&cd_code='+cd,
      dataType: "html",
      success: function(data) {
        $("#cdd").html(data);
      } 
    });
  }

  function tots3(val) {
    var ac= $("#cid").val();
    var price= $("#price").val();
    var buy_vol= $("#buy_vol").val();
    var b_commis= $("#b_commis").val();
    $.ajax({
      type: "POST", 
      url: "ja.php",
      data:'sy='+val+'&ac='+ac+'&price='+price+'&buy_vol='+buy_vol+'&b_commis='+b_commis,
      dataType: "html",
      success: function(data)
      {
        $("#cdd").html(data);
      } 
    });
  }

  $("#sellsubmit").click( function() {
    $("#sellsubmit").addClass( "disabled" );
    showLoading();
    var cdcode = $("#cid").val();
    var p_codeo = $("#p_codeo").val();
    var p_code = $("#p_code").val();
    var u_name = $("#u_name").val();
    var b_commis = $("#b_commis").val();
    var avl_vol = $("#avl_vol").val();
    var pov = $("#pov").val();
    var piv = $("#piv").val();
    var vol=$("#vol").val();
    var sy_id=$("#sy").val();
    var price=$("#price").val();
    var side = "S";
    var dataString = 'cdcode='+cdcode+'&p_code='+p_code+'&p_codeo='+p_codeo+'&u_name='+u_name+'&vol='+vol+'&sy_id='+sy_id+'&price='+price+'&side_for_order='+side+'&b_commis='+b_commis+'&avl_vol='+avl_vol+'&pov='+pov+'&piv='+piv;
    
    if (sy_id === '' || cdcode === ''|| vol === '' || price === '') {
      alert("Please Fill All Mandatory Fields");
      $("#sellsubmit").removeClass( "disabled" );
      hideloading();
    }
    else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType: 'html',
        success: function(data){
          hideloading();
          
          $("#order_msg").html(data);
          // showMessage();
          // $("#message").fadeOut(6000);

          // $("#sellsubmit").removeClass( "disabled" );
        }
      });
    }
    return false;
  });

  $("#buysubmit").click( function() {
    $("#buysubmit").addClass( "disabled" );
    showLoading();
    var cdcode = $("#cid").val();
    var p_codeo = $("#p_codeo").val();
    var p_code = $("#p_code").val();
    var u_name = $("#u_name").val();
    var b_commis = $("#b_commis").val();
    var avl_vol = $("#avl_vol").val();
    var pov = $("#pov").val();
    var piv = $("#piv").val();
    var vol = $("#buy_vol").val();
    var sy_id = $("#sy").val();
    var price = $("#price").val();
    var side = "B";

    var dataString = 'cdcode='+cdcode+'&p_code='+p_code+ '&p_codeo='+p_codeo+'&u_name='+u_name+'&vol='+vol+'&sy_id='+sy_id+'&price='+price+'&side_for_order='+side+'&b_commis='+b_commis+'&avl_vol='+avl_vol+'&pov='+pov+'&piv='+piv;
    
    if(sy_id === '' || cdcode === '' || vol === '' || price === '') {
      alert("Please Fill All Mandatory Fields");
      $("#buysubmit").removeClass( "disabled" );
      hideloading();
    } 
    else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType: 'html',
        success: function(data){
          hideloading();
          
          $("#order_msg").html(data);
          // showMessage();
          // $("#message").fadeOut(6000);

          // $("#buysubmit").removeClass( "disabled" );
        }
      });
    }
    return false;
  });

  function showLoading() {
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
  }

  function hideloading() {
    document.getElementById('loadingmsg').style.display = 'none';
    document.getElementById('loadingover').style.display = 'none';
  }

</script>
