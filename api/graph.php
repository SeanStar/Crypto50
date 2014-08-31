<?php
require('../config.php');
require('../checksession.php');
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}
if ($validsession) {
	//Check that all fields entered
	if (isset($_GET['coin'])) {
		$query = $db->prepare('SELECT bet_time,bet_amount,bet_newbal FROM bets WHERE bet_user_id= :userid AND bet_coin= :coin ORDER BY bet_id DESC LIMIT 0,75');
		$query->execute(array('userid' => $username, 'coin' => $_GET['coin']));
		$array = array_reverse($query->fetchAll(PDO::FETCH_ASSOC));
		$count = $query->rowCount();
		$allinone = array('bet_amount' => array(), 'bet_profit' => array(),'bet_newbal' => array());
		for($i=0; $i<$count; $i++) {
			array_push($allinone['bet_amount'], array(($i+1), $array[$i]['bet_amount']));
			array_push($allinone['bet_newbal'], array(($i+1), bal_f($array[$i]['bet_newbal'],8)));
		}
		echo json_encode($allinone);
	}
}
?>