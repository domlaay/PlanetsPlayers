<?php

require_once 'DB.class.php';

$db = new DB();
$db->connect();

$player_instances = $db.select('p_player_instance');

for($i=0;$i<=count($player_instances);$i++){
	echo $player_instances[$i];
}

?>