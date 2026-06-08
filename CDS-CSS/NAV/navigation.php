<?php
  date_default_timezone_set("Asia/Thimphu");
  include ('../../CONNECTIONS/db.php');
  $username = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : '';
  if (!$username) {
    header('Location: ../../access.php?err=2');
    exit;
  }
?>
 <header class="main-header">
    <a href="../FILES/cds-css-landing.php" class="logo">
      <span class="logo-lg"><b>RSEB</b>-CDS&CSS</span>
    </a>
    <script type="text/javascript">
      $(document).ready(function(){
        $('<audio id="chatAudio"><source src="notify.mp3" type="audio/mpeg"></audio>').appendTo('body');
        var a = $("#list_size").val();
        if(a > 0){
          // $('#chatAudio')[0].play();
        }
      });
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

                $query= $dbh->prepare('SELECT c.corp_announcement_id,s.symbol,c.rate,c.announcement_type,c.record_date from corporate_announcement c,
                symbol s where c.symbol_id=s.symbol_id AND c.record_date=:cd AND c.status=1');
                $query->bindParam(':cd',$current_date);
                $query->execute();
                $list = $query->rowcount();
                echo $list;
                echo '<input type="hidden" id="list_size" value="'.$list.'">';
              ?>
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header"><?php echo 'You have '.$list.' operations to process'; ?></li>
              <li>
                <ul class="menu">
                <?php
                while($result = $query->fetch()) {
                  switch ($result['announcement_type']) {
                      case 1:
                          $a_type = 'Rights';
                          break;
                      case 2:
                          $a_type = 'Bonus';
                          break;
                      case 3:
                          $a_type = 'Dividend';
                          break;
                      default:
                          $a_type = 'Buy Back';
                          break;
                  }
                  echo '
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
            </ul>
          </li>
           <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="../../dist/img/avatar5.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $_SESSION['sess_username']; ?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="../../dist/img/avatar5.png" class="img-circle" alt="User Image">
                <p>
                  <?php 
                    $q = $dbh->prepare("SELECT name, address, phone, email FROM users WHERE username = ?");
                    $q->execute([$username]);
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
        <?php if ($role == 3): ?>
        <li><a href="../FILES/account_reg.php"><i class="fa fa-users"></i> <span>Account Registration</span></a></li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-bullhorn"></i> <span>Corporate Actions</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/corporate_action.php"><i class="fa fa-circle-o text-red"></i> Corporate Action</a></li>
          </ul>
        </li>
        
        <li class="treeview">
          <a href="#">
            <i class="fa fa-arrows"></i> <span>Transactions</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/deposit.php"><i class="fa fa-lock text-red"></i>Withdraw / Deposit</a></li>
            <li><a href="../FILES/transfer.php"><i class="fa fa-exchange text-red"></i> Transfer (Posting)</a></li>
            <li><a href="../FILES/pledge-contract.php"><i class="fa fa-folder text-red"></i> Pledge Contract</a></li>
            <li><a href="../FILES/pledge.php"><i class="fa fa-folder text-red"></i> Pledge</a></li>
            <li><a href="../FILES/pledge-release.php"><i class="fa fa-folder text-red"></i> Pledge Release</a></li>
            <li><a href="../FILES/block-unblock.php"><i class="fa fa-folder text-red"></i> Block/Unblock</a></li>
          </ul>
        </li>

        <?php if (in_array($username, ['EMPRSEB002', 'EMPRSEB027', 'EMPRSEB009'], true)): ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-calendar-check-o"></i> <span>Clearing & Settlement</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/single-clearing.php"><i class="fa fa-circle-o text-aqua"></i> Equity Single Clearing</a></li>
            <li><a href="../FILES/bond_single_clearing.php"><i class="fa fa-circle-o text-aqua"></i> Bond Single Clearing</a></li>
          </ul>  
        </li>
        <?php endif ?>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-gear"></i> <span>Configuration</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/bank.php"><i class="fa fa-lock text-red"></i>Bank</a></li>
            <li><a href="../FILES/bank-branch.php"><i class="fa fa-lock text-red"></i>Bank Branch</a></li>
            <li><a href="../FILES/occupation.php"><i class="fa fa-lock text-red"></i>Occupation</a></li>
            <li><a href="../FILES/pledgee.php"><i class="fa fa-lock text-red"></i>Pledgee</a></li>
            <li><a href="../FILES/sett.php"><i class="fa fa-lock text-red"></i>Settlement Cycle</a></li>
            <li><a href="../FILES/hol.php"><i class="fa fa-lock text-red"></i>Holiday</a></li>
            <li><a href="../FILES/sett_cal.php"><i class="fa fa-lock text-red"></i>Settlement Calendar</a></li>
            <li><a href="../FILES/createBond.php"><i class="fa fa-lock text-red"></i>Create Symbol</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-user"></i> <span>Administration</span>
            <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/exe_corporate_announcement.php"><i class="fa fa-circle-o text-red"></i> Process Corp Announcement</a></li>
            <li><a href="../FILES/backup.php"><i class="fa fa-circle-o text-red"></i> Back Up</a></li>
            <li><a href="../FILES/download.php"><i class="fa fa-circle-o text-red"></i> Download</a></li>
            <li><a href="../FILES/price_adjustment.php"><i class="fa fa-circle-o text-red"></i> Price Adjustment</a></li>
          </ul>
        </li>
        <li><a href="../FILES/cidUpdate.php"><i class="fa fa-refresh"></i> <span>Update CID</span></a></li>
        <?php endif ?>
        
        <!-- <li class="treeview">
          <a href="#">
            <i class="fa fa-user"></i> <span>Escrow Account</span>
            <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/upload.php"><i class="fa fa-circle-o text-red"></i> Upload</a></li>
            <li><a href="../FILES/payment.php"><i class="fa fa-circle-o text-red"></i> Payment</a></li>
            <li><a href="../FILES/cdcodeUpdate.php"><i class="fa fa-circle-o text-red"></i> Update Details</a></li>
            <li><a href="../FILES/TDS.php"><i class="fa fa-circle-o text-red"></i> TDS</a></li>
          </ul>
        </li> -->

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
                <!-- <li><a href="../REPORTS/account_activity.php"><i class="fa fa-circle-o"></i> Account Activity</a></li> -->
                <li><a href="../REPORTS/individual_report.php"><i class="fa fa-circle-o"></i> Individual Report</a></li>
                <li><a href="../REPORTS/capitalisation.php"><i class="fa fa-circle-o"></i> Market Cap</a></li>
                <!-- <li><a href="../REPORTS/daily_transaction.php"><i class="fa fa-circle-o"></i> Daily Transaction Report</a></li> -->
                <li><a href="../REPORTS/top_vol_leaders.php"><i class="fa fa-circle-o"></i> Top Volume Leaders</a></li>
                <li><a href="../REPORTS/no_of_shareholders.php"><i class="fa fa-circle-o"></i> Number of Shareholders</a></li>
                <li><a href="../REPORTS/announcement.php"><i class="fa fa-circle-o"></i> Announcement Report</a></li>
                <li><a href="../REPORTS/entitlement_list.php"><i class="fa fa-circle-o"></i> Entitlement Lists</a></li>
                <li><a href="../REPORTS/generalshareholder_list.php"><i class="fa fa-circle-o"></i> General Shareholders Lists</a></li>
                 <li><a href="../REPORTS/numberofshares.php"><i class="fa fa-circle-o"></i> No of Shares</a></li>

                <li><a href="../REPORTS/pledge.php"><i class="fa fa-circle-o"></i> Pledge</a></li>
                <li><a href="../REPORTS/pledgedetails.php"><i class="fa fa-circle-o"></i> Pledge Details</a></li>
                <li><a href="../REPORTS/pledge_release.php"><i class="fa fa-circle-o"></i>Pledge Release</a></li>
                
                <li><a href="../REPORTS/orders.php"><i class="fa fa-circle-o"></i> Orders_audit</a></li>
                <li><a href="../REPORTS/rights.php"><i class="fa fa-circle-o"></i> Rights Issue</a></li>
                <li><a href="../REPORTS/rights-unsubscribed.php"><i class="fa fa-circle-o"></i> RIGHTS Unsubscribed List</a></li>
                <li><a href="../REPORTS/rights-refund.php"><i class="fa fa-circle-o"></i> RIGHTS AUCTION LIST</a></li>
                <li><a href="../REPORTS/ipo_orders.php"><i class="fa fa-circle-o"></i> IPO Orders</a></li>
                <!-- <li><a href="../REPORTS/deposits.php"><i class="fa fa-circle-o"></i> Deposit</a></li> -->
                <li><a href="../REPORTS/commionsReport.php"><i class="fa fa-circle-o"></i> Commision Report</a></li>
                <li><a href="../REPORTS/bondReport.php"><i class="fa fa-circle-o"></i>Bond Report</a></li>
                <li><a href="../REPORTS/pending.php"><i class="fa fa-circle-o"></i>Pending orders</a></li>
                <li><a href="../REPORTS/balance-confirmation.php"><i class="fa fa-circle-o"></i>Balance Confirmation</a></li>
                <li><a href="../REPORTS/terminalUser.php"><i class="fa fa-circle-o"></i>Terminal Users</a></li>
                <li><a href="../REPORTS/consolidate_report.php"><i class="fa fa-circle-o"></i>Consolidated Share Report</a></li>
                <li><a href="../REPORTS/share_details_statement.php"><i class="fa fa-circle-o"></i> Shares Statement Activity</a></li>
                <li><a href="../REPORTS/historical_data.php"><i class="fa fa-circle-o"></i>Historical Data of Script<br>(High, Low & Market Price)</a></li>
                <li><a href="../REPORTS/cid_update_audit.php"><i class="fa fa-circle-o"></i>CID Updated Audit</a></li>
                <!-- <li><a href="../REPORTS/escrow_account.php"><i class="fa fa-circle-o"></i>Escrow Account Report</a></li> -->
                <li><a href="../REPORTS/bond_subscription_report.php"><i class="fa fa-circle-o"></i>Bond Subscription Report</a></li>
                <li><a href="../REPORTS/share_transfer_report.php"><i class="fa fa-circle-o"></i>Share Transfer Report</a></li>
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
                <li><a href="../REPORTS/report_to_broker.php"><i class="fa fa-circle-o"></i>Settlement report to broker</a></li>
              </ul>
            </li>
          </ul>

          <?php if ($role == 9): ?>
          <li><a href="../REPORTS/rma_pledge_report.php"><i class="fa fa-paperclip" aria-hidden="true"></i> <span>Pledge Report RMA</span></a></li>
          <li><a href="../REPORTS/usrEMDList.php"><i class="fa fa-money"></i> <span>Online Terminal EMD</span></a></li>
          <li><a href="../REPORTS/bla_wallet.php"><i class="fa fa-dollar"></i> <span>BLA Wallet Transaction</span></a></li>
          <li><a href="../REPORTS/bla_wallet_balance.php"><i class="fa fa-dollar"></i> <span>Overall Wallet Balance</span></a></li>
          <?php endif ?>

          <?php if ($role == 3): ?>
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
              <li><a href="../FILES/user_list_bv.php"><i class="fa fa-user-plus"></i> Super Verification</a></li>
            </ul>
          </li>
          <li><a href="../FILES/oss_check.php"><i class="fa fa-share"></i> <span>Online Share Statement</span></a></li>
          <!-- <li><a href="../FILES/nrb_app_list.php"><i class="fa fa-flag"></i> <span>Non-Resident Bhutanese</span></a></li> -->
          <li><a href="../FILES/user_unlock.php"><i class="fa fa-unlock"></i> <span>User Unlock</span></a></li>
        </li>
        <?php endif ?>

        <?php if (in_array($username, ['EMPRSEB001', 'EMPRSEB002', 'EMPRSEB009', 'EMPRSEB027'], true)): ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-long-arrow-right"></i> <span>Share Transfer</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../FILES/share_transfer.php"><i class="fa fa-send-o text-red"></i> Submit</a></li>
            <?php if (in_array($username, ['EMPRSEB001', 'EMPRSEB009'], true)): ?>
              <li><a href="../FILES/share_transfer_list.php"><i class="fa fa-check text-red"></i> Approval</a></li>
            <?php endif ?>
          </ul>  
        </li>
        <?php endif ?>

      </ul>
    </section>
  </aside>