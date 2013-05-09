<?php
//Post.class.php
require_once 'PlayerData.class.php';

$pd = new PlayerData();

echo "";
if(@$_POST['Load']){
	
	$players = $pd->GetAllPlayers(TRUE);

	echo $players;
	
}
if(@$_POST['Post_Update_Games']){
	$from_api_gameids = $_POST['gameids'];
	$from_api_gamenames = $_POST['gamenames'];
	$result = $pd->CheckForNewGames($from_api_gameids,$from_api_gamenames);
	echo $result;
}
if(@$_POST['NewFinish']){
	$gameid = $_POST['gameid'];
	$accountid = $_POST['accountid'];
	$username = $_POST['username'];
	$finishrank = $_POST['finishrank'];
	$planets = $_POST['planets'];
	$militaryscore = $_POST['militaryscore'];
	$turnjoined = $_POST['turnjoined'];
	$turnfinish = $_POST['turnfinish'];
	$ships = $_POST['ships'];
	$starbases = $_POST['starbases'];
	
	$result = $pd->UpdateData($gameid, $accountid, $username, $finishrank, $planets, $militaryscore, $turnjoined, $turnfinish, $ships, $starbases);
	echo $result;
}

?>