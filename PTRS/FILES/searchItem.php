<?php 
include ('../../CONNECTIONS/db.php');

if(isset($_POST['operation'])) 
{
    $html = '<tr>';
    $html .= '<td>caidString</td>';
    $html .= '<td>symbolString</td>';
    $html .= '<td>rdString</td>';
    $html .= '<td>edString</td>';
    $html .= '<td>adString</td>';
    $html .= '<td>rateString</td>';
    $html .= '<td>typeString</td>';
    $html .= '<td>statusString</td>';
    $html .= '<td>editString</td>';
    $html .= '</tr>';
    $search_string = preg_replace("/[^A-Za-z0-9]/", " ", $_POST['query']);
    if (strlen($search_string) >= 1 && $search_string !== ' ') {
      if($_POST['operation'] == "search_rights")
      {
      $query = '  SELECT a.*,b.symbol from corporate_announcement a,symbol b WHERE a.symbol_id=b.symbol_id and a.announcement_type=1 and
      b.symbol LIKE "%'.$search_string.'%" ORDER BY corp_announcement_id';
      }
      elseif($_POST['operation'] == "search_bonus")
      {
      $query = '  SELECT a.*,b.symbol from corporate_announcement a,symbol b WHERE a.symbol_id=b.symbol_id and a.announcement_type=2 and
      b.symbol LIKE "%'.$search_string.'%" ORDER BY corp_announcement_id';
      }
      elseif($_POST['operation'] == "search_dividend")
      {
      $query = '  SELECT a.*,b.symbol from corporate_announcement a,symbol b WHERE a.symbol_id=b.symbol_id and a.announcement_type=3 and
      b.symbol LIKE "%'.$search_string.'%" ORDER BY corp_announcement_id';
      }
      else
      {
      }
      $wc= $dbh->prepare($query);
      $wc->execute();
      $io=1;
        if($wc->rowCount() > 0)
        {
        while($result=$wc->fetch(PDO::FETCH_ASSOC))
        {
           if($result['status']==1){$status='Active';}elseif ($result['status']==0) {$status='Inactive';}elseif ($result['status']==2) {$status='Completed';}else{$status='Select One';}
           $ca_id = preg_replace("/".$search_string."/i", "<b>".$search_string."</b>", $result['corp_announcement_id']);
           $symbol = $result['symbol'];           
           $rDate = '<input type="text" size="9" class="form-control" value="'.$result['record_date'].'" name="rdate" id="rdate'.$result['corp_announcement_id'].'">';
           $eDate = '<input type="text" size="9" class="form-control" value="'.$result['ex_date'].'" name="edate" id="edate'.$result['corp_announcement_id'].'">';
           $aDate = '<input type="text" size="9" class="form-control" value="'.$result['announcement_date'].'" name="adate" id="adate'.$result['corp_announcement_id'].'">';       
           $rate = '<input type="text" size="9" class="form-control" value="'.$result['rate'].'" name="rate" id="rate'.$result['corp_announcement_id'].'">';
           $type = '<select class="form-control" name="type" id="type'.$result['corp_announcement_id'].'">
                                <option value="'.$result['type'].'" selected>'.$result['type'].'</option>
                                <option value="Interim">Interim</option>
                                <option value="Final">Final</option>
                              </select>';
           $status = '<select class="form-control" name="status" id="status'.$result['corp_announcement_id'].'">
                                <option value="'.$result['status'].'" selected>'.$status.'</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                                <option value="2">Completed</option>
                              </select>';
           $update = '<button  data-toggle="modal" data-target="#myModal" class="btnpress" name="edit_CA"  id="'.$result['corp_announcement_id'].'" >
                              <i class="fa fa-edit"></i>UPDATE</button>';
           $o = str_replace('caidString', $ca_id, $html);
           $o = str_replace('symbolString', $symbol, $o);
           $o = str_replace('rdString', $rDate, $o);
           $o = str_replace('edString', $eDate, $o);
           $o = str_replace('adString', $aDate, $o);
           $o = str_replace('rateString', $rate, $o);
           $o = str_replace('typeString', $type, $o);
           $o = str_replace('statusString', $status, $o);
           $o = str_replace('editString', $update, $o);       
           echo($o);
           $io=$io+1;
        }
        }
        else
        {  
           $o = str_replace('caidString', '<span class="label label-danger">No result found</span>', $html);
           $o = str_replace('symbolString', '', $o);
           $o = str_replace('rdString', '', $o);
           $o = str_replace('edString', '', $o);
           $o = str_replace('adString', '', $o);
           $o = str_replace('rateString', '', $o);
           $o = str_replace('typeString', '', $o);
           $o = str_replace('statusString', '', $o);
           $o = str_replace('editString', '', $o);
           echo($o);
        }
    } 
}

if(isset($_POST['search_accounts'])) {

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
                      $name = ($res['acc_type'] = 'I') ? $res['f_name'].' '.$res['l_name'] : $res['f_name'];
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
<SCRIPT language=JavaScript>
$(".btnpress").click(function(event){ 
   var id = $(this).attr('id');
   var adate = $("#adate"+id).val();
   var rdate = $("#rdate"+id).val();
   var edate = $("#edate"+id).val();
   var rate = $("#rate"+id).val();
   var type = $("#type"+id).val();
   var status = $("#status"+id).val();
   if (confirm("Are you sure you want to update record Id # "+ id + '?'))
             {
            $.post( 
             "e-cds-css.php",
             {
                corp_announcement_id:id,
                adate:adate,
                edate:edate, 
                rdate:rdate,
                rate:rate,
                type:type,
                status:status,
             },
             function(data) {
              alert('SAVED SUCCESSFULLY');
              });
            }
             else
             {
                 return false;
             }
       });
</script>