<?php 

class Repeaters {
	public $jsonObject;
	public $jsonUrl;
	public $reportTime;
	public $w5auu1;
	public $w5auu2;
	public $w5auu3;
	public $telemetry;

	function __construct() {
		$this->jsonUrl = "https://api.aprs.fi/api/get?name=W5AUU-1,W5AUU-2,W5AUU-3&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";		
		$jsonData = $this->getJson($this->jsonUrl); 
		$this->jsonObject = json_decode($jsonData, true);
		
		$this->w5auu1 = $this->jsonObject["entries"][0];
		$this->w5auu2 = $this->jsonObject["entries"][1];
		$this->w5auu3 = $this->jsonObject["entries"][2];
		
		$this->telemetry = new stdClass();
		
		$this->parseTelemetry("W5AUU-1", 1, 5, 2);
		$this->parseTelemetry("W5AUU-2", 1, 5, 2);
		$this->parseTelemetry("W5AUU-3", 1, 3, 2);

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
	function parseTelemetry($repeaterName, $telemetryVoltageChannel, $telemetryGridPowerStatusChannel, $telemetryTempuratureChannel) {
		$this->telemetry->$repeaterName = new stdClass();
		$telemetryUrl = "https://aprs.fi/telemetry/" . $repeaterName . "&key=100665.Mj8HjUvXqEHYjrV6";
		$telemetryPage = file_get_contents($telemetryUrl,true);

		$this->telemetry->$repeaterName->telemetryURL = $telemetryUrl;
		echo "<pre>$telemetryPage</pre>";
		
		// voltage
		preg_match("/Channel\s" . $telemetryVoltageChannel . "\:\s[0-9]+/", $telemetryPage, $match);
		$this->telemetry->$repeaterName->voltage = number_format(substr($match[0],11));

		// grid power status
		preg_match("/Channel\s" . $telemetryGridPowerStatusChannel . "\:\s[0-9]+/", $telemetryPage, $match);
		$this->telemetry->$repeaterName->powerValue = number_format(substr($match[0],11));
		
		// tempurature
		preg_match("/Channel\s" . $telemetryTempuratureChannel . "\:\s[0-9]+/", $telemetryPage, $match);
		$this->telemetry->$repeaterName->tempurature = number_format(substr($match[0],11));
	}
}

?>