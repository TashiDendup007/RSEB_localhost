<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#"> Finance</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php'); ?>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bond Finance</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <?php 
            $dateselect = date("Y-m-d H:i:s");

            $stmt = $dbh->prepare("SELECT b.id, b.symbol_id, b.start_bond_at, b.end_bond_at, b.status, 
                DATE_FORMAT(b.start_bond_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
                DATE_FORMAT(b.end_bond_at, '%W %M %e, %Y %h:%i %p') AS end_at_format
                FROM bond_offers b
                WHERE b.status = 1 
                ORDER BY b.id DESC
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($stmt->rowcount() < 1) {
              echo'<div class="box-body"><h3>Currently, there are no active bond offers.</h3></div>'; 
              die();
            }

            if ($result['start_bond_at'] > $dateselect) {
              echo"<div class='box-body'><h3>The Bid for the Bond will open on : <b>".$result['start_at_format']."</b></h3></div>";
              die();
            } elseif($result['end_bond_at'] < $dateselect) {
              echo "<div class='box-body'><h3>The Bid for the Bond has ended on <b>".$result['end_at_format']."</b></h3></div>";
              die();
            }

            /*if (substr($username, 0, 7) == 'MEMDKLT') {
            
            } else {
              die("<div class='box-body'><h3>The Bid for the Bond has ended.</b></h3></div>");
            }*/
          ?>
          <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
            <div class="box-body">
              <div class="box-body">
                <div class="row">  
                  <div class="col-lg-3 col-md-3 col-sm-12">
                    <label>CD Code<font color="red">*</font></label>
                    <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="getState23(this.value);" required>
                  </div>  
                   <div class="col-lg-3 col-md-3 col-sm-12">
                    <label>Amount<font color="red">*</font></label>
                    <input type="number" class="form-control" name="amt" id="amt" min="1">
                  </div> 
                  <div id="cd"></div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                  <button type="button" class="btn btn-success" style="display:none;" id="cre" name="cre" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Credit </button>
                  <button type="button" class="btn btn-warning" style="display:none;" id="deb" name="deb" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-minus"></i>  Debit </button>
              </div>
            </div>
          </form>
        </div>

        <div class="box">
          <div class="box-header with-border"><strong>Search CD Code</strong></div>
          <div class="box-body">
            <form action="" method="POST">
              <div class="input-group col-lg-6 col-md-6">
                <input type="text" name="search_cid_no" id="search_cid_no" class="form-control" placeholder="Enter CID Number">
                <span class="input-group-btn">
                  <button class="btn btn-primary" type="button" id="search_CD_Code" name="search_CD_Code">Search</button>
                </span>
                <div id="search_erro_msg" style="color: red;"></div>
              </div>
            </form>
            <div id="cd_code_dtls_id" style="display: none;"></div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <div class="box">
              <div class="header"></div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6">
                  <label>From Date<font color="red">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                  </div>
                  <span id="f_dateErr" style="color: red;"></span>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label>To Date<font color="red">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="to_date" id="to_date" onChange="return checkDate();" required>
                  </div>
                  <span id="t_dateErr" style="color: red;"></span>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-lg-6 col-md-6">          
                  <button type="button" class="btn btn-info" id="fin" name="fin" value=""><i class="fa fa-list"></i> List</button>
                </div>
              </div>
            </div>
            <div class="box" style="display: none;" id="tableId">
              <div class="box-body">
                <div id="details"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
  <script type="text/javascript">
  function getState23(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'bond_fin='+val,
      dataType: 'html',
      success: function(response){ 
        $("#cd").html(response);
      } 
    });
  }

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "b-edit.php",
      data:'edit_fin='+val, 
      dataType: "html",
      success: function(data){ 
        $("#myModal").html(data);
      }
    });
  }

  function fun(io) {
    var val = document.getElementById('delete_fin'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
    } else {
      return false;
    }
  }

  $("#cre").click(function() { 
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "cre";

    if(cdcode === "" || amount === "" || remark === "") {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      var data = {
        cdcode: cdcode,
        amt: amount,
        rm: remark,
        cre: operation,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_process.php",
        data: data,
        dataType: 'html',
        success: function(response) {
          hideloading();
          $("#message").html(response);
          showMessage();
          $("#cdcode").val('');
          $("#amt").val('');
          $("#rm").val('');
        }
      });
    }
    return false;
  });

  $("#deb").click(function() {
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "deb";

    if(cdcode === "" || amount === "" || remark === "") {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      var data = {
        cdcode: cdcode,
        amt: amount,
        rm: remark,
        deb: operation,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_process.php",
        data: data,
        dataType: 'html',
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
          $("#cdcode").val('');
          $("#amt").val('');
          $("#rm").val('');
        }
      });
    }
    return false;
  });

  function checkDate() {
    var f = document.getElementById("to_date").value;
    var from = new Date(f);
    var t = document.getElementById("to_date").value;
    var to = new Date(t);
    if (from > to) {
      alert("To date should be greater than From date ");
      return false;
    } else {
      return true;
    }
  }

    $('#fin').click(function() {
      showLoading();
      var fromDateFld = $("#from_date").val();
      var toDateFld = $("#to_date").val();
      var operation = 'financebond';

      if(fromDateFld == '') {
        hideloading();
        $("#f_dateErr").html("Select From date");
        return false;
      }
      if(toDateFld == '') {
        hideloading();
        $("#t_dateErr").html("Select To date");
        return false;
      }

      var data = {
        fromDate: fromDateFld,
        toDate: toDateFld,
        financebond: operation,
      };


      $.ajax({
        type: "POST",
        url: "load.php",
        data: data,
        dataType: 'html',
        success: function(data){
          hideloading();
          $("#tableId").show();
          $("#details").html(data);
        }
      });
    });

    $("#search_CD_Code").click( function () {
      var cid_no = $("#search_cid_no").val();
      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_process.php",
        data: { search_cd_code_for_ipo: cid_no },
        dataType: 'html',
        success: function(data) {
          $("#cd_code_dtls_id").show();
          $("#cd_code_dtls_id").html(data);
        }
      });
    });

    $('#from_date').click(function() {
      $("#f_dateErr").html("");
    });

    $('#to_date').click(function() {
      $("#t_dateErr").html("");
    });
  </script>
</html>