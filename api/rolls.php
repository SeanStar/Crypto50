<?php
require('../config.php');
require('../checksession.php');
//Check if variables are set before we do anything else
if(isset($_POST['count']) && isset($_POST['bet_id'])) {
	//Select all rows from bets
	$query = $db->prepare('SELECT * FROM bets ORDER BY bet_id DESC LIMIT 0,:count');
	$query->execute(array('count' => $_POST['count']));
	$array = $query->fetchAll();	
	//Echo json encoded array
	echo json_encode($array);
}
?>