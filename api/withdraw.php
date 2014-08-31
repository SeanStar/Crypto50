<?php
require('../config.php');
require('../checksession.php');
require('../coinhandler.php');
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}

$btc_fee = 0.0005;
$ltc_fee = 0.03;
$nmc_fee = 0.015;
$ftc_fee = 0.1;

if ($validsession) {
	//Check that all post fields entered
	if (isset($_POST['amount']) && isset($_POST['coin']) && isset($_POST['address']) && isset($_POST['password'])) {
		//Check validity of coin, set variables
		switch (strtolower($_POST['coin'])) {
			case 'btc':
				$fee = $btc_fee;
				$balc = 'bal_btc';
				$coin = $btc;
				break;
			case 'ltc':
				$fee = $ltc_fee;
				$balc = 'bal_ltc';
				$coin = $ltc;
				break;
			case 'nmc':
				$fee = $nmc_fee;
				$balc = 'bal_nmc';
				$coin = $nmc;
				break;
			case 'ftc':
				$fee = $ftc_fee;
				$balc = 'bal_ftc';
				$coin = $ftc;
				break;
			default:
				die(json_encode(array('error' => 1, 'message' => 'Invalid coin.')));
				break;
		}
		//Check password
		if (hash('sha512',hash('sha512',$_POST['password'])) == $check_data[0]['password']) {
			//Next check that all transactions are completed
			$query = $db->prepare('SELECT * FROM transactions WHERE trx_user= :username AND trx_type=0 AND trx_status=0');
			$query->execute(array('username' => $username));
			$result = $query->rowCount();
			if ($result == 0) {
				$query = $db->prepare('SELECT * FROM balances WHERE user_id= :userid');
				$query->execute(array('userid' => $userid));
				$bal = $query->fetchAll();
				//Check that balance is greater than fee, withdraw amount is greater than fee and finally that balance is greater or equal to withdraw amount
				if ((($bal[0][$balc] - $fee) > 0) && (($_POST['amount'] - $fee) > 0) && (($bal[0][$balc] - $_POST['amount']) >= 0)) {
					try {
						//Validate
						$isvalid = $coin->validateaddress($_POST['address']);
						if($isvalid['isvalid']) {
							$trxhash = $coin->sendtoaddress($_POST['address'],(floatval($_POST['amount']) - $fee));							
							$query = $db->prepare('UPDATE balances SET '.$balc.'= :newbal WHERE username= :username');
							$query->execute(array('newbal' => ($bal[0][$balc] - $_POST['amount']), 'username' => $username));
							$query2 = $db->prepare('INSERT INTO transactions (trx_id, trx_user, trx_type, trx_status, trx_confirms, trx_hash, trx_ct, trx_amount, trx_wallet, trx_time) VALUES (NULL,:trxuser,:trxtype,:trxstatus,:trxconfirms,:trxhash,:trxct,:trxamount,:trxwallet,:trxtime)');
							$query2->execute(array('trxuser' => $username, 'trxtype' => 1, 'trxstatus' => 0, 'trxconfirms' => 0, 'trxhash' => $trxhash, 'trxct' => strtoupper($_POST['coin']), 'trxamount' => (($_POST['amount']) * -1), 'trxwallet' => $_POST['address'], 'trxtime' => time()));
							$query3 = $db->prepare('SELECT * FROM main WHERE 1');
							$query3->execute();
							$mainbal = $query3->fetchAll();
							$query4 = $db->prepare('UPDATE main SET user_balances_'.strtolower($_POST['coin']).'= :ub, main_balance_'.strtolower($_POST['coin']).'= :mb');
							$query4->execute(array('ub' => ($mainbal[0]['user_balances_'.strtolower($_POST['coin'])] - $_POST['amount']), 'mb' => ($mainbal[0]['main_balance_'.strtolower($_POST['coin'])] - $_POST['amount'])));
							echo json_encode(array('success' => 1, 'message' => 'Success! Withdrawal should show up in your transactions soon.', 'balance' => bal_f(($bal[0][$balc] - $_POST['amount']),8)));
						} else {
							echo json_encode(array('error' => 1, 'message' => 'Address seems to be invalid, please re-enter.'));
						}
					} catch(Exception $e) {
						echo json_encode(array('error' => 1, 'message' => 'Error connecting to daemon, please contact admin.'));
						error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
					}
				} else {
					echo json_encode(array('error' => 1, 'message' => ' Your balance does not cover transaction or fee, or the withdrawal is less than fee.'));
				}
			} else {
				echo json_encode(array('error' => 1, 'message' => 'You have pending deposits. Please wait for them to complete before withdrawing.'));
			}
		} else {
			echo json_encode(array('error' => 1, 'message' => 'Invalid password.'));
		}
	} else {
		echo json_encode(array('error' => 1, 'message' => 'One or more variables not set.'));
	}
} else {
	echo json_encode(array('error' => 1, 'message' => 'Please re-login to continue transaction.'));
}
?>