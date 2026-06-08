<?php 
include ('../../CONNECTIONS/db.php');

if (isset($_POST['search_users_details'])) {
  $name = isset($_POST['name']) ? $_POST['name'] : '';
  $cid = isset($_POST['cid']) ? $_POST['cid'] : '';
  $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
  $email = isset($_POST['email']) ? $_POST['email'] : '';
  $cdCode = isset($_POST['cdCode']) ? $_POST['cdCode'] : '';
  $address = isset($_POST['address']) ? $_POST['address'] : '';

  $sql = "SELECT * FROM users WHERE 1 = 1";

  if(!empty($name)) {
    $sql .= " AND name LIKE :name";
  }
  if(!empty($cid)) {
    $sql .= " AND cid = :cid";
  }
  if(!empty($phone)) {
    $sql .= " AND phone = :phone";
  }
  if(!empty($email)) {
    $sql .= " AND email = :email";
  }
  if(!empty($cdCode)) {
    $sql .= " AND cd_code = :cdCode";
  }
  if(!empty($address)) {
    $sql .= " AND address LIKE :address";
  }

  $stmt = $dbh->prepare($sql);

  if(!empty($name)) {
    $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);
  }
  if(!empty($cid)) {
    $stmt->bindValue(':cid', $cid, PDO::PARAM_STR);
  }
  if(!empty($phone)) {
    $stmt->bindValue(':phone', $phone, PDO::PARAM_INT);
  }
  if(!empty($email)) {
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
  }
  if(!empty($cdCode)) {
    $stmt->bindValue(':cdCode', $cdCode, PDO::PARAM_STR);
  }
  if(!empty($address)) {
    $stmt->bindValue(':address', '%' . $address . '%', PDO::PARAM_STR);
  }

  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Name</th>
          <th>Username</th>
          <th>Phone</th>
          <th>Participant</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io = 1;
      foreach ($results as $row) {
        echo'
        <tr data-id="'.$row['user_id'].'">
          <td>'.$io.'</td>
          <td>'.$row['name'].'</td>
          <td>'.$row['username'].'</td>
          <td>'.$row['phone'].'</td>
          <td>'.$row['participant_code'].'</td>
          <td>
            <button class="btn btn-success" data-toggle="modal" data-target="#myModal" name="edit_user" id="edit_user" value="'.$row['user_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button>
            <button class="btn btn-danger" onclick="delete_user('.$row['user_id'].')" name="delete_user" id="delete_user'.$io.'" value="'.$row['user_id'].'"><i class="fa fa-trash-o"></i></button>
          </td>
        </tr>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      $("#resultTable").DataTable();
    })
  </script>';
  die();
} 
elseif (isset($_POST['search_participant'])) {
  $participant_code = isset($_POST['participant_code']) ? $_POST['participant_code'] : '';
  $org_name = isset($_POST['org_name']) ? $_POST['org_name'] : '';
  $contact_person = isset($_POST['contact_person']) ? $_POST['contact_person'] : '';
  $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
  $address = isset($_POST['address']) ? $_POST['address'] : '';

  $sql = "SELECT * FROM adm_participants WHERE 1=1";

  if(!empty($participant_code)){
    $sql .= " AND participant_code LIKE :participant_code";
  }
  if(!empty($contact_person)){
    $sql .= " AND contact_person LIKE :contact_person";
  }
  if(!empty($phone)){
    $sql .= " AND phone=:phone";
  }
  if(!empty($org_name)){
    $sql .= " AND name LIKE :org_name";
  }
  if(!empty($address)){
    $sql .= " AND address LIKE :address";
  }

  $stmt = $dbh->prepare($sql);

  if(!empty($participant_code)){
    $stmt->bindValue(':participant_code', '%'.$participant_code.'%', PDO::PARAM_STR);
  }
  if(!empty($contact_person)){
    $stmt->bindValue(':contact_person', '%'.$contact_person.'%', PDO::PARAM_STR);
  }
  if(!empty($phone)){
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
  }
  if(!empty($org_name)){
    $stmt->bindValue(':org_name', '%'.$org_name.'%', PDO::PARAM_STR);
  }
  if(!empty($address)){
    $stmt->bindValue(':address', '%'.$address.'%', PDO::PARAM_STR);
  }

  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>P Code</th>
          <th>Contact Person</th>
          <th>Address</th>
          <th>Phone</th>
          <th>email</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io=1;
      foreach ($results as $row) {
        echo'
        <tr data-id="'.$row['participant_id'].'">
          <td>'.$row['participant_id'].'</td>
          <td>'.$row['participant_code'].'</td>
          <td>'.$row['contact_person'].'</td>
          <td>'.$row['address'].'</td>
          <td>'.$row['phone'].'</td>
          <td>'.$row['email'].'</td>
          <td>
            <button class="btn btn-success" data-toggle="modal" data-target="#myModal" name="edit_part" id="edit_part" value="'.$row['participant_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button>
            <button class="btn btn-danger" onclick="deleteParticipant('.$row['participant_id'].')" name="delete_part" id="delete_part'.$io.'" value="'.$row['participant_id'].'"><i class="fa fa-trash-o"></i></button>
          </td>
        </tr>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#resultTable").DataTable();
          })
        </script>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>';
  die();
}
elseif (isset($_POST['search_institution'])) {
  $inst_name = isset($_POST['inst_name']) ? $_POST['inst_name'] : '';
  $address = isset($_POST['address']) ? $_POST['address'] : '';

  $contact_person = '';
  $phone = '';

  $sql = "SELECT * FROM adm_institution WHERE 1=1";

  if(!empty($inst_name)){
    $sql .= " AND name LIKE :inst_name";
  }
  if(!empty($contact_person)){
    $sql .= " AND contact_person LIKE :contact_person";
  }
  if(!empty($phone)){
    $sql .= " AND phone=:phone";
  }
  if(!empty($address)){
    $sql .= " AND address LIKE :address";
  }

  $stmt = $dbh->prepare($sql);

  if(!empty($inst_name)){
    $stmt->bindValue(':inst_name', '%'.$inst_name.'%', PDO::PARAM_STR);
  }
  if(!empty($contact_person)){
    $stmt->bindValue(':contact_person', '%'.$contact_person.'%', PDO::PARAM_STR);
  }
  if(!empty($phone)){
    $stmt->bindValue(':phone', $phone, PDO::PARAM_INT);
  }
  if(!empty($address)){
    $stmt->bindValue(':address', '%'.$address.'%', PDO::PARAM_STR);
  }

  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Name</th>
          <th>Address</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io = 1;
      foreach ($results as $row) {
        echo'
        <tr data-id="'.$row['institution_id'].'">
          <td>'.$io.'</td>
          <td>'.$row['name'].'</td>
          <td>'.$row['address'].'</td>
          <td>
            <button class="btn btn-success" data-toggle="modal" data-target="#myModal" name="edit_ins" id="edit_ins" value="'.$row['institution_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button>
            <button class="btn btn-danger" onclick="return delete_inst('.$row['institution_id'].');" name="delete_inst" id="delete_inst'.$io.'" value="'.$row['institution_id'].'"><i class="fa fa-trash-o"></i></button>
          </td>
        </tr>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#resultTable").DataTable();
          })
        </script>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>';
  die();
}
elseif (isset($_POST['search_symbols'])) {
  $symbol_id = isset($_POST['symbol_id']) ? $_POST['symbol_id'] : '';
  $symbol_name = isset($_POST['symbol_name']) ? $_POST['symbol_name'] : '';

  $sql = "SELECT * FROM symbol WHERE 1=1";

  if(!empty($symbol_id)){
    $sql .= " AND symbol_id=:symbol_id";
  }
  if(!empty($symbol_name)){
    $sql .= " AND symbol LIKE :symbol_name";
  }

  $stmt = $dbh->prepare($sql);

  if(!empty($symbol_id)){
    $stmt->bindValue(':symbol_id', $symbol_id, PDO::PARAM_INT);
  }
  if(!empty($symbol_name)){
    $stmt->bindValue(':symbol_name', '%'.$symbol_name.'%', PDO::PARAM_STR);
  }

  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Symbol</th>
          <th>Sector</th>
          <th>Face Value</th>
          <th>Security Type</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io=1;
      foreach ($results as $row) {
        echo'
        <tr data-id="'.$row['symbol_id'].'">
          <td>'.$row['symbol_id'].'</td>
          <td>'.$row['symbol'].'</td>
          <td>'.$row['sector'].'</td>
          <td>'.$row['face_value'].'</td>
          <td>'.$row['security_type'].'</td>
          <td>
            <button class="btn btn-success" data-toggle="modal" data-target="#myModal" name="edit_symbol" id="edit_symbol" value="'.$row['symbol_id'].'" onClick="getStateSymbol(this.value);"><i class="fa fa-edit"></i></button>
          </td>
        </tr>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#resultTable").DataTable();
          })
        </script>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>';
  die(); 
}
elseif (isset($_POST['search_linkusers'])) {
  $cid_no = isset($_POST['cid_no']) ? trim($_POST['cid_no']) : '';
  $participant_code = isset($_POST['part_code']) ? trim($_POST['part_code']) : '';
  $cd_code = isset($_POST['cd_code']) ? trim($_POST['cd_code']) : '';

  $sql = "SELECT id, participant_code, client_code, username, broker_user_name FROM linkuser WHERE 1=1";

  if(!empty($cid_no)){
    $sql .= " AND username LIKE :cid_no";
  }
  if(!empty($participant_code)){
    $sql .= " AND participant_code LIKE :participant_code";
  }
  if(!empty($cd_code)){
    $sql .= " AND client_code = :cd_code";
  }

  $stmt = $dbh->prepare($sql);

  if(!empty($cid_no)){
    $stmt->bindValue(':cid_no', '%'.$cid_no.'%', PDO::PARAM_STR);
  }
  if(!empty($participant_code)){
    $stmt->bindValue(':participant_code', '%'.$participant_code.'%', PDO::PARAM_STR);
  }
  if(!empty($cd_code)){
    $stmt->bindValue(':cd_code', $cd_code, PDO::PARAM_STR);
  }

  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Participant Code</th>
          <th>Client Account</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io=1;
      foreach ($results as $row) {
        echo'
        <tr data-id="'.$row['id'].'">
          <td>'.$row['id'].'</td>
          <td>'.$row['participant_code'].'</td>
          <td>'.$row['client_code'].'</td>
          <td>
            <button type="button" class="btn btn-warning btn-lg" data-toggle="tooltip" data-placement="top" title="Reset Password" onclick="return reset_pass('.$io.');" name="reset_pass" id="reset_pass'.$io.'" value="'.$row['id'].'"><i class="fa fa-key"></i></button>

            <button type="button" class="btn btn-info btn-lg" data-toggle="tooltip" data-placement="top" title="Unlock Account" onclick="return unlock_account('.$io.');" name="unlock" id="unlock'.$io.'" value="'.$row['username'].'"><i class="fa fa-unlock"></i></button>

            <button type="button" class="btn btn-danger btn-lg" data-toggle="tooltip" data-placement="top" title="Delete Link User" onclick="return delete_account('.$io.');" name="delete_linkuser" id="delete_linkuser'.$io.'" value="'.$row['id'].'"><i class="fa fa-trash-o"></i></button>
          </td>
        </tr>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#resultTable").DataTable();
          })
        </script>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>';
  die();
}
elseif (isset($_POST['search_assign_broker'])) {
  $participant_code = isset($_POST['participant_code']) ? $_POST['participant_code'] : '';
  $usr_name = isset($_POST['usr_name']) ? $_POST['usr_name'] : '';

  $sql = "SELECT * FROM assign_broker WHERE 1=1";

  if(!empty($participant_code)){
    $sql .= " AND participant_code LIKE :participant_code";
  }
  if(!empty($usr_name)){
    $sql .= " AND username LIKE :usr_name";
  }

  $stmt = $dbh->prepare($sql);

  if(!empty($participant_code)){
    $stmt->bindValue(':participant_code', '%'.$participant_code.'%', PDO::PARAM_STR);
  }  
  if(!empty($usr_name)){
    $stmt->bindValue(':usr_name', '%'.$usr_name.'%', PDO::PARAM_STR);
  }

  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Participant Code</th>
          <th>Username</th>
          <th>Type</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io=1;
      foreach ($results as $row) {
        echo'
        <tr data-id="'.$row['id'].'">
          <td>'.$io.'</td>
          <td>'.$row['participant_code'].'</td>
          <td>'.$row['username'].'</td>
          <td>'.$row['type'].'</td>
          <td>
            <button class="btn btn-success" data-toggle="modal" data-target="#myModal" name="edit_assign_broker" id="edit_assign_broker" value="'.$row['id'].'" onClick="getAssignBrokerDtls(this.value);"><i class="fa fa-edit"></i></button>
            <button class="btn btn-danger" onclick="return deleteAssignBrokerDtls('.$row['id'].');" name="delete_assign_broker" id="delete_assign_broker'.$io.'" value="'.$row['id'].'"><i class="fa fa-trash-o"></i></button>
          </td>
        </tr>
        <script type="text/javascript">
          $(document).ready(function() {
            $("#resultTable").DataTable();
          })
        </script>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>';
  die(); 
}
elseif (isset($_POST['get_user_details'])) {
  $usr_name = $_POST['usr_name'] ?? '';

  $stmt = $dbh->prepare("SELECT user_id, name, username, email, phone, participant_code, status FROM users WHERE username = ? AND role_id != 4");
  $stmt->bindParam(1, $usr_name);
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo'
  <hr>
  <div class="table-responsive">
    <table id="resultTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Name</th>
          <th>Username</th>
          <th>Phone</th>
          <th>Participant</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>                  
      <tbody>';
      $io = 1;
      foreach ($results as $row) {
        $usr_status = ($row['status'] == '1') ? 'Active' : 'Inactive';
        echo'
        <tr data-id="'.$row['user_id'].'">
          <td>' . $io . '</td>
          <td>' . $row['name'] . '</td>
          <td>' . $row['username'] . '</td>
          <td>' . $row['phone'] . '</td>
          <td>' . $row['participant_code'] . '</td>
          <td>' . $usr_status . '</td>
          <td>
            <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Reset Password" name="reset_pwd_adm" id="reset_pwd_adm" value="'.$row['user_id'].'" onClick="reset_pwd_adm('.$row['user_id'].');"><i class="fa fa-unlock"></i></button>
            <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Inactive User" onclick="inactive_user('.$row['user_id'].')" name="inactive_usr" id="inactive_usr'.$io.'" value="'.$row['user_id'].'"><i class="fa fa-user-times"></i></button>
            <button class="btn btn-info" data-toggle="tooltip" data-placement="top" title="Active User" onclick="active_user('.$row['user_id'].')" name="active_user" id="active_user'.$io.'" value="'.$row['user_id'].'"><i class="fa fa-user-plus"></i></button>
          </td>
        </tr>';
        $io++;
      }
      echo'
      </tbody>
    </table>
  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      $("#resultTable").DataTable();
    })
  </script>';
  die();
} 
?>