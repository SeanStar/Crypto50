<?php
set_time_limit(0);
require('config.php');
require('coinhandler.php');
$btc_conf = 1;
$ltc_conf = 3;
$nmc_conf = 3;
$ftc_conf = 4;

/////BTC UPDATE PENDING TRANSACTIONS!
try {
	$query = $db->prepare('SELECT * FROM transactions WHERE trx_status=0 AND trx_ct=\'BTC\'');
	$query->execute();
	$btc_array = $query->fetchAll();

	for ($i=0; $i<count($btc_array); $i++) {
		$btc_trxcheck = $btc->gettransaction($btc_array[$i]['trx_hash']);
		if($btc_trxcheck['confirmations'] >= $btc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $btc_trxcheck['confirmations'], 'trxid' => $btc_array[$i]['trx_id']));
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}

/////LTC UPDATE PENDING TRANSACTIONS!
try {
	$query = $db->prepare('SELECT * FROM transactions WHERE trx_status=0 AND trx_ct=\'LTC\'');
	$query->execute();
	$ltc_array = $query->fetchAll();

	for ($i=0; $i<count($ltc_array); $i++) {
		$ltc_trxcheck = $ltc->gettransaction($ltc_array[$i]['trx_hash']);
		if($ltc_trxcheck['confirmations'] >= $ltc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $ltc_trxcheck['confirmations'], 'trxid' => $ltc_array[$i]['trx_id']));
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}


/////NMC UPDATE PENDING TRANSACTIONS!
try {
	$query = $db->prepare('SELECT * FROM transactions WHERE trx_status=0 AND trx_ct=\'NMC\'');
	$query->execute();
	$nmc_array = $query->fetchAll();

	for ($i=0; $i<count($nmc_array); $i++) {
		$nmc_trxcheck = $nmc->gettransaction($nmc_array[$i]['trx_hash']);
		if($nmc_trxcheck['confirmations'] >= $nmc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $nmc_trxcheck['confirmations'], 'trxid' => $nmc_array[$i]['trx_id']));
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}


/////FTC UPDATE PENDING TRANSACTIONS!
try {
	$query = $db->prepare('SELECT * FROM transactions WHERE trx_status=0 AND trx_ct=\'FTC\'');
	$query->execute();
	$ftc_array = $query->fetchAll();

	for ($i=0; $i<count($ftc_array); $i++) {
		$ftc_trxcheck = $ftc->gettransaction($ftc_array[$i]['trx_hash']);
		if($ftc_trxcheck['confirmations'] >= $ftc_conf) {
			$status = 1;
		} else {
			$status = 0;
		}
		$query = $db->prepare('UPDATE transactions SET trx_status= :status, trx_confirms= :confirms WHERE trx_id= :trxid');
		$query->execute(array('status' => $status, 'confirms' => $ftc_trxcheck['confirmations'], 'trxid' => $ftc_array[$i]['trx_id']));
	}
} catch (Exception $e) {
	error_log("Caught exception: ".$e->getMessage()."\n", 3, "cron_errors.log");
}

?>