 <header class="main-header">
    <a href="../FILES/Admin-dashboard.php" class="logo">
      <span class="logo-lg"><b>RSEB</b> Admin</span>
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
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="../../dist/img/avatar5.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $_SESSION['sess_username'];?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="../../dist/img/avatar5.png" class="img-circle" alt="User Image">
                <p>
                <?php $m = $_SESSION['sess_username'] ;
                 $q = $dbh->prepare('SELECT * FROM users where username =:m');
                 $q->bindParam(':m', $_SESSION['sess_username']);
                 $q->execute();
                 $qq = $q->fetch();
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
        <!-- <li class="header">MAIN NAVIGATION</li> -->
        <li><a href="../FILES/Admin-dashboard.php"><i class="fa fa-home"></i> <span>Home</span></a></li>
        
        <!-- <li><a href="../FILES/users.php"><i class="fa fa-user"></i><span>User Creation</span></a></li> -->

        <li class="treeview">
          <a href="#">
            <i class="fa fa-user"></i> <span>User Creation</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/users.php"><i class="fa fa-user-plus"></i> <span>New User</span></a></li>            
            <li><a href="../FILES/user_pwd_reset.php"><i class="fa fa-key"></i>User Password Reset</a></li>
          </ul>
        </li>
        
        <li><a href="../FILES/participants.php"><i class="fa fa-users"></i> <span>Participants</span></a></li>
        <li><a href="../FILES/institution.php"><i class="fa fa-bank"></i> <span>Institution</span></a></li>
        <li><a href="../FILES/settings.php"><i class="fa fa-refresh"></i> <span>LinkUser</span></a></li>
        <li><a href="../FILES/m_watch.php"><i class="fa fa-bar-chart"></i> <span>Market Watch</span></a></li>
        <li><a href="../FILES/cancel_order.php"><i class="fa fa-remove"></i> <span>Cancel Order</span></a></li>
        <li><a href="../FILES/db_backup_log.php"><i class="fa fa-database"></i> <span>DB Backup Log</span></a></li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-file"></i> <span>Configurations</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/symbol.php"><i class="fa fa-bank"></i> <span>Symbol</span></a></li>            
            <li><a href="../FILES/price.php"><i class="fa fa-circle-o"></i>Price Update</a></li>
            <li><a href="../FILES/assignB.php"><i class="fa fa-bank"></i> <span>Assign Broker</span></a></li>
            <!-- <li><a href="../FILES/ipo.php"><i class="fa fa-bank"></i> <span>IPO</span></a></li> -->
            <!-- <li><a href="../PROCESS/riup.php"><i class="fa fa-lock"></i> CDS Holding Update RIGHTS</a></li>
            <li><a href="../PROCESS/ipoupdate.php"><i class="fa fa-lock"></i> CDS Holding Update IPO</a></li>
            <li><a href="../PROCESS/bondudate.php"><i class="fa fa-lock"></i> CDS Holding Update BOND</a></li> -->
            <li><a href="../FILES/trade_schedular.php"><i class="fa fa-binoculars"></i> Trade Schedular Mode</a></li>
            <li><a href="../FILES/user_role.php"><i class="fa fa-user-plus"></i> Create User Role</a></li>
            <li><a href="../FILES/create_sector.php"><i class="fa fa-magic"></i> Create Sector</a></li>
            <li><a href="../FILES/circuit_breaker.php"><i class="fa fa-gg"></i> Circuit Breaker</a></li>
            <li><a href="../FILES/create_occupation.php"><i class="fa fa-tasks"></i> Create Occupation</a></li>
            <li><a href="../FILES/create_corp_action.php"><i class="fa fa-compress"></i> Create Corporate Master</a></li>
            <li><a href="../FILES/create_rights_offer.php"><i class="fa fa-chevron-right"></i> Create Rights Offer</a></li>
            <li><a href="../FILES/create_bond_offer.php"><i class="fa fa-chevron-left"></i> Create Bond Offer</a></li>
            <li><a href="../FILES/create_ipo_offer.php"><i class="fa fa-chevron-down"></i> Create IPO Offer</a></li>
            <li><a href="../FILES/create_auction.php"><i class="fa fa-money"></i> Create Auction</a></li>
            <li><a href="../FILES/add_email.php"><i class="fa fa-envelope"></i> Add Broker Email</a></li>

          </ul>
        </li>
        <li><a href="../FILES/cidUpdate.php"><i class="fa fa-refresh"></i> <span>Update CID</span></a></li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-video-camera"></i> <span>Surveillance</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../REPORTS/monthwise_trade.php"><i class="fa fa-circle-o"></i> Trade Dtls (Month Wise) </a></li>
            <li><a href="../REPORTS/daily_trade.php"><i class="fa fa-circle-o"></i> Daily Trade with price</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-users"></i> <span>Online Users</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/userList.php"><i class="fa fa-user-times"></i> Approval List</a></li>
            <li><a href="../FILES/userApprovedList.php"><i class="fa fa-user-plus"></i> Approved List</a></li>
          </ul>
        </li>
        <li><a href="../FILES/negativeCheck.php"><i class="fa fa-minus-circle" aria-hidden="true"></i> <span>Negative Check</span></a></li>
        <!-- <li><a href="../FILES/kiduAuction.php"><i class="fa fa-minus-circle" aria-hidden="true"></i> <span>Kidu Auction</span></a></li> -->
        <li class="treeview">
          <a href="#">
            <i class="fa fa-th-list"></i> <span>Process</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/corporate_process.php"><i class="fa fa-tasks"></i> Corporate Action Migration</a></li>
            <li><a href="../FILES/bond_allocation.php"><i class="fa fa-circle-o"></i> BOND Allocation</a></li>
            <li><a href="../FILES/ipo_allocation.php"><i class="fa fa-circle-o"></i> IPO Allocation</a></li>

            <!-- <li><a href="../PROCESS/riup.php"><i class="fa fa-circle-o"></i> CDS Holding Update Rights</a></li>
            <li><a href="../PROCESS/ipoupdate.php"><i class="fa fa-circle-o"></i> CDS Holding Update IPO</a></li> -->
            <li><a href="../PROCESS/bondudate.php"><i class="fa fa-circle-o"></i> CDS Holding Update Bond</a></li>
            <!-- <li><a href="../PROCESS/prorata.php"><i class="fa fa-lock"></i> Process Rights Auction</a></li> -->
          </ul>
        </li>
      </ul>
    </section>
  </aside>