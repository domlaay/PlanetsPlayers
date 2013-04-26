<?php

class Player{
	
	public $accountid;
	public $weight;
	public $expected;
	public $actual;
	
	public function updateID($a_id){
		$this->accountid = $a_id;
	}
	public function updateW($w){
		$this->weight = $w;
	}
	public function updateE($e){
		$this->expected = $e;
	}
	public function updateA($a){
		$this->actual = $a;
	}
	public function getID(){
		return $this->accountid;
	}
	public function getW(){
		return $this->weight;
	}
	public function getE(){
		return $this->expected;
	}
	public function getA(){
		return $this->actual;
	}
}
    
?>