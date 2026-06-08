<footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.0
    </div>
    <strong>Copyright &copy; 2025 <a href="http://www.rsebl.org.bt">RSEB</a>.</strong> All rights
    reserved.
  </footer>
<div class="control-sidebar-bg"></div>

<?php include"../../GifLoader/gif.php" ?>

<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/data.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/modules/export-data.js"></script>
<script src="https://code.highcharts.com/stock/modules/accessibility.js"></script>


<script type="text/javascript">
    function getStateb() {
        var val = "BUY";
        $.ajax({
          type: "POST",
          url: "load.php",
          data:'BUY='+val,
          success: function(data){
            $("#myModal").html(data);
          }
        });
    }

    function getStates() {
        var val = "SELL";
        $.ajax({
          type: "POST",
          url: "load.php",
          data:'SELL='+val,
          success: function(data){
            $("#myModal").html(data);
          }
        });
    }
</script>

<script type="text/javascript">
document.addEventListener('contextmenu', event => event.preventDefault());

	document.onkeydown = function(e) {
    if(event.keyCode == 123) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
        return false;
    }
}
</script>