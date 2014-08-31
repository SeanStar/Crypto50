<?php
require('../config.php');
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}
if(isset($_GET['id'])) {
	$query = $db->prepare('SELECT * FROM bets WHERE bet_id= :betid');
	$query->execute(array('betid' => $_GET['id']));
	$num = $query->rowCount();
	if($num > 0) {
		$query_array = $query->fetchAll();
		$bet_type = $query_array[0]['bet_type'];
		$bet_game = $query_array[0]['bet_game'];
		$bet_outcome = $query_array[0]['bet_outcome'];
		$bet_profit = bal_f($query_array[0]['bet_profit'],8);
		switch($bet_type) {
			case 0:
				$bet_game = "<".$bet_game;
				break;
			case 1:
				$bet_game = ">".$bet_game;
				break;
		}
		switch($bet_outcome) {
			case 0:
				if($bet_profit == '0.00000000') {
					$bet_profit = "color=\"red\">-".$bet_profit;
				} else {
					$bet_profit = "color=\"red\">".$bet_profit;
				}
				break;
			case 1:
				$bet_profit = "color=\"green\">+".$bet_profit;
				break;
		}
	} else {
		die("Bet information not found, invalid id.");
	}
}
?>
<div id="modal">
<h1>Bet Information</h1>
<table class="i-table" style="width:650px;">
<thead>
<tr>
<td>Bet #</td>
<td>Username</td>
<td>Time</td>
<td>Risk</td>
<td>Roll</td>
</tr>
</thead>
<tr>
<td><?php echo $query_array[0]['bet_id']; ?></td>
<td><?php echo $query_array[0]['bet_user_id']; ?></td>
<td class="timestamp"><?php echo $query_array[0]['bet_time']; ?></td>
<td><?php echo $bet_game; ?></td>
<td><?php echo $query_array[0]['bet_roll']; ?></td>
</tr>
</table>
<table class="i-table" style="width:650px;">
<thead>
<tr>
<td>Amount</td>
<td>Return</td>
<td>Profit</td>
</tr>
</thead>
<tr>
<td><?php echo $query_array[0]['bet_amount']." ".$query_array[0]['bet_coin']; ?> <img src="img/icons/16/<?php echo strtolower($query_array[0]['bet_coin']); ?>.png" alt="<?php echo $query_array[0]['bet_coin']; ?>"></td>
<td><?php echo "x".$query_array[0]['bet_return']; ?></td>
<td><font <?php echo $bet_profit." ".$query_array[0]['bet_coin']; ?></font> <img src="img/icons/16/<?php echo strtolower($query_array[0]['bet_coin']); ?>.png" alt="<?php echo $query_array[0]['bet_coin']; ?>"></td>
</tr>
</table>
<table class="i-table" style="width:650px;">
<thead>
<tr>
<td>Key to Encrypted Roll Value</td>
</tr>
</thead>
<tr>
<td><?php echo $query_array[0]['bet_secret']; ?></td>
</tr>
<thead>
<tr>
<td>Encrypted Roll Value</td>
</tr>
</thead>
<tr>
<td><?php echo $query_array[0]['bet_ciphertext']; ?></td>
</tr>
</table>
</div>