 <header class="main-header">
    <!-- Logo -->
    <a href="../FILES/custodial_landing.php" class="logo">
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>RSEB</b>-Custodial</span>
    </a>
    <script type="text/javascript">
$(document).ready(function(){
 $('<audio id="chatAudio"><source src="notify.mp3" type="audio/mpeg"></audio>').appendTo('body');
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
        <ul class="nav navbar-nav">
          <!-- Notifications: style can be found in dropdown.less -->
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
                $list=$query->rowcount();
                echo $list;
               echo '<input type="hidden" id="list_size" value="'.$list.'">';
                ?>
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header"><?php echo 'You have '.$list.' operations to process' ?></li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                <?php
                  while($result=$query->fetch())
                  {
                    if($result['announcement_type']==1){$a_type='Rights';} elseif($result['announcement_type']==2){$a_type='Bonus';}
                    elseif($result['announcement_type']==3){$a_type='Dividend';}
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
                  $q = $dbh->prepare('SELECT * from users where username =:m');
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
        <li class="header">MAIN NAVIGATION</li>
        <li class="treeview">
          <a href="../FILES/accountRegistration.php">
            <i class="fa fa-registered"></i> <span>Account Registration</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
        <li class="treeview">
          <a href="../FILES/shareVolEntry.php">
            <i class="fa fa-share-alt"></i> <span>Volume Entry</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
        <li class="treeview">
          <a href="../REPORTS/generalshareholder_list.php">
            <i class="fa fa-file"></i> <span>General Share Holder List</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
        <li class="treeview">
          <a href="../REPORTS/individual_report.php">
            <i class="fa fa-user"></i> <span>Individual Report</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
        </li>
      </ul>
    </section>
  </aside>
