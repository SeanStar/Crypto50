<?php
require('header.html');
function genRandomString($length) {
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "";	
	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters) - 1)];
	}
	return $string;
}
?>
			
				<div id="main-container">
					<div id="body-section">
						<div id="content-container">
							<div id="content">
								<div class="c-forms">	   
									<div class="box-element">
										<div class="box-head-light"><span class="forms-16"></span><h3>Forgot Password</h3></div>
										<div class="box-content no-padding">
										<fieldset>
<?php
if ((!empty($_POST)) || (!empty($_GET))) {
?>
										<section>
												<?php
if(isset($_GET['key'])) {
	//If key is set, check against database
	$checkkey = $db->prepare('SELECT * FROM users WHERE forgotpass = :forgotpass');
	$checkkey->execute(array('forgotpass' => $_GET['key']));
	$checkkey_result = $checkkey->rowCount();
	//Did we find any valid results?
	if($checkkey_result > 0) {
		$checkkey_array = $checkkey->fetchAll();
		$resetpass = genRandomString(10);
		$resetpass_hashed = hash('sha512',hash('sha512',$resetpass));
		$updateusers = $db->prepare('UPDATE users SET password= :password,forgotpass=`` WHERE forgotpass= :forgotpass');
		$updateusers->execute(array('password' => $resetpass_hashed, 'forgotpass' => $_GET['key']));
		@mail($checkkey_array[0]['email'], 'Forgot Password or Username', "Your username is ".$checkkey_array[0]['username'].".\r\nYour new password is ".$resetpass.".", "From: forgotpass@mcmultiplayer.com\r\nReply-To: forgotpass@mcmultiplayer.com\r\nContent-Type: text/html; charset=ISO-8859-1\r\n");
		echo "<div class=\"alert-msg success-msg\">New password sent to email!</div>";
	} else {
		echo "<div class=\"alert-msg error-msg\">Invalid key!</div>";
	}
} elseif((isset($_POST['username'])) || (isset($_POST['email']))) {
	$emailsent = 0;
	if(isset($_POST['username'])) {
		$checkuser = $db->prepare('SELECT * FROM users WHERE username = :username');
		$checkuser->execute(array('username' => $_POST['username']));
		$checkuser_result = $checkuser->rowCount();
		if ($checkuser_result > 0) {
			$checkuser_array = $checkuser->fetchAll();
			$genkey = genRandomString(30);
			$updateusers = $db->prepare('UPDATE users SET forgotpass= :forgotpass WHERE username= :username');
			$updateusers->execute(array('forgotpass' => $genkey, 'username' => $_POST['username']));
			$usernamemessage = "<p>Click here to reset your password: <a href=\"http://www.mcmultiplayer.com/forgotpass.php?key=".$genkey."\">http://www.mcmultiplayer.com/forgotpass.php?key=".$genkey."</a></p>";
			@mail($checkuser_array[0]['email'], 'Forgot Password or Username', $usernamemessage, "From: forgotpass@mcmultiplayer.com\r\nReply-To: forgotpass@mcmultiplayer.com\r\nContent-Type: text/html; charset=ISO-8859-1\r\n");
			echo "<div class=\"alert-msg success-msg\">Password reset instructions sent to email (".$checkuser_array[0]['email'].")!";
			$emailsent = 1;
		}
	}
	if((isset($_POST['email'])) && ($emailsent == 0)) {
		$checkuser = $db->prepare('SELECT * FROM users WHERE email = :email');
		$checkuser->execute(array('email' => $_POST['email']));
		$checkuser_result = $checkuser->rowCount();
		if ($checkuser_result > 0) {
			$checkuser_array = $checkuser->fetchAll();
			$genkey = genRandomString(30);
			$updateusers = $db->prepare('UPDATE users SET forgotpass= :forgotpass WHERE email= :email');
			$updateusers->execute(array('forgotpass' => $genkey, 'email' => $_POST['email']));
			$usernamemessage = "<p>Click here to reset your password: <a href=\"http://www.mcmultiplayer.com/forgotpass.php?key=".$genkey."\">http://www.mcmultiplayer.com/forgotpass.php?key=".$genkey."</a></p>";
			@mail($checkuser_array[0]['email'], 'Forgot Password or Username', $usernamemessage, "From: forgotpass@mcmultiplayer.com\r\nReply-To: forgotpass@mcmultiplayer.com\r\nContent-Type: text/html; charset=ISO-8859-1\r\n");
			echo "<div class=\"alert-msg success-msg\">Password reset instructions sent to email (".$checkuser_array[0]['email'].")!";
			$emailsent = 1;
		}
	}
	if($emailsent == 0) {
		echo "<div class=\"alert-msg error-msg\">Username and/or Email not found in database.</div>";
	}
}
?>
										   <div class=\"clearfix\"></div>
										</section>
<?php } ?>
											<form method="post" action="forgotpass.php" class="i-validate"> 
													<section>
														<div class="section-left-s">
															<label for="text_field">Username</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="text" name="username" id="text_field" class="i-text" maxlength="20"></div>
														</div>
														<div class="clearfix"></div>
													</section>
													<section>
														<div class="section-left-s">
															<label for="text_field">or Email Address</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="text" name="email" id="text_field" class="i-text" maxlength="50"></div>
														</div>
														<div class="clearfix"></div>
													</section>													   

													<section>
														<input type="submit" name="submit" id="" class="i-button no-margin" value="Submit" />
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