<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
	<div id="loader" class="text-center my-4" style="display: none;">
	    <div class="spinner-border text-primary" role="status">
	        <span class="visually-hidden">Loading...</span>
	    </div>
	</div>

    <div class="row">
        <!-- First Form: Order No -->
        <h2 class="text-center">Check Transaction of TBL Auction</h2>
        <div class="col-md-6 mb-4">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="order_no" class="form-label">Order No</label>
                    <input type="text" class="form-control" id="order_no" name="order_no" required>
                </div>
                <button type="button" class="btn btn-primary" id="submit_orderno_id">Submit Order</button>
            </form>
        </div>

        <!-- Second Form: CID No -->
        <div class="col-md-6 mb-4">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="cid_no" class="form-label">CID No</label>
                    <input type="text" class="form-control" id="cid_no" name="cid_no" required>
                </div>
                <button type="button" class="btn btn-success" id="cid_no_submit_id">Submit CID</button>
            </form>
        </div>
        <hr>
        <div id="detials_id"></div>
        <hr>

        <div class="col-md-6 mb-4">
        	<h4 class="text-center">Insert Transaction of Force Credit</h4>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="order_no_bfs" class="form-label">BFS Order No:</label>
                    <input type="text" class="form-control" id="order_no_bfs" name="order_no_bfs" required>
                </div>
                <button type="button" class="btn btn-warning" id="submit_transaction_id">Submit</button>
            </form>
        </div>
        <hr>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</body>
<script type="text/javascript">
	$("#submit_transaction_id").click( function() {
		document.getElementById("loader").style.display = "block";
		const order_no_bfs = $("#order_no_bfs").val();
		const operation = 'submit__successful__transaction_auction';
		if (order_no_bfs == '') {
			document.getElementById("loader").style.display = "none";
			alert('required order no');
			return false;
		} else {
			$.ajax({
	            url: 'process/bond_subscription.php',
	            type: 'POST',
	            data: {
	                bfs_order_no: order_no_bfs,
	                submit__successful__transaction_auction: operation
	            },
	            dataType: 'html',
	            success: function(response) {
	            	document.getElementById("loader").style.display = "none";
	                alert(response);
	            },
	        });
		}
	});

	$("#submit_orderno_id").click( function() {
		document.getElementById("loader").style.display = "block";
		const order_no = $("#order_no").val();
		const operation = 'get__auction__dtls';
		const type = 'orderno_type';
		if (order_no == '') {
			document.getElementById("loader").style.display = "none";
			alert('required order no');
			return false;
		} else {
			$.ajax({
	            url: 'process/bond_subscription.php',
	            type: 'POST',
	            data: {
	                id: order_no,
	                type: type,
	                get__auction__dtls: operation
	            },
	            dataType: 'html',
	            success: function(response) {
	            	document.getElementById("loader").style.display = "none";
	                $("#detials_id").html(response);
	            },
	        });
		}
	});

	$("#cid_no_submit_id").click( function() {
		document.getElementById("loader").style.display = "block";
		const cid_no = $("#cid_no").val();
		const operation = 'get__auction__dtls';
		const type = 'cidno_type';
		if (cid_no == '') {
			document.getElementById("loader").style.display = "none";
			alert('required CID no');
			return false;
		} else {
			$.ajax({
	            url: 'process/bond_subscription.php',
	            type: 'POST',
	            data: {
	                id: cid_no,
	                type: type,
	                get__auction__dtls: operation
	            },
	            dataType: 'html',
	            success: function(response) {
	            	document.getElementById("loader").style.display = "none";
	                $("#detials_id").empty().append(response);
	            },
	        });
		}
	});
</script>
</html>
