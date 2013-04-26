<?php 
require_once 'DB.class.php';
require_once 'JSON.class.php';
require_once 'Player.class.php';

class PlayerData{
	
	public function CheckForUpdate(){
		//call local DB and get gameIDs for standard games
		//call api for games list with 11 slots - 
		// compare number of entries - 
	}
	public function GetAllPlayers($convertToJson = false){
		
		$db = new DB();
		$db->connect();
		
		$table = "p_players";
		
		$players = $db->selectALL($table);
		
		if($convertToJson){
			
			$players = $this->PlayersToJson($players);
			return $players;
			
		}
		else{
			return $players;
		}
		
	}
	public function GetPLayer($accountid){
		$db = new DB();
		$db ->connect();
		
		$table = "p_players";
		
		$player = $db->select($table,"accountid ='$accountid'");
		
		return $player;
	}
	public function GetGames($converToJson = false, $ranked = -1){
		$db = new DB();
		$db->connect();
		
		$table = "p_games";
		//if ranked = -1 all Games will be returned
		if($ranked == -1){
			$games = $db->selectALL($table);
		}
		//if ranked = 0 all the Unranked Games will be returned
		//if ranked = 1 all the Ranked Games will be returned
		if($ranked == 0 || $ranked == 1){
			$games = $db->select($table, "ranked='$ranked'");
		}
		
		if($converToJson){
			return $games;
		}
		else{
			return $games;
		}
		
		
	}
	//This Function will calculate the rankings of all Unranked Games and upate the DB
	public function CalculateUnRanked(){
		
		$games = $this->GetGames(false, 0);
		
		$length = count($games);
		
		//CRAZY FOR LOOP
		for($i=0; $i<$length; $i++){
			//$gameid = $games[$i]['gameid'];
			$finishers = array();
			$weights = array();
			
			for($k=0;$k<11;$k++){
				
				$fn = $k+1;
				if($games[$i]["finishrank$fn"] != 0){
					
					$p_d = $this->GetPLayer($games[$i]["finishrank$fn"]);
					$accountid = $p_d['accountid'];
					$weight = $p_d['weight'];
					$p = new Player();
					$p -> updateID($accountid);
					$p -> updateW($weight);
					$p -> updateA($fn);
					array_push($weights, $weight);
					array_push($finishers, $p);
					
				}
			}
		 	
			$fl = count($finishers);
			
			for($k=0;$k<($fl - 1);$k++){
				$EX = $k + 1;
				$p_temp1 = $finishers[$k];
				$p_temp2 = $finishers[$k + 1];
				$p_temp1->updateE($EX);
				$p_temp2->updateE($EX + 1);
				$t1W = $p_temp1->getW();
				$t2W = $p_temp2->getW();
				
				if($t1W == $t2W){
					$random = rand(1, 100);
					if($random > 50){
						$p_temp2->updateE($EX);
						$p_temp1->updateE($EX + 1);
						$finishers[$k] = $p_temp2;
						$finishers[$k + 1] = $p_temp1;
					}
				}
				if($t1W < $t2W){
					$p_temp2->updateE($EX);
					$p_temp1->updateE($EX + 1);
					$finishers[$k] = $p_temp2;
					$finishers[$k + 1] = $p_temp1;
				}
			}
			
			
		}
		// END OF CRAZY FOR LOOP
		
	}
	// Function Returns average Weight
	function averageweight($weights){
		$wei_length = count($weights);
		$totalweight = 0;
		for($t=0;$t<$wei_length;$t++){
			$totalweight = $totalweight + $weights[$t];
		}
		$average = $totalweight / $wei_length;
		return $average;
	}
	//Function returns all the Player Data as a Json String
	public function PlayersToJson($players){
		
		$json = new JSON();
		
		$length = count($players);
		$string = "";
		$string = $json->jStart($string);
		$string = $json->jSuccess($string);
		$string = $json->JOpenArray($string, "Players");
		for($i=0;$i<$length;$i++){
			$string = $json->jStart($string);
			$string = $json->jAdd($string, "username", $players[$i]['username']);
			$string = $json->jAdd($string, "weight", $players[$i]['weight'] );
			$string = $json->jLast($string);
			$string = $json->jClose($string);
		}
		$string = $json->jLast($string);	
		$string = $json->jCloseArray($string);
		$string = $json->jLast($string);
		$string = $json->jEnd($string);
		
		return $string;
			
	}
	public function GamesToJson($games){
		
	}
}

$pd = new PlayerData();


if(@$_POST['Load']){
	
	$players = $pd->GetAllPlayers(TRUE);

	echo $players;
	
}
if(@$_POST['Calc']){
	
	$result = $pd->CalculateUnRanked();
	
	echo $result;
}

?>