<?php 
	include ('session_start_file.php');
	include ('../../CONNECTIONS/db.php');
	include('../../Functions/f.php');

	$check = $dbh->prepare('SELECT a.institution_id,c.participant_code  from adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un');
	$check->bindParam(':un', $username);
	$check->execute();
	$res = $check->fetch();
	$institution_id = $res['institution_id'];
	$participant_code = $res['participant_code'];


	if(!empty($_POST["rightIsueCD"])) {
		  $cd = $_POST['rightIsueCD'];

		  $cdCod = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID FROM client_account a WHERE a.cd_code = :cd");
		  $cdCod->bindParam(':cd', $cd);
		  $cdCod->execute();
		  $state1 = $cdCod->fetch();
		  if ($cdCod->rowCount() < 1) {
		    echo'
		    <div class="col-lg-6 col-md-6 col-sm-12">
		      <label>Client Details</label>
		      <input type="text" class="form-control" style="color:red;" value="No Details. Please check CD CODE" readonly>
		    </div>';
		  } else {
			// check if already bid or not
			$stmt = $dbh->prepare("SELECT 1 FROM rights_issue r WHERE r.cid_no = ? AND r.symbol_id = 20 AND r.`type` = 'B' AND r.`status` = 0");
			$stmt->execute([$state1['ID']]);
			$row = $stmt->fetchColumn();
			if ($row) {
				echo'
			    <div class="col-lg-12 col-md-12 col-sm-12">
			      <label>Message</label>
			      <input type="text" class="form-control" style="color:red;" value="The client has an existing bid. Consider updating it.">
			    </div>';
			    exit;
			}

		    echo'
		    <div class="col-lg-6 col-md-6 col-sm-12">
		      <label>Client Details</label>
		      <input type="hidden" class="form-control" name="cid_no" id="cid_no" value="'.$state1['ID'].'" readonly>
		      <input type="text" class="form-control" value="NAME : '.$state1['f_name'].' '.$state1['l_name'].' , CID/DISN# '.$state1['ID'].'" readonly>
		    </div>

		    <div class="col-lg-3 col-md-3 col-sm-12">
		      <label>Symbol<font color="red">*</font></label>
		      <select class="form-control" name="rights_symbol_id" id="rights_symbol_id" onChange="showAnnType(this.value)" required>
		        <option value="">--Select Symbol--</option>
		        <option value="20">TBL</option>
		      </select>
		    </div>

		    <div class="col-lg-3 col-md-3 col-sm-12 has-success" id="bid_option_id" style="display: none;">
		       <label class="control-label" for="options">Options <font color="red">*</font></label>
		        <select class="form-control" id="options" name="options" onChange="get_state_bid(this.value);">
		          <option value="">--Select--</option>
     			  <option value="B">Bid</option>

		          <!-- 
		          <option value="O">Offer</option>
		          <option value="SA">Share Auction</option> -->
		        </select>
	      	</div>

	      	<div style="display: none;" id="bid_div_id">
	      	<div class="col-lg-3 col-md-3 col-sm-12">
		        <label for="bidPrice">Bid Price (per share) <font color="red">*</font></label>
		        <input type="number" class="form-control" name="bidPrice" id="bidPrice" min="11" max="50.47" step="0.05" required oninput="calculateTotalBidAmount()">
		      </div>

		      <div class="col-lg-3 col-md-3 col-sm-12">
		       <label>Total Volume (Shares) <font color="red">*</font></label>
		        <input type="number" class="form-control" name="volume" id="volume" min="100" step="10" required oninput="calculateTotalBidAmount()">
		      </div>

		      <div class="col-lg-3 col-md-3 col-sm-12">
		        <label for="totalBidAmt">Total Amount (2% commission)</label>
		        <input type="number" class="form-control" name="totalBidAmt" id="totalBidAmt" readonly>
		      </div>
		    </div>

		    <script type="text/javascript">
		      	function showAnnType(val) {
			        if (val == "") {
						$("#bid_option_id").hide();
						$("#bid_div_id").hide();

						$("#bidPrice").val("");
						$("#volume").val("");
						$("#totalBidAmt").val("");
			        } else {
		          		$("#bid_option_id").show();
			        }
		      	}

		      	document.getElementById("bidPrice").addEventListener("blur", function () {
				    let value = parseFloat(this.value);
				    if (!isNaN(value)) {
				        this.value = value.toFixed(2);
				    }
				});


		      	function get_state_bid(val) {
		          if (val == "B") {
		          	$("#bid_div_id").show();
		            $("#riSave").show();
		          } else {
		          	$("#bidPrice").val("");
		          	$("#volume").val("");
		          	$("#totalBidAmt").val("");

		          	$("#bid_div_id").hide();
		            $("#riSave").hide();
		          }
		          
		        }

		        function calculateTotalBidAmount() {
		            var bidPrice = parseFloat(document.getElementById("bidPrice").value);
		            var volume = parseFloat(document.getElementById("volume").value);
		            var totalBidAmt = (bidPrice * volume * 0.02) + (bidPrice * volume);

		            document.getElementById("totalBidAmt").value = totalBidAmt.toFixed(2);
		        }

		    </script>';
		  }
	}

?>