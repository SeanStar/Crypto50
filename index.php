<?php
require('header.html');
if($validsession) {
$query = $db->prepare('SELECT * FROM prerolls WHERE username= :username');
$query->execute(array('username' => $username));
$query_array = $query->fetchAll();
}
?>
			   <div id="main-container">
					<div id="body-section">
						<div id="content-container">
							<div id="content">
								<div class="c-tables">
									<div class="box-element small">
										<div class="box-head"></div>
										<div class="box-content" style="height:320px">
											<fieldset class="crypto50"><span id="dialog" title=""></span>
											<div style="margin-bottom:5px;"><b style="font-size:18px;">Choose a balance:</b><?php if($validsession) { ?><span style="float:right;"><a id="pfc" href="#">Provably Fair Code<span id="pfcdialog" title="Provably Fair Encrypted Roll Value"><?php echo $query_array[0]['ciphertext']; ?></span></a> <a class="modal" href="modals/pfchelp.html">[?]</a></span><?php } ?></div>
											<ul class="actions">
												<li class="bal active"><a href="#btc-amt" class=""><input type="hidden" name="" value="0.00000000" id="btc-amt"><div><p id="btc">0.00000000</p><span>BTC</span> <img src="img/icons/16/btc.png" alt="BTC"></div></a></li>
												<li class="bal"><a href="#ltc-amt" class=""><input type="hidden" name="" value="0.00000000" id="ltc-amt"><div><p id="ltc">0.00000000</p><span>LTC</span> <img src="img/icons/16/ltc.png" alt="LTC"></div></a></li>
												<li class="bal"><a href="#nmc-amt" class=""><input type="hidden" name="" value="0.00000000" id="nmc-amt"><div><p id="nmc">0.00000000</p><span>NMC</span> <img src="img/icons/16/nmc.png" alt="NMC"></div></a></li>
												<li class="bal"><a href="#ftc-amt" class=""><input type="hidden" name="" value="0.00000000" id="ftc-amt"><div><p id="ftc">0.00000000</p><span>FTC</span> <img src="img/icons/16/ftc.png" alt="FTC"></div></a></li>
											</ul>
											<div class="clearfix"></div>
											<?php if (($validsession)) { ?>
											<table style="width:700px";>
											<tr>
											<td><span id="walletlabel"></span></td>
											<td style="float:right;"><input type="text" id="wallet" value="" /> <a class="modal" href="modals/qr.php?ct=btc">[QR]</a> <a class="modal" href="modals/deposithelp.html">[?]</a></td>
											</tr>
											</table>
<?php } ?>
											<div class="diceapp">
											<table>
											<tr>
											<td><b>Bet amount:</b></td><td><b>Profit calculator:</b></td>
											</tr>
											<tr>
											<td><input type="text" name="bet-amount" id="bet-amount" title="How much do you want to bet?" class="i-text short i-form-tooltip" maxlength="25" value="0.00000000">
											<input type="button" name="bet-x2" id="bet-x2" class="i-button stubby" value="x2">
											<input type="button" name="bet-all" id="bet-all" class="i-button stubby" value="ALL"></td>
											<td><input type="text" name="bet-profit" id="bet-profit" title="Calculates profit on win" class="i-text short i-form-tooltip" maxlength="25" value="0.00000000" disabled></td>
											</tr>
											<tr>
											<td>
											<input type="hidden" id="bet-type-val" name="" value="under"/>
											<input type="button" name="bet-type" id="bet-type" class="i-button toggle" value="Roll under">
											<input type="text" name="bet-risk" id="bet-risk" title="Select a number from 2-98" class="i-text short mini i-form-tooltip" style="" maxlength="5" value="50.00">
											<span class="return-label-32"></span>
											</td>
											<td>
											<input type="text" name="bet-return" id="bet-return" title="Return multiplier" class="i-text short i-form-tooltip" value="1.98000">
											</td>
											</tr>
											</table>
											<div class="clearfix"></div>
											<input type="hidden" name="" value="0.00" id="percent">
											<input type="submit" name="roll" id="roll" class="i-button big" value="Roll">
											<div class="clearfix"></div>
											</div>
											</fieldset>
										</div>
									</div>
									<div class="box-element chat">
										<div class="box-head"></div>
										<div class="box-content" style="height:320px;">
											<fieldset class="chat">
											<div id="chatbox"></div>  
											<form name="message" action="#">  
											<input name="usermsg" type="text" id="usermsg"<?php if (!($validsession)) { echo " value=\" You must login to chat.\" disabled"; } ?> />  
											<input name="submitmsg" type="submit"  id="submitmsg" value="Send"<?php if (!($validsession)) { echo " disabled"; } ?> />  
											</form>  
											</fieldset>
										</div>
									</div>
									<div class="clearfix"></div>
									<div class="box-element">
										<div class="box-head-light"><h3>Bets [Last 50]</h3><a href="" class="collapsable"></a></div>
										<div class="box-content no-padding">
											<table class="i-table fullwidth" id="table-all">
												<thead>
													<tr>
														<td>Bet #</td>
														<td>Username</td>
														<td>Time</td>
														<td>Amount</td>
														<td>Risk</td>
														<td>Roll</td>	
														<td>Return</td>														
														<td>Profit</td>
													</tr>
												</thead>
											</table>
											<div class="clearfix"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
<?php
require('footer.html');
?>