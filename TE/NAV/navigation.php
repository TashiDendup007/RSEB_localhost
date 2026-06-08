<?php
  date_default_timezone_set("Asia/Thimphu");
  include('../../Functions/f.php');

  $cdcode = find_link_user_cd_code($username);
  $list =  ins_id($username);
  $ins_id = $list[0];
  $p_code = $list[1];

  // error_log($cdcode);

  $mc = $_SESSION['sess_part_code'];
  $m = $_SESSION['sess_username'];
?>
<header class="main-header">
  <a href="../FILES/te-landing.php" class="logo">
    <span class="logo-lg"><b>RSEB</b>-TRADING</span>
  </a>
  <script type="text/javascript">
  $(document).ready(function(){
    $('<audio id="chatAudio"><source src="38758176.mp3" type="audio/mpeg"></audio>').appendTo('body');
    var a = $("#list_size").val();
    if(a > 0){
      $('#chatAudio')[0].play();
    }});
  </script>
  <!-- Header Navbar: style can be found in header.less -->
  <nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </a>
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav black">
        <!-- Notifications: style can be found in dropdown.less -->
        <!-- User Account: style can be found in dropdown.less -->

        <li class="dropdown notifications-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-bell-o fa-spin"></i>
            <span class="label label-danger">
              <?php
              date_default_timezone_set("Asia/Thimphu");
              $current_date=date("Y-m-d");
              $query= $dbh->prepare('SELECT o.symbol_id,o.price from orders o where o.order_entry=:un order by o.symbol_id');
              $query->bindParam(':un',$m);
              $query->execute();

              $notif = 0;
              foreach($query as $q)
              {
                $symb_id= $q['symbol_id'];
                $price= $q['price'];

                $cap_name='CAP';
                $pr = $dbh->prepare('SELECT market_price from market_price WHERE symbol_id=:id');
                $pr ->bindParam(':id',$symb_id);
                $pr ->execute();
                $value= $pr->fetch();
                $market_price=$value['market_price'];
                $pr = $dbh->prepare('SELECT margin from circuit_breaker WHERE name=:n');
                $pr ->bindParam(':n',$cap_name);
                $pr ->execute();
                $value= $pr->fetch();
                $cap=$value['margin'];
                $cap_value=($market_price * $cap) / 100;

                $up = $market_price+$cap_value;
                $dw = $market_price-$cap_value;
                //echo $price.'oi'.$up.'|'.$dw.'<br>';
                if($price > $up || $price < $dw)
                {
                  $notif = $notif+1;
                }
                else
                {
                  //$notif=0;
                }
              }
              echo $notif;
              echo '<input type="hidden" id="list_size" value="'.$notif.'">';
              ?>
            </span>
          </a>
          <ul class="dropdown-menu">
            <li class="header"><?php echo 'You have '.$notif.' orders to update' ?></li>
            <li>
              <!-- inner menu: contains the actual data -->
              <ul class="menu">

                <?php
                $query= $dbh->prepare('SELECT o.symbol_id,o.price,s.symbol from orders o,symbol s where o.order_entry=:un and o.symbol_id=s.symbol_id order by o.symbol_id');
                $query->bindParam(':un',$m);
                $query->execute();
                foreach($query as $q)
                {
                  $symb_id= $q['symbol_id'];
                  $price= $q['price'];
                  $cap_name='CAP';
                  $pr = $dbh->prepare('SELECT market_price from market_price WHERE symbol_id=:id');
                  $pr ->bindParam(':id',$symb_id);
                  $pr ->execute();
                  $value= $pr->fetch();
                  $market_price=$value['market_price'];
                  $pr = $dbh->prepare('SELECT margin from circuit_breaker WHERE name=:n');
                  $pr ->bindParam(':n',$cap_name);
                  $pr ->execute();
                  $value= $pr->fetch();
                  $cap=$value['margin'];
                  $cap_value=($market_price*$cap)/100;

                  $up = $market_price+$cap_value;
                  $dw = $market_price-$cap_value;
                  //echo $price.'oi'.$up.'|'.$dw.'<br>';
                  if($price > $up || $price < $dw)
                  {
                    echo '<li>
                    <a href="../FILES/tec.php">
                    <i class="fa fa-warning text-red"></i> Orders for '.$q['symbol'].' - '.$q['price'].'
                    </a>
                    </li>';
                  }
                  else
                  {
                    //$notif=0;
                  }
                }
                ?>

              </ul>
            </li>
            <li class="footer"><a href="../FILES/tec.php">Update</a></li>
          </ul>
        </li>
        <?php
        $m= $_SESSION['sess_username'] ;
        $q = $dbh->prepare('SELECT * FROM users where username =:m');
        $q->bindParam(':m', $_SESSION['sess_username']);
        $q->execute();
        $qq= $q->fetch();
        ?>

        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="../../dist/img/avatar5.png" class="user-image" alt="User Image">
            <span class="hidden-xs"><?php echo $qq['name'];?></span>
          </a>
          <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
              <img src="../../dist/img/avatar5.png" class="img-circle" alt="User Image">

              <p>
                <?php
                echo $qq['name'].'<small>'.$qq['address'].'</small>';
                ?>
              </p>
            </li>
            <li class="user-footer">
              <div class="pull-left">
                <a href="../FILES/profile.php" class="btn btn-default btn-flat">Profile</a>
              </div>
              <div class="pull-right">
                <a href="../../Authentication/logout.php" class="btn btn-default btn-flat">Sign out</a>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
<!-- =============================================== -->
<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <li ><a href="../FILES/te-landing.php"><i class="fa fa-home"></i><span>Home</span></a></li>
      <!-- <li ><a href="../FILES/tewatch.php"><i class="fa fa-signal "></i><span>Market Watch</span></a></li> -->
      <!-- <li ><a href="../FILES/earnings.php"><i class="fa fa-money "></i><span>Earnings</span></a></li> -->
      <?php

      if($_SESSION['isNRB'] == 'Y'){
        echo ' <li ><a href="../FILES/wallet.php"><i class="fa fa-money "></i><span>Wallet</span></a></li>';
      }
      
      ?>
     
      <li ><a href="../FILES/balance.php"><i class="fa fa-file "></i><span>Balance Confirmation</span></a></li>
      <li class="treeview">
        <a href="#">
          <i class="fa fa-users"></i> <span>Order Manager</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="../FILES/tep.php"><i class="fa fa-reorder text-blue"></i> Pending Orders</a></li>
          <li><a href="../FILES/tee.php"><i class="fa fa-terminal text-blue"></i> Executed Orders</a></li>
        </ul>
      </li>
    </ul>
  </section>
</aside>

<script type="text/javascript">
  function CheckWalletBalance () {
    var amount = $('#Withdraw_amt').val();
    $.ajax({
      type: "POST",
      url: "http://localhost/RSEB2020/api2/indivclentholding.php",
      data:'Amount='+amount+'&cd_code=<?php echo $cdcode; ?>'+'&WithdrawWalletBalance=WithdrawWalletBalance&username=<?php echo $username;?>',
      success: function(data) {
        $("#cdd").html('<div class="alert alert-info" role="alert">'+$.parseJSON(data)+'</div>');
        setTimeout(function() {
          window.location.href = 'wallet.php';
        }, 2000);
      }
    });
  }

  /*function getStateb() {
    var val = "BUY";
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'BUY='+val,
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }

  function getStates() {
    var val = "SELL";
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'SELL='+val,
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }*/

  function getStateWithdraw(val) {
    var val = "getStateWithdraw";
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'getStateWithdraw='+val,
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }

  function GetSymbolModal(val) {
    var SymbolLoad = "SymbolLoad";
    var symbolName = $('#symbolName'+val).val();

    $.ajax({
      type: "POST",
      url: "load.php",
      data:'SymbolLoad='+SymbolLoad+'&val='+symbolName,
      success: function(response) {
        $("#myModal").html(response);
        
          Highcharts.getJSON('http://localhost/RSEB_NEW/TE/FILES/stock_prices.php?symbol='+symbolName, function(data) {
            Highcharts.stockChart('containerChart', {
              rangeSelector: {
                selected: 1
              },

              title: {
                text: 'Price Movement'
              },
              credits: {
                enabled: false
              },

              series: [{
                name: symbolName,
                data: data,
                tooltip: {
                  valueDecimals: 2
                }
              }]
            });
          });

          /*$.getJSON('http://localhost/RSEB_NEW/TE/FILES/stock_prices.php?symbol=' + symbolName, function(data) {
            Highcharts.stockChart('containerChart', {
              rangeSelector: {
                selected: 1
              },

              title: {
                text: 'Price Movement'
              },
              credits: {
                enabled: false
              },

              series: [{
                name: symbolName,
                data: data, // Ensure data is correctly formatted
                tooltip: {
                  valueDecimals: 2
                }
              }]

            });

          });*/

        }
    });
  }
</script>
