<?php
    
class JSON{
	
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
	
}
?>