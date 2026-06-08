<?php
	<html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Account Summary Details</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table  table-striped">
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                    <tr>
                      <th>Sl#</th>
                      <th>CD Code/Symbol</th>
                      <th style="text-align:right;">Volume</th>
                      <th style="text-align:right;">Block Vol</th>
                      <th style="text-align:right;">Pledged Vol</th>
                      <th style="text-align:right;">PIV</th>
                      <th style="text-align:right;">POV</th>
                      <th style="text-align:right;">Total</th>
                    </tr>
                  </thead>
                  <tbody>';
                  <tr style="font-size: 70%;">
                     <td>'.$i.'</td>
                     <td>'.$get['cd_code'].'-'.$get['symbol'].'</td>
                     <td style="text-align:right;">'.$vol.'</td>
                     <td style="text-align:right;">'.$bv.'</td>
                     <td style="text-align:right;">'.$pv.'</td>
                     <td style="text-align:right;">'.$piv.'</td>
                     <td style="text-align:right;">'.$pov.'</td>
                     <td style="text-align:right;">'.number_format($get['total_volume'],0,".",",").'</td>
                  </tr>';
            </tbody>
          </table>
	      </div>
	  </div>
          <br><br><br>
          _________________________________________________________________________________
          &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;This is a computer generated report and requires no signatory.
          _________________________________________________________________________________
        </section>    
      </div>
    </body>
  </html>

?>