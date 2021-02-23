<?php 

class Repeaters {
	public $w5auu1;
	public $w5auu2;
	public $w5auu3;
	public $reportTime;

	function __construct() {
		$this->getJson("https://api.aprs.fi/api/get?name=W5AUU-1,W5AUU-2,W5AUU-3&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json");
		
		$jsonData = $this->getJson($this->jsonUrl); 
		$jsonObject = json_decode($jsonData, true);
		
		$this->$w5auu1 = $jsonObject["entries"][0];
		$this->$w5auu2 = $jsonObject["entries"][1];
		$this->$w5auu3 = $jsonObject["entries"][2];
		
		//$this->w5auu1 = new Repeater("W5AUU-1", "146.97 repeater", "146.97", 1, 5, 50, 2);
		//$this->w5auu2 = new Repeater("W5AUU-2", "OEM repeater shack", "147.03", 1, 5, 50, 2);
		//$this->w5auu3 = new Repeater("W5AUU-3", "Greenbrier repeater shack", "146.625", 1, 3, 100, 2);

		date_default_timezone_set("America/Chicago");
		$this->reportTime = date("d/m/Y h:i:sa");
	}
	
	function getJson($url) {
			// cache files are created like cache/abcdef123456...
			$cacheFile = 'cache' . DIRECTORY_SEPARATOR . md5($url);

			if (file_exists($cacheFile)) {
					$fh = fopen($cacheFile, 'r');
					$cacheTime = trim(fgets($fh));

					// if data was cached recently, return cached data
					if ($cacheTime > strtotime('-5 minutes')) {
							return fread($fh,filesize("$cacheFile"));
					}

					// else delete cache file
					fclose($fh);
					unlink($cacheFile);
			}

			$json = file_get_contents($url);

			$fh = fopen($cacheFile, 'w');
			fwrite($fh, time() . "\n");
			fwrite($fh, $json);
			fclose($fh);
			return $json;
	}
}

?>