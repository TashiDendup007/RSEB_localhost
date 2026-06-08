<?php
    date_default_timezone_set("Asia/Thimphu");
    function market_price($sy_id)
    {
         include ('./CONNECTIONS/db.php');
         $pr = $dbh->prepare('SELECT market_price from market_price WHERE symbol_id=:id');
         $pr ->bindParam(':id',$sy_id);
         $pr ->execute();
         $value= $pr->fetch();
         $price=$value['market_price'];
         return $price; 
    }
    function circuit($cap_name)
    {
         include ('./CONNECTIONS/db.php');
         $pr = $dbh->prepare('SELECT margin from circuit_breaker WHERE name=:n');
         $pr ->bindParam(':n',$cap_name);
         $pr ->execute();
         $value= $pr->fetch();
         $cap=$value['margin'];
         return $cap; 
    }
    function cap_compute($market_price,$cap)
    {
         $value=($market_price*$cap)/100;
         return $value; 
    }
    function client_commission($cd_code,$username)
    {
         include ('./CONNECTIONS/db.php');
         $pr = $dbh->prepare('SELECT b.ID,b.bro_comm_id,b.f_name,b.l_name,b.cd_code,c.rate from client_account b,bbo_commission c
          where b.bro_comm_id=c.bro_comm_id and b.cd_code=:ac and b.user_name=:un');
         $pr->bindParam(':ac',$cd_code);
         $pr->bindParam(':un',$username);
         $pr ->execute();
         $value= $pr->fetch();
         $b_commis=$value['rate'];
         return $b_commis; 
    }
    function client_commission_te($cd_code,$broker_user_name)
    {
         include ('./CONNECTIONS/db.php');
         $pr = $dbh->prepare('SELECT b.ID,b.bro_comm_id,b.f_name,b.l_name,b.cd_code,c.rate from client_account b,bbo_commission c
          where b.bro_comm_id=c.bro_comm_id and b.cd_code=:ac and b.user_name=:un');
         $pr->bindParam(':ac',$cd_code);
         $pr->bindParam(':un',$broker_user_name);
         $pr ->execute();
         $value= $pr->fetch();
         $b_commis_te=$value['rate'];
         return $b_commis_te; 
    }
    function pending_vol($cd_code,$sy_id)
    {
         include ('./CONNECTIONS/db.php');
         $wc= $dbh->prepare("SELECT volume,pending_out_vol,pending_in_vol  from cds_holding where cd_code=:cd and symbol_id=:id");
         $wc->bindParam(':id',$sy_id);
         $wc->bindParam(':cd',$cd_code);
         $wc ->execute();
         $value= $wc->fetch();
         $piv=$value['pending_in_vol'];
         $pov=$value['pending_out_vol'];
         $vol=$value['volume'];
         return array($pov,$piv,$vol); 
    }
    function cash_total($cd_code,$username)
    {
         include ('./CONNECTIONS/db.php');
         $wc= $dbh->prepare("SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID
            FROM bbo_finance a, client_account b
            where  a.cd_code=:ac and b.cd_code=:ac and a.user_name=:un and a.flag=1");
         $wc->bindParam(':ac',$cd_code);
         $wc->bindParam(':un',$username);
         $wc ->execute();
         $value= $wc->fetch();
         $tot=$value['tot'];
         return $tot;

    }
    function cash_total_client($cd_code,$broker_user_name)
    {
         include ('./CONNECTIONS/db.php');
         $wc= $dbh->prepare("SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID
            from bbo_finance a,client_account b
            where  a.cd_code=:ac and b.cd_code=:ac and a.user_name=:un and a.flag=1");
         $wc->bindParam(':ac',$cd_code);
         $wc->bindParam(':un',$broker_user_name);
         $wc ->execute();
         $value= $wc->fetch();
         $tot=$value['tot'];
         return $tot;

    }
    function prev_amt_ord($fid)
    {
         include ('./CONNECTIONS/db.php');
         $wc= $dbh->prepare("SELECT * from bbo_finance where flag_id=:fid");
         $wc->bindParam(':fid',$fid);
         $wc->execute();
         $value= $wc->fetch();
         $e_amt=$value['amount'];
         return $e_amt;
    }
    function check_orders($cdcode, $sy_id, $side, $p_code)
    {
         include ('./CONNECTIONS/db.php');
         $wc= $dbh->prepare("SELECT * from orders where cd_code=:c and symbol_id=:sy_id and participant_code=:p and side=:s");
         $wc->bindParam(':c',$cdcode);$wc->bindParam(':sy_id',$sy_id);$wc->bindParam(':p',$p_code);$wc->bindParam(':s',$side);
         $wc->execute();
         if($wc->rowCount() > 0){
            return 1;
         }
         else{
            return 0;
         }
    }

    // matching starts
    function exe_vol($oidd){
         include ('./CONNECTIONS/db.php'); 
         $pr = $dbh->prepare('SELECT exe_vol from orders WHERE order_id=:oidd');
         $pr ->bindParam(':oidd',$oidd);
         $pr ->execute();
         $value= $pr->fetch();
         $exe_vol=$value['exe_vol'];
         return $exe_vol; 
    }
    function exe_vol_b($oidd){
         include ('./CONNECTIONS/db.php'); 
         $pr = $dbh->prepare('SELECT exe_vol from orders WHERE order_id=:oidd');
         $pr ->bindParam(':oidd',$oidd);
         $pr ->execute();
         $value= $pr->fetch();
         $exe_vol=$value['exe_vol'];
         return $exe_vol; 
    }
    function compare($pid){
       include ('./CONNECTIONS/db.php'); 
       $q222 = $dbh->prepare('SELECT diff_chk from price_table where pid=:pid');
       $q222 ->bindParam(':pid',$pid);
       $q222 ->execute();
       $value= $q222->fetch();
       $diff_chk=$value['diff_chk'];
       return $diff_chk; 
    }                 
    function rowcountsell($op,$sym_id){
      include ('./CONNECTIONS/db.php'); 
       $s=$sym_id;
       $o=$op;
         $pr = $dbh->prepare('SELECT * from orders WHERE price <= :op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol DESC');
         $pr ->bindParam(':op',$o);
         $pr ->bindParam(':sym_id',$s);
         $pr ->execute();
         $rowcount= $pr->rowcount();
         return $rowcount; 
    }   
    function rowcountbuy($op,$sym_id){
       include ('./CONNECTIONS/db.php'); 
       $s=$sym_id;
       $o=$op;
         $pr = $dbh->prepare('SELECT * from orders WHERE price >= :op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC');
         $pr ->bindParam(':op',$o);
         $pr ->bindParam(':sym_id',$s);
         $pr ->execute();
         $rowcount= $pr->rowcount();
         return $rowcount; 
    }
    function ins_id($username)
    {
        include ('./CONNECTIONS/db.php'); 
        $check= $dbh->prepare('SELECT a.institution_id,c.participant_code  from adm_institution a, adm_participants b,users c 
            where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un');
        $check->bindParam(':un',$username);
        $check->execute();
        $res=$check->fetch();
        $institution_id=$res['institution_id'];
        $participant_code=$res['participant_code'];
        return array($institution_id,$participant_code); 
    }
    function find_link_user_cd_code($username)
    {
        include ('./CONNECTIONS/db.php'); 
        $check= $dbh->prepare('SELECT *  from linkuser where username=:un');
        $check->bindParam(':un',$username);
        $check->execute();
        $res=$check->fetch();
        $cdcode=$res['client_code'];
        return $cdcode; 
    }
    function trade_confirm_sell($cd_code,$toDate,$fromDate){
      include ('./CONNECTIONS/db.php'); 
         $pr = $dbh->prepare('SELECT  cast(avg(order_exe_price) as decimal(13,2)) as avgp, sum(lot_size_execute) as lse from executed_orders  where cd_code=:cd and :fromDate <= order_date and order_date <= :toDate and side="S"');
         $pr->bindParam(':cd',$cd_code);
         $pr->bindParam(':fromDate',$fromDate);
         $pr->bindParam(':toDate',$toDate);
         $pr ->execute();
         $row= $pr->fetch();
         $p=$row['avgp'];
         $v=$row['lse'];
         $totals=$p*$v;
         return $totals;
    }
    function trade_confirm_buy($cd_code,$toDate,$fromDate){
      include ('./CONNECTIONS/db.php'); 
         $pr = $dbh->prepare('SELECT  cast(avg(order_exe_price) as decimal(13,2)) as avgp, sum(lot_size_execute) as lse from executed_orders  where cd_code=:cd and :fromDate <= order_date and order_date <= :toDate and side="B"');
         $pr->bindParam(':cd',$cd_code);
         $pr->bindParam(':fromDate',$fromDate);
         $pr->bindParam(':toDate',$toDate);
         $pr ->execute();
         $row= $pr->fetch();
         $p=$row['avgp'];
         $v=$row['lse'];
         $totalb=$p*$v;
         return $totalb;
    }
    function broker_user_name($username){
      include ('./CONNECTIONS/db.php'); 
         $pr = $dbh->prepare('SELECT * from linkuser where username=:un');
         $pr->bindParam(':un',$username);
         $pr ->execute();
         $row= $pr->fetch();
         $un=$row['broker_user_name'];
         return $un;
    }

    function order_audit($cdcode,$p_code,$u_name,$vol,$vol1,$sy_id,$price,$side,$commis_amt,$flag_id,$u_name1){
        include ('./CONNECTIONS/db.php'); 
        $b_order = $dbh->prepare("INSERT into orders_audit(cd_code,participant_code,order_entry,buy_vol,order_size,symbol_id,price,side,commis_amt,flag_id,member_broker) 
        VALUES ('$cdcode','$p_code','$u_name','$vol','$vol1','$sy_id','$price','$side','$commis_amt','$flag_id','$u_name1')");
         $b_order ->execute();
    }
?>                