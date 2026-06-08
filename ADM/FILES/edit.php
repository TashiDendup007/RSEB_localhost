<?php 
	include('sessionStartFile_admin.php');
	include ('../../CONNECTIONS/db.php');

	if(isset($_POST['edit_sector'])) {
		$id = $_POST['edit_sector'];

		$select = $dbh->prepare("SELECT * FROM sector_masters WHERE id=:id");
		$select->bindParam(':id', $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="../PROCESS/process.php" method="POST" onsubmit="confirmation()">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Sector</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Sector Name: <font color="red">*</font></label>
			            	<input type="text" class="form-control" name="name" id="name" value="'.$row['name'].'" required>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
			              		<option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_sector" id="update_sector" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
		$("#update_sector").click(function() {
		    showLoading();
		    var sector_id = $("#update_sector").val();
		    if (confirm("Are you sure you want to update Id # "+ sector_id + "?")) { 
		      	var $nameField = $("#name");
		      	var $statusField = $("#status");
		      	var operation = "update_sector";

		      	var data = {
		        	name: $nameField.val(),
		        	status: $statusField.val(),
		        	id: sector_id,
		        	update_sector: operation
		      	};

		      	$.ajax({ 
		        	type: "POST", 
		        	url: "../PROCESS/process.php", 
		        	data: data, 
		        	dataType: "html",
		        	success: function(response){ 
		          		hideloading(); 
		          		$("#myModal").modal("hide");
		          		$("#message").html(response);
		          		showMessage();

		          		// reload the table
				        $.ajax({
				            type: "POST",
				            url: "load.php",
				            data:"get_sector_list=get_sector_list",
				            cache: false,
				            success: function(data){
				              $("#tableList").html(data);
				            }
				        });
		        	},
		        	error: function(jqXHR, textStatus, errorThrown) {
		          		console.log(textStatus);
		        	}
		      	});
		    } else { 
		      	hideloading(); 
		      	return false; 
		    }
	  	});
	  	</script>';
	  	die();
	} else if(isset($_POST['edit_role'])) {
		$id = $_POST['id'];

		$select = $dbh->prepare("SELECT * FROM role_masters where id=:id");
		$select->bindParam(':id', $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
		<div class="modal-dialog modal-lg">
		<form action="../PROCESS/process.php" method="POST" onsubmit="confirmation()">
			<div class="modal-content">
			  	<div class="modal-header">
			    	<button type="button" class="close" data-dismiss="modal">&times;</button>
			    	<h4 class="modal-title">Edit Role</h4>
			  	</div>
			  	<div class="modal-body">
			    	<div class="box-body">
			      		<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			      		<div class="col-lg-6 col-md-6">
					        <label>Role Name: <font color="red">*</font></label>
					        <input type="text" class="form-control" name="name" id="name" value="'.$row['role_name'].'" required>
				      	</div>
				      	<div class="col-lg-6 col-md-6">
				        	<label>Status: <font color="red">*</font></label>
					        <select class="form-control" name="status" id="status" required>
					          <option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'>Active</option>
					          <option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'>InActive</option>
					        </select>
				      	</div>
				    </div>
				    <div class="modal-footer">
				      	<button type="submit" class="btn btn-primary" name="update_role" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
				      	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
				    </div>
			  	</div>
			</div>
		</form>
		</div>';
		die();
	} elseif (isset($_POST['edit_occupation'])) {
		$id = $_POST['edit_occupation'];

		$select = $dbh->prepare("SELECT * FROM occupation WHERE occupation=:id");
		$select->bindParam(':id', $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="../PROCESS/process.php" method="POST" onsubmit="confirmation()">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Occupation</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['occupation'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Occupation Name: <font color="red">*</font></label>
			            	<input type="text" class="form-control" name="name" id="name" value="'.$row['occupation_name'].'" required>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
			              		<option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_occupation" id="update_occupation" value="'.$row['occupation'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
		$("#update_occupation").click(function() {
		    showLoading();
		    var occupation_id = $("#update_occupation").val();
		    if (confirm("Are you sure you want to update Id # "+ occupation_id + "?")) { 
		      	var $nameField = $("#name");
		      	var $statusField = $("#status");
		      	var operation = "update_occupation";

		      	var data = {
		        	name: $nameField.val(),
		        	status: $statusField.val(),
		        	id: occupation_id,
		        	update_occupation: operation
		      	};

		      	$.ajax({ 
		        	type: "POST", 
		        	url: "../PROCESS/process.php", 
		        	data: data, 
		        	dataType: "html",
		        	success: function(response){ 
		          		hideloading(); 
		          		$("#myModal").modal("hide");
		          		$("#message").html(response);
		          		showMessage();

		          		$.ajax({
				            type: "POST",
				            url: "load.php",
				            data:"get_occupation_list=get_occupation_list",
				            cache: false,
				            success: function(response){
				              $("#tableList").html(response);
				            }
				          });
		        	},
		        	error: function(jqXHR, textStatus, errorThrown) {
		          		console.log(textStatus);
		        	}
		      	});
		    } else { 
		      	hideloading(); 
		      	return false; 
		    }
	  	});
	  	</script>';
	  	die();
	} elseif (isset($_POST['edit_corporate'])) {
		$id = $_POST['edit_corporate'];

		$select = $dbh->prepare("SELECT * FROM corporate_action_masters WHERE id=:id");
		$select->bindParam(':id', $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="../PROCESS/process.php" method="POST" onsubmit="confirmation()">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Corporate</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Corporate Name: <font color="red">*</font></label>
			            	<input type="text" class="form-control" name="name" id="name" value="'.$row['corporate_name'].'" required>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
			              		<option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_corporate" id="update_corporate" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
		$("#update_corporate").click(function() {
		    showLoading();
		    var id = $("#update_corporate").val();
		    if (confirm("Are you sure you want to update Id # "+ id + "?")) { 
		      	var $nameField = $("#name");
		      	var $statusField = $("#status");
		      	var operation = "update_corporate";

		      	var data = {
		        	name: $nameField.val(),
		        	status: $statusField.val(),
		        	id: id,
		        	update_corporate: operation
		      	};

		      	$.ajax({ 
		        	type: "POST", 
		        	url: "../PROCESS/process.php", 
		        	data: data, 
		        	dataType: "html",
		        	success: function(response){ 
		          		hideloading(); 
		          		$("#myModal").modal("hide");
		          		$("#message").html(response);
		          		showMessage();

		          		$.ajax({
				            type: "POST",
				            url: "load.php",
				            data:"get_corporate_list=get_corporate_list",
				            cache: false,
				            success: function(data){
				              $("#tableList").html(data);
				            }
			          	});
		        	},
		        	error: function(jqXHR, textStatus, errorThrown) {
		          		console.log(textStatus);
		        	}
		      	});
		    } else { 
		      	hideloading(); 
		      	return false; 
		    }
	  	});
	  	</script>';
	  	die();
	} elseif (isset($_POST['edit_rights_offer'])) {
		$id = $_POST['edit_rights_offer'];

		$select = $dbh->prepare("SELECT 
				r.id, r.symbol_id, r.start_at, r.end_at, r.corp_announcement_id, r.announcement_type, r.status
				FROM rights_offers r
				WHERE r.id=:id");
		$select->bindParam(':id', $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="../PROCESS/process.php" method="POST" onsubmit="confirmation()">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Rights Offer</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Symbol: <font color="red">*</font></label>';
		                    $getSymbol = $dbh->prepare("SELECT 
		                          c.symbol_id, s.symbol
		                        FROM corporate_announcement c 
		                        JOIN symbol s ON c.symbol_id = s.symbol_id 
		                        -- WHERE c.status = 1 
		                        GROUP BY c.symbol_id
		                        ORDER BY s.symbol ASC
		                    ");
		                    $getSymbol->execute();
		                    $options = '';
			                  while ($res = $getSymbol->fetch()) {
			                    $selected = '';
			                    if ($res['symbol_id'] == $row['symbol_id']) {
			                      $selected = 'selected';
			                    }
			                    $options .= '<option value="'.$res['symbol_id'].'" '.$selected.'>'.$res['symbol'].'</option>';
			                  }
		                  echo'<select name="symbol_id" id="symbol_id" class="form-control"> '.$options.' </select>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
			            	<label for="name">Corporate Announcement Type <span style="color:red;">*</span></label>';
		                    $corp_list = $dbh->prepare("SELECT m.id, m.corporate_name FROM corporate_action_masters m WHERE m.status=1");
		                    $corp_list->execute();
		                    $options = '';
			                  while ($res = $corp_list->fetch()) {
			                    $selected = '';
			                    if ($res['id'] == $row['announcement_type']) {
			                      $selected = 'selected';
			                    }
			                    $options .= '<option value="'.$res['id'].'" '.$selected.'>'.$res['corporate_name'].'</option>';
			                  }
		                  echo'<select name="corp_ann_type" id="corp_ann_type" class="form-control"> '.$options.' </select>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
		                  <label>Start Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="start_date" id="start_date" value="'.$row['start_at'].'" required>
		                </div>
		                <div class="col-lg-6 col-md-6">
		                  <label>End Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="end_date" id="end_date" value="'.$row['end_at'].'" required>
		                </div>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
			              		<option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_rights_offer" id="update_rights_offer" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
		$("#update_rights_offer").click(function() {
		    showLoading();
		    var rights_id = $("#update_rights_offer").val();
		    if (confirm("Are you sure you want to update ?")) { 
		      	var $symIdField = $("#symbol_id");
		      	var $annTypeField = $("#corp_ann_type");
		      	var $strDateField = $("#start_date");
		      	var $endDateField = $("#end_date");
		      	var $statusField = $("#status");
		      	var operation = "update_rights_offer";

		      	var data = {
		        	symbol_id: $symIdField.val(),
					corp_ann_type: $annTypeField.val(),
					start_date: $strDateField.val(),
					end_date: $endDateField.val(),
		        	status: $statusField.val(),
		        	id: rights_id,
		        	update_rights_offer: operation
		      	};

		      	$.ajax({ 
		        	type: "POST", 
		        	url: "../PROCESS/process.php", 
		        	data: data, 
		        	dataType: "html",
		        	success: function(response){ 
		          		hideloading(); 
		          		$("#myModal").modal("hide");
		          		$("#message").html(response);
		          		showMessage();
		          		// reload table content
		          		$.ajax({
					      	url: "load.php",
      						data: { get_rights_offer_list: "get_rights_offer_list" },
					      	type: "POST",
					      	dataType: "html",
						    success: function(data) {
						    	$("#tableList").html(data);
						    },
					    });
		        	},
		        	error: function(jqXHR, textStatus, errorThrown) {
		          		console.log(textStatus);
		        	}
		      	});
		    } else { 
		      	hideloading(); 
		      	return false; 
		    }
	  	});
	  	</script>';
	  	die();
	} elseif (isset($_POST['edit_share_auction'])) {
		$id = $_POST['edit_share_auction'];

		$select = $dbh->prepare("SELECT 
		          a.id, a.symbol_id, a.symbol, a.offer_volume, a.auction_date, a.end_date, a.start_price, a.max_price, a.status
		        FROM share_auctions a
		        WHERE a.id=:id");
		$select->bindParam(':id', $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="../PROCESS/process.php" method="POST" onsubmit="confirmation()">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Share Auction</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Symbol: <font color="red">*</font></label>';
		                    $getSymbol = $dbh->prepare("SELECT 
		                            	s.symbol_id, s.symbol
		                            FROM symbol s 
		                            WHERE s.status = 1 AND s.security_type IN ('OS')
		                            ORDER BY s.symbol ASC");
		                    $getSymbol->execute();
		                    $options = '';
			                  while ($res = $getSymbol->fetch()) {
			                    $selected = '';
			                    if ($res['symbol_id'] == $row['symbol_id']) {
			                      $selected = 'selected';
			                    }
			                    $options .= '<option value="'.$res['symbol_id'].'" '.$selected.'>'.$res['symbol'].'</option>';
			                  }
		                  echo'<select name="symbol_id" id="symbol_id" class="form-control"> '.$options.' </select>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
		                  <label for="offer_vol">Offer Volume <span style="color:red;">*</span></label>
		                  <input type="number" name="offer_vol" id="offer_vol" step="0" class="form-control" value="'.$row['offer_volume'].'" required>
		                </div>
		                <div class="col-lg-6 col-md-6">
		                  <label for="min_price">Min Price<span style="color:red;">*</span></label>
		                  <input type="number" name="min_price" id="min_price" step="0.01" class="form-control" value="'.$row['start_price'].'" required>
		                </div>
		                <div class="col-lg-6 col-md-6">
		                  <label for="max_price">Max Price<span style="color:red;">*</span></label>
		                  <input type="number" name="max_price" id="max_price" step="0.01" class="form-control" value="'.$row['max_price'].'" required>
		                </div>
			          	<div class="col-lg-6 col-md-6">
		                  <label>Start Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="start_date" id="start_date" value="'.$row['auction_date'].'" required>
		                </div>
		                <div class="col-lg-6 col-md-6">
		                  <label>End Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="end_date" id="end_date" value="'.$row['end_date'].'" required>
		                </div>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="Y" '; if($row['status'] == 'Y') echo 'selected="selected"'; echo'> Active </option>
			              		<option value="N" '; if($row['status'] == 'N') echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_share_auction" id="update_share_auction" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
			$("#update_share_auction").click(function() {
			    showLoading();
			    var auc_id = $("#update_share_auction").val();
			    if (confirm("Are you sure you want to update ?")) { 
			      	var symIdField = $("#symbol_id");
			      	var offerVolField = $("#offer_vol");
			      	var minPriceField = $("#min_price");
				    var maxPriceField = $("#max_price");
				    var strDateField = $("#start_date");
				    var endDateField = $("#end_date");
			      	var statusField = $("#status");
			      	var operation = "update_share_auction";
			      	
			      	var data = {
			        	symbol_id: symIdField.val(),
						offer_vol: offerVolField.val(),
						min_price: minPriceField.val(),
						max_price: maxPriceField.val(),
						start_date: strDateField.val(),
						end_date: endDateField.val(),
			        	status: statusField.val(),
			        	id: auc_id,
			        	update_share_auction: operation
			      	};

			      	if(symIdField.val() === "" || offerVolField.val() === "" || minPriceField.val() === "" || maxPriceField.val() === "" || strDateField.val() === "" || endDateField.val() === "" || statusField.val() === "") {
				      alert("Please Fill All Mandatory Fields");
				      hideloading();
				    } else {
				    	$.ajax({ 
				        	type: "POST", 
				        	url: "../PROCESS/process.php", 
				        	data: data, 
				        	dataType: "html",
				        	success: function(response){ 
				          		hideloading(); 
				          		$("#myModal").modal("hide");
				          		$("#message").html(response);
				          		showMessage();
				          		// reload table content
				          		$.ajax({
							      	url: "load.php",
										data: { get_auction_symbol_list: "get_auction_symbol_list" },
							      	type: "POST",
							      	dataType: "html",
								    success: function(data) {
								    	$("#tableList").html(data);
								    },
							    });
				        	},
				        	error: function(jqXHR, textStatus, errorThrown) {
				          		console.log(textStatus);
				        	}
				      	});
				    }
			    } else { 
			      	hideloading(); 
			      	return false; 
			    }
			});
		</script>';
	  	die();
	} elseif (isset($_POST['edit_bond_offer'])) {
		$id = $_POST['edit_bond_offer'];

		$select = $dbh->prepare("SELECT r.id, r.symbol_id, r.start_bond_at, r.end_bond_at, r.status, r.type, s.name 
				FROM bond_offers r 
				JOIN symbol s ON r.symbol_id = s.symbol_id
				WHERE r.id = ?
		");
		$select->bindParam(1, $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="" method="POST">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Bond Offer</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Symbol <font color="red">*</font></label>';
		                    $getSymbol = $dbh->prepare("SELECT DISTINCT s.symbol_id, s.symbol
			                      FROM symbol s 
			                      WHERE s.status = 1 AND s.security_type IN ('GB', 'CB')
			                      ORDER BY s.symbol_id DESC");
		                    $getSymbol->execute();
		                    $options = '';
			                  while ($res = $getSymbol->fetch()) {
			                    $selected = '';
			                    if ($res['symbol_id'] == $row['symbol_id']) {
			                      $selected = 'selected';
			                    }
			                    $options .= '<option value="'.$res['symbol_id'].'" '.$selected.'>'.$res['symbol'].'</option>';
			                  }
		                  echo'<select name="symbol_id" id="symbol_id" class="form-control" onchange="getSymbolName(this.value)"> '.$options.' </select>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
		                  <label>Bond Name</label>
		                  <input type="text" class="form-control" name="comp_name" id="comp_name" value="'.$row['name'].'" readonly>
		                </div>
			          	<div class="col-lg-6 col-md-6">
		                  <label>Start Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="start_date" id="start_date" value="'.$row['start_bond_at'].'" required>
		                </div>
		                <div class="col-lg-6 col-md-6">
		                  <label>End Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="end_date" id="end_date" value="'.$row['end_bond_at'].'" required>
		                </div>

		                <div class="col-lg-6 col-md-6">
	                  		<label>Bond Type</label>
							<select class="form-control" name="bond_type" id="bond_type" required>
								<option value="">-- Select Bond Type --</option>
								<option value="BLA" '; if($row['type'] == 'BLA') echo 'selected="selected"'; echo'>BLA (Bhutanese Living Abroad)</option>
								<option value="BLR" '; if($row['type'] == 'BLR') echo 'selected="selected"'; echo'>BLR (Bhutanese Living in Residence)</option>
								<option value="ALL" '; if($row['type'] == 'ALL') echo 'selected="selected"'; echo'>ALL (BOTH)</option>
							</select>
		                </div>

			          	
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
			              		<option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_bond_offer" id="update_bond_offer" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
  		function getSymbolName(id) {
			var op = "get_symbol_name";
			$.ajax({
		    type: "POST",
		    url: "load.php",
		    data:"get_symbol_name=" + op + "&id=" + id,
		    cache: false,
		    success: function(response) {
		      hideloading();
		      $("#comp_name").val(response);
		    }
		  });
		}
		$("#update_bond_offer").click(function() {
		    showLoading();
		    var bond_id = $("#update_bond_offer").val();
		    if (confirm("Do you want to update ?")) { 
		      	var $symIdField = $("#symbol_id");
		      	var $strDateField = $("#start_date");
		      	var $endDateField = $("#end_date");
		      	var $statusField = $("#status");
		      	var $typeField = $("#bond_type");
		      	var operation = "update_bond_offer";

		      	var data = {
		        	symbol_id: $symIdField.val(),
					start_date: $strDateField.val(),
					end_date: $endDateField.val(),
		        	status: $statusField.val(),
		        	type: $typeField.val(),
		        	id: bond_id,
		        	update_bond_offer: operation
		      	};

		      	$.ajax({ 
		        	type: "POST", 
		        	url: "../PROCESS/process.php", 
		        	data: data, 
		        	dataType: "html",
		        	success: function(response){ 
		          		hideloading(); 
		          		$("#myModal").modal("hide");
		          		$("#message").html(response);
		          		showMessage();
		          		// reload table content
		          		$.ajax({
					      	url: "load.php",
      						data: { get_bond_offer_list: "get_bond_offer_list" },
					      	type: "POST",
					      	dataType: "html",
						    success: function(data) {
						    	$("#tableList").html(data);
						    },
					    });
		        	},
		        	error: function(jqXHR, textStatus, errorThrown) {
		          		console.log(textStatus);
		        	}
		      	});
		    } else { 
		      	hideloading(); 
		      	return false; 
		    }
	  	});
	  	</script>';
	  	die();
	}
	elseif (isset($_POST['edit_ipo_offer'])) {
		$id = $_POST['edit_ipo_offer'];

		$select = $dbh->prepare("SELECT r.id, r.symbol_id, r.start_at, r.end_at, r.status, s.name 
				FROM ipo_offers r 
				JOIN symbol s ON r.symbol_id = s.symbol_id
				WHERE r.id = ?
		");
		$select->bindParam(1, $id);
		$select->execute();
		$row = $select->fetch();
	  	echo'
	  	<div class="modal-dialog modal-lg">
	    	<form action="" method="POST">
		    <div class="modal-content">
		      	<div class="modal-header">
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		        	<h4 class="modal-title">Edit Bond Offer</h4>
		      	</div>
		      	<div class="modal-body">
			        <div class="box-body">
			          	<input type="hidden" class="form-control" name="id" id="id" value="'.$row['id'].'" required>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Symbol <font color="red">*</font></label>';
		                    $getSymbol = $dbh->prepare("SELECT DISTINCT s.symbol_id, s.symbol
		                          FROM symbol s 
		                          WHERE s.status = 1
		                          AND s.security_type = 'OS' 
		                          ORDER BY s.symbol_id DESC LIMIT 5");
		                    $getSymbol->execute();
		                    $options = '';
			                  while ($res = $getSymbol->fetch()) {
			                    $selected = '';
			                    if ($res['symbol_id'] == $row['symbol_id']) {
			                      $selected = 'selected';
			                    }
			                    $options .= '<option value="'.$res['symbol_id'].'" '.$selected.'>'.$res['symbol'].'</option>';
			                  }
		                  echo'<select name="symbol_id" id="symbol_id" class="form-control" onchange="getSymbolName(this.value)"> '.$options.' </select>
			          	</div>
			          	<div class="col-lg-6 col-md-6">
		                  <label>Company Name</label>
		                  <input type="text" class="form-control" name="comp_name" id="comp_name" value="'.$row['name'].'" readonly>
		                </div>
			          	<div class="col-lg-6 col-md-6">
		                  <label>Start Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="start_date" id="start_date" value="'.$row['start_at'].'" required>
		                </div>
		                <div class="col-lg-6 col-md-6">
		                  <label>End Date <font color="red">*</font></label>
		                  <input type="datetime-local" class="form-control" name="end_date" id="end_date" value="'.$row['end_at'].'" required>
		                </div>
			          	<div class="col-lg-6 col-md-6">
			            	<label>Status: <font color="red">*</font></label>
			            	<select class="form-control" name="status" id="status" required>
			              		<option value="1" '; if($row['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
			              		<option value="0" '; if($row['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
			            	</select>
			          	</div>
			        </div>
			        <div class="modal-footer">
			        	<button type="button" class="btn btn-primary" name="update_ipo_offer" id="update_ipo_offer" value="'.$row['id'].'"><i class="fa fa-check"></i> Update</button>
			          	<button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
			        </div>
		      	</div>
	    	</div>
	    	</form>
  		</div>
  		<script type="text/javascript">
  		function getSymbolName(id) {
			var op = "get_symbol_name";
			$.ajax({
		    type: "POST",
		    url: "load.php",
		    data:"get_symbol_name=" + op + "&id=" + id,
		    cache: false,
		    success: function(response) {
		      hideloading();
		      $("#comp_name").val(response);
		    }
		  });
		}
		$("#update_ipo_offer").click(function() {
		    showLoading();
		    var ipo_id = $("#update_ipo_offer").val();
		    if (confirm("Do you want to update ?")) { 
		      	var $symIdField = $("#symbol_id");
		      	var $strDateField = $("#start_date");
		      	var $endDateField = $("#end_date");
		      	var $statusField = $("#status");
		      	var operation = "update_ipo_offer";

		      	var data = {
		        	symbol_id: $symIdField.val(),
					start_date: $strDateField.val(),
					end_date: $endDateField.val(),
		        	status: $statusField.val(),
		        	id: ipo_id,
		        	update_ipo_offer: operation
		      	};

		      	$.ajax({ 
		        	type: "POST", 
		        	url: "../PROCESS/process.php", 
		        	data: data, 
		        	dataType: "html",
		        	success: function(response){ 
		          		hideloading(); 
		          		$("#myModal").modal("hide");
		          		$("#message").html(response);
		          		showMessage();
		          		// reload table content
		          		$.ajax({
					      	url: "load.php",
      						data: { get_ipo_offer_list: "get_ipo_offer_list" },
					      	type: "POST",
					      	dataType: "html",
						    success: function(data) {
						    	$("#tableList").html(data);
						    },
					    });
		        	},
		        	error: function(jqXHR, textStatus, errorThrown) {
		          		console.log(textStatus);
		        	}
		      	});
		    } else { 
		      	hideloading(); 
		      	return false; 
		    }
	  	});
	  	</script>';
	  	die();
	}
?>