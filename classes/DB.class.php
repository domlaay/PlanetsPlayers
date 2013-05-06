<?php
//DB.class.php
ini_set('max_execution_time', 300);

class DB {
	
	protected $db_name = 'planets2';
	protected $db_user = 'root';
	protected $db_pass = 'culmanq2';
	protected $db_host = 'localhost';
	
	public function connect(){
		$DB_Connection = mysql_connect($this->db_host, $this->db_user, $this->db_pass) or exit( mysql_error() );
		mysql_select_db($this->db_name);
		
		return true;
	}
	
	public function processRowSet($rowSet, $singleRow=false){
		$resultArray = array();
		while($row = mysql_fetch_assoc($rowSet)){
			array_push($resultArray, $row);
		}
		
		if($singleRow === TRUE){
			return $resultArray[0];
		}
		
		return $resultArray;
	}
	
	public function select($table, $where){
		$query = "SELECT * FROM $table WHERE $where";
		$result = mysql_query($query);
		
		if(mysql_num_rows($result)==1){
			return $this->processRowSet($result,TRUE);
		}
		
		return $this->processRowSet($result);
	}
	
	public function selectALL($table){
		$query = "SELECT * FROM $table";
		$result = mysql_query($query);
		if(mysql_num_rows($result)==1){
			return $this->processRowSet($result,TRUE);
		}
		
		return $this->processRowSet($result);
	}
	public function update($data, $table, $where){
		foreach($data as $column => $value){
			$query = "UPDATE $table SET $column = $value WHERE $where";
			mysql_query($query) or die(mysql_error());
		}
		
		return TRUE;
	}
	
	public function insert($data, $table){
		
		$columns = "";
		$values  = "";
		
		foreach($data as $column => $value){
			$columns.= ($columns == "") ? "" : ", ";
			$columns.= $column;
			$values .= ($values == "") ? "" : ", ";
			$values .= $value;
		}
		
		$query = "INSERT INTO $table ($columns) VALUES ($values)";
		mysql_query($query) or die(mysql_error());
		return mysql_insert_id();
	}
}

?>