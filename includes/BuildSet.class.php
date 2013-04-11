<?php
//buildset.class.php
class BuildSet {
	
	
	public function getGames(){
		$url = 'http://api.planets.nu/games/list';
		$contents = @file_get_contents($url);
		echo $contents;
	}
	
}

$buildset = new BuildSet();
$buildset->getGames();
?>
<!--
	
$data="";
		$header = "GET /games/list HTTP/1.0\r\n";
	    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $header .= "Content-Length: " . strlen($data) . "\r\n\r\n";
	    $fp = fsockopen('ssl://api.planets.nu', 443, $errno, $errstr, 30);
	
	    if(!$fp)
	      return "ERROR. Could not open connection";
	    else {
	      fputs ($fp, $header.$data);
	      while (!feof($fp)) {
	        $res .= fread ($fp, 1024);
	      }
	      fclose($fp);
	    }
	    echo $res;
	  -->
	  