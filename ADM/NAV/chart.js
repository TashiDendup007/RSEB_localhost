$(document).ready(function() {

  var chart_data_volume;
  var active_user;
  var daily_trade = [];

  $.ajax({
    type: "POST",
    url: "../PROCESS/get_dtls_onLoad.php",
    data:'get_dtls_charts=get_dtls_charts',
    dataType: 'json',
    success: function(response) {

      chart_data_volume = response.chart_data;
      active_user = response.active_user;
      daily_trade = response.daily_trade;

      // yearly traded symbol through broker or online
      var area = new Morris.Area({
        element: 'sales-chart',
        resize: true,
        data: chart_data_volume,
        xkey: 'year',
        ykeys: ['broker', 'online'],
        labels: ['Broker', 'Online'],
        lineColors: ['#a0d0e0', '#3c8dbc'],
        hideHover: 'auto'
      }); 
      //chart for traded through online
      var line = new Morris.Line({
        element: 'line-chart',
        resize: true,
        data: active_user,
        xkey: 'year',
        ykeys: ['user'],
        labels: ['Active User'],
        lineColors: ['#efefef'],
        lineWidth: 2,
        hideHover: 'auto',
        gridTextColor: "#fff",
        gridStrokeWidth: 0.4,
        pointSize: 4,
        pointStrokeColors: ["#efefef"],
        gridLineColor: "#efefef",
        gridTextFamily: "Open Sans",
        gridTextSize: 10
      });

      // chart for daily trade
      /*var line = new Morris.Line({
        element: 'daily_chart',
        resize: true,
        data: daily_trade,
        xkey: 'trade_date',
        ykeys: ['symbol', 'volume'],
        labels: ['Symbol', 'Volume'],
        lineColors: ['#efefef','#819C79', '#fc8710', '#FF6541', '#A4ADD3', '#766B56','#819C79', '#fc8710', '#FF6541', '#A4ADD3', '#766B56', '#efefef',
                     '#819C79', '#fc8710', '#FF6541', '#A4ADD3', '#766B56','#819C79', '#fc8710', '#FF6541', '#A4ADD3', '#766B56', '#819C79', '#fc8710', 
                     '#FF6541', '#A4ADD3', '#766B56'],
        lineWidth: 2,
        hideHover: 'auto',
        gridTextColor: "#fff",
        gridStrokeWidth: 0.4,
        pointSize: 4,
        pointStrokeColors: ["#efefef"],
        gridLineColor: "#efefef",
        gridTextFamily: "Open Sans",
        gridTextSize: 10
      });*/

    }
  });

  //Make the dashboard widgets sortable Using jquery UI
  $(".connectedSortable").sortable({
    placeholder: "sort-highlight",
    connectWith: ".connectedSortable",
    handle: ".box-header, .nav-tabs",
    forcePlaceholderSize: true,
    zIndex: 999999
  });
  $(".connectedSortable .box-header, .connectedSortable .nav-tabs-custom").css("cursor", "move");


  //Fix for charts under tabs
  $('.box ul.nav a').on('shown.bs.tab', function () {
    area.redraw();
    line.redraw();
  });


  // Line chart
  /*var areaChartData = {
    labels: ["January", "February", "March", "April", "May", "June", "July"],
    datasets: [
      {
        label: "Electronics",
        fillColor: "rgba(210, 214, 222, 1)",
        strokeColor: "rgba(210, 214, 222, 1)",
        pointColor: "rgba(210, 214, 222, 1)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(220,220,220,1)",
        data: [65, 59, 80, 81, 56, 55, 40]
      },
      {
        label: "Digital Goods",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgba(60,141,188,0.8)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [28, 48, 40, 19, 86, 27, 90]
      }
    ]
  };

  var areaChartOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  var lineChartCanvas = $("#daily_trade_chart").get(0).getContext("2d");
  var lineChart = new Chart(lineChartCanvas);
  var lineChartOptions = areaChartOptions;
  lineChartOptions.datasetFill = false;
  lineChart.Line(areaChartData, lineChartOptions);*/

});
