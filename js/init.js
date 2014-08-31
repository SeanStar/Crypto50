jQuery(function ($) {
  
  function updateWallet(wallet) {	
	$.ajax({
		url: 'api/balances.php',
		type: 'post',
		data: {
			'wallet': 1,
		},
		dataType: 'json',
		success: function (data) {
			switch(wallet)
			{
			case 'BTC':
			  $('#walletlabel').text("BTC Deposit [1 confirm]: ");
			  $('#wallet').val(data.wal_btc);
			  $('#wtdr-fee').text("0.0005 BTC");
			  $('#wtdr-amt').val(data.btc);
			  break;
			case 'LTC':
			  $('#walletlabel').text("LTC Deposit [3 confirms]: ");
			  $('#wallet').val(data.wal_ltc);
			  $('#wtdr-fee').text("0.03 LTC");
			  $('#wtdr-amt').val(data.ltc);
			  break;
			case 'NMC':
			  $('#walletlabel').text("NMC Deposit [3 confirms]: ");
			  $('#wallet').val(data.wal_nmc);
			  $('#wtdr-fee').text("0.015 NMC");
			  $('#wtdr-amt').val(data.nmc);
			  break;
			case 'FTC':
			  $('#walletlabel').text("FTC Deposit [4 confirms]: ");
			  $('#wallet').val(data.wal_ftc);
			  $('#wtdr-fee').text("0.1 FTC");
			  $('#wtdr-amt').val(data.ftc);
			  break;
			default:
			  $('#walletlabel').text("BTC Deposit [1 confirm]: ");
			  $('#wallet').val(data.wal_btc);
			  $('#wtdr-fee').text("0.0005 BTC");
			  $('#wtdr-amt').val(data.btc);
			}			
		},
	});
  }
  
  var Balances = {
	activeBalClass: 'active',
	init: function() {
	  var that = this;
	  $('.bal a').on('click', function(e) {
		var $target = $($(this).attr('href')),
			$bal = $(this).parent();
		$bal.addClass(that.activeBalClass)
			.siblings().removeClass(that.activeBalClass);
		updateWallet($('.bal.active a div span').text());
		$('.bal div').removeAttr("style");
		$('.bal.active div').removeAttr("style");
		event.preventDefault();
	  });
	}
  }

  var BetCalculator = {

	init: function() {
	  this.executeBindings();
	  this.inputs.betRisk.change();
	  this.inputs.betReturn.keyup().change();
	  this.inputs.betAmount.keyup().blur();
	  return this;
	},
	
	root: $('.crypto50'),
	
	inputs: {
	  betAmount: $('#bet-amount'), //Amount field
	  betProfit: $('#bet-profit'), //Profit field
	  betX2: $('#bet-x2'), //Tiny double-down button
	  betAll: $('#bet-all'), //Tiny all button
	  betTypeVal: $('#bet-type-val'), //Hidden value for under/over button
	  betType: $('#bet-type'), //Actual button for under/over
	  betRisk: $('#bet-risk'), //Risk value for under/over
	  betReturn: $('#bet-return'), //Return multiplier field
	  betRoll: $('#roll'), //Roll button
	},

	validatorMethods: {
	  betAmount: function($field) {
		return !isNaN(+$field.val());
	  },
	  maxBalance: function($field) {
		return +$field.val() >= 0 && +$field.val() <= +$($('.bal.active a').attr('href')).val();
	  },
	  validateReturn: function($field) {
		return +$field.val() >= 1.0102 && +$field.val() <= 49.5;
	  },
	  validateRisk: function($field) {
		return +$field.val() >= 2  && +$field.val() <= 98;
	  }
	},

	validateField: function(self) {
	  var methods = $(this).attr('data-method') || false,
		  isValid = true,
		  all;
	  if (!methods) {
		return true;
	  }
	  all = Array.prototype.slice.call(methods.split(' '));

	  all.forEach(function(method) {
		if (!self.validatorMethods[method].apply(self, [$(this)])) {
		  isValid = false;
		}
	  }, this);

	  if (isValid) {
		$(this).removeClass('invalid');
	  } else {
		$(this).addClass('invalid');
	  }
	},

	validate: function(event) {
	  var self = event.data.self;
	  $.each(self.inputs, function () {
		  self.validateField.apply(this, [self]);
	  });

	  if ($('input.invalid', this.root).length) {
		self.formInvalid();
	  } else {
		self.formValid();
	  }
	},

	formInvalid: function() {
	  this.elements.submit.prop('disabled', true);
	  this.showError($('.invalid:first'));
	},

	formValid: function() {
	  this.inputs.betRoll.prop('disabled', false);
	  $('.error', this.root).removeClass('visible-all');
	  try {
		this.calculate();
	  } catch (ex) {
	  }
	},

	showError: function($input) {
	  $input.next('.error').addClass('visible-all');
	},

	executeBindings: function() {
	  var self = this;
	  
	  this.inputs.betX2.on('click', $.proxy(this.doubleBetAmount, this));
	  this.inputs.betAll.on('click', $.proxy(this.enterAllIn, this));

	  this.inputs.betAmount.on('blur', function() {
		if (isNaN(this.value)) {
		  this.value = 0;
		  $(this).next('.error').removeClass('visible-all');
		}
		this.value = (+this.value).toFixed(8);
	  });

	  this.inputs.betType.on('click', $.proxy(this.roll, this));
	  this.inputs.betReturn.on('keyup', {self: this}, this.getRiskValue);
	  this.inputs.betRisk.on('keyup', {self: this}, this.getReturnValue);
	  
	  this.inputs.betRisk.on('change', function() {
		var val = (+$(this).val()).toPrecision(4);
		self.inputs.betRisk.val(val);
	  }); 
	  
	  this.inputs.betReturn.on('change', function() {
		var val = (+$(this).val()).toPrecision(5);
		self.inputs.betReturn.val(val);
	  });
	  
	  this.inputs.betType.on('click', {self: this}, this.getReturnValue);

	  $('input', this.root).on('keyup', { self: this }, this.validate);
	},

	getNumericValue: function(field) {
	  var ret = field.val();
	  if (isNaN(+ret)) {
		return 0;
	  }
	  return +field.val();
	},

	setFinalResults: function(data) {

	  this.inputs.betProfit.val((+data.betProfit - this.inputs.betAmount.val()).toFixed(8)).change();

	  return this;
	},

	calculateMultiplier: function(chance) {
	  if (this.inputs.betTypeVal.val() === 'over' ? 0 : 1) {
		return (99 / +chance).toPrecision(5);
	  } else {
		return (99 / (100 - +chance)).toPrecision(5);
	  }
	},

	calculateRisk: function(multiplier) {
	  if (this.inputs.betTypeVal.val() === 'over' ? 0 : 1) {
		return (99 / +multiplier).toPrecision(4);
	  } else {
		return (99 / (100 - +multiplier)).toPrecision(4);
	  }
	},

	getRiskValue: function(event) {
	  var self = event.data.self,
		  $field = self.inputs.betReturn,
		  risk = self.calculateRisk(+$field.val()),
		  valid = self.validatorMethods.validateReturn($field);

	  if (valid) {
		self.inputs.betRisk.val(risk).change();
		self.calculate();
	  } else {
		console.log($field.val(), 'is not valid risk!');
	  }
	},

	getReturnValue: function(event) {
	  var self = event.data.self,
		  $field = self.inputs.betRisk,
		  multiplier = self.calculateMultiplier(+$field.val()),
		  valid = self.validatorMethods.validateRisk($field);

	  if (valid) {
		self.inputs.betReturn.val(multiplier).change();
		self.calculate();
	  } else {
		console.log($field.val(), 'is not valid multiplier!');
	  }
	},
	
	calculate: function() {
	  var betAmount = this.getNumericValue(this.inputs.betAmount),
		  risk = this.getNumericValue(this.inputs.betRisk).toFixed(2),
		  multiplier = this.inputs.betReturn.val();
	  this.setFinalResults({
		betProfit: betAmount * this.inputs.betReturn.val(),
		multiplier: multiplier,
		risk: risk
	  });
	},

	doubleBetAmount: function(e) {
	  var val = +(this.getNumericValue(this.inputs.betAmount, true));
	  e.preventDefault();
	  this
		.inputs
		.betAmount
		.val((val * 2).toFixed(8)).keyup().change();
	  return this;
	},

	enterAllIn: function(e) {
	  e.preventDefault();
	  this
		.inputs
		.betAmount
		.val(this.getNumericValue($($('.bal.active a').attr('href'))).toFixed(8)).keyup().change();
	  return this;
	},

	roll: function(e) {
	  e.preventDefault();
	  var type = this.inputs.betTypeVal,
		  typeText = 'Roll ';

		  type = type.val() === 'over' ? 'under': 'over';
		   
		  this.inputs.betTypeVal.val(type);
		  this.inputs.betType.val(typeText+type);

	  return this;
	}
  };
  
  Balances.init();
  BetCalculator.init();
  updateWallet();
  
});