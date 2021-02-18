<?php

include 'config.php';

class Repeater {
        public $name;
        public $lastReportedTime;
        public $lastReportedMinutesAgo;
        public $voltage;
        public $status;
        public $powerIsOn;
		private $powerValue;
        private $telemetryData;
        private $telemetryVoltageChannel;
		private $telemetryVoltageThreshold;
        private $telemetryGridPowerStatusChannel;
		private $telemetryGridPowerThreshold;

        function __construct($name, $telemetryVoltageChannel, $telemetryGridPowerStatusChannel, $telemetryGridPowerThreshold) {
                $this->name = $name;
                $this->telemetryVoltageChannel = $telemetryVoltageChannel;
                $this->telemetryGridPowerStatusChannel = $telemetryGridPowerStatusChannel;
				$this->telemetryGridPowerThreshold = $telemetryGridPowerThreshold;
				$this->powerIsOn = true; // Assume it's on
				$this->telemetryVoltageThreshold = 11.0;
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
				if ($this->powerStatus < 50) {
					$this->powerIsOn = false;
				}
			}
        }
        function toString() {
                echo "<h1>$this->name</h1>";
                echo "Reported in $this->lastReportedMinutesAgo minutes ago<br>";
                echo "Status message: $this->status<br>";
                echo "Power is on: ";
				echo $this->powerIsOn ? 'yes<br>' : 'no<br>';
                echo "Voltage: $this->voltage<br>";
        }
}

$w5auu1 = new Repeater("W5AUU-1", 1, 5, 50);
$w5auu1->toString();

$w5auu2 = new Repeater("W5AUU-2", 1, 5, 50);
$w5auu2->toString();

$w5auu3 = new Repeater("W5AUU-3", 1, 3, 100);
$w5auu3->toString();

?>