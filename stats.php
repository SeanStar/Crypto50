<?php
require('header.html');
?>

				<div id="main-container">
					<div id="body-section">
						<div id="content-container">
							<div id="content">
								<div class="c-forms">	   
									<div class="box-element">
										<div class="box-head-light"><span class="charts-16"></span><h3>Statistics</h3></div>
										<div class="box-content">
											<div style="margin-bottom:5px;"><b style="font-size:18px;">Choose a currency:</b></div>
											<ul class="actions">
												<li class="cur active"><a href="#" class=""><div><span>BTC</span> <img src="img/icons/16/btc.png" alt="BTC"></div></a></li>
												<li class="cur"><a href="#" class=""><div><span>LTC</span> <img src="img/icons/16/ltc.png" alt="LTC"></div></a></li>
												<li class="cur"><a href="#" class=""><div><span>NMC</span> <img src="img/icons/16/nmc.png" alt="NMC"></div></a></li>
												<li class="cur"><a href="#" class=""><div><span>FTC</span> <img src="img/icons/16/ftc.png" alt="FTC"></div></a></li>
											</ul>
											<div class="clearfix"></div>
											<div id="placeholder" style="padding: 0px; position: relative;width:100%; height:300px;"></div>
											<div class="clearfix"></div>
											<table id="statistics" class="i-table fullwidth">
											<thead>
											<tr>
											<td>Total Bets</td>
											<td>Bets Won</td>
											<td>Bets Lost</td>
											<td>Win/Lose Ratio</td>
											<td>Amount Wagered</td>
											<td>Profit</td>
											</tr>
											</thead>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
<script defer src="js/charts.js"></script>
<?php
require('footer.html');
?>