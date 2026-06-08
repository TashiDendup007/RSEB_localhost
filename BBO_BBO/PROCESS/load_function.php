<?php 
	include ('../../CONNECTIONS/db.php');
	date_default_timezone_set("Asia/Thimphu");
    
	if (isset($_POST['populateList'])) {

		$id = $_POST['id'];
		$list_name = $_POST['list_name'];

		$query = "";
		$options = "";

		if ($list_name == 'gewog_list') {
		    $query = "SELECT g.Gewog_Serial_No AS Serial_No, g.Gewog_Name AS Name FROM tbl_gewog_master g WHERE g.Dzongkhag_Serial_No = :id ORDER BY g.Gewog_Name ASC";
		    $value_field = 'Serial_No';
		    $name_field = 'Name';
		} 
		elseif ($list_name == "village_list") {
		    $query = "SELECT v.Village_Serial_No AS Serial_No, v.Village_Name AS Name FROM tbl_village_master v WHERE v.Gewog_Serial_No = :id ORDER BY v.Village_Name ASC";
		    $value_field = 'Serial_No';
		    $name_field = 'Name';
		}

		if ($query) {
		    $stmt = $dbh->prepare($query);
		    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
		    $stmt->execute();
		    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		    echo '<option value="">--Select།--</option>';

		    foreach ($rows as $row) {
		        echo '<option value="'.$row[$value_field].'">'.$row[$name_field].'</option>';
		    }
		}

	    exit();
	}

	else if (isset($_POST['populateGewogList'])) {

		$id = $_POST['id'];

		$query = "";
		$options = "";

		$query = "SELECT g.Gewog_Serial_No AS Serial_No, g.Gewog_Name AS Name FROM tbl_gewog_master g WHERE g.Dzongkhag_Serial_No = :id ORDER BY g.Gewog_Name ASC";
		$value_field = 'Serial_No';
		$name_field = 'Name';

		if ($query) {
		    $stmt = $dbh->prepare($query);
		    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
		    $stmt->execute();
		    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		    echo '<option value="">--Select།--</option>';

		    foreach ($rows as $row) {
		        echo '<option value="'.$row[$value_field].'">'.$row[$name_field].'</option>';
		    }
		}

	    exit();
	}

	else if (isset($_POST['populateVillageList'])) {

		$id = $_POST['id'];

		$query = "";
		$options = "";

		$query = "SELECT v.Village_Serial_No AS Serial_No, v.Village_Name AS Name FROM tbl_village_master v WHERE v.Gewog_Serial_No = :id ORDER BY v.Village_Name ASC";
		$value_field = 'Serial_No';
		$name_field = 'Name';

		if ($query) {
		    $stmt = $dbh->prepare($query);
		    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
		    $stmt->execute();
		    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		    echo '<option value="">--Select།--</option>';

		    foreach ($rows as $row) {
		        echo '<option value="'.$row[$value_field].'">'.$row[$name_field].'</option>';
		    }
		}

	    exit();
	}
?>