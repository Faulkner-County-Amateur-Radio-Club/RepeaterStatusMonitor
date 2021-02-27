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
		$this->getTelemetry();
		
		$this->parseTelemetry("W5AUU-1", "1", "5", "2");
		$this->parseTelemetry("W5AUU-2", "1", "5", "2");
		$this->parseTelemetry("W5AUU-3", "1", "3", "2");

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
	function getTelemetry() {
		$jsonData = file_get_contents("repeaterTelemetry.json",true);
		$this->telemetry->raw = json_decode($jsonData, true);
	}
	function parseTelemetry($repeaterName, $telemetryVoltageChannel, $telemetryGridPowerStatusChannel, $telemetryTempuratureChannel) {
		$telemetryVoltageChannel = "telemetry" . $telemetryVoltageChannel;
		$telemetryGridPowerStatusChannel = "telemetry" . $telemetryGridPowerStatusChannel;
		$telemetryTempuratureChannel = "telemetry" . $telemetryTempuratureChannel;

		$this->telemetry->$repeaterName = new stdClass();
		$this->telemetry->$repeaterName->voltage = $this->telemetry->raw[$repeaterName][$telemetryVoltageChannel];
		$this->telemetry->$repeaterName->gridPower = $this->telemetry->raw[$repeaterName][$telemetryGridPowerStatusChannel];
		$this->telemetry->$repeaterName->tempurature = $this->telemetry->raw[$repeaterName][$telemetryTempuratureChannel];
	}
}

?>