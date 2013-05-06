<?php 
require_once 'DB.class.php';
require_once 'JSON.class.php';
require_once 'Player.class.php';

class PlayerData{
	
	public function UpdateStandardGames($from_api_gameids, $from_api_gamenames){
		//This Function will return an array of gameids that are not in the database
		//If all the ids are in the DB already - it will return a 0.
		$db = new DB();
		$db->connect();
		$current_games = $this->GetGames();
		if($current_games == 0){
			for($i=0; $i<count($from_api_gameids); $i++){
				
				$name = $from_api_gamenames[$i];
				$name = str_replace("'", "", $name);
				$name = str_replace("\"", "", $name);
				$data = array(
					"gameid"=>"'$from_api_gameids[$i]'",
					"name"=>"'$name'"
				);
				$table = "p_standard_games";
				$db->insert($data,$table);
			}
			return $this->GameIDsToJson($from_api_gameids);
		}
		
		$from_db_gameids = array();
		for($i=0; $i<count($current_games); $i++){
			array_push($from_db_gameids, $current_games[$i]['gameid']);
		}	
		
		$table = "p_standard_games";
		$not_in_db = array();
		for($i=0; $i<count($from_api_gameids); $i++){
			if(!in_array($from_api_gameids[$i], $from_db_gameids)){
				array_push($not_in_db, $from_api_gameids[$i]);
				$data = array(
					"gameid"=>"'$from_api_gameids[$i]'",
					"name"=>"'$from_api_gamenames[$i]'"
				);
				$db->insert($data,$table);
			}
		}
		
		if(empty($not_in_db))
			return 0;
		else
			return $this->GameIDsToJson($not_in_db);
		
	}
	public function UpdateContinued($gameid,$accountid,$username,$finishrank){
		$username = str_replace("'", "", $username);
		$username = str_replace("\"", "", $username);
		
		$db = new DB();
		$db->connect();
		
		$table = "p_standard_games";
		
		$data = array(
			"finishrank$finishrank"=>"'$accountid'"
		);
		$result1 = $db->update($data,$table,"gameid='$gameid'");
		if($result1)
		$result1 = " Game Row Update Successful ";
		$table = "p_standard_players";
		$is_player = $db->select($table, "accountid = '$accountid'");
		$result2;
		if(empty($is_player)){
			$data = array(
				"accountid"=>"'$accountid'",
				"username"=>"'$username'",
				"weight"=>"'1500'",
				"game_count"=>"'1'",
				"drop_count"=>"'0'"
			);
			$result2 = $db->insert($data,$table);
		}
		else{
			$data = array(
				"game_count"=>"game_count + 1"
			);
			$result2 = $db->update($data, $table, "accountid = '$accountid'");
			if($result2)
			$result2 = " Player Update Successful ";
		}
		
		$table = "p_player_instance";
		$data = array(
			"gameid"=>"'$gameid'",
			"accountid"=>"'$accountid'",
			"username"=>"'$username'",
			"finishrank"=>"'$finishrank'"
		);
		$result3 = $db->insert($data,$table);
		
		return $result1.$result2.$result3;
		
	}
	public function GetAllPlayers($convertToJson = false){
		
		$db = new DB();
		$db->connect();
		
		$table = "p_standard_players";
		
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
		$table = "p_standard_games";
		//if ranked = -1 all Games will be returned
		if($ranked == -1){
			$games = $db->selectALL($table);
		}
		//if ranked = 0 all the Unranked Games will be returned
		//if ranked = 1 all the Ranked Games will be returned
		if($ranked == 0 || $ranked == 1){
			$games = $db->select($table, "ranked='$ranked'");
		}
		
		if(!empty($games)){
			if($converToJson){
				$this->GamesToJson($games);
				return $games;
			}
			else{
				return $games;
			}
		}
		else return 0;
		
	}
	function Drops($gameid){
		$db = new DB();
		$db->connect();
		
		$table = "p_drop_instance";
	
		$drops = $db -> select($table, "gameid = '$gameid'");
		
		//return count($drops);
		if(count($drops)>0){
			
			for($i=0;$i<count($drops);$i++){
				
				$accountid = $drops[$i]['accountid'];
				$table2 = "p_players";
				$player = $db -> select($table2, "accountid = '$accountid'");
				$weight = $player['weight'];
				$weight = $weight - 25;
				$data = array(
					"weight"=>"'$weight'"
				);
				$db->update($data,$table2,"accountid = '$accountid'");
			}
			return "something";
		}
		return "nothing";
		
	}
	function UpdatePlayerWeight($accountid, $weight){
		$db = new DB();
		$db->connect();
		
		$table="p_players";
		$data = array(
			"weight"=>"'$weight'"
		);
		$db->update($data,$table,"accountid = '$accountid'");
	}
	function UpdateRankedGame($gameid){
		$db = new DB();
		$db->connect();
		
		$table="p_games";
		$ranked = 1;
		$data = array(
			"ranked"=>"'$ranked'"
		);
		$db->update($data,$table,"gameid='$gameid'");
	}
	//This Function will calculate the rankings of all Unranked Games and update the DB
	// Meat and potatoes of the project -
	public function CalculateUnRanked(){
		
		$games = $this->GetGames(false, 0);
		$length = count($games);
		//FOR EACH GAMEID THAT IS UNRANKED IN THE DATA BASE
		for($i=0; $i<$length; $i++){
			//Find The Players that Dropped from the Game and subtract points
			$gameid = $games[$i]['gameid'];
			//$this->Drops($gameid);
			
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
			//SORT based on weight - Heightest to Lowest
			usort($finishers, array($this,'sortPlayers'));
			
			//This block of code calculates the "traveling score"
			//Take the absolute value of the average weights of the finishers = $distance
			//Then multiple the distance by the expected finish minus the actual finish all divided by 3 
			//Divide by 3 is to keep the traveling weight within reason and not be much larger than the finish points
			$average = $this->averageweight($weights);
			$fl = count($finishers);
			for($k=0; $k<$fl; $k++){ 
				 
				 $finishers[$k]->updateE($k + 1);	
				 $weight = $finishers[$k]->getW();
				 $distance =  abs($weight - $average);
				 $expected = $finishers[$k]->getE();
				 $actual = $finishers[$k]->getA();
				 $travelingScore = ($distance * ($expected - $actual)) / 3;
				 $weight = $weight + $travelingScore;
				 $finishers[$k]->updateW($weight);
				 
			}
			
			for($k=0; $k<$fl; $k++){
				
				$finish = $finishers[$k]->getA();
				$weight = $finishers[$k]->getW();
				
				if($finish == 1){
					$finishers[$k]->updateW($weight+300);
				}
				if($finish == 2){
					$finishers[$k]->updateW($weight+200);
				}
				if($finish == 3){
					$finishers[$k]->updateW($weight+150);
				}
				if($finish == 4){
					$finishers[$k]->updateW($weight+100);
				}
				if($finish == 5){
					$finishers[$k]->updateW($weight+75);
				}
				if($finish == 6){
					$finishers[$k]->updateW($weight+60);
				}
				if($finish == 7){
					$finishers[$k]->updateW($weight+50);
				}
				if($finish == 8){
					$finishers[$k]->updateW($weight+40);
				}
				if($finish == 9){
					$finishers[$k]->updateW($weight+30);
				}
				if($finish == 10){
					$finishers[$k]->updateW($weight+20);
				}
				if($finish == 11){
					$finishers[$k]->updateW($weight+10);
				}
			}
			
			for($k=0; $k<$fl; $k++){
				$accountid = $finishers[$k]->getID();
				$weight = $finishers[$k]->getW();
				$this->UpdatePlayerWeight($accountid, $weight);
			}
			
			$this->UpdateRankedGame($gameid);
			
		}
		// END FOR LOOP FOR EACH GAME
		return "Calculations Complete";
		
	}
	function sortPlayers($a,$b){
		if($a->getW() == $b->getW()){
			return 0;
		}
		if($a->getW() < $b->getW()){
			return 1;
		}
		if($a->getW() > $b->getW()){
			return -1;
		}
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
	function newDrop($accountid,$gameid){
		$db = new DB();
		$db->connect();
		$data = array(
			"accountid"=>"'$accountid'",
			"gameid"=>"'$gameid'"
		);
		$table = "p_drop_instance";
		$result = $db->insert($data,$table);
		return $result;
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
			$string = $json->jAdd($string, "accountid", $players[$i]['accountid']);
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
		
		$json = new JSON();
		
		$length = count($games);
		$string = "";
		$string = $json->jStart($string);
		$string = $json->jSuccess($string);
		$string = $json->JOpenArray($string, "games");
		for($i=0;$i<$length;$i++){
			$string = $json->jStart($string);
			$string = $json->jAdd($string, "gameid", $games[$i]['gameid']);
			$string = $json->jAdd($string, "name", $games[$i]['game']);
			$string = $json->jAdd($string, "finishrank1", $games[$i]['finishrank1'] );
			$string = $json->jAdd($string, "finishrank2", $games[$i]['finishrank2'] );
			$string = $json->jAdd($string, "finishrank3", $games[$i]['finishrank3'] );
			$string = $json->jAdd($string, "finishrank4", $games[$i]['finishrank4'] );
			$string = $json->jAdd($string, "finishrank5", $games[$i]['finishrank5'] );
			$string = $json->jAdd($string, "finishrank6", $games[$i]['finishrank6'] );
			$string = $json->jAdd($string, "finishrank7", $games[$i]['finishrank7'] );
			$string = $json->jAdd($string, "finishrank8", $games[$i]['finishrank8'] );
			$string = $json->jAdd($string, "finishrank9", $games[$i]['finishrank9'] );
			$string = $json->jAdd($string, "finishrank10", $games[$i]['finishrank10'] );
			$string = $json->jAdd($string, "finishrank11", $games[$i]['finishrank11'] );
			$string = $json->jAdd($string, "ranked", $games[$i]['ranked'] );
			$string = $json->jLast($string);
			$string = $json->jClose($string);
		}
		$string = $json->jLast($string);	
		$string = $json->jCloseArray($string);
		$string = $json->jLast($string);
		$string = $json->jEnd($string);
		
		return $string;
		
	}
	public function GameIDsToJson($gameids){
		$json = new JSON();
		
		$length = count($gameids);
		$string = "";
		$string = $json->jStart($string);
		$string = $json->jSuccess($string);
		$string = $json->JOpenArray($string, "gameids");
		for($i=0;$i<$length;$i++){
			$string = $json->jStart($string);
			$string = $json->jAdd($string, "gameid", $gameids[$i]);
			$string = $json->jLast($string);
			$string = $json->jClose($string);
		}
		$string = $json->jLast($string);	
		$string = $json->jCloseArray($string);
		$string = $json->jLast($string);
		$string = $json->jEnd($string);
		
		return $string;
	}
}

$pd = new PlayerData();


if(@$_POST['Load']){
	
	$players = $pd->GetAllPlayers(TRUE);

	echo $players;
	
}
if(@$_POST['Post_Update_Games']){
	$from_api_gameids = $_POST['gameids'];
	$from_api_gamenames = $_POST['gamenames'];
	$result = $pd->UpdateStandardGames($from_api_gameids,$from_api_gamenames);
	echo $result;
}
if(@$_POST['NewStuff']){
	$gameid = $_POST['gameid'];
	$accountid = $_POST['accountid'];
	$username = $_POST['username'];
	$finishrank = $_POST['finishrank'];
	
	$result = $pd->UpdateContinued($gameid, $accountid, $username, $finishrank);
	echo $result;
}
if(@$_POST['Calc']){
	
	$result = $pd->CalculateUnRanked();
	//$result = $pd->Drops(21499);
	echo $result;
}
if(@$_POST['GetTemp']){
	$result = $pd->GetAllPlayers(TRUE);
	echo $result;
}
if(@$_POST['NewDrop']){
	
	$accountid = $_POST['accountid'];
	$gameid = $_POST['gameid'];
	
	$result = $pd->newDrop($accountid, $gameid);
	echo $result;
}	
?>