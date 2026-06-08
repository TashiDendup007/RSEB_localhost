<!doctype html>
<?php
include 'CONNECTIONS/db.php';
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body style="background-color:grey;  width: 1020px; height:460px;">
    <div class=" container">
        <div class="row ">
          <!-- <img src="https://rsebl.org.bt/online/assets/img/companies/BBPL.png"  alt="Avatar" class="avatar"> -->
          <?php
          $dateselect=date("Y-m-d");
          $data=$dbh->prepare('SELECT s.symbol_id,s.symbol,SUBSTRING(mp.date,1,10) as date,mp.market_price,mp.market_price-mp.ex_market_price as diff from
                          market_price mp
                          left join symbol s on mp.symbol_id=s.symbol_id
                          where s.security_type="OS"
                          and s.status=1 and s.trsstatus=1 order by symbol asc');
          $data->execute();
          $i=1;
           foreach ($data as $value){
             if($value['diff'] > 0)
             {
               //$bg='3CFC0D';
               $cl='3CFC0D';
               $icon= '<svg class="bi bi-caret-up-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 00.753-1.659l-4.796-5.48a1 1 0 00-1.506 0z"/>
          </svg>';
             }
             elseif($value['diff']==0)
             {
               $icon='';
               $cl='fff';
             }
             elseif($value['diff'] < 0)
             {
               //$bg='F11E0D';
               $cl='F11E0D';
               $icon= '<svg class="bi bi-caret-down-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 01.753 1.659l-4.796 5.48a1 1 0 01-1.506 0z"/>
          </svg>';
             }
             else
             {

             }
             $date=latestTrade($value['symbol_id']);
             if($date==date("Y-m-d")){
               $bg_r='white';
               $f_c='black';
             }
             else{
               $bg_r='';

             }

              echo
              '
              <div class="col-lg-2 text-center p-1">
                    <div class="card text-white bg-dark">
                    <div class="card-body" >
                      <h5 class="card-title"  style="font-size:32px;">'.$value['symbol'].'</h5>
                      <span  >
                      <h6 class="card-subtitle mb-0" style="color:#'.$cl.'; font-size:38px;">'.$value['market_price'].'</h6>
                      <p class="card-text" style="color:#'.$cl.'; font-size:30px;">'.$icon.$value['diff'].'</p>
                      </span>
                    </div>
                  </div>
              </div>

              ';
           }
           function volumeTraded($sym_id,$date){
            global $dbh;
            $getvolumetraded=$dbh->prepare('SELECT sum(w.lot_size_execute) as sum from executed_orders w
            where  w.order_date
            like "%'.$date.'%" and w.side="S" and symbol_id=:sym_id');
            $getvolumetraded->bindParam(':sym_id', $sym_id);
            $getvolumetraded->execute();
            $sum=$getvolumetraded->fetch();
            if($sum > 0)
              {
              return $sum['sum'];
              }
              else
              {
              $data='No Trade';
              return $data;
              }
           }
           function valueTraded($sym_id,$date){
            global $dbh;
            $getvolumetraded=$dbh->prepare('SELECT sum(w.lot_size_execute) as sumofvol, avg(w.order_exe_price) as sumofprice from executed_orders w
            where order_date and w.order_date
            like "%'.$date.'%" and w.side="S" and symbol_id=:sym_id');
            $getvolumetraded->bindParam(':sym_id', $sym_id);
            $getvolumetraded->execute();
            $sum=$getvolumetraded->fetch();
            $vol=$sum['sumofvol'];
            $price=$sum['sumofprice'];
            $totalvalue=$vol*$price;
            return $totalvalue;
           }
           function latestTrade($sym_id){
            global $dbh;
            $specifieddate = $dbh->prepare('SELECT SUBSTRING(max(e.order_date),1,10) dat from executed_orders e where e.side="S" and e.symbol_id=:sym_id');
            $specifieddate->bindParam(':sym_id', $sym_id);
            $specifieddate->execute();
            $spdate = $specifieddate->fetch();
            $conditiondate = $spdate['dat'];
            return $conditiondate;
          }
          ?>
          <?php


        ?>
      </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script type="text/javascript">
    $(document).ready(function() {
  setInterval(function() {
    location.reload();
  }, 300000);
});


          function cache_clear() {
            window.location.reload(true);
            // window.location.reload(); use this if you do not remove cache
          }

          $('.carousel').carousel({
  interval: 2000
})
    </script>
  </body>
</html>
