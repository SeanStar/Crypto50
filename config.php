<?php
try {
	$db = new PDO('mysql:dbname=crypto50;host=localhost;charset=utf8', 'root', 'freecraft131099');
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}  catch(PDOException $e) {  
	echo "I'm sorry, Dave. I'm afraid I can't do that.";  
    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND); 
}

$sessionkey = '588768CD2CF45E5AE15FFC77769B7B780043CE8906D9C231D892C830B7C9CC32';
?>
