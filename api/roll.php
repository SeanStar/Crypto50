<?php
require('../config.php');
require('../checksession.php');

$max_btc_bet = 0.5;
$max_ltc_bet = 15;
$max_nmc_bet = 50;	
$max_ftc_bet = 500;
	
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}

//Check if variables are set before we do anything else
if(isset($_POST['amt']) && isset($_POST['coin']) && isset($_POST['risk']) && isset($_POST['type'])) {

	$hpercent = 99;
	$amount = $_POST['amt'];
	$coin = strtolower($_POST['coin']);
	$risk = round($_POST['risk'], 2);
	$type = $_POST['type'];
	$cointypes = array('btc','ltc','nmc','ftc');
	$types = array(0,1);
	
	//Coin validation
	if (!(in_array($coin,$cointypes))) {
		echo json_encode(array('error' => 1, 'message' => 'Invalid coin.'));
		exit;
	}
	//Negative values validation
	if (($amount < 0) || ($risk < 0) || ($type < 0)) {
		echo json_encode(array('error' => 1, 'message' => 'Negative values not allowed!'));
		exit;
	}
	//Risk validation
	if (($risk < 2) || ($risk > 98)) {
		echo json_encode(array('error' => 1, 'message' => 'Risk must be between 2 and 98 (Multiplier between 1.0102 and 49.5)'));
		exit;
	}
	//Type validation
	if (!(in_array($type,$types))) {
		echo json_encode(array('error' => 1, 'message' => 'Type must be 0 or 1.'));
		exit;
	}
		
	if ($validsession) {
		$balquery = $db->prepare('SELECT * FROM balances WHERE user_id= :userid');
		$balquery->execute(array('userid' => $userid));
		$bal = $balquery->fetchAll();
	
		//Balance validation
		if ($amount > $bal[0]['bal_'.$coin]) {
			echo json_encode(array('error' => 1, 'message' => 'Amount greater than balance!'));
			exit;
		}
		
		//Maximum bet validation
		switch ($coin) {
			case 'btc':
				$maxbet = $max_btc_bet;
				break;
			case 'ltc':
				$maxbet = $max_ltc_bet;
				break;
			case 'nmc':
				$maxbet = $max_nmc_bet;
				break;
			case 'ftc':
				$maxbet = $max_ftc_bet;
				break;
		}
		if($amount > $maxbet) {
			echo json_encode(array('error' => 1, 'message' => 'Maximum bet is '.$maxbet.' '.strtoupper($_POST['coin']).'.'));
			exit;
		}
		
		//Roll code and profit multiplier
		$rollquery = $db->prepare('SELECT * FROM prerolls WHERE username= :username');
		$rollquery->execute(array('username' => $username));
		$roll_array = $rollquery->fetchAll();
		$key = pack("H*",$roll_array[0]['secret']);
		$iv = pack("H*",substr($roll_array[0]['ciphertext'], 0, 32));
		$data = pack("H*",substr($roll_array[0]['ciphertext'], 32));		
		$roll = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
		
		
		if($type == 1) {
			$profitmultiplier = ($hpercent/(100 - $risk));
		} else {
			$profitmultiplier = ($hpercent/$risk);
		}
		
		//Potential profit validation
		$mainbalquery = $db->prepare('SELECT * FROM main WHERE 1');
		$mainbalquery->execute();
		$mainbal = $mainbalquery->fetchAll();
		if(($mainbal[0]['main_balance_'.$coin] - $mainbal[0]['user_balances_'.$coin]) < (($amount * $profitmultiplier) - $amount)) {
			echo json_encode(array('error' => 1, 'message' => 'Server balance cannot cover potential profit! Please roll with a profit potential of less than '.bal_f(($mainbal[0]['main_balance_'.$coin] - $mainbal[0]['user_balances_'.$coin]),8)));
			exit;
		}			

		if($type == 1) {
			if ($risk < $roll) {
			$profit = (($amount * $profitmultiplier) - $amount);
			$result = 1;
			} else {
			$profit = ($amount * -1);
			$result = 0;
			}
		} else {			
			if ($risk > $roll) {
			$profit = (($amount * $profitmultiplier) - $amount);
			$result = 1;
			} else {
			$profit = ($amount * -1);
			$result = 0;
			}
		}
		
		$newbal = ($bal[0]['bal_'.$coin] + bal_f($profit,12)); //New balance
		$newwgr = ($bal[0][$coin.'_wgr'] + bal_f($amount,8)); //New wagered
		$newprf = ($bal[0][$coin.'_prf'] + bal_f($profit,12)); //New profit
		//Run queries
		$query = $db->prepare('UPDATE balances SET bal_'.$coin.'= :newbal, '.$coin.'_wgr= :wagered,'.$coin.'_prf= :profit WHERE user_id= :userid');
		$query->execute(array('newbal' => $newbal, 'wagered' => $newwgr, 'profit' => $newprf, 'userid' => $userid));
		$query2 = $db->prepare('INSERT INTO bets (bet_id, bet_user_id, bet_time, bet_type, bet_game, bet_roll, bet_return, bet_coin, bet_amount, bet_outcome, bet_profit, bet_newbal, bet_secret, bet_ciphertext) VALUES (NULL,:userid,:time,:type,:risk,:roll,:return,:coin,:amount,:outcome,:profit,:newbal,:key,:ciphertext)');
		$query2->execute(array('userid' => $username, 'time' => time(), 'type' => $type, 'risk' => $risk, 'roll' => $roll, 'return' => bal_f($profitmultiplier,5), 'coin' => strtoupper($coin), 'amount' => $amount, 'outcome' => $result, 'profit' => bal_f($profit,12), 'newbal' => $newbal, 'key' => $roll_array[0]['secret'], 'ciphertext' => $roll_array[0]['ciphertext']));
		$query3 = $db->prepare('SELECT * FROM main WHERE 1');
		$query3->execute();
		$mainbal = $query3->fetchAll();
		$query4 = $db->prepare('UPDATE main SET user_balances_'.$coin.'= :ub');		
		$query4->execute(array('ub' => ($mainbal[0]['user_balances_'.$coin] + bal_f($profit,12))));
		//Generate next roll
		$geniv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
		$genkey = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
		$genroll = (rand(0,9999)/100);
		$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $genkey, $genroll, MCRYPT_MODE_CBC, $geniv);
		$ciphertext = bin2hex($geniv).bin2hex($crypttext);
		$query5 = $db->prepare('UPDATE prerolls SET secret= :gen, ciphertext= :ciphertext WHERE username= :username');
		$query5->execute(array('gen' => bin2hex($genkey), 'ciphertext' => $ciphertext, 'username' => $username));		
		//Display data
		echo json_encode(array('bet_id' => $db->lastInsertId(), 'bet_user_id' => $username, 'bet_time' => time(), 'bet_type' => $type, 'bet_game' => bal_f($risk,2), 'bet_roll' => $roll, 'bet_return' => bal_f($profitmultiplier,5), 'bet_coin' => strtoupper($coin), 'bet_amount' => $amount, 'bet_outcome' => $result, 'bet_profit' => bal_f($profit,12), 'balance' => bal_f($newbal,8), 'next_roll' => $ciphertext));
	
	} else {
		//Test roll
		if($type == 1) {
			if ($risk < (rand(0,9999)/100)) {
			$result = 1;
			} else {
			$result = 0;
			}
		} else {
			if ($risk > (rand(0,9999)/100)) {
			$result = 1;
			} else {
			$result = 0;
			}
		}
		//Display data
		echo json_encode(array('balance' => '0.00000000', 'bet_outcome' => $result,));
	}
} else {
	echo json_encode(array('error' => 1, 'message' => 'One or more variables are not set!'));
}
?>