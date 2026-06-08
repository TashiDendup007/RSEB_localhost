<?php
include('../FILES/sessionStartFile_admin.php');
include ('../../CONNECTIONS/db.php');

if(isset($_POST['get_monthwise_trade_dtls'])) {
	$symbol = $_POST['symbol'];
  	$startDate = $_POST['startDate'].' 00:00:00';
  	$endDate = $_POST['endDate'].' 23:59:59';

  	try {
		$getsymbol = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id=:symbol");
		$getsymbol->bindParam(':symbol', $symbol);
		$getsymbol->execute();
		$res = $getsymbol->fetch();
		echo'
		<div class="row">
          <div class="col-lg-12">
            <div class="box">
            	<div class="box-header">Symbol = <strong>'.$res['symbol'].'</strong></div>
              	<div class="box-body table-responsive">
                <table id="tableListId" class="table table-bordered table-striped">
			      <thead>
			        <tr>
			          <th>#</th>
			          <th>Year</th>
			          <th>Month</th>
			          <th>Volume</th>
			          <th>Max Price</th>
			          <th>Min Price</th>
			        </tr>
			      </thead>
			      <tbody>';
			      	$sql = $dbh->prepare("SELECT 
							SUM(e.lot_size_execute) traded_Volume, 
							MAX(e.order_exe_price) max_price, 
							MIN(e.order_exe_price) min_price, 
							MONTHNAME(e.order_date) month_wise, 
							YEAR(e.order_date) trade_year
						FROM 
							executed_orders e
						WHERE 
							e.side='S' 
							AND e.symbol_id=:symbol
							AND e.order_date BETWEEN :start_date AND :end_date
						GROUP BY 
							MONTH(e.order_date)");
				    $sql->bindParam(':symbol', $symbol);
				    $sql->bindParam(':start_date', $startDate);
				    $sql->bindParam(':end_date', $endDate);
				    $sql->execute();
				    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
				    $i = 1;
				    foreach ($rows as $row) {
				    	echo'
				      	<tr>
				      		<td>'.$i.'</td>
				      		<td>'.$row['trade_year'].'</td>
				      		<td>'.$row['month_wise'].'</td>
				      		<td>'.$row['traded_Volume'].'</td>
				      		<td>'.$row['max_price'].'</td>
				      		<td>'.$row['min_price'].'</td>
				      	</tr>';
				      	$i++;
				    }
			      echo'
			      </tbody>
    			</table>
              </div>
            </div>
          </div>
        </div>';
	    $dbh = null;
	} catch(PDOException $e) {
	    $dbh->rollBack();
	}
	die();
} 
elseif(isset($_POST['get_daywise_trade_dtls'])) {
		$symbol = $_POST['symbol'];
  	$startDate = $_POST['startDate'];
  	$endDate = $_POST['endDate'];

  	try {
				$getsymbol = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id=:symbol");
				$getsymbol->bindParam(':symbol', $symbol);
				$getsymbol->execute();
				$res = $getsymbol->fetch();
				echo'
				<div class="row">
          <div class="col-lg-12">
            <div class="box">
            	<div class="box-header">Symbol = <strong>'.$res['symbol'].'</strong></div>
              	<div class="box-body table-responsive">
                <table id="tableListId" class="table table-bordered table-striped">
			      <thead>
			        <tr>
			          <th>#</th>
			          <th>Date</th>
			          <th>Volume</th>
			          <th>Max Price</th>
			          <th>Min Price</th>
			          <th>Market Price</th>
			        </tr>
			      </thead>
			      <tbody>';
			      $query = "SELECT 
							    s.symbol,
							    DATE_FORMAT(eo.order_date, '%d/%m/%Y') AS order_date_format,
							    eo.lot_size_execute AS total_traded_vol,
							    eo.max_order_exe_price AS max_price,    
							    eo.min_order_exe_price AS min_price,
							    mph.price AS market_price
							FROM (
							    SELECT 
							        DATE(order_date) AS order_date,
							        SUM(lot_size_execute) AS lot_size_execute,
							        MAX(order_exe_price) AS max_order_exe_price,
							        MIN(order_exe_price) AS min_order_exe_price,
							        symbol_id
							    FROM executed_orders
							    WHERE symbol_id = :symbol 
							        AND side = 'B' 
							        AND DATE(order_date) >= :start_date
							        AND DATE(order_date) <= :end_date
							    GROUP BY DATE(order_date), symbol_id
							) AS eo
							JOIN symbol AS s ON eo.symbol_id = s.symbol_id 
							JOIN market_price_history AS mph ON DATE(mph.date) = eo.order_date AND mph.symbol_id = :symbol 
							GROUP BY eo.order_date
						";
						$sql = $dbh->prepare($query);
				    $sql->bindParam(':symbol', $symbol);
				    $sql->bindParam(':start_date', $startDate);
				    $sql->bindParam(':end_date', $endDate);
				    $sql->execute();
				    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
				    $i = 1;
				    foreach ($rows as $row) {
				    	if($row['market_price'] != NULL && $row['market_price'] != ''){
				    		echo'
					      	<tr>
					      		<td>'.$i.'</td>
					      		<td>'.$row['order_date_format'].'</td>
					      		<td>'.$row['total_traded_vol'].'</td>
					      		<td>'.$row['max_price'].'</td>
					      		<td>'.$row['min_price'].'</td>
					      		<td>'.$row['market_price'].'</td>
					      	</tr>';
					      	$i++;
				    	}
				    }
			      echo'
			      </tbody>
    			</table>
              </div>
            </div>
          </div>
        </div>';
        echo"
        <script type='text/javascript'>
				  $(document).ready(function() {
				    $('#tableListId').DataTable({
				    	'lengthMenu': [ [10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] ],
				    });
				} );
				</script>";
	    $dbh = null;
	} catch(PDOException $e) {
	    // $dbh->rollBack();
		echo $e->getMessage();
	}
	die();
} else {
	echo'
	<div class="col-lg-12 col-md-12">
		<div class="alert alert-danger alert-dismissible">
			<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Sorry! There was no function operation.
		</div>
	</div>';
	die();
}

?>