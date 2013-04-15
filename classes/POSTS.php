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
if(@$_POST['ADD_STUFF']){
	
	$username = $_POST['username'];
	$username = str_replace("'", "", $username);
	$username = str_replace("\"", "", $username);
	$accountid = $_POST['accountid'];
	$finishrank = $_POST['finishrank'];
	$gameid = $_POST['gameid'];
	
	if(($accountid!=0) && ($accountid!="0") && ($finishrank!=0) &&($finishrank!="0")){
		$query = "UPDATE p_games SET finishrank".$finishrank." = '$accountid' WHERE gameid = '$gameid'";
		$gameupdate = mysql_query($query)or die(mysql_error());
		
		$data = array(
			"accountid" => "'$accountid'",
			"username" => "'$username'",
			"gameid" => "'$gameid'",
			"finishrank" => "'$finishrank'"
		);
		
		$playerinsert = $db->insert($data, "p_players" );
		echo $gameupdate." ".$playerinsert;
	}
}
//------------------------------------------------------------------------------------------------
//  PRODUCTS DATABASE RETRIEVAL
if(@$_POST['GET_GAMES']){
	
	
	$result = $db->selectALL('p_games');
	$length = count($result);
	$string = "";
	$string = jStart($string);
	$string = jSuccess($string);
	$string = JOpenArray($string, "Games");
	for($i=0;$i<$length;$i++){
		$string = jStart($string);
		$string = jAdd($string, "gameid", $result[$i]['gameid']);
		$string = jLast($string);
		$string = jClose($string);
	}
	$string = jLast($string);	
	$string = jCloseArray($string);
	$string = jLast($string);
	$string = jEnd($string);
	echo $string;
}

//-------------------------------------------------------------------------------
//JSON STRING CONSTRUCTOR FUNCTIONS
function jStart($string){
	return $string."{";
}
function jSuccess($string){
	return $string."\"success\":true,";
}
function jOpen($string,$value){
	return $string."\"$value\":{";
}
function jAdd($string, $name, $value){
	return $string."\"$name\":\"$value\",";
}
function jLast($string){
	$length = strlen($string);
	$string = substr($string, 0, $length -1);
	return $string;
}
function jClose($string){
	return $string."},";
}
function jEnd($string){
	return $string."}";
}
function jOpenArray($string, $name){
	return $string."\"$name\":[";
}
function jCloseArray($string){
	return $string."],";
}
 
?>