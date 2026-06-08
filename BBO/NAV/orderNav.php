
  <?php if ($role == 2): ?>
    <a class="btn btn-app" data-toggle="modal" data-target="#myModal" onclick="getStateb();" style="background-color: #5DADE2;">
        <i class="fa fa-plus"></i><span>BUY</span>
    </a>
    </a>
    <a class="btn btn-app" data-toggle="modal" data-target="#myModal" onclick="getStates();" style="background-color: #E74C3C;">
        <i class="fa fa-minus"></i> SELL
    </a>
    <a class="btn btn-app"  href="../FILES/tec.php" style="background-color: #f39c12;">
        <i class="fa fa-repeat"></i> CHANGE
    </a>
    <a class="btn btn-app" href="../FILES/tex.php">
        <i class="fa fa-remove"></i> CANCEL
    </a>
  <?php endif ?>
