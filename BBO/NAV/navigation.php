 <?php 
  date_default_timezone_set("Asia/Thimphu");
  $mc = isset($_SESSION['sess_part_code']) ? $_SESSION['sess_part_code'] : ''; 
  $m = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : '';
?>
  <header class="main-header">
    <a href="../FILES/bbo-landing.php" class="logo">
      <span class="logo-lg"><b>RSEB</b>-BBO&OMS</span>
    </a>
    <script type="text/javascript">
      /*$(document).ready(function(){
       $('<audio id="chatAudio"><source src="38701974.mp3" type="audio/mpeg"></audio>').appendTo('body');
          var a = $("#list_size").val();
          if(a > 0){
            $('#chatAudio')[0].play();
          }
      });*/
    </script>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o fa-spin"></i>
              <span class="label label-danger">
                <?php 
                  $current_date = date("Y-m-d");
                  $query= $dbh->prepare("SELECT o.symbol_id,o.price FROM orders o WHERE o.order_entry=:un ORDER BY o.symbol_id");
                  $query->bindParam(':un', $m);
                  $query->execute();                

                  $notif = 0;
                  foreach($query as $q) {
                    $symb_id = $q['symbol_id'];
                    $price = $q['price'];
                    $cap_name = 'CAP';

                    $pr = $dbh->prepare('SELECT market_price from market_price WHERE symbol_id=:id');
                    $pr ->bindParam(':id',$symb_id);
                    $pr ->execute();
                    $value = $pr->fetch();
                    $market_price = $value['market_price'];

                    $pr = $dbh->prepare('SELECT margin from circuit_breaker WHERE name=:n');
                    $pr ->bindParam(':n',$cap_name);
                    $pr ->execute();
                    $value = $pr->fetch();
                    $cap = $value['margin'];
                    $cap_value = ($market_price * $cap) / 100;

                    $cap_value = round($cap_value, 2);
                    $dw = round($market_price - $cap_value, 2);
                    $up = round($market_price + $cap_value, 2);

                    if($price > $up || $price < $dw) {
                      $notif = $notif + 1;
                    } else {
                      //$notif = 0;
                    }
                  }
                  echo $notif;
                  echo'<input type="hidden" id="list_size" value="'.$notif.'">';
                ?>
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header"><?php echo 'You have '.$notif.' orders to update' ?></li>
              <li>
                <ul class="menu">
                <?php
                  $query= $dbh->prepare("SELECT o.symbol_id, o.price, s.symbol 
                    FROM orders o, symbol s WHERE o.order_entry = :un AND o.symbol_id = s.symbol_id 
                    ORDER BY o.symbol_id
                  ");
                  $query->bindParam(':un', $m);
                  $query->execute();
                  
                  foreach($query as $q) {
                    $symb_id = $q['symbol_id'];
                    $price = $q['price'];
                    $cap_name = 'CAP';
                    $pr = $dbh->prepare('SELECT market_price FROM market_price WHERE symbol_id=:id');
                    $pr ->bindParam(':id',$symb_id);
                    $pr ->execute();
                    $value = $pr->fetch();
                    $market_price = $value['market_price'];

                    $pr = $dbh->prepare('SELECT margin FROM circuit_breaker WHERE name=:n');
                    $pr ->bindParam(':n',$cap_name);
                    $pr ->execute();
                    $value = $pr->fetch();
                    $cap = $value['margin'];
                    $cap_value = ($market_price * $cap) / 100;

                    $cap_value = round($cap_value, 2);
                    $dw = round($market_price - $cap_value, 2);
                    $up = round($market_price + $cap_value, 2);
                    if($price > $up || $price < $dw) {
                      echo'
                      <li>
                        <a href="../FILES/tec.php">
                          <i class="fa fa-warning text-red"></i> Orders for '.$q['symbol'].' - '.$q['price'].'
                        </a>
                      </li>';
                    } else {
                      //$notif=0;
                    }                    
                  }
                  ?>
                </ul>
              </li>
              <li class="footer"><a href="../FILES/tec.php"> Update</a></li>
            </ul>
          </li>
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="../../dist/img/avatar5.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $_SESSION['sess_username'];?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="../../dist/img/avatar5.png" class="img-circle" alt="User Image">
                <p>
                <?php 
                  $q = $dbh->prepare("SELECT name, address, email, phone FROM users WHERE username =:m");
                  $q->bindParam(':m', $_SESSION['sess_username']);
                  $q->execute();
                  $qq = $q->fetch();
                  echo $qq['name'].'<small>'.$qq['address'].'</small>';

                  $stmt = $dbh->prepare("SELECT participant_code, username, type FROM assign_broker WHERE username =:m and status = 1");
                  $stmt->bindParam(':m', $_SESSION['sess_username']);
                  $stmt->execute();
                  $brokerLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <aside class="main-sidebar">
    <section class="sidebar">
      <ul class="sidebar-menu">
        <li><a href="../FILES/bbo-landing.php"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="../FILES/account_reg.php"><i class="fa fa-users"></i> Account Registration</a></li>
        <!-- <li><a href="../FILES/bond_trading.php"><i class="fa fa-suitcase"></i> Bond Trading</a></li> -->
        <!-- <i class="fa fa-users text-red"></i> -->
        <li class="treeview">
          <a href="#">
            <i class="fa fa-arrows"></i> <span>BackOffice</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <?php if ($role == 2): ?>
              <li><a href="../FILES/bbo-fin.php"><i class="fa fa-money text-red"></i> Finance</a></li>
            <?php endif ?>

            <?php foreach ($brokerLinks as $key => $value): ?>
              <?php if ($value['type'] === 'IPO'): ?>
                <li><a href="../FILES/ipo_fin.php"><i class="fa fa-money text-red"></i> IPO Finance</a></li>
              <?php elseif ($value['type'] === 'RIGHTS'): ?>
                <li><a href="../FILES/rights_fin.php"><i class="fa fa-money text-red"></i> Rights Finance</a></li>
              <?php else: ?>
                <li><a href="../FILES/bond_fin.php"><i class="fa fa-money text-red"></i> Bond Finance</a></li>
              <?php endif ?>
            <?php endforeach ?>
          </ul>
        </li>
        <!--this section is for RMA's Pledge -->
        <?php if (substr($_SESSION['sess_username'], 0, 6) == 'MEMRMA'): ?>
          <li class="treeview">
            <a href="#">
              <i class="fa fa-arrows"></i> <span>Pledge Transaction</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li><a href="../FILES/pledge-contract.php"><i class="fa fa-folder text-red"></i>Pledge Contract</a></li>
              <li><a href="../FILES/pledge.php"><i class="fa fa-folder text-red"></i>Pledge </a></li>
              <li><a href="../FILES/pledge-release.php"><i class="fa fa-folder text-red"></i>Pledge Release </a></li>
            </ul>
          </li>
        <?php endif ?>
        <li class="treeview">
          <a href="#">
             <i class="fa fa-sort-amount-desc"></i><span>Order Manager</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <?php if ($role == 2): ?>
              <li><a href="../FILES/bbo-om.php"><i class="fa fa-signal text-red"></i> Market</a></li>
              <li><a href="../FILES/bond_order_market.php"><i class="fa fa-sort text-red"></i> Bond Order Market</a></li>
              <li><a href="../FILES/om-pending.php"><i class="fa fa-reorder text-red"></i> Pending Orders</a></li>
              <li><a href="../FILES/om-e.php"><i class="fa fa-terminal text-red"></i> Executed Orders</a></li>
            <?php endif ?>

            <?php foreach ($brokerLinks as $key => $value): ?>
              <?php if ($value['type'] === 'IPO'): ?>
                <li><a href="../FILES/om-e.php"><i class="fa fa-terminal text-red"></i> IPO Market</a></li>
              <?php elseif ($value['type'] === 'RIGHTS'): ?>
                <li><a href="../FILES/rights-om.php"><i class="fa fa-signal text-red"></i> Rights Market</a></li>
                <li><a href="https://rsebl.org.bt/online/loadAuctionBid.php" target="_blank"><i class="fa fa-map-signs text-red"></i> Auction Market</a></li>
              <?php else: ?>
                <li><a href="../FILES/bond-om.php"><i class="fa fa-signal text-red"></i> Bond Market</a></li>
              <?php endif ?>
            <?php endforeach ?>
          </ul>
        </li>
        <?php if ($role == 2): ?>
          <li class="treeview">
            <a href="#">
              <i class="fa fa-gear"></i> <span>Configuration</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li><a href="../FILES/commission.php"><i class="fa fa-lock text-red"></i>Commission</a></li>
            </ul>
          </li>
        <?php endif ?>

        <?php foreach ($brokerLinks as $key => $value): ?>
          <?php if ($value['type'] == 'IPO'): ?>
            <li class="treeview">
              <a href="#"><i class="fa fa-users"></i> IPO
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">                 
                 <li><a href="../FILES/ipo_by.php"><i class="fa fa-circle-o"></i> Buy Order</a></li>
                 <li><a href="../FILES/ipo_up.php"><i class="fa fa-circle-o"></i> Update Order</a></li>
              </ul>
            </li>
          <?php elseif ($value['type'] == 'RIGHTS'): ?>
            <li class="treeview">
              <a href="#"><i class="fa fa-angle-double-right" aria-hidden="true"></i> RIGHTS
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
              </a>
              <ul class="treeview-menu">
                <li><a href="../FILES/rights_issue.php"><i class="fa fa-lock text-aqua"></i> Rights Issue</a></li>
                <li><a href="../FILES/rights_auction_bid.php"><i class="fa fa-lock text-aqua"></i> Rights Auction BID</a></li>
                <li><a href="../FILES/tec_rights.php"><i class="fa fa-lock text-aqua"></i> Rights bids Update</a></li>
                <li><a href="../FILES/onlinerightsbiduses.php"><i class="fa fa-circle-o"></i> Rights bids Online Users</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="treeview">
              <a href="#"><i class="fa fa-paper-plane"></i> BOND
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
              </a>
              <ul class="treeview-menu">                 
                 <li><a href="../FILES/bond_by.php"><i class="fa fa-circle-o"></i> Bond Subscribe</a></li>
                 <?php if (substr($username, 0, 6) == 'MEMRMA'): ?>
                    <li><a href="../FILES/bond_yeild_auction.php"><i class="fa fa-circle-o"></i> Bond Subscribe - Yeild Rate</a></li>
                    <li><a href="../FILES/bond_yeild_auction_online.php"><i class="fa fa-circle-o"></i> Online Subscribe Verify</a></li>
                 <?php endif ?>
                 <li><a href="../FILES/bond_up.php"><i class="fa fa-circle-o"></i> Bond Update</a></li>
                 <!-- <li><a href="../FILES/bond_cancel.php"><i class="fa fa-circle-o"></i> Bond Cancel</a></li> -->
              </ul>
            </li>
          <?php endif ?>
        <?php endforeach ?>

        <?php if ($role == 2): ?>
          <li><a href="../FILES/userList.php"><i class="fa fa-user"></i> Online Users</a></li>
          <li><a href="../FILES/user_unlock.php"><i class="fa fa-unlock"></i> Unlock mCaMS</a></li>
          <!-- <li><a href="../FILES/bond_trading.php"><i class="fa fa-bold"></i> Bond Trading</a></li> -->
          <li class="treeview">
            <a href="#">
              <i class="fa fa-bold"></i><span>Bond Trading</span>
              <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
            </a>
            <ul class="treeview-menu">
              <li><a href="../FILES/bond_trading.php"><i class="fa fa fa-plus"></i> New Order</a></li>
              <li><a href="../FILES/b_pending_orders.php"><i class="fa fa fa-bars"></i> Pending Order</a></li>
              <li><a href="../FILES/b_cancel_ords.php"><i class="fa fa fa-minus"></i> Cancel Order</a></li>
              <li><a href="../FILES/bond_exec_orders.php"><i class="fa fa fa-exchange"></i> Executed Bond Order</a></li>
            </ul>
          </li>
        <?php endif ?>

        <li class="treeview">
          <a href="#"><i class="fa fa-files-o"></i> Reports
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
          <ul class="treeview-menu">
            <?php if (!empty($role) && $role == 2): ?>
              <li><a href="../REPORTS/tc.php"><i class="fa fa-circle-o"></i> Trade Confirmation</a></li>
              <li><a href="../REPORTS/fa.php"><i class="fa fa-circle-o"></i> Finance Activity</a></li>
              <li><a href="../REPORTS/c.php"><i class="fa fa-circle-o"></i> Commission</a></li>
              <li><a href="../REPORTS/cash-transaction.php"><i class="fa fa-circle-o"></i> Cash Transaction</a></li>
              <li><a href="../REPORTS/dTradeDetails.php"><i class="fa fa-circle-o"></i> Detailed Trade Details</a></li>
            <?php endif ?>

            <?php 
              $stmt = $dbh->prepare("
                      SELECT i.gst_register
                      FROM adm_participants p 
                      JOIN adm_institution i ON p.institution_id = i.institution_id
                      WHERE p.participant_code = ?
              ");
              $stmt->execute([$mc]);
              $gst_register = $stmt->fetchColumn();
            ?>

            <?php if ($gst_register == 'Y'): ?>
              <li><a href="../REPORTS/gst_report.php"><i class="fa fa-circle-o"></i> GST Report</a></li>
            <?php endif ?>

            <?php if (substr($username, 0, 6) == 'MEMRMA'): ?>
              <hr style="margin-top: 0px; margin-bottom: 0px;">
              <li><a href="../REPORTS/pledge-report.php"><i class="fa fa-circle-o"></i>Pledge Report</a></li>
            <?php endif ?>

            <?php foreach ($brokerLinks as $key => $value): ?>
              <?php if ($value['type'] == 'IPO'): ?>
                <hr style="margin-top: 0px; margin-bottom: 0px;">
                <li><a href="../REPORTS/ipo_orders.php"><i class="fa fa-circle-o"></i> IPO Orders</a></li>
                <li><a href="../IPO/report.php"><i class="fa fa-circle-o"></i> Report(Australia)</a></li>
                <li><a href="../REPORTS/ipo_s.php"><i class="fa fa-circle-o"></i> IPO Subscription</a></li>
                <li><a href="../REPORTS/ipo_tc.php"><i class="fa fa-circle-o"></i> IPO Trade Confirmation</a></li>
              <?php elseif ($value['type'] == 'RIGHTS'): ?>
                <hr style="margin-top: 0px; margin-bottom: 0px;">
                <li><a href="../REPORTS/rights.php"><i class="fa fa-circle-o"></i> Rights Audit</a></li>
                <li><a href="../REPORTS/rights_tc.php"><i class="fa fa-circle-o"></i> Rights Trade Confirmation</a></li>
                <li><a href="../REPORTS/shareAucAudit.php"><i class="fa fa-circle-o"></i> Share Auction Audit</a></li>
              <?php else: ?>
                <hr style="margin-top: 0px; margin-bottom: 0px;">
                <li><a href="../REPORTS/bond_s.php"><i class="fa fa-circle-o"></i> BOND Subscription</a></li>
                <li><a href="../REPORTS/bond_subp_summary.php"><i class="fa fa-circle-o"></i> BOND Subscription Summary</a></li>
                <li><a href="../REPORTS/bond_tc.php"><i class="fa fa-circle-o"></i> BOND Trade Confirmation</a></li>
                <li><a href="../REPORTS/bond_allocation.php"><i class="fa fa-circle-o"></i> BOND Allocation summary</a></li>
              <?php endif ?>
            <?php endforeach ?>
          </ul>
        </li>
        
        <?php if (substr($_SESSION['sess_username'], 0, 7) == 'MEMRNRB'): ?>
          <li class="treeview">
            <a href="#">
              <i class="fa fa-flag"></i><span>BLA</span>
              <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
            </a>
            <ul class="treeview-menu">
              <li><a href="../FILES/nrb_app_list.php"><i class="fa fa fa-bars"></i> BLA List</a></li>
              <li><a href="../FILES/nrb_payment_list.php"><i class="fa fa-money"></i> BLA Payment</a></li>
              <li><a href="../FILES/nrb_dash.php"><i class="fa fa-money"></i> BLA Dashboard</a></li>
            </ul>
          </li>
        <?php endif ?>

      </ul>
    </section>
  </aside>

  <script language="JavaScript">
    function getStateb(val) {
      var val = "BUY";
      $.ajax({
        type: "POST",
        url: "load.php",
        data:"BUY="+val,
        dataType: "html",
        success: function(response){
          $("#myModal").html(response);
        }
      });
    }

    function getStates(val) {
      var val = "SELL";
      $.ajax({
        type: "POST",
        url: "load.php",
        data: "SELL="+val,
        dataType: "html",
        success: function(response){
          $("#myModal").html(response);
        }
      });
    }
  </script>