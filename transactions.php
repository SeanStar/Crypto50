<?php
require('header.html');
function bal_f($balance, $d) {
	$dd = 1;
	for ($x=1; $x<=$d; $x++) { $dd = $dd*10; }
	return sprintf("%01.".$d."f", floor($balance * $dd) / $dd);
}
?>
				<div id="main-container">
					<div id="body-section">
						<div id="content-container">
							<div id="content">
								<div class="c-tables">
									<div class="box-element"><span id="dialog" title=""></span>
										<div class="box-head-light"><h3>Make a Transaction</h3></div>
										<div class="box-content">
											<div style="margin-bottom:5px;"><b style="font-size:18px;">Choose a balance:</b></div>
											<ul class="actions">
												<li class="bal active"><a href="#btc-amt" class=""><input type="hidden" name="" value="0.00000000" id="btc-amt"><div><p id="btc">0.00000000</p><span>BTC</span> <img src="img/icons/16/btc.png" alt="BTC"></div></a></li>
												<li class="bal"><a href="#ltc-amt" class=""><input type="hidden" name="" value="0.00000000" id="ltc-amt"><div><p id="ltc">0.00000000</p><span>LTC</span> <img src="img/icons/16/ltc.png" alt="LTC"></div></a></li>
												<li class="bal"><a href="#nmc-amt" class=""><input type="hidden" name="" value="0.00000000" id="nmc-amt"><div><p id="nmc">0.00000000</p><span>NMC</span> <img src="img/icons/16/nmc.png" alt="NMC"></div></a></li>
												<li class="bal"><a href="#ftc-amt" class=""><input type="hidden" name="" value="0.00000000" id="ftc-amt"><div><p id="ftc">0.00000000</p><span>FTC</span> <img src="img/icons/16/ftc.png" alt="FTC"></div></a></li>
											</ul>
											<div class="clearfix"></div>
											<table style="width:700px";>
											<tr>
											<td class="label big">Deposit</td>
											</tr>
											<tr>
											<td><span id="walletlabel"></span></td>
											<td style="float:right;"><input type="text" id="wallet" value="" /> <a class="modal" href="modals/deposithelp.html">[?]</a></td>
											</tr>
											<tr>
											<td class="label big">Withdrawal</td>
											</tr>
											<tr>
											<td class="label">Withdrawal amount [<span id="wtdr-fee"></span> fee]:</td>
											<td style="float:right;"><input type="text" class="wtdr box" id="wtdr-amt" value="" /></td>
											</tr>
											<tr>
											<td class="label">Withdrawal address:</td>
											<td style="float:right;"><input type="text" class="wtdr box" id="wtdr-adr" value="" /></td>
											</tr>
											<tr>
											<td class="label">Password:</td>
											<td style="float:right;"><input type="password" class="wtdr box" id="wtdr-pwd" value="" /></td>
											</tr>
											<tr>
											<td></td>
											<td style="float:right;"><input type="button" class="i-button no-margin wtdr box" style="height:30px;" id="wtdr-submit" value="Withdraw!" /></td>
											</table>
										<div class="clearfix"></div>
										</div>
									</div>
<?php
$query = $db->prepare('SELECT * FROM transactions WHERE trx_user= :username ORDER BY trx_id DESC LIMIT 0,10');
$query->execute(array('username' => $username));
$num = $query->rowCount();
$result = $query->fetchAll();
if ($num > 0) {
?>
									<div class="box-element">
										<div class="box-head-light"><h3>Last 10 Transactions</h3></div>
										<div class="box-content no-padding">
											<table class="i-table fullwidth">
												<thead>
													<tr>
														<td>Type</td>
														<td>Coin</td>
														<td>Amount</td>
														<td>Status</td>
														<td>Confirms</td>
														<td>Wallet</td>
														<td>Time</td>														
														<td>TrxID</td>
													</tr>
												</thead>
												<tbody>
<?php
for($i=0; $i<$num; $i++) {

//trx_id trx_user_id trx_type trx_status trx_confirms trx_hash trx_ct trx_amount trx_wallet trx_time
$trx_id = $result[$i]['trx_id'];
$trx_user = $result[$i]['trx_user'];
$trx_type = $result[$i]['trx_type'];
$trx_status = $result[$i]['trx_status'];
$trx_confirms = $result[$i]['trx_confirms'];
$trx_hash = $result[$i]['trx_hash'];
$trx_ct = $result[$i]['trx_ct'];
$trx_amount = $result[$i]['trx_amount'];
$trx_wallet = $result[$i]['trx_wallet'];
$trx_time = $result[$i]['trx_time'];

switch($trx_status) {
	case 0:
		$status = ">Processing";
		break;
	case 1:
		$status = ">Completed";
		break;
	case 2:
		$status = "color=\"red\">Rejected";
		break;
}
switch($trx_type) {
	case 0:
		$type = "Deposit";
		$amtpre = "color=\"green\">+";
		break;
	case 1:
		$type = "Withdrawal";
		$amtpre = "color=\"red\">";
		break;
	case 2:
		$type = "Move";
		$amtpre = "color=\"yellow\">";
		break;
}
?>
													<tr>
														<td><?php echo $type; ?></td>
														<td><?php echo $trx_ct; ?></td>
														<td><font <?php echo $amtpre.$trx_amount; ?></font></td>
														<td><font <?php echo $status; ?></font></td>
														<td><?php echo $trx_confirms; ?></td>
														<td><?php echo $trx_wallet; ?></td>
														<td class="timestamp"><?php echo $trx_time; ?></td>
														<td><a href="javascript:alert('<?php echo $trx_hash; ?>')">TRXID</td>
													</tr>
<?php } ?>
												</tbody>
											</table>
											<div class="clearfix"></div>
										</div>
									</div>
<?php
}
$query2 = $db->prepare('SELECT * FROM bets WHERE bet_user_id= :userid ORDER BY bet_id DESC LIMIT 0,50');
$query2->execute(array('userid' => $username));
$num = $query2->rowCount();
$result2 = $query2->fetchAll();
if($num > 0) {
?>
									<div class="box-element">
										<div class="box-head-light"><h3>Last 50 Bets</h3></div>
										<div class="box-content no-padding">
											<table class="i-table fullwidth">
												<thead>
													<tr>
														<td>Bet #</td>
														<td>Time</td>
														<td>Amount</td>
														<td>Risk</td>
														<td>Roll</td>
														<td>Return</td>					
														<td>Profit</td>
													</tr>
												</thead>
												<tbody>
<?php
for($i=0; $i<$num; $i++) {

$bet_id = $result2[$i]['bet_id'];
$bet_time = $result2[$i]['bet_time'];
$bet_type = $result2[$i]['bet_type'];
$bet_game = $result2[$i]['bet_game'];
$bet_roll = $result2[$i]['bet_roll'];
$bet_return = $result2[$i]['bet_return'];
$bet_coin = $result2[$i]['bet_coin'];
$bet_amount = $result2[$i]['bet_amount'];
$bet_outcome = $result2[$i]['bet_outcome'];
$bet_profit = bal_f($result2[$i]['bet_profit'],8);

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
?>
													<tr>
														<td><a class="modal" href="modals/betid.php?id=<?php echo $bet_id; ?>"><?php echo $bet_id; ?></a></td>
														<td class="timestamp"><?php echo $bet_time; ?></td>
														<td><?php echo $bet_amount.' '.$bet_coin; ?> <img src="img/icons/16/<?php echo strtolower($bet_coin); ?>.png" alt="<?php echo $bet_coin; ?>"></td>
														<td><?php echo $bet_game; ?></td>
														<td><?php echo $bet_roll; ?></td>
														<td>x<?php echo $bet_return; ?></td>
														<td><font <?php echo $bet_profit.' '.$bet_coin; ?></font> <img src="img/icons/16/<?php echo strtolower($bet_coin); ?>.png" alt="<?php echo $bet_coin; ?>"></td>
													</tr>
<?php
}
?>
												</tbody>
											</table>
											<div class="clearfix"></div>
										</div>												
									</div>
<?php } ?>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
<?php
require('footer.html');
?>