<?php

require_once 'DB.class.php';

$db = new DB();
$db->connect();

$table = "p_standard_games";

$games = $db->selectALL($table);

function updateplayer(){
	$player_instances = $db->selectALL('p_player_instance');

	for($i=0;$i<=(count($player_instances)-1);$i++){
		$username = $player_instances[$i]['username'];
		$accountid = $player_instances[$i]['accountid'];
		$weight = 1500;
		$duplicates = $db->select("p_players","accountid = '$accountid'");
		
		if(empty($duplicates)){
			$table = "p_players";
			$data = array(
				"accountid" => "'$accountid'",
				"username" => "'$username'",
				"weight" => "'$weight'"
			);
			$result = $db->insert($data, $table);
			
			echo $username."<br/>".$result." ".$i;
		}
	
	}
}


?>