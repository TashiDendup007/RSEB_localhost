<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>RSEB | CapitalMarketSolution</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  
  <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
  <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
  <style type="text/css">
    .button {
      background-color: #5D3FD3;
      border: none;
      color: white;
      padding: 10px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      box-shadow: 5px 2px 10px #301934;
    }
    .button1 { border-radius: 15px; }
  </style>
  <?php
  session_start();
  // Check if the 'usernames' session variable is set
  if (isset($_SESSION['usernames'])) {
      $usernames = $_SESSION['usernames'];
      $threadId = $_SESSION['threadId'];
      $relationshipid = $_SESSION['relationshipid'];
      // Unset the session variable to clear it after use
      unset($_SESSION['usernames']);
  } else {
      // Handle the case where the 'usernames' session variable is not set
      $usernames = array();
  }

  // Output the <select> dropdown
  ?>
  <?php include('GifLoader/gifComponent.php'); include ('CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition login-page" style="color:white;background: radial-gradient(#5D3FD3, #301934);">
  <!-- <div class="login-box-body" id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none; color:#fff;'></div></div> -->
  <div id="message"></div>
  <div class="login-box">
    <div class="login-logo">
      <a><b style="color:white;">RSEB-CaMS-</b><span style="color:magenta;">Access</span></a>
    </div>
    <div class="login-box-body button1">
      <div class="row">
        <img src="img/rseb_logo2.png" class="profile-user-img img-responsive " />
      </div>
      <p class="login-box-msg">Select User to start your session</p>
      <form action="" method="POST" onsubmit="showLoading();">
        <div class="form-group has-feedback">
        <input type="text" id="threadId" value="<?php echo $threadId; ?>">
        <input type="text" id="relationshipid" value="<?php echo $relationshipid; ?>">
        <select class="form-control button1" id="usernameSelect" name="selectedUsername" onchange="handleSelectChange(this.value)">
            <option value="">--SELECT--</option>
            <?php
            foreach ($usernames as $username) {
                echo '<option value="' . htmlspecialchars($username) . '">' . htmlspecialchars($username) . '</option>';
            }
            ?>
        </select>
        </div>
        <hr>
        <div class="row">
          <div class="text-center">
            <a href="access.php" class="btn"><i class="fa fa-arrow-circle-o-left"></i> Back to Login</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php include('GifLoader/gif.php'); ?>
</body>
<script src="nats/nats.js"></script>
</html>