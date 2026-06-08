<?php
include ('../../CONNECTIONS/db.php');

if (isset($_POST['search_corporation_announcement'])) {
  $announce_type = $_POST['announce_type'];
  $symbol = $_POST['symbol'];

  $sql = "SELECT a.corp_announcement_id, a.symbol_id, a.announcement_type, a.record_date, a.ex_date, a.announcement_date, a.rate, a.type, a.status, a.created_date, b.symbol 
          FROM corporate_announcement a 
          JOIN symbol b ON a.symbol_id = b.symbol_id 
          WHERE a.symbol_id = :sym AND a.announcement_type = :annType ORDER BY a.corp_announcement_id ASC";

  // $params = [':sym' => "%{$symbol}%", ':annType' => $announce_type];
  $params = [':sym' => $symbol, ':annType' => $announce_type];

  /*if ($action_type == 'search_rights') {
    $sql .= " AND a.announcement_type = 1";
  } elseif ($action_type == 'search_bonus') {
     $sql .= " AND a.announcement_type = 2";
  } elseif ($action_type == 'search_dividend') {
     $sql .= " AND a.announcement_type = 3";
  } elseif ($action_type == 'search_bback') {
     $sql .= " AND a.announcement_type = 4";
  } 
  $sql .= " ORDER BY a.corp_announcement_id ASC";*/
  $stmt = $dbh->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Symbol</th>
          <th>Record Date</th>
          <th>Ex Date</th>
          <th>Announcement Date</th>
          <th>RIGHTS %</th>
          <th>Type</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>';
      $i = 1;
      foreach ($rows as $row) {
        echo'
        <tr>
          <td>'.$i.'</td>
          <td>'.$row['symbol'].'</td>
          <td>
            <input type="hidden" value="'.$row['announcement_type'].'" name="announce_type" id="announce_type'.$row['corp_announcement_id'].'">
            <input type="date" size="5" class="form-control" value="'.$row['record_date'].'" name="rdate" id="rdate'.$row['corp_announcement_id'].'">
          </td>
          <td>
            <input type="date" size="5" class="form-control" value="'.$row['ex_date'].'" name="edate" id="edate'.$row['corp_announcement_id'].'">
          </td>
          <td>
            <input type="date" size="5" class="form-control" value="'.$row['announcement_date'].'" name="adate" id="adate'.$row['corp_announcement_id'].'">
          </td>
          <td>
            <input type="text" size="4" class="form-control" value="'.number_format($row['rate'], 2).'" name="rate" id="rate'.$row['corp_announcement_id'].'">
          </td>
          <td>
            <select class="form-control" name="type" id="type'.$row['corp_announcement_id'].'">
              <option value="">--Select Status--</option>
              <option value="Interim" '; if($row['type'] == "Interim") echo 'selected="selected"'; echo'>Interim</option>
              <option value="Final" '; if($row['type'] == "Final") echo 'selected="selected"'; echo'>Final</option>
            </select>
          </td>
          <td>
            <select class="form-control" name="status" id="status'.$row['corp_announcement_id'].'">
              <option value="">--Select Status--</option>
              <option value="1" '; if($row['status'] == "1") echo 'selected="selected"'; echo'>Active</option>
              <option value="0" '; if($row['status'] == "0") echo 'selected="selected"'; echo'>Inactive</option>
              <option value="2" '; if($row['status'] == "2") echo 'selected="selected"'; echo'>Completed</option>
            </select>
          </td>
          <td>
            <button type="button" class="btn btn-success btn-flat btnpress" data-toggle="modal" data-target="#myModal" name="edit_CA" id="'.$row['corp_announcement_id'].'"><i class="fa fa-edit"></i> Update</button>
          </td>
        </tr>';
        $i++;
      }
      echo'
      </tbody>
    </table>
  </div>';
}

if (isset($_POST['search_accounts'])) {

  $value_enter = isset($_POST['cid_number']) ? $_POST['cid_number'] : 0;

  $stmt = $dbh->prepare("SELECT client_id, acc_type, cd_code, f_name, l_name, ID 
                       FROM client_account 
                       WHERE ID LIKE :st 
                       OR cd_code LIKE :st 
                       OR f_name LIKE CONCAT(:st, '%') 
                       OR l_name LIKE CONCAT(:st, '%') 
                       ORDER BY client_id");
  $stmt->bindValue(':st', $value_enter . '%', PDO::PARAM_STR);
  $stmt->execute();

  if($stmt->rowCount() > 0) {
      ob_start(); // Start output buffering
      echo'
      <div class="col-lg-12 col-md-12">
          <div class="table-responsive">
              <table id="resultTable" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>CD</th>
                          <th>Name</th>
                          <th>CID/DISN</th>
                          <th>Action</th>
                      </tr>
                  </thead>                  
                  <tbody>';
                  $i = 1;
                  while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
                      $name = '';
                      if ($res['acc_type'] == 'I') {
                          $name = $res['f_name'].' '.$res['l_name'];
                      } else {
                          $name = $res['f_name'];
                      }
                      echo'
                      <tr>
                          <td>'.$i.'</td>
                          <td>'.$res['cd_code'].'</td>
                          <td>'.$name.'</td>
                          <td>'.$res['ID'].'</td>
                          <td>
                          <button class="btn btn-success" data-toggle="modal tooltip" data-target="#myModal" name="cli_id" id="cli_id" value="'.$res['client_id'].'" onClick="getState(this.value);" data-placement="top" title="Edit Account"><i class="fa fa-edit"></i></button>
                          </td>
                      </tr>';
                      $i++;
                  }
                  echo'
                  </tbody>
              </table>
          </div>
      </div>
      <script type="text/javascript">
        $(document).ready(function() {
            $("#resultTable").DataTable();
        });
      </script>';
      $output = ob_get_clean(); // Get the HTML output
      echo $output; // Echo the HTML output
  } else {
      echo'<div class="col-lg-12 col-md-12" style="color: red;"><h3>No Account Details</h3></div>';
  }

}
else
{
}
?>
<script language=JavaScript>
$(".btnpress").click( function( event ) {
  var $this = $(this);
  var id = $this.attr('id');
  var ann_type = $("#announce_type"+id).val();
  var adate = $("#adate"+id).val();
  var rdate = $("#rdate"+id).val();
  var rate = $("#rate"+id).val();
  var type = $("#type"+id).val();
  var status = $("#status"+id).val();
  var edate = '0000-00-00';
  if (ann_type != 4) {
    edate = $("#edate"+id).val();
  }

  if (confirm("Are you sure you want to update record Id # "+ id + '?')) {
    $.post(
      "e-cds-css.php", {
        corp_announcement_id: id,
        adate: $.trim(adate),
        edate: $.trim(edate),
        rdate: $.trim(rdate),
        rate: $.trim(rate),
        type: $.trim(type),
        status: $.trim(status),
      },
      function (data) {
        if (data == 'YES') {
          $("#message").html('<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Record was saved successfully.</div></div></div>');
        } else {
          $("#message").html('<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Sorry there was an error.</div></div></div>');
        }
        showMessage();
      }
    );
  } else {
    return false;
  }
});
</script>
