<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="3")
  {
    header('Location: ../../access.php?err=2');
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); 
    }
  }
  $_SESSION['timeout'] = time();
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
  <div id="cidModal"></div>
  <div class="wrapper">
  <?php include('../NAV/navigation.php') ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">CDCODE-CID-Update</a></li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <form action="" method="POST" onsubmit="showLoading();">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">CD Code/CID Update</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
          <div class="box-body" id="message" style='display: none;'></div>
            <div class="row">
                <div class="col-sm-6">
                  <div class="form-group clearfix">
                      <input type="radio" class="" id="radioPrimary1" name="cid_cd" value="cid" onclick="show_div(this.value)">
                      <label for="radioPrimary1"> CID Update
                      </label>
                      &nbsp;&nbsp;&nbsp;
                      <input type="radio" class="" id="radioPrimary1" name="cid_cd" value="cd" onclick="show_div(this.value)">
                      <label for="radioPrimary1"> CD CODE Update
                      </label>
                  </div>
                </div>
            </div>
            <div class="row cd_div" style="display: none;">
              <div class="col-xs-4">
                <label>OLD CD CODE</label>
                <?php
                  $cn= $dbh->prepare("SELECT DISTINCT cd_code from unclaimed_dividend ORDER BY cd_code ASC");
                  $cn->execute();
                  echo'<select class="form-control select2bs4" style="width: 100%;" name="ocdCode" id="ocdCode">';
                  echo '<option value=" ">-SELECT-</option>';
                  while($res= $cn->fetch())
                  {
                    echo '<option value="'.$res['cd_code'].'">'.$res['cd_code'].'</option>';
                  }
                  echo'</select>';
                ?>
              </div>
              <div class="col-xs-4">
                <label>NEW CD Code</label>
                <input type="text" class="form-control" name="ncdCode" id="ncdCode" required>
              </div>
            </div>

            <div class="row cid_div" style="display:none">
              <div class="col-xs-4">
                <label>CD CODE</label>
                <?php
                  $cn= $dbh->prepare("SELECT DISTINCT cd_code from unclaimed_dividend ORDER BY cd_code ASC");
                  $cn->execute();
                  echo'<select class="form-control select2bs4" style="width: 100%;" name="olcdCode" id="olcdCode" onchange="get_cid()">';
                  echo '<option value=" ">-SELECT-</option>';
                  while($res= $cn->fetch())
                  {
                    echo '<option value="'.$res['cd_code'].'">'.$res['cd_code'].'</option>';
                  }
                  echo'</select>';
                ?>
              </div>
              <div class="col-xs-4">
                <label>OLD CID</label>
                <input type="text" class="form-control" name="ocid" id="ocid" required readonly>
              </div>
              <div class="col-xs-4">
                <label>NEW CID</label>
                <input type="number" class="form-control" name="ncid" id="ncid" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="11" required>
              </div>
            </div>

          </div>
          <div class="box-footer" style="display:none">
             <div class="col-xs-4">
              <input class="btn btn-primary" id="update_cd_code" type="button" value="Submit">
             </div> 
          </div>
        </div>
       </form>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?> 
</body>
<script type="text/javascript">
function showLoading() {
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
}
function hideloading() {
    document.getElementById('loadingmsg').style.display = 'none';
    document.getElementById('loadingover').style.display = 'none';
}
</script>
<script type="text/javascript">
$(document).ready(function(){
  $("#ocdCode").select2();
  $("#olcdCode").select2();
});

  $("#update_cd_code").click(function() {
    showLoading();

    var ocd = $("#ocdCode").val();
    var olcdCode = $("#olcdCode").val();
    var ncid = $("#ncid").val();
    var ncd = $("#ncdCode").val();
    var cid_cd = $("input[name=cid_cd]:checked").val();
    var update_cdcode = "update_cdcode";

    if (cid_cd === "" && ncd === "") {
        alert("Please enter new CD Code");
        hideloading();
        return false;
    } else if (!confirm("Are you sure you want to update?")) {
        hideloading();
        return false;
    }

    var dataString = {
        ocd: ocd,
        ncd: ncd,
        update_cdcode: update_cdcode,
        cid_cd: cid_cd,
        ncid: ncid,
        olcdCode: olcdCode
    };

    $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        success: function(data) {
            hideloading();
            $("#message").show().html(data).fadeOut(5000);
            setTimeout(function() {
                location.reload();
            }, 4000);
        }
    });
});


function get_cid()
{
  var ocd = $("#olcdCode").val();

  $.ajax({
    type : "POST",
    url : "../PROCESS/process.php",
    data : {'cd_code':ocd, 'get-cd-cid':'get-cd-cid'},
    success : function(res)
    {
        $('#ocid').val(res);
    }
  });
}

function show_div(value)
{
  if(value=="cid")
  {
    $(".cid_div").show();
    $(".cd_div").hide();
    $(".box-footer").show();
  }
  else
  {
    $(".cd_div").show();
    $(".cid_div").hide();
    $(".box-footer").show();
  } 
}
</script>
</html>
