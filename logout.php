<?php
setcookie("username", "", time()-3600);
setcookie("sessionid", "", time()-3600);
header('Location: index.php');
require('config');
require('checksession.php');
function genRandomString() {
    $length = 32;
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $string = "";    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}
if($validsession) {
$sessionid = genRandomString();
$query = $db->prepare('UPDATE users SET sessionid= :sessionid WHERE username= :username');
$query->execute(array('sessionid' => $sessionid, 'username' => $username));
}
?>