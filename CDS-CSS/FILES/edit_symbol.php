<?php
    session_start();
    include ('../../CONNECTIONS/db.php');

    $role = $_SESSION['sess_userrole'];
    if( $role!=3)
    {
      header('Location: ../../access.php?err=2');
    }
    $inactive = 1500;

    if(isset($_SESSION['timeout'])) 
    {
      $session_life = time() - $_SESSION['timeout'];
      if($session_life > $inactive)
      { 
        header("Location: ../../Authentication/Logout.php"); 
      }
    }
    $_SESSION['timeout'] = time();

  if(!empty($_POST["edit_symbols"])) 
  {
    $wc= $dbh->prepare("SELECT * FROM symbol WHERE symbol_id = :id");
    $wc->bindParam(':id',$_POST['edit_symbols']);
    $wc->execute();
    $state=$wc->fetch();
    echo'
    <div class="modal-dialog modal-lg">
      <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <div class="col-lg-12 text-center"><h4 class="modal-title"><strong>Edit</strong></h4></div>
        </div>
        <div class="modal-body"><div id="loadingover" style="display: none;"><div id="loadingmsg" style="display: none;"></div></div>
        <div class="box-body">
          <p class="col-lg-12 statusMsg" style="display: none;"></p>
          <div class="row">
            <div class="col-lg-4">
              <label for="isin">ISIN</label>
              <input type="number" class="form-control" onKeyPress="if(this.value.length==11) return false;" name="isin" id="isin" required value="'.$state['isin'].'">
            </div>
            <div class="col-lg-4">
              <label for="sy">Symbol</label>
              <input type="text" class="form-control" name="sy" id="sy" readonly value="'.$state['symbol'].'">
            </div>
            <div class="col-lg-4">
              <label>Sector</label>
              <select class="form-control" name="sector" id="sector">
                <option value="Manufacturing" '; if($state['sector']=='Manufacturing') echo 'selected="selected"'; echo'>Manufacturing</option>
                <option value="Mining" '; if($state['sector']=='Mining') echo 'selected="selected"'; echo'>Mining</option>
                <option value="Insurance" '; if($state['sector']=='Insurance') echo 'selected="selected"'; echo'>Insurance</option>
                <option value="Banking" '; if($state['sector']=='Banking') echo 'selected="selected"'; echo'>Banking</option>
                <option value="Technology" '; if($state['sector']=='Technology') echo 'selected="selected"'; echo'>Technology</option>
                <option value="Construction" '; if($state['sector']=='Construction') echo 'selected="selected"'; echo'>Construction</option>
                <option value="Hospitality" '; if($state['sector']=='Hospitality') echo 'selected="selected"'; echo'>Hospitality</option>
                <option value="Media" '; if($state['sector']=='Media') echo 'selected="selected"'; echo'>Media</option>
                <option value="Government" '; if($state['sector']=='Government') echo 'selected="selected"'; echo'>Government</option>
                <option value="Aviation" '; if($state['sector']=='Aviation') echo 'selected="selected"'; echo'>Aviation</option>
              </select>
            </div>
            <div class="col-lg-12">
              <label for="name">Company Name</label>
              <input type="text" class="form-control" name="name" id="name" required value="'.$state['name'].'">
            </div>
            <div class="col-lg-4">
              <label for="fv">Face Value</label>
              <input type="number" min="1" class="form-control" name="fv" id="fv" value="'.$state['face_value'].'" required>
            </div>
            <div class="col-lg-4">
              <label for="pv">Premium Value</label>
              <input type="number" min="1" class="form-control" name="pv" id="pv" value="'.$state['premium_value'].'">
            </div>
            <div class="col-lg-4">
              <label for="bl">Board Lot</label>
              <input type="number" min="1" class="form-control" name="bl" id="bl" required value="'.$state['board_lot'].'">
            </div>
            <div class="col-lg-4">
              <label for="pus">Paid up Shares</label>
              <input type="number" min="1" class="form-control" name="pus" id="pus" value="'.$state['paid_up_shares'].'">
            </div>
            <div class="col-lg-4">
              <label for="doe">Date of Est.</label>
              <input type="date" class="form-control" name="doe" id="doe" value="'.$state['date_of_est'].'">
            </div>
            <div class="col-lg-4">
              <label for="dol">Date of Listing</label>
              <input type="date" class="form-control" name="dol" id="dol" value="'.$state['date_of_listing'].'">
            </div>
            <div class="col-lg-4">
              <label>Security Type</label>
              <select class="form-control" name="stype" id="stype">
                <option value="OS" '; if($state['security_type']=='OS') echo 'selected="selected"'; echo'>Ordinary Shares</option>
                <option value="CB" '; if($state['security_type']=='CB') echo 'selected="selected"'; echo'>Corporate Bonds</option>
                <option value="GB" '; if($state['security_type']=='GB') echo 'selected="selected"'; echo'>Government Bonds</option>
                <option value="CP" '; if($state['security_type']=='CP') echo 'selected="selected"'; echo'>Commercial Paper</option>
              </select>
            </div>';
          if($state['security_type']=='GB' || $state['security_type']=='CB' || $state['security_type']=='CP'){
            echo'
            <div class="col-lg-4">
              <label for="matPeriod">Maturity Period</label>
              <input type="number" class="form-control" name="matPeriod" id="matPeriod" value="'.$state['maturity_period'].'">
            </div>
            <div class="col-lg-4">
              <label for="issueDate">Date of Issue</label>
              <input type="date" class="form-control" name="issueDate" id="issueDate" value="'.$state['date_of_issue'].'">
            </div>
            <div class="col-lg-4">
              <label for="matDate">Maturity Date</label>
              <input type="date" class="form-control" name="matDate" id="matDate" value="'.$state['maturity_date'].'">
            </div>
            <div class="col-lg-4">
              <label for="couponRate">Coupon Rate</label>
              <input type="number" class="form-control" name="couponRate" id="couponRate" value="'.$state['coupon_rates'].'">
            </div>
            <div class="col-lg-4">
              <label for="couponPayable">Coupon Payable</label>
              <select class="form-control" name="couponPayable" id="couponPayable">
                <option value="1" '; if($state['coupon_payable']==1) echo 'selected="selected"'; echo'>Annually</option>
                <option value="2" '; if($state['coupon_payable']==2) echo 'selected="selected"'; echo'>Semi-annually</option>
                <option value="3" '; if($state['coupon_payable']==3) echo 'selected="selected"'; echo'>Quarterly</option>
              </select>
            </div>';
          }else{
            echo'
            <input type="hidden" class="form-control" name="matPeriod" id="matPeriod" value="0">
            <input type="hidden" class="form-control" name="matDate" id="matDate" value="0000-00-00">
            <input type="hidden" class="form-control" name="issueDate" id="issueDate" value="0000-00-00">
            <input type="hidden" class="form-control" name="couponRate" id="couponRate" value="0">
            <input type="hidden" class="form-control" name="couponPayable" id="couponPayable" value="0">
            ';
          }
            echo'
            <div class="col-lg-4">
              <label>Status</label>
              <select class="form-control" name="status" id="status">
                <option value="1" '; if($state['status']==1) echo 'selected="selected"'; echo'>Active</option>
                <option value="2" '; if($state['status']==2) echo 'selected="selected"'; echo'>InActive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" name="edit_sym" id="edit_sym" value="'.$state['symbol_id'].'">UPDATE</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
        </div>
      </div>
      </form>
    </div>
    <script type="text/javascript">
      $("#edit_sym").click(function(){
        var symId = $("#edit_sym").val();
        if (confirm("Are you sure you want to update record Id #"+symId+" ?"))
        {
          showLoading();
          var isin = $("#isin").val();
          var symbol = $("#sy").val();
          var name = $("#name").val();
          var sector = $("#sector").val();
          var faceValue = $("#fv").val();
          var premiumValue = $("#pv").val();
          var boardLot = $("#bl").val();
          var paidUpShares = $("#pus").val();
          var dateOfEst = $("#doe").val();
          var dateOfList = $("#dol").val();
          var securityType = $("#stype").val();
          var status = $("#status").val();

          var matPeriod = $("#matPeriod").val();
          var matDate = $("#matDate").val();
          var issueDate = $("#issueDate").val();
          var cpnRate = $("#couponRate").val();
          var cpnPayable = $("#couponPayable").val();

          if(dateOfEst==""){
            dateOfEst="0000-00-00";
          }
          if(dateOfList==""){
            dateOfList="0000-00-00";
          }

          var operation = "edit_symbol";
          var dataString = "isin="+ isin +"&sy="+ symbol + "&name="+ name +"&sector="+ sector + "&fv="+ faceValue + "&pv="+ premiumValue + "&bl="+ boardLot + "&pus="+ paidUpShares + "&doe="+ dateOfEst + "&dol="+ dateOfList + "&stype="+ securityType + "&status="+ status + "&symId="+ symId + "&edit_symbol="+ operation + "&matPeriod="+ matPeriod + "&matDate="+ matDate + "&issueDate="+ issueDate + "&cpnRate="+ cpnRate + "&cpnPayable="+ cpnPayable;
          $.ajax({
              type: "POST",
              url: "../../ADM/PROCESS/process.php",
              data: dataString ,
              success: function(data){
                hideloading();
                $("#statusMsg").show();
                $(".statusMsg").show().html(data);
                $(".statusMsg").fadeOut(5000);
              }
          });
        }
        else
        {
          return false;
        }
      });
    </script>';
}
?>