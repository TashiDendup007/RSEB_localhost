 <?php 
  date_default_timezone_set("Asia/Thimphu");
  $mc = isset($_SESSION['sess_part_code']) ? $_SESSION['sess_part_code'] : ''; 
  $m = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : '';

  $check = $dbh->prepare("SELECT c.cd_code FROM users c WHERE c.username = ?");
  $check->execute([$m]);
  $cdcode_session = $check->fetchColumn();
?>
  <header class="main-header">
    <a href="../FILES/landing.php" class="logo">
      <span class="logo-lg"><b>RSEB</b>-DEALER</span>
    </a>
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
                  /*$current_date = date("Y-m-d");
                  $query= $dbh->prepare("SELECT o.symbol_id, o.price FROM orders o WHERE o.order_entry=:un ORDER BY o.symbol_id");
                  $query->bindParam(':un', $m);
                  $query->execute();                

                  $notif = 0;
                  foreach($query as $q) {
                    $symb_id = $q['symbol_id'];
                    $price = $q['price'];
                    $cap_name = 'CAP';

                    $pr = $dbh->prepare('SELECT market_price FROM market_price WHERE symbol_id=:id');
                    $pr ->bindParam(':id', $symb_id);
                    $pr ->execute();
                    $market_price = $pr->fetchColumn();

                    $pr = $dbh->prepare('SELECT margin FROM circuit_breaker WHERE name=:n');
                    $pr ->bindParam(':n',$cap_name);
                    $pr ->execute();
                    $cap = $pr->fetchColumn();

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
                  echo'<input type="hidden" id="list_size" value="'.$notif.'">';*/
                ?>
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header"><?php // echo 'You have '.$notif.' orders to update' ?></li>
              <li>
                <ul class="menu">
                <?php
                  /*$query = $dbh->prepare("SELECT o.symbol_id, o.price, s.symbol 
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
                    $pr ->bindParam(':id', $symb_id);
                    $pr ->execute();
                    $market_price = $pr->fetchColumn();

                    $pr = $dbh->prepare('SELECT margin FROM circuit_breaker WHERE name=:n');
                    $pr ->bindParam(':n',$cap_name);
                    $pr ->execute();
                    $cap = $pr->fetchColumn();

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
                    }                
                  }*/
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
                  $q = $dbh->prepare("SELECT u.`name`, u.address, u.email, u.phone, a.cd_code
                        FROM users u
                        LEFT JOIN client_account a ON u.cid = a.ID 
                        WHERE username =:m
                  ");
                  $q->bindParam(':m', $_SESSION['sess_username']);
                  $q->execute();
                  $qq = $q->fetch();
                  echo $qq['cd_code']. ', '. $qq['name'].'<small>'.$qq['address'].'</small>';
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
        <li><a href="../FILES/landing.php"><i class="fa fa-home"></i> Home</a></li>
        <li class="treeview">
          <a href="#">
             <i class="fa fa-sort-amount-desc"></i><span>Order Manager</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <?php if ($role == 12): ?>
                <li><a href="../FILES/bond_order_market.php"><i class="fa fa-sort text-red"></i> Bond Order Market</a></li>
            <?php endif ?>
          </ul>
        </li>

        <?php if ($role == 12): ?>
          <li class="treeview">
            <a href="#">
              <i class="fa fa-bold"></i><span>Bond Trading</span>
              <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
            </a>
            <ul class="treeview-menu">
              <li><a href="../FILES/bond_trading.php"><i class="fa fa fa-plus"></i> New Order</a></li>
              <li><a href="../FILES/b_pending_orders.php"><i class="fa fa fa-bars"></i> My Pending Orders</a></li>
              <li><a href="../FILES/b_cancel_ords.php"><i class="fa fa fa-minus"></i> Cancel Orders</a></li>
              <li><a href="../FILES/rfq_orders.php"><i class="fa fa fa-arrow-right"></i> Incoming RFQ</a></li>
              <li><a href="../FILES/bond_exec_orders.php"><i class="fa fa fa-exchange"></i> Executed Orders</a></li>
            </ul>
          </li>
        <?php endif ?>

        <li class="treeview">
          <a href="#"><i class="fa fa-files-o"></i> Reports
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
          <ul class="treeview-menu">
            <?php if (!empty($role) && $role == 12): ?>
              <li><a href="../REPORTS/tc.php"><i class="fa fa-circle-o"></i> Trade Confirmation</a></li>
              <li><a href="../REPORTS/fa.php"><i class="fa fa-circle-o"></i> Finance Activity</a></li>
              <!-- <li><a href="../REPORTS/c.php"><i class="fa fa-circle-o"></i> Commission</a></li> -->
              <li><a href="../REPORTS/cash-transaction.php"><i class="fa fa-circle-o"></i> Cash Transaction</a></li>
              <li><a href="../REPORTS/dTradeDetails.php"><i class="fa fa-circle-o"></i> Detailed Trade Details</a></li>
            <?php endif ?>

            <?php 
              $stmt = $dbh->prepare("SELECT i.gst_register
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

          </ul>
        </li>

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