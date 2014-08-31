<?php
$validsession = FALSE;
if (isset($_COOKIE['username'])) {
	$username = $_COOKIE['username'];
	$sessionidc = $_COOKIE['sessionid'];
	$check = $db->prepare('SELECT * FROM users WHERE username= :username');
	$check->execute(array('username' => $username));
	$check_data = $check->fetchAll();
	$checksession = $check_data[0]['sessionid'];
	$userid = $check_data[0]['user_id'];
	$checksessionhash = hash('sha512', $sessionkey.$checksession);	
	if($checksessionhash == $sessionidc) {
		$validsession = TRUE;
	}
}
?>