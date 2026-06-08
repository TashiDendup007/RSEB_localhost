<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
$username=$_SESSION['sess_username'];
$cdcode=find_link_user_cd_code($username);
$list= ins_id($username);
$ins_id=$list[0];$p_code=$list[1];
?><!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
  <!-- Site wrapper -->
  <div class="wrapper">
    <?php include('../NAV/navigation.php') ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="te-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">earnings</a></li>
        </ol>
      </section>
      <!-- Main content -->
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php') ?>
        <div class="box">
          <div class="row">


            </div>
          </div>
          <!-- /.box -->
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
      <?php include('../NAV/footer.php') ?>
    </div>
  </body>
  <script type="text/javascript">

</script>
</html>
