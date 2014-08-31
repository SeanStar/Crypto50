$(function(){
  function updateChart(coin){ 
	$.ajax({
		url: 'api/graph.php?coin='+coin,
		type: 'get',
		dataType: 'json',
		success: function (data) {
			var options = {
				lines: {
					show: true
				},
				points: {
					show: true
				},
				xaxis: {
					tickDecimals: 0,
					tickSize: 1,
					tickLength: 0, // Hide gridlines
					ticks: false
				},
				yaxes: [
				{
					autoscaleMargin: 0.04,
					axisLabel: "Bet Amount in "+coin,
					axisLabelUseCanvas: true,
					axisLabelFontSizePixels: 12,
					axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
					axisLabelPadding: 5
				},
				{
					position: 0,
					autoscaleMargin: 0.04,
					axisLabel: "Balance in "+coin,
					axisLabelUseCanvas: true,
					axisLabelFontSizePixels: 12,
					axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
					axisLabelPadding: 5
				}
				],
				grid: {
					hoverable: true,
					borderWidth: 1
				}
			};
			
			var chartdata = [
			{
				label: "Balance",
				data: data["bet_newbal"],
				lines: {
					show: true,
					fill: false
				},
				points: {
					show: true,
					fillColor: '#478514'
				},
				color: "#478514",
				yaxis: 2
			},
			{
				label: "Bet Amount",
				data: data["bet_amount"],
				lines: {
					show: true,
					fill: false
				},
				points: {
					show: true,
					fillColor: '#AA4643'
				},
				color: '#AA4643'
			}/*,
			{
				label: "Bet Profit",
				data: data["bet_profit"],
				lines: {
					show: true,
					fill: false
				},
				points: {
					show: true,
					fillColor: '#4572A7'
				},
				color: '#4572A7'
			}*/
		];

		$.plot("#placeholder", chartdata, options);
		
		},
	});

    function showTooltip(x, y, contents, z) {
        $('<div id="flot-tooltip">' + contents + '</div>').css({
            position: 'absolute',
            display: 'none',
            top: y - 30,
            left: x + 30,
            border: '2px solid',
            padding: '2px',
            'background-color': '#FFF',
            opacity: 0.80,
            'border-color': z,
            '-moz-border-radius': '5px',
            '-webkit-border-radius': '5px',
            '-khtml-border-radius': '5px',
            'border-radius': '5px'
        }).appendTo("body").fadeIn(200);
    }

    var previousPoint = null;
    var previousPointLabel = null;

    $("#placeholder").bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousPoint != item.dataIndex) || (previousLabel != item.series.label)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
     
                $("#flot-tooltip").remove();
     
                var x = item.datapoint[0],
                y = item.datapoint[1],
                z = item.series.color;
     
                showTooltip(item.pageX, item.pageY, y, z);
            }
        } else {
            $("#flot-tooltip").remove();
            previousPoint = null;
        }
    });
  }
  function updateStats(coin) {	
	$.ajax({
	url: 'api/statistics.php?coin='+coin,
	type: 'get',
	dataType: 'json',
	success: function (data) {
		var $table = $('#statistics'),
		$tableod = 0,
		rowstring = '';
		$table.stop(true);
		$tableod = $table.clone();
		rowstring += '<tr>' +
		'<td>' + data.bet_total + '</td>' +
		'<td>' + data.bets_won + '</td>' +
		'<td>' + data.bets_lost + '</td>' +
		'<td>' + data.winlose + '</td>' +
		'<td>' + data.wagered + ' '+ coin + ' <img src="img/icons/16/'+ coin.toLowerCase() +'.png" alt="'+ coin +'"></td>' +
		'<td>' + data.profit + ' '+ coin + ' <img src="img/icons/16/'+ coin.toLowerCase() +'.png" alt="'+ coin +'"></td>' +
		'</tr>';
		$tableod.prepend(rowstring);
        $tableod.find('tr:gt(1)').addClass('removing');
        $table.replaceWith($tableod);
        $tableod.find('tr.removing').remove();
	},
	});
  }
  var Action = {
    activeClass: 'active',
    init: function() {
      var that = this;
      $('.cur a').on('click', function(e) {
        var $par = $(this).parent();
        $par.addClass(that.activeClass)
            .siblings().removeClass(that.activeClass);
		updateChart($('.cur.active a div span').text());
		updateStats($('.cur.active a div span').text());
      });
    }
  }
  updateChart('BTC');
  updateStats('BTC');
  Action.init();
});