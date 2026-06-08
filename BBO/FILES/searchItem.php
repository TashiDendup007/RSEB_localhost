<?php 
    include ('session_start_file.php');
    include ('../../CONNECTIONS/db.php');

    $check= $dbh->prepare("SELECT a.institution_id, c.participant_code 
        FROM adm_institution a 
        JOIN adm_participants b ON a.institution_id = b.institution_id
        JOIN users c ON b.participant_code = c.participant_code 
        WHERE c.username = :un
    ");
    $check->bindParam(':un', $username);
    $check->execute();
    $res=$check->fetch();
    $institution_id = $res['institution_id'];

    if(isset($_POST['search_accounts'])) {
        $value_enter = isset($_POST['cid_number']) ? $_POST['cid_number'] : 0;

        $select = $dbh->prepare("SELECT client_id, cd_code, f_name, l_name, ID, acc_type 
            FROM client_account 
            WHERE SUBSTR(user_name, 1, 7) = :passCode 
                AND (ID LIKE CONCAT(:value, '%') 
                OR cd_code LIKE CONCAT(:value, '%') 
                OR f_name LIKE CONCAT(:value, '%') 
                OR l_name LIKE CONCAT(:value, '%')) 
                AND institution_id = :ins_id 
                -- AND user_name=:un
        ");
        $select->bindParam(':passCode', $pass_code);
        $select->bindParam(':value', $value_enter);
        $select->bindParam(':ins_id', $institution_id);
        // $select->bindParam(':un', $username);
        $select->execute();
        if($select->rowCount() > 0){
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
                        while ($res = $select->fetch(PDO::FETCH_ASSOC)) {
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
                                <button class="btn btn-success" data-toggle="modal tooltip" data-target="#myModal" name="edit_user" id="edit_user" value="'.$res['client_id'].'" onClick="getState(this.value);" data-placement="top" title="Edit Account"><i class="fa fa-edit"></i></button>
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
              $( document ).ready(function() {
                $("#resultTable").DataTable();
              });
            </script>';
            $output = ob_get_clean(); // Get the HTML output
            echo $output; // Echo the HTML output
        } else {
            echo'<div class="col-lg-12 col-md-12" style="color: red;"><h3>No Account Details</h3></div>';
        }
        die();
    }
    else {
        echo '<div class="col-lg-12 col-md-12">No Function Call</div>';
        die();
    }
?>