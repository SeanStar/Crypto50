<?php
require('header.html');
require('coinhandler.php');
require_once('recaptchalib.php');
$privatekey = "6LcYGuwSAAAAALmOgMDcPuYfgPqyl9GIDKxO58Ns";
?>		  
				<div id="main-container">
					<div id="body-section">
						<div id="content-container">
							<div id="content">
								<div class="c-forms">	   
									<div class="box-element">
										<div class="box-head-light"><span class="forms-16"></span><h3>Register</h3><a href="" class="collapsable"></a></div>
										<div class="box-content no-padding">
<?php if (!empty($_POST)) { ?>
										<fieldset>
											<section>
												<?php	
	//Are all of our variables set?
	if (isset($_POST['username']) && isset($_POST['password1']) && isset($_POST['password2']) && isset($_POST['email']) && 
		isset($_POST["recaptcha_challenge_field"]) && isset($_POST["recaptcha_response_field"])) {

		$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		$query = $db->prepare('SELECT * FROM users WHERE username= :username');
		$query->execute(array('username' => $_POST['username']));
		$result = $query->rowCount();

		//////BEGIN FORM VALIDATION CHECKS
		
		//Incorrect CAPTCHA
		if (!$resp->is_valid) {
			echo "<div class=\"alert-msg error-msg\">The reCAPTCHA wasn't entered correctly. Go back and try it again.</div>";
		//Check that password fields match
		} elseif($_POST['password1'] != $_POST['password2']) {
			echo "<div class=\"alert-msg error-msg\">Password fields did not match!</div>";
		//Check that username does not exist
		} elseif($result > 0) {
			echo "<div class=\"alert-msg error-msg\">The username you have chosen already exists.</div>";
		//Check that username does not contain special characters
		} elseif(!preg_match("/^[A-Za-z0-9_]+$/", $_POST['username'])) {
			echo "<div class=\"alert-msg error-msg\">The username must not contain special characters.</div>";
		//Check that username does not exceed 20 characters
		} elseif(strlen($_POST['username']) > 20) {
			echo "<div class=\"alert-msg error-msg\">The username must be 20 characters or less.</div>";
		//Check that email address is valid
		} elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			echo "<div class=\"alert-msg error-msg\">Please enter a valid email address.</div>";
		} else {			
		//////END FORM VALIDATION CHECKS, FORM IS VALID

			//Double sha512 hash the password
			$password = hash('sha512',hash('sha512',$_POST['password1']));
			//Attempt to generate wallet addresses for user
			try {
				$query = $db->prepare('INSERT INTO balances (username, wal_btc, wal_ltc, wal_nmc, wal_ftc) VALUES (:username,:wal_btc,:wal_ltc,:wal_nmc,:wal_ftc)');
				$query->execute(array('username' => $_POST['username'], 'wal_btc' => $btc->getnewaddress($_POST['username']), 'wal_ltc' => $ltc->getnewaddress($_POST['username']), 'wal_nmc' => $nmc->getnewaddress($_POST['username']), 'wal_ftc' => $ftc->getnewaddress($_POST['username'])));
			} catch (Exception $e) {
				$query = $db->prepare('INSERT INTO balances (username) VALUES (:username)');
				$query->execute(array('username' => $_POST['username']));
				echo "<div class=\"alert-msg error-msg\">Unable to generate wallet addresses, please contact administrator.</div>";
			}
			//Insert user data into database
			$query = $db->prepare('INSERT INTO users (username, password, email, ip) VALUES (:username,:password,:email,:ip)');
			$query->execute(array('username' => $_POST['username'], 'password' => $password, 'email' => $_POST['email'], 'ip' => $_SERVER['REMOTE_ADDR']));
			//Produce the first pre-generated roll for user
			$iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
			$key = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
			$roll = (rand(0,9999)/100);
			$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $roll, MCRYPT_MODE_CBC, $iv);
			$ciphertext = bin2hex($iv).bin2hex($crypttext);
			//Insert roll data into database
			$query2 = $db->prepare('INSERT INTO prerolls (username, secret, ciphertext) VALUES (:username,:secret,:ciphertext)');
			$query2->execute(array('username' => $_POST['username'], 'secret' => bin2hex($key), 'ciphertext' => $ciphertext));
			echo "<div class=\"alert-msg success-msg\">Congratulations! You have successfully registered! You may now login.</div>";
		}
	} else {
		echo "<div class=\"alert-msg error-msg\">Please fill in all of the fields!</div>";
	}
?>

											   <div class="clearfix"></div>
											</section>
										</fieldset>
<?php } ?>
										<fieldset>
											<form method="post" action="" class="i-validate"> 
													<section>
														<div class="section-left-s">
															<label for="text_field">Desired Username</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="text" name="username" id="text_field" class="i-text required" maxlength="20"></div>
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
															<label for="text_field">Confirm Password</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="password" name="password2" id="text_field" class="i-text required" maxlength="40"></div>
														</div>
														<div class="clearfix"></div>
													</section>
													<section>
														<div class="section-left-s">
															<label for="text_field">Email Address</label>
														</div>								  
														<div class="section-right">
															<div class="section-input"><input type="text" name="email" id="text_field" class="i-text required" maxlength="50"></div>
														</div>
														<div class="clearfix"></div>
													</section>													   
													<section>
													<div class="section-left-s">
														<label for="text_field">reCAPTCHA</label>
													</div>   
													<div class="section-right">
													<script type="text/javascript"
													   src="http://www.google.com/recaptcha/api/challenge?k=6LcYGuwSAAAAAOXvb25PE47e5QrLjyyuuX4I95uJ">
													</script>
													<noscript>
													   <iframe src="http://www.google.com/recaptcha/api/noscript?k=6LcYGuwSAAAAAOXvb25PE47e5QrLjyyuuX4I95uJ"
														   height="300" width="500" frameborder="0"></iframe><br>
													   <textarea name="recaptcha_challenge_field" rows="3" cols="40">
													   </textarea>
													   <input type="hidden" name="recaptcha_response_field"
														   value="manual_challenge">
													</noscript>
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