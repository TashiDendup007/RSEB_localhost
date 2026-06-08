 <header class="main-header">
    <a href="../FILES/ptrs_landing.php" class="logo">
      <span class="logo-lg"><b>RSEB</b>-PTRS</span>
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
                date_default_timezone_set("Asia/Thimphu");
                $current_date=date("Y-m-d");
                $query= $dbh->prepare('SELECT c.corp_announcement_id,s.symbol,c.rate,c.announcement_type,c.record_date from corporate_announcement c,
                symbol s where c.symbol_id=s.symbol_id and c.record_date=:cd and c.status=1');
                $query->bindParam(':cd',$current_date);
                $query->execute();
                $list1=$query->rowcount();
              ?>
              <?php 
                $query1= $dbh->prepare("SELECT 
                  t.symbol_id, m.symbol, m.name
                  FROM 
                  (SELECT 
                  r.symbol_id,
                  MAX(DATE(r.order_date)) Order_Date
                  FROM executed_orders r 
                  GROUP BY r.symbol_id 
                  ORDER BY r.symbol_id ASC
                  ) t
                  LEFT JOIN symbol m ON t.symbol_id = m.symbol_id 
                  LEFT JOIN market_price p ON t.symbol_id = p.symbol_id 
                  -- WHERE t.Order_Date NOT BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW() 
                  -- WHERE t.Order_Date < DATE(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
                  WHERE DATE(t.Order_Date) < DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH)) 
                  AND DATE(p.date) < DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH)) 
                  AND m.security_type NOT IN ('GB', 'CP') AND m.status=1 AND m.trsstatus=1 GROUP BY t.symbol_id 
                  UNION ALL
                  SELECT s.symbol_id, s.symbol, s.name 
                  FROM symbol s WHERE s.symbol_id NOT IN (SELECT symbol_id FROM executed_orders r) AND s.security_type ='OS' 
                  AND s.status='1' AND s.trsstatus='2' ORDER BY symbol ASC");
                $query1->execute();
                $list2=$query1->rowcount();
              ?>
              <?php
                $list = $list2 + $list1;
                echo $list;
                echo '<input type="hidden" id="list_size" value="'.$list.'">';
              ?>
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header"><?php echo 'You have '.$list1.' operations to process' ?></li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                <?php
                  while($result=$query->fetch())
                  {
                    if($result['announcement_type']==1){
                      $a_type='Rights';
                    } 
                    elseif($result['announcement_type']==2){
                      $a_type='Bonus';
                    }
                    elseif($result['announcement_type']==3){
                      $a_type='Dividend';
                    }
                      echo'
                      <li>
                        <a href="#">
                          <i class="fa fa-warning text-red"></i> '.$a_type.' for '.$result['symbol'].' - '.$result['record_date'].'
                        </a>
                      </li>';
                  }
                ?>
                </ul>
              </li>
              <li class="footer"><a href="../FILES/exe_corporate_announcement.php">Process</a></li>
              <li class="header"><?php echo 'You have '.$list2.' operations to process for Price Adjustment' ?></li>
              <li class="footer"><a href="../FILES/price_update.php">Click here to process</a></li>
            </ul>
          </li>
          <!-- User Account: style can be found in dropdown.less -->
           <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="../../dist/img/avatar5.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $_SESSION['sess_username'];?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="../../dist/img/avatar5.png" class="img-circle" alt="User Image">
                <p>
                <?php $m= $_SESSION['sess_username'] ;
                  $q = $dbh->prepare('SELECT * FROM users WHERE username =:m');
                  $q->bindParam(':m', $_SESSION['sess_username']);
                  $q->execute();
                  $qq= $q->fetch();
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
  <aside class="main-sidebar">
    <section class="sidebar">
      <ul class="sidebar-menu">
        <li><a href="../FILES/acc-reg.php"><i class="fa fa-user"></i> <span>Search Account</span></a></li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-file"></i> <span>Reports</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li>
              <a href="#"><i class="fa fa-lock text-red"></i> Depository
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="../REPORTS/account_activity.php"><i class="fa fa-circle-o"></i> Account Activity</a></li>
                <li><a href="../REPORTS/individual_report.php"><i class="fa fa-circle-o"></i> Individual Report</a></li>
                <li><a href="../REPORTS/top_vol_leaders.php"><i class="fa fa-circle-o"></i> Top Volume Leaders</a></li>
                <li><a href="../REPORTS/no_of_shareholders.php"><i class="fa fa-circle-o"></i> Number of Shareholders</a></li>
                <li><a href="../REPORTS/announcement.php"><i class="fa fa-circle-o"></i> Announcement Report</a></li>
                <li><a href="../REPORTS/entitlement_list.php"><i class="fa fa-circle-o"></i> Entitlement Lists</a></li>
                <li><a href="../REPORTS/generalshareholder_list.php"><i class="fa fa-circle-o"></i> General Shareholders Lists</a></li>
                 <li><a href="../REPORTS/numberofshares.php"><i class="fa fa-circle-o"></i> No of Shares</a></li>
                <li><a href="../REPORTS/pledge.php"><i class="fa fa-circle-o"></i> Pledge</a></li>
                <li><a href="../REPORTS/orders.php"><i class="fa fa-circle-o"></i> Orders_audit</a></li>
                <li><a href="../REPORTS/rights.php"><i class="fa fa-circle-o"></i> Rights Issue</a></li>
                <li><a href="../REPORTS/rights-unsubscribed.php"><i class="fa fa-circle-o"></i> RIGHTS Unsubscribed List</a></li>
                <li><a href="../REPORTS/rights-refund.php"><i class="fa fa-circle-o"></i> RIGHTS AUCTION LIST</a></li>
                <li><a href="../REPORTS/commionsReport.php"><i class="fa fa-circle-o"></i> Commision Report</a></li>
                <li><a href="../REPORTS/price_adjustment.php"><i class="fa fa-circle-o"></i> Price Adjustment(Untraded)</a></li>
                <li><a href="../REPORTS/pledge_audit.php"><i class="fa fa-circle-o"></i> No of Pledge Audit</a></li>
                <li><a href="../REPORTS/historical_data.php"><i class="fa fa-circle-o"></i>Historical Data of Script<br>(High, Low & Market Price)</a></li>
                <li><a href="../REPORTS/share_details_statement.php"><i class="fa fa-circle-o"></i> Shares Statement Activity</a></li>
                <li><a href="../REPORTS/capitalisation.php"><i class="fa fa-money"></i> Market Cap</a></li>
              </ul>
            </li>
            <li>
              <a href="#"><i class="fa fa-refresh text-aqua"></i> Clearing
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="../REPORTS/net.php"><i class="fa fa-circle-o"></i>Detail Netting </a></li>
                <li><a href="../REPORTS/netsum.php"><i class="fa fa-circle-o"></i>Clearing Detail </a></li>
                <li><a href="../REPORTS/ptrs.php"><i class="fa fa-circle-o"></i>Trade Details</a></li>
                <li><a href="../REPORTS/detailedtradedetails.php"><i class="fa fa-circle-o"></i>Detailed Trade Details</a></li>
              </ul>
            </li>
            
          </ul>
        </li>
        <li>
          <a href="../FILES/price_update.php">
            <i class="fa fa-money"></i> <span>Price Adjustment</span>
          </a>
        </li>
        <li>
          <a href="../FILES/indexUpdate.php">
            <i class="fa fa-file"></i> <span>Bhutan Stock Index(BSI)</span>
          </a>
        </li>
        
        <li>
          <a href="../FILES/SectorIndexUpdate.php">
            <i class="fa fa-line-chart"></i> <span>Sector Index</span>
          </a>
        </li>

        <li><a href="../FILES/user_unlock.php"><i class="fa fa-user"></i> <span>User Unlock</span></a></li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
