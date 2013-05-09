<?php 
require_once 'DB.class.php';
require_once 'JSON.class.php';

class PlayerData{
	
	public function CheckForNewGames($from_api_gameids, $from_api_gamenames){
		//This Function will return an array of gameids that are not in the database
		//If all the ids are in the DB already - it will return a 0.
		$db = new DB();
		$db->connect();
		$current_games = $this->GetGames();
		
		if(empty($current_games)){
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
				$name = $from_api_gamenames[$i];
				$name = str_replace("'", "", $name);
				$name = str_replace("\"", "", $name);
				$data = array(
					"gameid"=>"'$from_api_gameids[$i]'",
					"name"=>"'$name'"
				);
				$db->insert($data,$table);
			}
		}
		
		if(empty($not_in_db))
			return 0;
		else
			return $this->GameIDsToJson($not_in_db);
		
	}
	public function UpdateData($gameid, $accountid, $username, $finishrank, $planets, $militaryscore, $turnjoined, $turnfinish, $ships, $starbases){
		$username = str_replace("'", "", $username);
		$username = str_replace("\"", "", $username);
		
		$db = new DB();
		$db->connect();
		
		$table = "p_standard_games";
		
		$data = array(
			"finishrank$finishrank"=>"'$accountid'"
		);
		
		$db->update($data,$table,"gameid='$gameid'");
		
		$table = "p_standard_players";
		
		$is_player = $db->select($table, "accountid = '$accountid'");
		
		$game_weight = $this->CalculatePoints($finishrank, $planets, $militaryscore, $turnjoined, $turnfinish, $ships, $starbases);
		$game_turns = $turnfinish - $turnjoined;
		
		if(empty($is_player)){
			$data = array(
				"accountid"=>"'$accountid'",
				"username"=>"'$username'",
				"weight"=>"'$game_weight'",
				"game_count"=>"'1'",
				"total_planets"=>"'$planets'",
				"total_militaryscore"=>"'$militaryscore'",
				"total_turns"=>"'$game_turns'",
				"total_ships"=>"'$ships'",
				"total_starbases"=>"'$starbases'"
			);
			$db->insert($data,$table);
		}
		else{
			
			$weight = $is_player['weight'] + $game_weight;
			$total_planets = $is_player['total_planets'] + $planets;
			$total_militaryscore = $is_player['total_militaryscore'] + $militaryscore;
			$total_turns = $is_player['total_turns'] + $game_turns;
			$total_ships = $is_player['total_ships'] + $ships;
			$total_starbases = $is_player['total_starbases'] + $starbases;
			
			$data = array(
				"weight"=>"'$weight'",
				"game_count"=>"game_count + 1",
				"total_planets"=>"'$total_planets'",
				"total_militaryscore"=>"'$total_militaryscore'",
				"total_turns"=>"'$total_turns'",
				"total_ships"=>"'$total_ships'",
				"total_starbases"=>"'$total_starbases'"
			);
			$db->update($data, $table, "accountid = '$accountid'");
		}
		
		$table = "p_player_instance";
		$data = array(
			"gameid"=>"'$gameid'",
			"accountid"=>"'$accountid'",
			"username"=>"'$username'",
			"finishrank"=>"'$finishrank'",
			"planets"=>"'$planets'",
			"militaryscore"=>"'$militaryscore'",
			"turnjoined"=>"'$turnjoined'",
			"turnfinish"=>"'$turnfinish'",
			"ships"=>"'$ships'",
			"starbases"=>"'$starbases'",
			"game_weight"=>"$game_weight"
		);
		$db->insert($data,$table);
		
		return 1;
		
	}
	public function CalculatePoints($finishrank, $planets, $militaryscore, $turnjoined, $turnfinish, $ships, $starbases){
		
		$m_score = $militaryscore * 0.00005;
		$m_score = round($m_score, 2);
		
		$s_score = $ships * 0.5;
		$s_score = round($s_score, 2);
		
		$sb_score = $starbases * 0.25;
		$sb_score = round($sb_score, 2);
		
		$t_score = $turnfinish - $turnjoined;
		
		$p_score = $planets;
		
		$fn_score = 0;
		
		if($finishrank == 1){
			$fn_score = 300;
		}
		if($finishrank == 2){
			$fn_score = 200;
		}
		if($finishrank == 3){
			$fn_score = 150;
		}
		if($finishrank == 4){
			$fn_score = 100;
		}
		if($finishrank == 5){
			$fn_score = 75;
		}
		if($finishrank == 6){
			$fn_score = 60;
		}
		if($finishrank == 7){
			$fn_score = 50;
		}
		if($finishrank == 8){
			$fn_score = 40;
		}
		if($finishrank == 9){
			$fn_score = 30;
		}
		if($finishrank == 10){
			$fn_score = 20;
		}
		if($finishrank == 11){
			$fn_score = 10;
		}
		
		$game_weight = $fn_score + $m_score + $p_score + $s_score + $sb_score + $t_score;
		
		return $game_weight;
		
	}
	public function GetAllPlayers($convertToJson = false){
		
		$db = new DB();
		$db->connect();
		
		$table = "p_standard_players";
		
		$players = $db->selectALL($table);
		if(!empty($players)){
			if($convertToJson){
				
				$players = $this->PlayersToJson($players);
				return $players;
			
			}
			else{
				return $players;
			}
		}
		return 0;
	}
	public function GetGames(){
		
		$db = new DB();
		$db->connect();
		$table = "p_standard_games";
		
		$games = $db->selectALL($table);
		
		return $games;
	}
	//Function returns all the Player Data as a Json String
	public function PlayersToJson($players){
		$total_games = 0;
		$total_planets = 0;
		$total_ships = 0;
		$total_starbases = 0;
		$total_military = 0;
		$total_turns = 0;
		$total_points = 0;
		
		$json = new JSON();
		
		$length = count($players);
		for($i=0;$i<$length;$i++){
			$total_points = $total_points + $players[$i]['weight'];
			$total_games = $total_games + $players[$i]['game_count'];
			$total_planets = $total_planets + $players[$i]['total_planets'];
			$total_military = $total_military + $players[$i]['total_militaryscore'];
			$total_turns = $total_turns + $players[$i]['total_turns'];
			$total_ships = $total_ships + $players[$i]['total_ships'];
			$total_starbases = $total_starbases + $players[$i]['total_starbases'];
		}
		$string = "";
		$string = $json->jStart($string);
		$string = $json->jSuccess($string);
		$string = $json->JOpenArray($string, "players");
		for($i=0;$i<$length;$i++){
			$string = $json->jStart($string);
			$string = $json->jAdd($string, "accountid", $players[$i]['accountid']);
			$string = $json->jAdd($string, "username", $players[$i]['username']);
			$string = $json->jAdd($string, "weight", $players[$i]['weight'] );
			$weightP = ($players[$i]['weight']/$total_points)*100;
			$weightP = round($weightP, 4);
			$string = $json->jAdd($string, "weightP", $weightP);
			$string = $json->jAdd($string, "game_count", $players[$i]['game_count'] );
			$gamesP = ($players[$i]['game_count']/$total_games)*100;
			$gamesP = round($gamesP,4);
			$string = $json->jAdd($string, "gamesP", $gamesP);
			$string = $json->jAdd($string, "total_planets", $players[$i]['total_planets'] );
			$planetsP = ($players[$i]['total_planets']/$total_planets)*100;
			$planetsP = round($planetsP,4);
			$string = $json->jAdd($string, "planetsP", $planetsP);
			$string = $json->jAdd($string, "total_militaryscore", $players[$i]['total_militaryscore'] );
			$militaryP = ($players[$i]['total_militaryscore']/$total_military)*100;
			$militaryP = round($militaryP,4);
			$string = $json->jAdd($string, "militaryP", $militaryP);
			$string = $json->jAdd($string, "total_turns", $players[$i]['total_turns'] );
			$turnsP = ($players[$i]['total_turns']/$total_turns)*100;
			$turnsP = round($turnsP, 4);
			$string = $json->jAdd($string, "turnsP", $turnsP);
			$string = $json->jAdd($string, "total_ships", $players[$i]['total_ships'] );
			$shipsP = ($players[$i]['total_ships']/$total_ships)*100;
			$shipsP = round($shipsP, 4);
			$string = $json->jAdd($string, "shipsP", $shipsP);
			$string = $json->jAdd($string, "total_starbases", $players[$i]['total_starbases'] );
			$starbasesP = ($players[$i]['total_starbases']/$total_starbases)*100;
			$starbasesP = round($starbasesP, 4);
			$string = $json->JAdd($string, "starbasesP", $starbasesP);
			$string = $json->jLast($string);
			$string = $json->jClose($string);
		}
		$string = $json->jLast($string);	
		$string = $json->jCloseArray($string);
		$string = $json->jOpen($string, "totals");
		$string = $json->jAdd($string, "totalgames", $total_games);
		$string = $json->jAdd($string, "totalplanets", $total_planets);
		$string = $json->jAdd($string, "totalturns", $total_turns);
		$string = $json->jAdd($string, "totalships", $total_ships);
		$string = $json->jAdd($string, "totalstarbases", $total_starbases);
		$string = $json->jAdd($string, "totalmilitary", $total_military);
		$string = $json->jAdd($string, "totalpoints", $total_points);
		$string = $json->jLast($string);
		$string = $json->jEnd($string);
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

?>