<?php
require('../config.php');
require('../checksession.php');
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}
if ($validsession) {
	$query = $db->prepare('SELECT * FROM balances WHERE user_id= :userid');
	$query->execute(array('userid' => $userid));
	$bal = $query->fetchAll();
	if(!isset($_POST['wallet'])) {
		echo json_encode(array('btc' => bal_f($bal[0]['bal_btc'],8), 'ltc' => bal_f($bal[0]['bal_ltc'],8), 'nmc' => bal_f($bal[0]['bal_nmc'],8), 'ftc' => bal_f($bal[0]['bal_ftc'],8)));
	} else {
		echo json_encode(array('wal_btc' => $bal[0]['wal_btc'], 'wal_ltc' => $bal[0]['wal_ltc'], 'wal_nmc' => $bal[0]['wal_nmc'], 'wal_ftc' => $bal[0]['wal_ftc'], 'btc' => bal_f($bal[0]['bal_btc'],8), 'ltc' => bal_f($bal[0]['bal_ltc'],8), 'nmc' => bal_f($bal[0]['bal_nmc'],8), 'ftc' => bal_f($bal[0]['bal_ftc'],8)));
	}
} else {
	echo json_encode(array('btc' => '0.00000000', 'ltc' => '0.00000000', 'nmc' => '0.00000000', 'ftc' => '0.00000000'));
}
?>