<?php
include ('../../CONNECTIONS/db.php');
session_start();
if(!empty($_GET["op"]) && $_GET["op"]=="terminal_report") 
{
  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");

  $cidNo=$_GET['cidNo'];
  $cdCode=$_GET['cdCode'];
  $pCode=$_GET['pCode'];

  $wc= $dbh->prepare("SELECT c.cd_code, u.name, u.cid, u.address, u.phone, u.participant_code, c.user_name, c.title, a.name pCode, u.email, DATE(u.created_at) cDate, DAY(u.created_at) cDay, MONTHNAME(u.created_at) cMonth, YEAR(u.created_at) cYear
    FROM users u 
    LEFT JOIN adm_participants a ON u.participant_code=a.participant_code
    LEFT JOIN client_account c ON u.cid = c.ID
    WHERE u.cid=:cid AND u.participant_code=:pCode AND c.user_name LIKE '{$pCode}%'");
  $wc->bindParam(':cid',$cidNo);
  $wc->bindParam(':pCode',$pCode);
  $wc->execute();
  $state = $wc->fetch();
  echo'
  <html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Online Terminal Form</title>
  </head>
  <body onload="window.print();">
    <div class="wrapper">
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="">
              <div class="col-xs-2">
                <img src="../../dist/img/Logo.png">
              </div>
              <div class="col-xs-10">
                <center><b style="font-size: 25px;">༄༄།། རྒྱལ་གཞུང་གན་ལེན་བདོག་གཏད་བརྗེ་སོར་ཁང་།</b></center><br>
                <center><b style="font-size: 25px; float: left;">ROYAL SECURITIES EXCHANGE OF BHUTAN LIMITED</b></center><br><br>
              </div>
            </div>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-xs-12">
            <center><div class="lead" style=""><b>Application for Online Trading(Internet Trading Terminal) to be submitted along with application fee of Nu.500/-</b></div>
            </center>
          </div>
          <div class="col-xs-12">
            <span style="float:right;"><b>DATE[Y-M-D]:</b> '.$state['cDate'].'</span>
            <b>Chief Executive Officer,<br>
            Royal Securities Exchange of Bhutan Ltd,<br>
            Thimphu, Bhutan<br><br></b>

            Sir,<br><br>

            I wish to apply for Online Trading Terminal (Internet Trading) through <b>'.$pCode.'</b> Securities Limited to transact on my own behalf which my details are filled in as follow:-<br><br>
            
            FULL NAME: <b>'.$state['name'].'</b><br><br>
            CITIZEN IDENTITY CARD NO: <b>'.$state['cid'].'</b>&emsp; &emsp; &emsp;
            CD CODE: <b>'.$state['cd_code'].'</b>&emsp; &emsp; &emsp;
            PARTICIPATE CODE: <b>'.$state['participant_code'].'</b><br><br>

            MOBILE NO: <b>'.$state['phone'].'</b>&emsp; &emsp; &emsp;
            Email Address: <b>'.$state['email'].'</b><br><br>

            CURRENT ADDRESS: <b>'.$state['address'].'</b><br><br><br><br>

            <center><b>Declaration</b></center>
            <center>I declare that, the information stated above are true to the best of my knowledge and belief.</center><br><br><br><br>

            <img src="../../dist/img/stamp.jpg" style="float:right; height: 150px;"><br><br><br><br><br><br><br><br>
            <p style="float:right;">Name and Signature of the applicant</p>

            <p style="float:left;">Recommendation by the Brokerage Firm/ Signature</p><br><br><br><br><br><br>



            <center><b>AGREEMENT BETWEEN THE ROYAL SECURITIES EXCHANGE OF BHUTAN (RSEB) AND THE CLIENT SEEKING TO USE ONLINE TRADING TERMINAL WITH THE RSEB</b></center><br>

            <p align="justify">This Agreement is drawn on this <b>'.$state['cDay'].'</b> day of <b>'.$state['cMonth'].'</b> between the <b>ROYAL SECURITIES EXCHANGE OF BHUTAN LIMITED (RSEB)</b> situated at Thimphu, Bhutan hereinafter called the <b>“RSEB”</b> of the One part; AND <b>'.$state['name'].'</b> situated at <b>'.$state['address'].'</b> hereinafter called <b>“the Client”</b> of the <b>Other Part</b>.<p>

            <center><b>Witnesseth</b></center><br>
            <b>WHEREAS</b> the Client has furnished to the RSEB the duly filled-in application in the specified form requesting the RSEB for online trading terminal.<br><br>

            <b>NOW THEREFORE</b> in consideration of the RSEB having agreed to provide online trading terminal to the client, the parties hereto do hereby agree and covenant with each other as follows:<br><br>

            <b>1.&nbsp;&nbsp; General Clauses</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp; 1.1 Words and expressions used but not defined in this Agreement but defined under, The Companies Act 2000, The Financial Services Act 2011, The Exchange ATS Rule shall have the meaning assigned to them under the aforesaid Acts, Regulations or Rules as the case may be. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;1.2 The parties hereto shall be bound by the Companies Act 2000, The Financial Services Act 2011, The Exchange ATS Rule and agree to abide by the Rules and Operating Instructions issued from time to time by the RSEB in the same manner and to the same extent as if the same were set out herein and formed part of this Agreement. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;1.3 The Client shall continue to be bound by the Rules and Operating Instructions / User Manual of RSEB even after ceasing to be a Client in so far as may be necessary for completion of or compliance with its obligations in respect of all matters, entries or transactions which the Client may have carried out, executed, entered into, undertaken or may have been required to do, before ceasing to be the Client and which may have remained outstanding, incomplete or pending at the time of its ceasing to be a Client.<br><br>

            <b>2. &nbsp;&nbsp Fees and Charges</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;2.1 &nbsp;&nbsp;The Client shall pay such fees and charges to the RSEB, as may be mutually agreed upon, for availing online trading terminal for rendering such other services as are incidental or consequential to the Client.

            &nbsp;&nbsp;&nbsp;&nbsp;2.2 &nbsp;&nbsp;The RSEB shall be entitled to change or revise the fees and charges from time to time provided however that no increase therein shall be effected by the RSEB unless the RSEB shall have given at least one months notice in writing to the Client in that behalf.<br> 

            &nbsp;&nbsp;&nbsp;&nbsp;2.3 &nbsp;&nbsp;The Client further agrees that in the event of default in the payment of any of the fees or charges to the RSEB on their respective due dates or within one month of the same being demanded then, without prejudice to the right of the RSEB to terminate the Agreement and close the Online Trading Terminal of the Client, the RSEB shall be entitled to charge interest on the amount remaining outstanding or unpaid at the highest prevailing Bank Rate.<br><br>

            <b>3. &nbsp;&nbsp Responsibilities</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.1&nbsp;&nbsp;  The RSEB shall ensure that satisfactory arrangements are in place to ensure confidentiality of information in such a way that information is only accessible to an authorized person.<br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.2&nbsp;&nbsp;  The client shall safeguard the integrity of the service including the control to prevent: <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)  Non-compliance with laws, Rules, Regulations and Guidelines issued by the RSEB, leading to illegal transactions, fraud or malpractice,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii) Presentation of incorrect data, whether unintentionally or malevolently, <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iii)  False presentation or the use of incomplete information for transactions,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iv) Manipulation of data,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;v)  Viruses, leading to inter alia loss of data, unauthorized access to or manipulation of data, unavailability or threat of unavailability of system, <br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.3  &nbsp;&nbsp;Ensure the availability of the service in the event that:<br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)  In the event of any failure in the Online Trading Services arising through the failure of internet or the Online System the client shall route their orders through their respective brokers.<br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.4  &nbsp;&nbsp;The Client shall be held responsible for any kind of transaction arising due to his /her negligence such as loss of password, unauthorized transactions or any kind of fraud or malpractice. <br><br>

            <b>4.  &nbsp;&nbsp;Redressal of Grievances </b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;The RSEB shall promptly attend to all grievances / complaints of the Client and shall resolve all such grievances / complaints as it relate to matters exclusively within the domain of the RSEB and shall endeavor to resolve the same at the earliest.<br><br>

            <b>5.  &nbsp;&nbsp;Termination</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.1  &nbsp;&nbsp;The RSEB shall be entitled to terminate this agreement in the event of the Client: <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i.  Failing to pay the fees or charges as may be mutually agreed upon within a period of one month from the date of demand made in that behalf; <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii. commits or participates in any fraud or other act of moral turpitude in his / its dealings with the RSEB; <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iii.  otherwise misconducts himself in any manner. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.2  &nbsp;&nbsp;The RSEB may also terminate the Agreement without assigning any reasons for such termination provided the RSEB shall have issued at least one months prior notice in writing to the Client in that behalf. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.3  &nbsp;&nbsp;The Client may at any time terminate the Agreement by calling upon the RSEB to close his / her Online Trading terminal with the RSEB provided no instructions remain pending or unexecuted and no fees or charges remain payable by the Client to the RSEB. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.4 &nbsp;&nbsp; Notwithstanding termination of the Agreement by the RSEB or closure of his / its Online Trading Terminal by the Client, the provisions of the Agreement and all mutual rights and obligations arising therefrom shall, except in so far as the same are contrary to or inconsistent with such termination or closure, shall continue to be binding on the parties in respect of all acts, deeds, matters and things done and transactions effected during the period when the Agreement was effective.<br><br>

            <b>6.  Authorized Representative </b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;Where the Client is a body corporate, it shall, simultaneously with the execution of the Agreement furnish to the RSEB, a list of officials authorized by it, who shall represent and interact on its behalf with the RSEB. Any change in such list including additions, deletions or alterations thereto shall be forthwith communicated to the RSEB.<br><br>

            <b>7.  Service of Notice</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.1  &nbsp;&nbsp;Any notice or communication required to be given under the Agreement shall not be binding unless the same is in writing and shall have been served by delivering the same at the address set out hereinabove against a written acknowledgement of receipt thereof or by sending the same by pre-paid registered post at the aforesaid address or transmitting the same by facsimile transmission, electronic mail or electronic data transfer at number or address that shall have been previously specified by the party to be notified. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.2  &nbsp;&nbsp;Notice given by personal delivery shall be deemed to be given at the time of delivery. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.3  &nbsp;&nbsp;Notice sent by post in accordance with this clause shall be deemed to be given at the commencement of business of the recipient of the notice on the third working day next following its posting. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.4  &nbsp;&nbsp;Notice sent by facsimile transmission, electronic mail or electronic data transfer shall be deemed to be given at the time of its actual transmission.<br><br>


            <b>8.  Governing Law</b><br> 

            &nbsp;&nbsp;&nbsp;&nbsp;The Agreement shall be governed by and construed in accordance with the laws in force in Kingdom of Bhutan.<br> <br> 

            <b>9.  Interpretation</b> <br>

            &nbsp;&nbsp;&nbsp;&nbsp;Unless the context otherwise requires, words denoting the singular shall include the plural and vice versa and words denoting the masculine gender shall include the feminine and vice versa and any reference to any stature, enactment or legislation or any provision thereof shall include any amendment thereto or any reenactment thereof.<br> <br> 

            <b>10. Jurisdiction</b> <br>

            &nbsp;&nbsp;&nbsp;&nbsp;The parties hereto agree to submit to the exclusive jurisdiction of the Royal Court of Justice, Kingdom of Bhutan.<br> <br> 

            <b>11. Execution of Agreement</b> <br>

            &nbsp;&nbsp;&nbsp;&nbsp;This Agreement is executed in duplicate and a copy each shall be retained by each of the parties hereto.<br><br>

            <b>IN WITNESS WHEREOF</b> the parties hereto have hereunto set and subscribed their respective hands/seals to this Agreement in duplicate on the day <b>'.$state['cDay'].'</b>, month <b>'.$state['cMonth'].'</b>, year <b>'.$state['cYear'].'</b>.<br>

            <br><br><br><br><br><br>
          </div>
          <div class="col-xs-12">
            <div class="col-xs-10"><br>
              SIGNED AND DELIVERED<br>
              By the within named RSEB<br>
              by the hand of its authorized representative: <b>IT Department</b><br>
              in the presence of: <b>RSEB</b><br>
              Name & Address of witness:<br>
            </div>
            <div class="col-xs-2">
              <img src="../../dist/img/stamp.jpg" style="float:right; height: 150px;">
            </div>
            <div class="col-xs-10"><br>
              SIGNED AND DELIVERED<br>
              By the within named CLIENT<br>
              by the hand of its authorized representative: <b>'.$state['name'].'</b><br>
              in the presence of: <b>'.$pCode.'</b><br>
              Name & Address of witness:<br>
            </div>
            <div class="col-xs-2">
              <img src="../../dist/img/stamp.jpg" style="float:right; height: 150px;">
            </div>

            <div class="col-xs-12 text-center">
              <br><br><br><br><br><br><br><br>
              <!-- _________________________________________________________________________________<br>
                This is a computer generated report and required no signatory. <br>
              _________________________________________________________________________________<br>-->
            </div>
          </div>
        </div>
      </section>    
    </div>
  </body>
</html>';
}
else{}

?>
<link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../../plugins/datatables/dataTables.bootstrap.css">
  <link rel="stylesheet" href="../../dist/css/skins/_all-skins.min.css">

  <!-- iCheck -->
  <link rel="stylesheet" href="../../plugins/iCheck/flat/blue.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="../../plugins/morris/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="../../plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="../../plugins/datepicker/datepicker3.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="../../modal/jquery.min.js">
  <script src="../../plugins/input-mask/jquery.inputmask.js"></script>
<script src="../../plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="../../plugins/input-mask/jquery.inputmask.extensions.js"></script>
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <script src="../../plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="../../bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables/dataTables.bootstrap.min.js"></script>
<script src="../../plugins/datepicker/bootstrap-datepicker.js"></script>
<!-- SlimScroll -->
<script src="../../plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="../../plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<script src="../../dist/js/angular.min.js"></script>

<!-- page script -->
<script>
  $(function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>
<!-- Page script -->
<script src="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<link rel="stylesheet" href="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="https://cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>


