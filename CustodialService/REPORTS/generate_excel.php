<?php
include ('../../CONNECTIONS/db.php');
date_default_timezone_set("Asia/Thimphu");
if(isset($_POST['gen_ent'])) 
{
    $corp_announcement_id=$_POST['gen_ent'];
    $announcement_type=$_POST['atype'];
    //rights
   if($announcement_type==1)
   {
		$rec1=$dbh->prepare("SELECT a.ribon_volume,a.volume,b.cd_code,b.f_name,b.l_name,b.ID,b.address,b.tpn,b.phone,b.bank_account,c.symbol,c.face_value,an.rate,ban.bank_name
		from spot_date_holding a,custodial_account b,symbol c, corporate_announcement an,banks ban 
		where a.corp_announcement_id=:gid
		and a.client_id=b.client_id 
		and a.symbol_id=c.symbol_id
		and a.corp_announcement_id=an.corp_announcement_id
		and b.bank_id=ban.bank_id");  
		 $rec1->bindParam(':gid',$corp_announcement_id);
		$rec1->execute();
		$columnHeader = '';  
		$i=1;
		$columnHeader = "SNO" . "\t" . "CD CODE" . "\t". "NAME" . "\t" ."CID" . "\t" . "ADDRESS" ."\t". "TPN" . "\t". "PHONE" . "\t". "BANK ACCOUNT" . "\t". "BANK NAME" . "\t". "VOLUME" . "\t". "RATE" . "\t". "RIGHTS VOLUME" . "\t" ; 
		$setData = '';  
		while ($rec=$rec1->fetch()) { 
	        //$total=($rec['volume']*$rec['rate'])/100; 
	        $rowData = '';   
	        $value = $i++ . "\t ". $rec['cd_code'] . "\t". $rec['f_name']." ".$rec['l_name']. "\t" .$rec['ID'] . "\t". $rec['address'] . "\t". $rec['tpn'] . "\t". $rec['phone'] . "\t". trim($rec['bank_account'])." -" . "\t". $rec['bank_name'] . "\t". $rec['volume'] . "\t". $rec['rate'] . "\t". $rec['ribon_volume'] . "\t";  
	        $rowData .= $value;  
	        $setData .= trim($rowData) . "\n";   
		}  
		header("Content-type: application/octet-stream");  
		header("Content-Disposition: attachment; filename=Entitlement_List_Rights.xls");  
		header("Pragma: no-cache");  
		header("Expires: 0");  
		echo ucwords($columnHeader) . "\n" . $setData . "\n";
	}
	 //dividend
   else if($announcement_type==3)
   {                                                                                                                              
		$rec1=$dbh->prepare("SELECT a.volume,b.cd_code,b.title,b.f_name,b.l_name,b.ID,b.address,b.tpn,b.phone,b.bank_account,c.symbol,c.face_value,an.rate,ban.bank_name
		from spot_date_holding a,custodial_account b,symbol c, corporate_announcement an,banks ban 
		where a.corp_announcement_id=:gid
		and a.client_id=b.client_id 
		and a.symbol_id=c.symbol_id
		and a.corp_announcement_id=an.corp_announcement_id
		and b.bank_id=ban.bank_id");  
		$rec1->bindParam(':gid',$corp_announcement_id);
		$rec1->execute();
		$columnHeader = '';  
		$i=1;
		$columnHeader = "SNO" . "\t" . "CD CODE" . "\t". "NAME" . "\t". "CID" . "\t" . "ADDRESS" . "\t" . "TPN" . "\t". "PHONE" . "\t". "BANK ACCOUNT" . "\t". "BANK NAME" . "\t". "VOLUME" . "\t". "FACE VALUE" . "\t". "RATE" . "\t". "AMOUNT" . "\t" ; 
		$setData = '';  
		while ($rec=$rec1->fetch()) {
				$bacc = ($rec['bank_account']); 
				if($rec['bank_account'] == "")
				{
					$bankName = "";
				}
				else
				{
					$bankName = $rec['bank_name'];
				}
			    $total=($rec['face_value']*$rec['volume']*$rec['rate'])/100;
		        $rowData = '';   
		        $value = $i++ . "\t ". $rec['cd_code'] . "\t". trim($rec['title']) ." ". $rec['f_name'] ." ". $rec['l_name']. "\t". $rec['ID'] . "\t". $rec['address'] ."\t". $rec['tpn'] . "\t". $rec['phone'] . "\t". trim($bacc)." -" ."\t" .$bankName . "\t". $rec['volume'] . "\t". $rec['face_value'] . "\t". $rec['rate'] . "\t". $total . "\t";  
		        $rowData .= $value;  
		        $setData .= trim($rowData) . "\n";   
		}  
		header("Content-type: application/octet-stream");  
		header("Content-Disposition: attachment; filename=Entitlement_List_Dividend.xls");  
		header("Pragma: no-cache");  
		header("Expires: 0");  
		echo ucwords($columnHeader) . "\n" . $setData . "\n";
	}
	else if($announcement_type==2)
   {                                                                                                                              
		$rec1=$dbh->prepare("SELECT a.ribon_volume,a.volume,b.cd_code,b.f_name,b.l_name,b.ID,b.address,b.tpn,b.phone,b.bank_account,c.symbol,c.face_value,an.rate,ban.bank_name
		from spot_date_holding a,custodial_account b,symbol c, corporate_announcement an,banks ban 
		where a.corp_announcement_id=:gid
		and a.client_id=b.client_id 
		and a.symbol_id=c.symbol_id
		and a.corp_announcement_id=an.corp_announcement_id
		and b.bank_id=ban.bank_id");  
		$rec1->bindParam(':gid',$corp_announcement_id);
		$rec1->execute();
		$columnHeader = '';  
		$i=1;
		$columnHeader = "SNO" . "\t" . "CD CODE" . "\t". "NAME" . "\t". "CID" . "\t" . "ADDRESS" . "\t". "TPN"  . "\t". "BANK NAME" . "\t". "INITIAL VOLUME" . "\t". "BONUS VOLUME" . "\t". "TOTAL VOLUME" . "\t" . "PHONE" . "\t". "BANK ACCOUNT" . "\t" ; 
		$setData = '';  
		while ($rec=$rec1->fetch()) {  
			    $total=$rec['volume'];
			    $ribntotal=$rec['ribon_volume'];
			    $tot = $total+$ribntotal;
		        $rowData = '';   
		        $value = $i++ . "\t ". $rec['cd_code'] . "\t". $rec['f_name'] ." ". $rec['l_name'] . "\t". $rec['ID'] . "\t". $rec['address'] . "\t". $rec['tpn'] . "\t". $rec['bank_name']."\t". $rec['ribon_volume'] . "\t". $total . "\t". $tot . "\t". $rec['phone'] . "\t". trim($rec['bank_account']." -")."\t" ;  
		        $rowData .= $value;  
		        $setData .= trim($rowData) . "\n";   
		}  
		header("Content-type: application/octet-stream");  
		header("Content-Disposition: attachment; filename=Entitlement_List_Bonus.xls");  
		header("Pragma: no-cache");  
		header("Expires: 0");  
		echo ucwords($columnHeader) . "\n" . $setData . "\n";
	}
}
elseif(!empty($_GET['ge_export'])) 
{       
    $replace   = array("\n","\r\n","\r");
    $search  = array('','','');
    $symbol_id=$_GET['symbol_id'];
    //echo $symbol_id;
    $wc= $dbh->prepare("SELECT c.cd_code, a.title, a.f_name, a.l_name, a.tpn, a.phone, a.ID, a.address, a.bank_account, 
    	ban.bank_name, c.volume+c.pledge_volume+c.block_volume+c.pending_out_vol AS total 
    	FROM custodial_cds c, custodial_account a,banks ban 
    	WHERE c.cd_code=a.cd_code 
	    AND c.symbol_id=:sid
	    -- AND a.bank_id=ban.bank_id 
	    ORDER BY a.bank_account ASC");
    $wc->bindParam(':sid',$symbol_id);
    $wc->execute(); 
	$columnHeader = '';  
	$i=1;
	$columnHeader = "SNO" . "\t" . "CD CODE" . "\t". "NAME" . "\t". "TPN" . "\t". "PHONE" . "\t". "ADDRESS" . "\t". "ID" . "\t". "VOLUME" . "\t". "BANK name" . "\t" . "BANK Account" . "\t"; 
	$setData = '';  
	while ($rec=$wc->fetch()) { 
       if($wc->rowCount() <= 0) 
       {}
   	   if($rec['bank_account'] == "")
   	   {
   	   	$bankName = "";
   	   }
   	   else
   	   {
   	   	$bankName = $rec['bank_name'];
   	   }
        $rowData = '';  
        $value = $i++ . "\t ". str_replace($search,$replace,$rec['cd_code']). "\t" .str_replace($search,$replace,trim($rec['title'])." ".$rec['f_name'].' '.$rec['l_name'])
         . "\t". str_replace($search,$replace,$rec['tpn']) . "\t". str_replace($search,$replace,$rec['phone']) . 
         "\t". str_replace($search,$replace,$rec['address']) . "\t". str_replace($search,$replace,$rec['ID'])
          . "\t". str_replace($search,$replace,$rec['total']) . "\t". str_replace($search,$replace,$bankName) . "\t" . str_replace($search,$replace,trim($rec['bank_account']). " -") . "\t";  
        $rowData .= $value;  
        $setData .= trim($rowData) . "\n";     
	}  
	header("Content-type: application/octet-stream");  
	header("Content-Disposition: attachment; filename=General_Shareholders_List.xls");  
	header("Pragma: no-cache");  
	header("Expires: 0");  
	echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
?>
