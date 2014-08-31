<?php
set_time_limit(0);
require('config.php');
require('coinhandler.php');
require('functions.php');

$listsize = 40; //Update accordingly
$btc_conf = 1;
$ltc_conf = 3;
$nmc_conf = 3;
$ftc_conf = 4;

/////BTC UPDATE TRANSACTIONS!
try {
	$btc_trxlist = $btc->listtransactions("*",$listsize);
	foreach($btc_trxlist as $key => $row) {
	   if($row['category'] == "move") unset($btc_trxlist[$key]);
	}
	$btc_trxlist = array_values($btc_trxlist);

	$query = $db->prepare('SELECT * FROM  transactions WHERE trx_hash IN (\''.implode("', '",array_column($btc_trxlist, 'txid')).'\') ORDER BY trx_id');
	$query->execute();
	$btc_array = $query->fetchAll();

	for ($i=0; $i<count($btc_array); $i++) {
		if($btc_trxlist[$i]['confirmations'] >= $btc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $btc_trxlist[$i]['confirmations'], 'trxid' => $btc_array[$i]['trx_id']));
	} 

	for ($i=count($btc_array); $i<count($btc_trxlist); $i++) {
		if($btc_trxlist[$i]['confirmations'] >= $btc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		if ($btc_trxlist[$i]['category'] == 'receive') {
			$trx_type = 0;
		} else {
			$trx_type = 1;
		}
		$query = $db->prepare('INSERT INTO transactions (trx_id, trx_user, trx_type, trx_status, trx_confirms, trx_hash, trx_ct, trx_amount, trx_wallet, trx_time) VALUES (NULL,:trxuser,:trxtype,:trxstatus,:trxconfirms,:trxhash,:trxct,:trxamount,:trxwallet,:trxtime)');
		$query->execute(array('trxuser' => $btc_trxlist[$i]['account'], 'trxtype' => $trx_type, 'trxstatus' => $status, 'trxconfirms' => $btc_trxlist[$i]['confirmations'], 'trxhash' => $btc_trxlist[$i]['txid'], 'trxct' => 'BTC', 'trxamount' => $btc_trxlist[$i]['amount'], 'trxwallet' => $btc_trxlist[$i]['address'], 'trxtime' => $btc_trxlist[$i]['time']));	
		if($trx_type == 0) {
				$query = $db->prepare('SELECT * FROM balances WHERE username= :username');
				$query->execute(array('username' => $btc_trxlist[$i]['account']));
				$bal = $query->fetchAll();
				$query2 = $db->prepare('UPDATE balances SET bal_btc= :bal WHERE username= :username');
				$query2->execute(array('bal' => ($bal[0]['bal_btc'] + $btc_trxlist[$i]['amount']), 'username' => $btc_trxlist[$i]['account']));
				$query3 = $db->prepare('SELECT * FROM main WHERE 1');
				$query3->execute();
				$mainbal = $query3->fetchAll();
				$query4 = $db->prepare('UPDATE main SET user_balances_btc= :ub, main_balance_btc= :mb');
				$query4->execute(array('ub' => ($mainbal[0]['user_balances_btc'] + $btc_trxlist[$i]['amount']), 'mb' => ($mainbal[0]['main_balance_btc'] + $btc_trxlist[$i]['amount'])));
		}
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}

/////LTC UPDATE TRANSACTIONS!
try {
	$ltc_trxlist = $ltc->listtransactions("*",$listsize);
	foreach($ltc_trxlist as $key => $row) {
	   if($row['category'] == "move") unset($ltc_trxlist[$key]);
	}
	$ltc_trxlist = array_values($ltc_trxlist);

	$query = $db->prepare('SELECT * FROM  transactions WHERE trx_hash IN (\''.implode("', '",array_column($ltc_trxlist, 'txid')).'\') ORDER BY trx_id');
	$query->execute();
	$ltc_array = $query->fetchAll();

	for ($i=0; $i<count($ltc_array); $i++) {
		if($ltc_trxlist[$i]['confirmations'] >= $ltc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $ltc_trxlist[$i]['confirmations'], 'trxid' => $ltc_array[$i]['trx_id']));
	} 

	for ($i=count($ltc_array); $i<count($ltc_trxlist); $i++) {
		if($ltc_trxlist[$i]['confirmations'] >= $ltc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		if ($ltc_trxlist[$i]['category'] == 'receive') {
			$trx_type = 0;
		} else {
			$trx_type = 1;
		}
		$query = $db->prepare('INSERT INTO transactions (trx_id, trx_user, trx_type, trx_status, trx_confirms, trx_hash, trx_ct, trx_amount, trx_wallet, trx_time) VALUES (NULL,:trxuser,:trxtype,:trxstatus,:trxconfirms,:trxhash,:trxct,:trxamount,:trxwallet,:trxtime)');
		$query->execute(array('trxuser' => $ltc_trxlist[$i]['account'], 'trxtype' => $trx_type, 'trxstatus' => $status, 'trxconfirms' => $ltc_trxlist[$i]['confirmations'], 'trxhash' => $ltc_trxlist[$i]['txid'], 'trxct' => 'LTC', 'trxamount' => $ltc_trxlist[$i]['amount'], 'trxwallet' => $ltc_trxlist[$i]['address'], 'trxtime' => $ltc_trxlist[$i]['time']));	
		if($trx_type == 0) {
				$query = $db->prepare('SELECT * FROM balances WHERE username= :username');
				$query->execute(array('username' => $ltc_trxlist[$i]['account']));
				$bal = $query->fetchAll();
				$query2 = $db->prepare('UPDATE balances SET bal_ltc= :bal WHERE username= :username');
				$query2->execute(array('bal' => ($bal[0]['bal_ltc'] + $ltc_trxlist[$i]['amount']), 'username' => $ltc_trxlist[$i]['account']));
				$query3 = $db->prepare('SELECT * FROM main');
				$query3->execute();
				$mainbal = $query3->fetchAll();
				$query4 = $db->prepare('UPDATE main SET user_balances_ltc= :ub, main_balance_ltc= :mb');
				$query4->execute(array('ub' => ($mainbal[0]['user_balances_ltc'] + $ltc_trxlist[$i]['amount']), 'mb' => ($mainbal[0]['main_balance_ltc'] + $ltc_trxlist[$i]['amount'])));
		}
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}

/////NMC UPDATE TRANSACTIONS!
try {
	$nmc_trxlist = $nmc->listtransactions("*",$listsize);
	foreach($nmc_trxlist as $key => $row) {
	   if($row['category'] == "move") unset($nmc_trxlist[$key]);
	}
	$nmc_trxlist = array_values($nmc_trxlist);

	$query = $db->prepare('SELECT * FROM  transactions WHERE trx_hash IN (\''.implode("', '",array_column($nmc_trxlist, 'txid')).'\') ORDER BY trx_id');
	$query->execute();
	$nmc_array = $query->fetchAll();

	for ($i=0; $i<count($nmc_array); $i++) {
		if($nmc_trxlist[$i]['confirmations'] >= $nmc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $nmc_trxlist[$i]['confirmations'], 'trxid' => $nmc_array[$i]['trx_id']));
	} 

	for ($i=count($nmc_array); $i<count($nmc_trxlist); $i++) {
		if($nmc_trxlist[$i]['confirmations'] >= $nmc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		if ($nmc_trxlist[$i]['category'] == 'receive') {
			$trx_type = 0;
		} else {
			$trx_type = 1;
		}
		$query = $db->prepare('INSERT INTO transactions (trx_id, trx_user, trx_type, trx_status, trx_confirms, trx_hash, trx_ct, trx_amount, trx_wallet, trx_time) VALUES (NULL,:trxuser,:trxtype,:trxstatus,:trxconfirms,:trxhash,:trxct,:trxamount,:trxwallet,:trxtime)');
		$query->execute(array('trxuser' => $nmc_trxlist[$i]['account'], 'trxtype' => $trx_type, 'trxstatus' => $status, 'trxconfirms' => $nmc_trxlist[$i]['confirmations'], 'trxhash' => $nmc_trxlist[$i]['txid'], 'trxct' => 'NMC', 'trxamount' => $nmc_trxlist[$i]['amount'], 'trxwallet' => $nmc_trxlist[$i]['address'], 'trxtime' => $nmc_trxlist[$i]['time']));	
		if($trx_type == 0) {
				$query = $db->prepare('SELECT * FROM balances WHERE username= :username');
				$query->execute(array('username' => $nmc_trxlist[$i]['account']));
				$bal = $query->fetchAll();
				$query2 = $db->prepare('UPDATE balances SET bal_nmc= :bal WHERE username= :username');
				$query2->execute(array('bal' => ($bal[0]['bal_nmc'] + $nmc_trxlist[$i]['amount']), 'username' => $nmc_trxlist[$i]['account']));
				$query3 = $db->prepare('SELECT * FROM main');
				$query3->execute();
				$mainbal = $query3->fetchAll();
				$query4 = $db->prepare('UPDATE main SET user_balances_nmc= :ub, main_balance_nmc= :mb');
				$query4->execute(array('ub' => ($mainbal[0]['user_balances_nmc'] + $nmc_trxlist[$i]['amount']), 'mb' => ($mainbal[0]['main_balance_nmc'] + $nmc_trxlist[$i]['amount'])));
		}
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}

/////FTC UPDATE TRANSACTIONS!
try {
	$ftc_trxlist = $ftc->listtransactions("*",$listsize);
	foreach($ftc_trxlist as $key => $row) {
	   if($row['category'] == "move") unset($ftc_trxlist[$key]);
	}
	$ftc_trxlist = array_values($ftc_trxlist);

	$query = $db->prepare('SELECT * FROM  transactions WHERE trx_hash IN (\''.implode("', '",array_column($ftc_trxlist, 'txid')).'\') ORDER BY trx_id');
	$query->execute();
	$ftc_array = $query->fetchAll();

	for ($i=0; $i<count($ftc_array); $i++) {
		if($ftc_trxlist[$i]['confirmations'] >= $ftc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $ftc_trxlist[$i]['confirmations'], 'trxid' => $ftc_array[$i]['trx_id']));
	} 

	for ($i=count($ftc_array); $i<count($ftc_trxlist); $i++) {
		if($ftc_trxlist[$i]['confirmations'] >= $ftc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		if ($ftc_trxlist[$i]['category'] == 'receive') {
			$trx_type = 0;
		} else {
			$trx_type = 1;
		}
		$query = $db->prepare('INSERT INTO transactions (trx_id, trx_user, trx_type, trx_status, trx_confirms, trx_hash, trx_ct, trx_amount, trx_wallet, trx_time) VALUES (NULL,:trxuser,:trxtype,:trxstatus,:trxconfirms,:trxhash,:trxct,:trxamount,:trxwallet,:trxtime)');
		$query->execute(array('trxuser' => $ftc_trxlist[$i]['account'], 'trxtype' => $trx_type, 'trxstatus' => $status, 'trxconfirms' => $ftc_trxlist[$i]['confirmations'], 'trxhash' => $ftc_trxlist[$i]['txid'], 'trxct' => 'FTC', 'trxamount' => $ftc_trxlist[$i]['amount'], 'trxwallet' => $ftc_trxlist[$i]['address'], 'trxtime' => $ftc_trxlist[$i]['time']));	
		if($trx_type == 0) {
				$query = $db->prepare('SELECT * FROM balances WHERE username= :username');
				$query->execute(array('username' => $ftc_trxlist[$i]['account']));
				$bal = $query->fetchAll();
				$query2 = $db->prepare('UPDATE balances SET bal_ftc= :bal WHERE username= :username');
				$query2->execute(array('bal' => ($bal[0]['bal_ftc'] + $ftc_trxlist[$i]['amount']), 'username' => $ftc_trxlist[$i]['account']));
				$query3 = $db->prepare('SELECT * FROM main');
				$query3->execute();
				$mainbal = $query3->fetchAll();
				$query4 = $db->prepare('UPDATE main SET user_balances_ftc= :ub, main_balance_ftc= :mb');
				$query4->execute(array('ub' => ($mainbal[0]['user_balances_ftc'] + $ftc_trxlist[$i]['amount']), 'mb' => ($mainbal[0]['main_balance_ftc'] + $ftc_trxlist[$i]['amount'])));
		}
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}
?>