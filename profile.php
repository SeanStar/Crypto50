<?php
require('header.html');
?>
			
				<div id="main-container">
					<div id="body-section">
						<div id="content-container">
							<div id="content">
								<div class="c-forms">	   
									<div class="box-element">
										<div class="box-head-light"><span class="forms-16"></span><h3>Profile - Change Password</h3><a href="" class="collapsable"></a></div>
										<div class="box-content no-padding">
										<fieldset>
<?php
if(!empty($_POST)) {
?>
										<section>
												<?php
	if(isset($_POST['oldpassword']) && isset($_POST['password1']) && isset($_POST['password2'])) {
		if($_POST['password1'] != $_POST['password2']) {
			echo "<div class=\"alert-msg error-msg\">New password fields did not match!</div>";
		} elseif(hash('sha512',hash('sha512',$_POST['oldpassword'])) != $check_data[0]['password']) {
			echo "<div class=\"alert-msg error-msg\">Current password is invalid!</div>";
		} else {
			$newpassword = hash('sha512',hash('sha512',$_POST['password1']));
			$query = $db->prepare('UPDATE users SET password= :password WHERE username= :username');
			$query->execute(array('password' => $newpassword, 'username' => $username));
			echo "<div class=\"alert-msg success-msg\">Password successfully changed!</div>";
		}
	} else {
		echo "<div class=\"alert-msg error-msg\">Please fill in all of the fields!</div>";
	}
?>
										   <div class="clearfix"></div>
										</section>
<?php } ?>
											<form method="post" action="profile.php" class="i-validate"> 
													<section>
														<div class="section-left-s">
															<label for="text_field">Current Password</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="password" name="oldpassword" id="text_field" class="i-text required" maxlength="40"></div>
														</div>
														<div class="clearfix"></div>
													</section>
													<section>
														<div class="section-left-s">
															<label for="text_field">Desired Password</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="password" name="password1" id="text_field" class="i-text required" maxlength="40"></div>
														</div>
														<div class="clearfix"></div>
													</section>
													<section>
														<div class="section-left-s">
															<label for="text_field">Desired Password Again</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="password" name="password2" id="text_field" class="i-text required" maxlength="40"></div>
														</div>
														<div class="clearfix"></div>
													</section>													 

													<section>
														<input type="submit" name="submit" class="i-button no-margin" value="Submit" />
														<div class="clearfix"></div>
													</section>
											</form>
										</fieldset>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>					
				</div> <!--! end of #main-container -->
<?php
require('footer.html');
?>