<?php
require('../config.php');
require('../checksession.php');

session_start();
if ($validsession) {
$_SESSION['name'] = $username;  
if(isset($_SESSION['name'])){  
    $text = $_POST['text'];  
    $log = "../log.html";
	$linecount = 0;
	$handle = fopen($log, "r");
	while(!feof($handle)){
		$line = fgets($handle);
		$linecount++;
	}
	fclose($handle);
	
	if($linecount > 250) {	
	$line_to_strip = ($linecount - 150);
	$file = file($log);
	$file = array_splice($file, $line_to_strip, $linecount);
	$fp = fopen($log, 'w');
	fwrite($fp, implode('', $file));
	fclose($fp);
	}

	$fp = fopen($log, 'a');  
    fwrite($fp, "<div class='msgln'>(".date("g:i A").") <b>".$_SESSION['name']."</b>: ".stripslashes(htmlspecialchars($text))."<br></div>\n");  
    fclose($fp); 
}
}
?> 