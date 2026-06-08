<?php 
  date_default_timezone_set("Asia/Thimphu");
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
  $list = ins_id($username);
  $ins_id = $list[0];$p_code=$list[1];
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
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">BOND</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">BOND Yeild Online applicants</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="" method="post" onsubmit="showLoading();">
        <div class="box-body">
          <div class="box-body">
              <div class="row">
                <div class="table-responsive">
                <table id="onlineapplicants" class="table table-striped table-bordered table-hover" style="font-size:11px;">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Name</th>
                    <th>CD CODE</th>
                    <th>CID</th>
                    <th>RATE</th>
                    <th>AMOUNT</th>
                    <th>Jrl.No</th>
                    <th>TPN</th>
                    <th>CONTACT</th>
                    <th>Email</th>
                    <th>Bank</th>
                    <th>Acc.No</th>
                    <th>Address</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <?php 

               /* $query = $dbh->prepare('SELECT * FROM bond_application_temp where cd_code='0' AND symbol_id=82 ');
                $query->execute();
                foreach($query as $row){
                  
                }
                $query = $dbh->prepare('SELECT cd_code from client_account where ID=11102003698');
                $query->execute(); */

                $query = $dbh->prepare("SELECT a.*, b.cd_code AS cdfromca 
                    FROM bond_application_temp a
                    LEFT JOIN client_account b on b.cd_code = a.cd_code
                    WHERE status = 1
                    -- WHERE status=0 and symbol_id=73");
                $query->execute();
                $i = 1;
                $cidlist = array();
                foreach ($query as $row) {
                  /*array_push($cidlist, $row['cd_code']);
                  $occurance= array_count_values($cidlist);
                  if($occurance[$row['cd_code']]>2){

                  }else{*/
                  if($row['cd_code'] == 'NO ACC' OR $row['cd_code'] == 0) {
                    $cd_code = 'Needs CD ACC';
                    $color = 'Red';
                  } else {
                    $cd_code=$row['cd_code'];
                    $color='Black';
                  }
                  if ($row['cdfromca'] != '' ||  $row['cdfromca'] != NULL || !empty($row['cdfromca'])) {
                    $cd_code = $row['cdfromca'];
                    $color='Black';
                  }
                  if ($row['Approved'] == 'N'){
                   $state = 'N';
                  } else {
                    $state = 'Y';
                  }
                  echo'
                  <tr>
                  <input type="hidden" value="'.$row['cd_code'].'"  id="cd_code'.$i.'">
                  <input type="hidden" value="'.$row['yeild_rate'].'"  id="rate'.$i.'">
                  <input type="hidden" value="'.$row['amount'].'"  id="amount'.$i.'">
                  <td>'.$i.'</td>
                  <td>'.$row['first_name'].'</td>
                  <td style="Color: '.$color.'">'.$cd_code.'</td>
                  <td style="Color: '.$color.'">'.$row['cid_no'].'</td>
                  <td>**</td>
                  <td>'.$row['amount'].'</td>
                  <td><a href='.$row['file_path'].' target="_blank">'.$row['journal_no'].'</a></td>
                  <td>'.$row['tpn_no'].'</td>
                  <td>'.$row['phone_no'].'</td>
                  <td>'.$row['email'].'</td>
                  <td>'.$row['bank_name'].'</td>
                  <td>'.$row['bank_account_no'].'</td>
                  <td>'.$row['address'].'</td>';
                  if ($color=='Red') {
                    echo'<td style="background-color:Red; color:white;">Open CD Account</td>';
                  }
                  else if ($color=='Black' && $state == 'N'){
                    echo'<td>
                    <button name="approve" id="approve'.$i.'" value="'.$i.'" onclick="return fun('.$i.');" ><i class="fa fa-refresh"></i>Approve</button> </td>
                    <td></td>';
                  }
                  else if($color=='Black' && $state == 'Y'){
                    echo'<td style="background-color:Green; color:white;">Approved</td>';
                  }
                  echo'</tr>';
                  $i++;
                //  }   for counting the accounts without zeros
                  
                  
                }
                ?>
              </tbody>
              </table>
            </div>
              </div>
            </div>
        </div>
        </form>

      </div>
    </section>
  </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>

<script type="text/javascript">
 function fun(i) 
 {
    var val= document.getElementById('approve'+i).value;
    var cd_code= document.getElementById('cd_code'+i).value;
    var volume= document.getElementById('amount'+i).value;
    var bidprice= document.getElementById('rate'+i).value;
    var operation = "save_bond";
    var bondMechanism='YRO';
    var symbol_id=82;
    var face_value=1000;
    var cid ='10101010101';
    //alert(val+'--'+val1+'--'+cd_code+'--'+fid+'--'+vol+'--'+side+'--'+e_p+'--'+e_v+'--'+sy_id);
     if(cd_code=='NO ACC'){
        alert("This Client need to open a CD account first.");
        hideloading();
     }
     else{
      if (confirm("Are you sure you want this BID ?")){
          $.ajax({
          type: "POST",
          url: "../PROCESS/bond_process.php",
          data:'cdcode='+ cd_code + '&symbol_id=' + symbol_id+ '&face_value=' + face_value+ '&bidprice=' + bidprice+'&volume='+ volume + '&save_bond='+ operation+'&bondMechanism='+bondMechanism+'&cid='+cid,
          success: function(data){
            $("#message").show().html(data).delay(300000);
            document.location.reload();
          }
          });

      }
      else{
         return false;
      }
    }
 }
</script>
</html>
