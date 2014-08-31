jQuery(function ($) {
	"use strict";
	var roll_speed = 100,
		table_reload_interval = 1500,
		id = 0,
		timedCount, timer, lastYourBet = 0,
		lastAllBet = 0,
		lastBigBet = 0,
		first_load = true,
		prev_balance = 0,
		bet_ids = [],
		dialog = $('#dialog'),
		pfcdialog = $('#pfcdialog');
	
	dialog.dialog({
		dialogClass: 'noTitleStuff',
		width:'auto',
		height:'auto',
		resizable: false,
		modal: true,
		autoOpen: false
	});
	
	pfcdialog.dialog({
		width:'auto',
		resizable: false,
		autoOpen: false
	});
	
	dialog.dialog("widget")
		.find(".ui-dialog-titlebar").css({
			"float": "right",
			border: 0,
			padding: 0
		})
		.find(".ui-dialog-title").css({
			display: "none"
		}).end()
		.find(".ui-dialog-titlebar-close").css({
			top: 0,
			right: 0,
			margin: 0,
			"z-index": 999
		});

	function bet(bet, cur, odds, type, callback) {
		$.ajax({
			url: 'api/roll.php',
			type: 'post',
			data: {
				'amt': bet,
				'coin': cur,
				'risk': odds,
				'type': type == 0 ? 1 : 0,
			},
			dataType: 'json',
			success: function (data) {
				if (data.error) {
					alert(data.message);
					return;
				}
				pfcdialog.text(data.next_roll);
				prev_balance = data.balance;
				$($('.bal.active a').attr('href')).val(data.balance);
				$('.bal.active a div p').text(data.balance);
				$('.bal.active div').css({
					backgroundColor: data.bet_outcome == "1" ? '#9EE2A8' : '#FF9691'
				}).animate({
					backgroundColor: '#E4EEF4'
				}, 500);
				//addRows([data]);
			},
			error: function (jqXHR) {
				alert('Error:\n' + jqXHR.responseText);
			},
			complete: function (jqXHR) {
				var data;
				try {
					data = $.parseJSON(jqXHR.responseText);
				} catch (e) {
					data = false;
				}
				if (typeof callback === 'function') {
					callback(data);
				}
			}
		});
	}
	
	function dialogpop(url) {
		$.get(url,{},function(html) {
			dialog.html(html);
			dialog.dialog('open');
		});
	}
	$("#main-container").on('click', 'a.modal', function(event) {
		event.preventDefault();
		dialogpop($(this).attr('href'));
	});
	
	$('#pfc').on('click', function (event) {
		event.preventDefault();
		pfcdialog.dialog('open');
	});
	
	$('#roll').on('click', function (event) {
		event.preventDefault();
		var self = $(this);
		if (self.is('.disabled')) {
		   return false;
		}
		self.addClass('disabled');
		self.val('-----');
		setTimeout(function () {
			bet($('#bet-amount').val(), $('.bal.active a div span').text(), $('#bet-risk').val(), $('#bet-type-val').val() === 'over' ? 0 : 1, function () {
				self.removeClass('disabled');
				self.val('Roll');
			});
		}, roll_speed);
		return false;
	});
	
	$('#wtdr-submit').on('click', function (event) {
		$.ajax({
			url: 'api/withdraw.php',
			type: 'post',
			data: {
				'amount': $('#wtdr-amt').val(),
				'coin': $('.bal.active a div span').text(),
				'address': $('#wtdr-adr').val(),
				'password': $('#wtdr-pwd').val(),
			},
			dataType: 'json',
			success: function (data) {
				if (data.error) {
					alert("ERROR: "+data.message);
					return;
				} else {
					alert(data.message);
				}
				$($('.bal.active a').attr('href')).val(data.balance);
				$('.bal.active a div p').text(data.balance);
			},
			error: function (jqXHR) {
				alert('Error:\n' + jqXHR.responseText);
			},
		});
	});
	
	function update(callback) {
		var did_count = 0,
			checked = function () {
				did_count++;
				if (did_count == 2 && typeof callback == 'function') callback();
			}
		updatestatistics(checked);
		updateAllBets(checked, first_load);
		updateBalance(checked);
		loadLog(first_load);
		first_load = false;
	}
	
	function getDate(timestamp) {
		var date = new Date(timestamp * 1000);	  
		var year	= date.getFullYear();
		var month   = date.getMonth() + 1;
		var day	 = date.getDate();
		var hours   = date.getHours();
		var minutes = date.getMinutes();
		var seconds = date.getSeconds();
			 
		month   = (month < 10) ? '0' + month : month;
		day	 = (day < 10) ? '0' + day : day;
		hours   = (hours < 10) ? '0' + hours : hours;
		minutes = (minutes < 10) ? '0' + minutes : minutes;
		seconds = (seconds < 10) ? '0' + seconds: seconds;
			 
		return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;		
	}
	
	$(".timestamp").each(function () {
		var timestamp = $(this).text();
		$(this).text(getDate(timestamp));
	});
	
	function updatestatistics(checked) {
		$.ajax({
			url: 'api/statistics.php',
			type: 'get',
			dataType: 'json',
			success: function (data) {
				$('#totalbets').text(data.betstotal);
				$('#betstoday').text(data.betstoday);
			},
			complete: checked
		})
	}

   function addRows(rows) {
		var $table = $('#table-all'),
			table_selector = $table.selector,
			$tableod, $tr, top, new_top, count = 0,
			need_placeholder = false,
			rowstring = '';
		if (rows.error) {
			alert(rows.message);
			return;
		}
		$table.stop(true);
		$tableod = $table.clone();
		$.each(rows, function(i, data) {
			if (data.error) {
				alert(data.message);
				return;
			}
			if (count == 50 || (data.bet_id && $.inArray(data.bet_id, bet_ids) !== -1)) return;
			need_placeholder = !need_placeholder;
			count++;
			bet_ids.push(data.bet_id);
			var value = data.bet_profit.toString(),
				multiplier = data.bet_return.toString();
			if (value.indexOf('.') !== -1) {
				value += '00000000';
			}
			value = value.substr(0, value.indexOf('.') + 9);
			value = value.indexOf('-') != -1 ? value.substr(1) : value;
			value = (data.bet_outcome == "1" ? '+' : '-') + value;
			if (multiplier.indexOf('.') == -1) {
				multiplier += '.00000';
			} else {
				multiplier += '00000';
				multiplier = multiplier.substr(0, multiplier.indexOf('.') + 6);
			}
			rowstring += '<tr>' +
				'<td><a class="modal" href="modals/betid.php?id=' + data.bet_id + '">' + data.bet_id + '</a></td>' +
				'<td>' + data.bet_user_id + '</td>' +
				'<td class="center">' + getDate(data.bet_time) + '</td>' +
				'<td class="center">' + data.bet_amount + ' '+ data.bet_coin + ' <img src="img/icons/16/'+ data.bet_coin.toLowerCase() +'.png" alt="'+ data.bet_coin +'"></td>' +
				'<td class="center">' + (data.bet_type == "1" ? '>' : '<') + data.bet_game + '</td>' +
				'<td class="center">' + data.bet_roll + '</td>' +
				'<td class="center">x' + data.bet_return + '</td>' +
				'<td class="center" style="color: ' + (data.bet_outcome == "1" ? 'green' : 'red') + '">' + value + ' '+ data.bet_coin + ' <img src="img/icons/16/'+ data.bet_coin.toLowerCase() +'.png" alt="'+ data.bet_coin +'"></td>' +
				'</tr>';
		});
		$tableod.prepend(rowstring);
		$tableod.find('tr:gt(50)').addClass('removing');
		$table.replaceWith($tableod);
		$tableod.find('tr.removing').remove();
	}

	function updateYourBets() {
		$.ajax({
			url: 'api/rolls.php',
			data: {
				id: parseInt($('#user-id').text()),
				count: '50',
				bet_id: lastYourBet
			},
			type: 'post',
			dataType: 'json',
			success: function (data) {
				if (!data || data.length === 0) return;
				lastYourBet = data[0].bet_id;
				addRows(data);
			}
		});
	}

	function updateAllBets(callback, first) {
		if (first) {
			$.ajax({
				url: 'api/rolls.php',
				data: {
					count: '50',
					bet_id: lastAllBet
				},
				type: 'post',
				dataType: 'json',
				success: function (data) {
					if (!data || data.length === 0) return;
					lastAllBet = data[0].bet_id;
					addRows(data);
				},
				complete: callback
			});
			return;
		}
		var did_one = false,
			checker = function () {
				if (did_one && typeof callback == 'function') callback();
				did_one = true;
			};
		$.ajax({
			url: 'api/rolls.php',
			data: {
				count: '50',
				bet_id: lastAllBet
			},
			type: 'post',
			dataType: 'json',
			success: function (data) {
				if (!data || data.length === 0) return;
				lastAllBet = data[0].bet_id;
				addRows(data);
			},
			complete: checker
		});
	}

	function updateBalance(checker) {
		$.ajax({
			url: 'api/balances.php',
			dataType: 'json',
			success: function (data) {
				$('#btc').text(data.btc);
				$('#ltc').text(data.ltc);
				$('#nmc').text(data.nmc);
				$('#ftc').text(data.ftc);
				$('#btc-amt').val(data.btc);
				$('#ltc-amt').val(data.ltc);
				$('#nmc-amt').val(data.nmc);
				$('#ftc-amt').val(data.ftc);
			},
			complete: checker
		});
	}
	
	function loadLog(first){	   
	var oldscrollHeight = ($("#chatbox").scrollTop() + 250);
		if (first) {
			$.ajax({
				url: "log.html",  
				cache: false,  
				success: function(html){  
					$("#chatbox").html(html);
					$('#chatbox').scrollTop(5000);
									
				}, 
			});
			return;
		}
		$.ajax({  
			url: "log.html",  
			cache: false,  
			success: function(html){  
				$("#chatbox").html(html);
				var newscrollHeight = $("#chatbox").prop("scrollHeight"); 
				if(newscrollHeight < (oldscrollHeight + 75)){  
					var height = $('#chatbox')[0].scrollHeight;
					$('#chatbox').scrollTop(height);
				}				 
			},  
		});  
	}

	$("#submitmsg").click(function(){	 
	  var clientmsg = $("#usermsg").val();  
	  $.post("api/chat.php", {text: clientmsg});				
	  $("#usermsg").val('');  
	  return false;  
	});	
	
	$('#wallet').on("click", function() {
				$(this).select();
			});
	
	timedCount = function () {
		try {
			update(function () {
				timer = setTimeout(timedCount, table_reload_interval);
			});
		} catch (e) {
			alert(e);
		}
	}
	window.rollUpdate = function () {};
	$('#table-all').find('tbody').html('');
	//updateYourBets();
	timedCount();
	
});