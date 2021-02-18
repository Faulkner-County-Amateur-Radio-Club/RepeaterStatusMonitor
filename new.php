<?php

include 'config.php';

class Repeater {
	public $name;
	public $frequency;
	public $lastReportedTime;
	public $lastReportedMinutesAgo;
	public $voltage;
	public $tempurature;
	public $status;
	public $powerIsOn;
	private $powerValue;
	private $telemetryData;
	private $telemetryVoltageChannel;
	private $telemetryVoltageThreshold;
	private $telemetryGridPowerStatusChannel;
	private $telemetryGridPowerThreshold;
	private $telemetryTempuratureChannel;

	function __construct($name, $frequency, $telemetryVoltageChannel, $telemetryGridPowerStatusChannel, $telemetryGridPowerThreshold, $telemetryTempuratureChannel) {
			$this->name = $name;
			$this->frequency = $frequency;
			$this->telemetryVoltageChannel = $telemetryVoltageChannel;
			$this->telemetryGridPowerStatusChannel = $telemetryGridPowerStatusChannel;
			$this->telemetryGridPowerThreshold = $telemetryGridPowerThreshold;
			$this->powerIsOn = true; // Assume it's on
			$this->telemetryVoltageThreshold = 11.0;
			$this->telemetryTempuratureChannel = $telemetryTempuratureChannel;
			$this->loadData();
	}
	function loadData() {
		$jsonUrl = "https://api.aprs.fi/api/get?name=" . $this->name . "&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";
		$jsonData = file_get_contents($jsonUrl); 
		$jsonObject = json_decode($jsonData, true);
		$this->timeLastReported = $jsonObject[entries][0][lasttime];
		$this->lastReportedMinutesAgo = (time() - $this->timeLastReported)/60; 
		$this->status = $jsonObject[entries][0][status];

		$this->pageURL = "https://aprs.fi/telemetry/" . $this->name . "&key=100665.Mj8HjUvXqEHYjrV6";
		$this->page = file_get_contents($this->pageURL,true);

		// voltage
		preg_match("/Channel\s" . $this->telemetryVoltageChannel . "\:\s[0-9]+/", $this->page, $match);
		$this->voltage = number_format(substr($match[0],11)) / 10;

		// grid power status
		preg_match("/Channel\s" . $this->telemetryGridPowerStatusChannel . "\:\s[0-9]+/", $this->page, $match);
		$this->powerValue = number_format(substr($match[0],11));

		if ($this->telemetryGridPowerThreshold == 100) {
			if ($this->powerValue > 100) {
				$this->powerIsOn = false;
			}
		}
		else {
			if ($this->powerValue < 50) {
				$this->powerIsOn = false;
			}
		}

		// tempurature
		preg_match("/Channel\s" . $this->telemetryTempuratureChannel . "\:\s[0-9]+/", $this->page, $match);
		$this->tempurature = number_format(substr($match[0],11));
	}
	function toString() {
			echo "<h1>$this->name - $this->frequency</h1>";
			echo "Reported in " . round($this->lastReportedMinutesAgo) . " minutes ago<br>";
			echo "Status message: $this->status<br>";
			echo "Power is on: ";
			echo $this->powerIsOn ? 'yes<br>' : 'no<br>';
			echo "Voltage: $this->voltage<br>";
			echo "Tempurature: " . $this->tempurature . "&deg;F";
	}
}

$w5auu1 = new Repeater("W5AUU-1", "146.97", 1, 5, 50, 2);
$w5auu1->toString();

$w5auu2 = new Repeater("W5AUU-2", "147.03", 1, 5, 50, 2);
$w5auu2->toString();

$w5auu3 = new Repeater("W5AUU-3", "146.625", 1, 3, 100, 2);
$w5auu3->toString();

?>