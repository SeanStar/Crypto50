<?php
require('../config.php');
require('../checksession.php');
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}
if(isset($_GET['coin'])) {
	if($validsession) {
		$coin = strtolower($_GET['coin']);
		$cointypes = array('btc','ltc','nmc','ftc');
		//Coin validation
		if (!(in_array($coin,$cointypes))) {
			echo json_encode(array('error' => 1, 'message' => 'Invalid coin.'));
			exit;
		}
		$usertotal = $db->prepare('SELECT COUNT(*) as Num FROM bets WHERE bet_user_id= :userid AND bet_coin= :coin');
		$userwin = $db->prepare('SELECT COUNT(*) as Num FROM bets WHERE bet_user_id= :userid AND bet_coin= :coin AND bet_outcome=1');
		$userlose = $db->prepare('SELECT COUNT(*) as Num FROM bets WHERE bet_user_id= :userid AND bet_coin= :coin AND bet_outcome=0');
		$usertotal->execute(array('userid' => $username, 'coin' => strtoupper($coin)));
		$userwin->execute(array('userid' => $username, 'coin' => strtoupper($coin)));
		$userlose->execute(array('userid' => $username, 'coin' => strtoupper($coin)));
		$t = $usertotal->fetchColumn();
		$w = $userwin->fetchColumn();
		$l = $userlose->fetchColumn();
		$winlose = round(($w / $l),2);
		$balquery = $db->prepare('SELECT * FROM balances WHERE user_id= :userid');
		$balquery->execute(array('userid' => $userid));
		$bal = $balquery->fetchAll();
		echo json_encode(array('bet_total' => $t, 'bets_won' => $w, 'bets_lost' => $l, 'winlose' => $winlose, 'wagered' => bal_f($bal[0][$coin.'_wgr'],8), 'profit' => bal_f($bal[0][$coin.'_prf'],8)));
	}
} else {
	$totalbets = $db->query("SELECT COUNT(*) as Num FROM `bets`")->fetchColumn();
	$betstoday = $db->query("SELECT COUNT(*) as Num FROM `bets` WHERE `bet_time` >= '".(floor(time()/86400)*86400)."'")->fetchColumn();
	echo json_encode(array('betstotal' => $totalbets, 'betstoday' => $betstoday));
}
?>