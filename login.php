<?php
require('config.php');
//Function for generating sessionid's
function genRandomString() {
	$length = 32;
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "";	
	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters))];
	}
	return $string;
}

//Set redirect
if(!isset($_GET['redirect'])) {
	$redirect = "index.php";
} else {
	$redirect = $_GET['redirect'];
}

$l = 0;

if (!empty($_POST)) {
	//Are all of our variables set?
	if (isset($_POST['username']) && isset($_POST['password'])) {
		//Query user data
		$query = $db->prepare('SELECT * FROM users WHERE username= :username');
		$query->execute(array('username' => $_POST['username']));
		$result = $query->rowCount();
		//Does user exist in database?
		if($result > 0) {
			//Get row data into an array
			$row = $query->fetchAll();
			//Check posted password against password in database
			if (hash('sha512',hash('sha512',$_POST['password'])) == $row[0]['password']) {
				//Generate sessionid for this session
				$sessionid = @genRandomString();
				$sessionidhash = hash('sha512', $sessionkey.$sessionid);
				//Set new sessionid in database
				$query = $db->prepare('UPDATE users SET sessionid= :sessionid WHERE username= :username');
				$query->execute(array('sessionid' => $sessionid, 'username' => $_POST['username']));
				//Set user cookie
				setcookie("username", $row[0]['username'], (time()+604800));
				setcookie("sessionid", $sessionidhash, (time()+604800));
				$l = 4;
			} else {
				$l = 3;
			}
		} else {
			$l = 2;
		}
	} else {
		$l = 1;
	}
}
//It feels weird putting this at the bottom.. damn cookies have to be sent first to the header.
require('header.html');
?>
			<div id="login-container">
				<div id="login" class="i-box">
					<div class="login-title"><h1>Crypto50 Login</h1></div>
<?php if (!empty($_POST)) { ?>
					<fieldset>
						<section>
							<?php
	switch ($l) {
	case 0:
		break;
	case 1:
		echo "<div class=\"alert-msg error-msg\">Invalid Username or Password.</div>";
		break;
	case 2:
		echo"<div class=\"alert-msg error-msg\">The username you specified does not exist!</div>";
		break;
	case 3:
		echo "<div class=\"alert-msg error-msg\">Invalid Username or Password.</div>";
		break;
	case 4:
		//Redirect user
		echo"<script type=\"text/javascript\">setTimeout(\"window.location = '".$redirect."';\",5000);</script>";
		echo "<div class=\"alert-msg success-msg\">Successfully logged in! If the page doesn't redirect in 5 seconds, click a button on the navbar.</div>";
		break;
	}
?>

						<div class=\"clearfix\"></div>
						</section>
					</fieldset>
<?php } ?>
					<form name="login-form" id="login-form" action="login.php?redirect=<?php echo strip_tags($redirect); ?>" method="post">
						<fieldset>
							<section>
								<input class="i-text required" type="text" name="username" placeholder="Username">
							</section>
							<section>
								<input class="i-text required" type="password" name="password" placeholder="Password">
							</section>
						</fieldset>
						<a href="forgotpass.php">Forgot your password?</a>
						<input class="i-button" type="submit" value="Login" />
					</form>
				</div>
			</div>
<?php
require('footer.html');
?>