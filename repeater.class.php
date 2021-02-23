<?php

require "config.php";

class Repeater {
	// Note that these are in alphabetical order
	public $description;
	public $frequency;
	public $jsonUrl;
	public $lastReportedMinutesAgo;
	public $lastReportedTime;
	public $name;
	private $poorHealthMessage;
	public $powerIsOn;
	public $powerValue;
	public $powerValueForCharts;
	private $previousStatePower;
	private $previousStateBattery;
	private $previousStateTime;
	public $status;
	private $telemetryGridPowerStatusChannel;
	public $telemetryGridPowerThreshold;
	private $telemetryPage;
	private $telemetryTempuratureChannel;
	public $telemetryURL;
	private $telemetryVoltageChannel;
	public $telemetryVoltageThreshold;
	public $tempurature;
	public $voltage;

	function __construct($name, $description, $frequency, $telemetryVoltageChannel, $telemetryGridPowerStatusChannel, $telemetryGridPowerThreshold, $telemetryTempuratureChannel) {
		$this->name = $name;
		$this->description = $description;
		$this->frequency = $frequency;
		$this->telemetryVoltageChannel = $telemetryVoltageChannel;
		$this->telemetryGridPowerStatusChannel = $telemetryGridPowerStatusChannel;
		$this->telemetryGridPowerThreshold = $telemetryGridPowerThreshold;
		$this->powerIsOn = true; // Assume it's on
		$this->powerValueForCharts = 33; // ^^
		$this->telemetryVoltageThreshold = 11.0;
		$this->telemetryTempuratureChannel = $telemetryTempuratureChannel;
		$this->loadData();
	}
	function loadData() {
		$this->jsonUrl = "https://api.aprs.fi/api/get?name=" . $this->name . "&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";
		$jsonData = file_get_contents($jsonUrl); 
		$jsonObject = json_decode($jsonData, true);
		$this->lastReportedTime = $jsonObject["entries"][0]["lasttime"];
		$this->lastReportedMinutesAgo = round((time() - $this->lastReportedTime)/60); 
		$this->status = $jsonObject["entries"][0]["status"];

		$this->telemetryURL = "https://aprs.fi/telemetry/" . $this->name . "&key=100665.Mj8HjUvXqEHYjrV6";
		$this->telemetryPage = file_get_contents($this->telemetryURL,true);

		// voltage
		preg_match("/Channel\s" . $this->telemetryVoltageChannel . "\:\s[0-9]+/", $this->telemetryPage, $match);
		$this->voltage = number_format(substr($match[0],11)) / 10;

		// grid power status
		preg_match("/Channel\s" . $this->telemetryGridPowerStatusChannel . "\:\s[0-9]+/", $this->telemetryPage, $match);
		$this->powerValue = number_format(substr($match[0],11));

		if ($this->telemetryGridPowerThreshold == 100) {
			if ($this->powerValue > 100) {
				$this->powerIsOn = false;
				$this->powerValueForCharts = 7;
			}
		}
		else {
			if ($this->powerValue < 50) {
				$this->powerIsOn = false;
				$this->powerValueForCharts = 7;
			}
		}

		// tempurature
		preg_match("/Channel\s" . $this->telemetryTempuratureChannel . "\:\s[0-9]+/", $this->telemetryPage, $match);
		$this->tempurature = number_format(substr($match[0],11));
		
		// Let's go get the previously reported states
		$connecton = new mysqli(database::$address, database::$username, database::$password, database::$schema);
		if ($connecton->connect_error) {
			die("Database connection failed: " . $connecton->connect_error);
		}
		$sql = "Select powerOn, batteryGood, reportingOnTime from repeaterState where repeater='$this->name'";
		$result = $connecton->query($sql);
		while($row = $result->fetch_assoc()) {
			$this->previousStatePower = $row["powerOn"];
			$this->previousStateBattery = $row["batteryGood"];
			$this->previousStateTime = $row["reportingOnTime"];
		}
		$result -> free_result();
		$connecton -> close();
		
		if ((isset($_GET['test'])) && ($_GET['test'] != "")) {
			$this->voltage = 0;
			$this->lastReportedMinutesAgo = 999;
		}
	}
	function toString() {
		echo "<h1>$this->name - $this->frequency</h1>";
		echo "Reported in " . round($this->lastReportedMinutesAgo) . " minutes ago<br>";
		echo "Status message: $this->status<br>";
		echo "Power is on: ";
		echo $this->powerIsOn ? 'yes<br>' : 'no<br>';
		echo "Voltage: $this->voltage<br>";
		echo "Tempurature: " . $this->tempurature . "&deg;F";
		
		if ($this->poorHealthMessage != "") {
			echo "<br><span>" . $this->poorHealthMessage . "</span>";
		}
	}
	function toJson() {
		$json = json_encode($this);
	}
	function doHealthCheck() {
		global $recipients;
		var sendAlerts = false;
		
		// Recipients are defined in the config.php
		$currentStatePower = 1;
		$currentStateBattery = 1;
		$currentStateTime = 1;
		$sendAlertTo = "";
		
		if ((!$this->powerIsOn) && ($this->previousStatePower == 1)) {
			$this->poorHealthMessage .= "Power is out. ";
			$sendAlertTo = $recipients[$this->name . "Power"] + ",";
			$currentStatePower = 0;
		}
		
		if (($this->voltage < $this->telemetryVoltageThreshold) && ($this->previousStateBattery == true)) {
			$this->poorHealthMessage .= "Battery voltage is low. ";
			$currentStateBattery = 0;
		}
		
		if (($this->lastReportedMinutesAgo > 360) && ($this->previousStateTime == true)) {
			$this->poorHealthMessage .= "Hasn't reported in over 6 hours. ";
			$currentStateTime = 0;
		}
		
		if ($this->poorHealthMessage != "") {
			$this->poorHealthMessage = "Problems at the $this->description: " . $this->poorHealthMessage;
			
			if ($_GET['test'] != "") { 
				$this->poorHealthMessage = "*TESTING* " . $this->poorHealthMessage; 
				$sendAlertTo = $recipients["testMode"];
			}
			else {
				$sendAlertTo .= $recipients["typicalSuspects"];
			}
			
			if (sendAlerts) {
				if (!mail($sendAlertTo, "", $this->poorHealthMessage, "from: w5auu@ddse.net")) {
					echo "Error sending email.";
				} 
			}
		}
		else {
			// All is well.  Let's see if there were problems before..
			if ($this->previousStatePower == 0) {
				$this->poorHealthMessage .= "Power is back on. ";
				$sendAlertTo = $recipients[$this->name . "Power"] . ",";
			}
			if ($this->previousStateBattery == 0) {
				$this->poorHealthMessage .= "Battery voltage is good. ";
			}
			if ($this->previousStateTime == 0) {
				$this->poorHealthMessage .= "Repeater is reporting again. ";
			}
			
			if ($this->poorHealthMessage != "") {
				$sendAlertTo .= $recipients["typicalSuspects"];
				if (sendAlerts) {
					if (!mail($sendAlertTo, "", "Good news! " . $this->poorHealthMessage, "from: w5auu@ddse.net")) {
						echo "Error sending email.";
					}
				}
			}
			$this->poorHealthMessage = "";
		}
		
		// Save current state
		$connecton = new mysqli(database::$address, database::$username, database::$password, database::$schema);
		if ($connecton->connect_error) {
			die("Database connection failed: " . $connecton->connect_error);
		}
		$sql = "Update repeaterState set powerOn=$currentStatePower,batteryGood=$currentStateBattery,reportingOnTime=$currentStateTime where repeater='$this->name';";
		$result = $connecton->query($sql);
		$connecton -> close();
	}
}

?>