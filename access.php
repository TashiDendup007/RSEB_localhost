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

    #loadingmsg { 
      top:0px;
      left: 0px;
      color: black;
      height: 100%;
      width:100%;
      background: url("GifLoader/Atom.gif")  50% 50% no-repeat rgb(78,82,84);
      background-repeat:no-repeat;
      position: fixed;
      z-index: 100;
    }

    #loadingover {
      background: black;
      z-index: 99;
      width: 100%;
      height: 100%;
      position: fixed;
      top: 0;
      left: 0;
      -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
      filter: alpha(opacity=80);
      -moz-opacity: 0.8;
      -khtml-opacity: 0.8;
      opacity: 0.8;
    }
    #qrcode canvas {
      border: 2px solid #5AC994;  /* Add a black border */
      padding: 10px;  /* Optional padding around the QR code */
      background-color: white;  /* Ensure the background color is white */
      display: block;
      margin: 0 auto;  /* Center the QR code within the div */
    }
    .centered-list {
            padding-left: 0;
            list-style-position: inside; /* Ensures the numbers are inside the container */
            text-align: center; /* Aligns the content of each list item */
            margin: 0 auto; /* Centers the entire list */
            display: inline-block; /* Helps in centering the list */
        }

    #deepLinkBtn {
        background: none; /* Remove background */
        border: none; /* Remove border */
        color: #007bff; /* Anchor-like color */
        text-decoration: underline; /* Underline text like a link */
        cursor: pointer; /* Change cursor to pointer on hover */
        padding: 0; /* Remove any padding */
        font-size: inherit; /* Inherit font size from parent */
    }

    /* Optional: Add hover effect to mimic anchor hover effect */
    #deepLinkBtn:hover {
        color: #0056b3; /* Darker shade of blue when hovered */
        text-decoration: none; /* Remove underline on hover */
    }
    /* Responsive styles */
    @media (max-width: 768px) {
        #deepLinkBtn, h2 {
            font-size: 16px; /* Adjust font size for smaller screens */
        }

        #deepLinkBtn {
            width: 30%; /* Make the button more flexible on smaller screens */
        }

        h2 {
            font-size: 18px; /* Adjust heading size for smaller screens */
        }
    }
    .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ccc;
            margin: 0 10px;
        }
        .divider span {
            color: #808080;
            font-size: 14px;
        }
  </style>
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


      <!-- <p style="margin-top: 10px; font-size: 20px;">Due to data migration and maintenance, the service is currently unavailable. We are working to restore it as soon as possible. Thank you for your patience.</p> -->

      <?php // exit(); ?>
        
      <p class="login-box-msg">Sign in to start your session</p>
      <form action="Authentication/verification.php" method="POST" onsubmit="showLoading();">
        <div class="form-group has-feedback">
          <input type="text" class="form-control button1" placeholder="UserName" name="username" autofocus required>
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input type="password" class="form-control button1" placeholder="Password" name="password" required>
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        
        <div class="row">
          <div class="col-lg-12 text-center">
            <button type="submit" class="button button1">&nbsp;&nbsp;&nbsp;<i class="fa fa-sign-in" aria-hidden="true"></i>  Login &nbsp;&nbsp;&nbsp;</button>
          </div>
        </div>
        <div class="divider">
          <span>Or Continue</span>
        </div>
        <div class="row">
          <div class="col-lg-12 text-center">
            <button type="button" onclick="authenticateWithAPI()" data-toggle="modal" data-target="#ndi_modal" class="button button1 text-white btn-bg cus-button" id="ndi_button" style="background: rgb(18, 65, 67); border-color: rgb(9, 121, 105); height: 32px; width: 100%; margin: 0; padding: 0;">
              <img src="img/NDI_logo.png" style="height: 25px; width: 25px; margin-right: 10px;">Login with Bhutan NDI
            </button>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-xs-8 left">
            <a href="#" class="btn" data-toggle="modal" data-target="#resetModalId"><i class="fa fa-fw fa-question"></i> Forgot Password</a>
          </div>
          <div class="col-xs-4 right">
            <a href="https://rsebl.org.bt/#/OnlineRenew/1" class="btn" target="_blank"><i class="fa fa-fw fa-refresh"></i> Renew</a>
          </div>
        </div>
        <p class="message">
          <?php
            $errors = array(
                1=>"Invalid Credentials, Try again",
                2=>"Please login to access this area",
                3=>"Password Changed Successfully",
                4=>"Your account has expired. Kindly renew.",
                5 => "Password reset successfully",
                6 => 'Your password cannot be the same as your username. Please reset it using "Forgot Password."',
                7 => 'Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character. Please reset it using "Forgot Password."',
                8 => 'You are using the default password. Please reset it using "Forgot Password."',
                9 => "Proof Not Shared,Please try again!",
              );
            $error_id = isset($_GET['err']) ? (int)$_GET['err'] : 0;
            if ($error_id >= 1 && $error_id <= 9) {
                // $alert_type = ($error_id == 1 || $error_id == 2) ? 'error' : (($error_id == 4 || $error_id == 6 || $error_id == 7) ? 'warning' : 'success');
                $alert_type = in_array($error_id, [1, 2]) ? 'error' : (in_array($error_id, [4, 6, 7, 8, 9]) ? 'warning' : 'success');
                echo '<div class="alert alert-' . $alert_type . ' alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>' . $errors[$error_id] . '</div>';
            }
           ?>
        </p>
      </form>

      <div class="modal fade" id="resetModalId" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="resetModalLabel">Password Reset</h4>
            </div>
            <form action="#" method="POST" id="resetPasswordForm">
              <div class="modal-body">
                <div id="reset_msg" class="mb-3"></div>
                
                <div class="form-group">
                  <label for="username">Username <span class="text-danger">*</span></label>
                  <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                </div>
                
                <div class="details_div d-none">
                  <div class="row">
                    <div class="col-md-6 form-group">
                      <label for="name">Name <span class="text-danger">*</span></label>
                      <input type="text" name="name" id="name" class="form-control" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                      <label for="contact">Contact No <span class="text-danger">*</span></label>
                      <input type="text" name="contact" id="contact" class="form-control" readonly>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" readonly>
                    <small class="form-text text-muted">Password will be sent to this email.</small>
                  </div>
                </div>
              </div>
              
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  <i class="fa fa-times mr-1"></i> Close
                </button>
                <button type="submit" class="btn btn-primary" id="password_reset_submit">
                  <i class="fa fa-share mr-1"></i> Submit
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="ndi_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body justify-content-center">
              <div class="card">
                <div class="card-body mt-2 mb-2">
                <div style="display: flex; align-items: center; justify-content: center; font-weight: 500; text-align: center;">
                      <h2 class="text-black mb-4" style="margin: 0; font-weight: inherit";>
                          Scan with <span style="color: #5AC994 !important;">Bhutan NDI </span> Wallet
                      </h2>
                  </div>
                  <div class="row" style="margin-top: 30px">
                    <div class="col-lg-12 col-md-12">
                      <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                          <div class="col-12 mt-3 text-center">
                            <div class="text-center" id="qrcode"><canvas width="200" height="200" style="display: none;"></canvas></div>
                            <img id="logo" src="nats/NDIlogobg.png" style="display: none;" />
                            <div class="row text-center">
                                <div class="counter" id="clockdiv" style="display: none;">
                                    <div class="sq">
                                      <span class="seconds bord" id="timer"></span> <span class="smalltext">Secs</span>
                                    </div>
                                </div>
                            </div>
                          </div>
                          <br>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row text-center" style="margin-top: 30px;">
                      <div class="col-lg-12 col-md-12">
                          <ol class="centered-list" style="padding-left: 20px; text-align: left;">
                              <li class="text-black">
                                  Open Bhutan NDI wallet on your phone.
                              </li>
                              <li class="text-black">
                                  Tap the Scan button located on the menu bar 
                                  <img src="img/ScanButton.png" class="header-brand-img mb-2" style="height:29px; margin-left: 5px;">
                                  <br>
                                  and capture the code.
                              </li>
                          </ol>
                      </div>
                  </div>

                  <div id="deepLink" class="text-center" style="display: none;">
                    <div style="display: flex; align-items: center; margin: 20px 0;">
                        <hr style="flex: 1; border: 1px solid #ccc;" />
                        <span style="margin: 0 10px; color: gray; font-weight: bold;">OR</span>
                        <hr style="flex: 1; border: 1px solid #ccc;" />
                    </div>
                    <div style="display: flex; align-items: center; justify-content: center; font-weight: 500; text-align: center;">
                        <h2 class="text-black mb-4" style="margin: 0; font-weight: inherit; padding-right: 5px;">
                            Open <span style="color: #5AC994 !important;">Bhutan NDI </span> Wallet
                        </h2>
                        <button id="deepLinkBtn" class="text-white" style="margin-left: -38px; padding: 0; font-size: inherit; line-height: inherit;">
                            Here
                        </button>
                    </div>
                  </div><br>
                  <div class="row" style="margin-top: 30px">
                      <div class="col-lg-12 col-md-12 text-center">
                          <a href="https://www.youtube.com/watch?v=A_k79pml9k8" target="_blank" class="btn btn-success text-white" style="color: white !important;background-color: #5AC994;border-color: #5AC994">
                              Watch video guide &nbsp;<i class="fa fa-play-circle"></i>
                          </a>
                      </div>
                  </div>
                  <div class="row" style="margin-top: 30px">
                      <div class="col-lg-12 col-md-12">
                          <ul class="list-unstyled">
                              <li class="text-black text-center">Download Now!
                                  <div class="app-link">
                                      <a href="https://play.google.com/store/search?q=bhutan%20ndi&amp;c=apps&amp;hl=en_IN&amp;gl=US" target="_blank" ><img src="img/play_store.png" style="height: 40px;"></a>
                                      <a href="https://apps.apple.com/in/app/bhutan-ndi/id1645493166" target="_blank" ><img src="img/app_store.png" style="height: 40px;"></a>
                                  </div>
                              </li>
                          </ul>
                      </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- <div class="modal fade" id="password_reset_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel"><strong>Password Reset</strong></h4>
            </div>
            <form action="" method="POST">
              <div class="modal-body">
                <input type="text" name="cid" id="cid" class="form-control" placeholder="Enter CID Number" maxlength="11" required>
                <span id="cidError" style="color: red;"></span>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="passwordreset"><i class="fa fa-database"></i> Submit</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
              </div>
            </form>
          </div>
        </div>
      </div> -->

    </div>
  </div>
  <?php include('GifLoader/gif.php'); ?>
  <script src="dist/js/jquery.min.js"></script>
  <script src="dist/js/jquery-qrcode.min.js"></script>
  <script src="nats/nats.js"></script>
</body>
<script type="text/javascript">
  // $("#password_reset_submit").click( function () {
  //     const usr_nam = $("#username").val(); 
  //     const pwd = $("#new_pwd").val(); 
  //     const confirm = $("#confirm_pwd").val(); 

  //     if (usr_nam === '' || pwd === '' || confirm === '') {
  //       alert("All fields are Mandatory");
  //       return false;
  //     }

  //     if (pwd != confirm) {
  //       alert("The new and confirm passwords do not match");
  //       return false;
  //     } else {
  //       $.ajax({
  //         type: "POST",
  //         url: "Authentication/password_reset.php",
  //         data: "reset_password=reset_password&username=" + usr_nam + "&password=" + pwd + "&confirm_password=" + confirm,
  //         dataType:"json",
  //         success: function(data) {
  //           if (data.status === 'success') {
  //             window.location.href = data.redirect;
  //           } else {
  //             $("#reset_msg").show().html(data.message);
  //             setTimeout(function() { $("#reset_msg").hide(); }, 7000);
  //           }
  //         },
  //         error: function(xhr, status, error) {
  //             console.error("AJAX error: " + status + ' : ' + error);
  //         }
  //       });
  //     }
  // });

  /*$("#passwordreset").click(function() {
    showLoading();
    var operation = "ResetFromMobileApp";
    var cidFld = $("#cid");

    if (cidFld.val() === '') {
      hideloading();
      $("#cidError").html("Enter CID Number");
      return false;
    }

    if (cidFld.val().length < 11) {
      hideloading();
      $("#cidError").html("CID Number should be 11 digits");
      return false;
    }

    if (isNaN(cidFld.val())) {
      hideloading();
      $("#cidError").html("The CID should only contain numerical values");
      return false;
    }

    $('#password_reset_modal').modal('hide');
    
    var data = {
      PassWordReset: operation,
      CID: cidFld.val()
    };
    
    $.ajax({
      type: "POST",
      url: "http://localhost/RSEB2020/api2/indivclentholding.php",
      data: data, 
      dataType: "html",
      success: function(response) { 
        hideloading();
        var data = JSON.parse(response);
        const statusMsg = $('<div>').addClass('alert alert-success alert-dismissible').append(
          $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
            $('<i>').addClass('icon fa fa-check'), data.message );
        $("#message").html(statusMsg);
        showMessage();
      },
      error: function(xhr, status, error) {
        console.error(xhr.responseText);
        alert("An error occurred. Please try again later.");
      }
    });
  });*/

  $("#cid").click(function() {
    $("#cidError").html("");
  });

  $("#password_reset_submit").click(function() {
    var username = $('#username').val();
    if (username) {
        $.ajax({
            url: 'Authentication/password_reset.php', // Your PHP file
            type: 'POST',
            data: { 'get_username_details':'get_username_details', 'username': username },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show and populate fields
                    $(".details_div").show();
                    $("#name").val(response.name);
                    $("#email").val(response.email);
                    $("#contact").val(response.contact);

                    $("#password_reset_submit").text("Reset");
                    $("#password_reset_submit").attr("id", "reset_password");

                    // Change Name
                    
                } else {
                    $(".details_div").hide();
                    alert(response.message);
                }
            },
            error: function() {
                alert("Error fetching user details!");
            }
        });
    } else {
        $(".details_div").hide();
    }
});

$(document).on('click', '#reset_password', function() {
    var username = $('#username').val();
    showLoading();
    if (username) {
        $.ajax({
            url: 'Authentication/password_reset.php', // Your PHP file
            type: 'POST',
            data: { 'reset_password':'reset_password', 'username': username },
            dataType: 'json',
            success: function(response) {
                if (response.status == "success") {
                    // Show and populate fields
                    hideloading();
                    $("#reset_msg").html(`
                      <div class="alert alert-success show" role="alert">
                        Password reset successful! Please check your email.
                      </div>
                    `).show();
                    setTimeout(() => {
                      $(".alert").alert('close');
                    }, 5000);
                    setTimeout(() => {
                      window.location = window.location.href;
                    }, 6000);
                } else {
                    hideloading();
                    $(".details_div").hide();
                    alert(response.message);
                }
            },
            error: function() {
                hideloading();
                alert("Error Updating Password!");
            }
        });
    } else {
        hideloading();
        $(".details_div").hide();
    }
});

</script>
</html>