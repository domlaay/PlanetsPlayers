<?php
//Accept POSTS
require_once 'DB.class.php';

$db = new DB();
$db->connect();

if(@$_POST['gameID']){
	
	$data = array(
		"gameid" => $_POST['gameID']
	); 
	$table = $_POST['table'];
	//echo($data['gameid'].$table);
	$value = $db->insert($data, $table);
}

 
?>