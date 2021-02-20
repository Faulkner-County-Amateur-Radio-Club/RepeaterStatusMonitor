<?php 
	require "repeater.class.php";
		
	class Repeaters {
		public $w5auu1;
		public $w5auu2;
		public $w5auu3;
		public $reportTime;
		
		function __construct() {
			$this->w5auu1 = new Repeater("W5AUU-1", "146.97 repeater", "146.97", 1, 5, 50, 2);
			$this->w5auu2 = new Repeater("W5AUU-2", "OEM repeater shack", "147.03", 1, 5, 50, 2);
			$this->w5auu3 = new Repeater("W5AUU-3", "Greenbrier repeater shack", "146.625", 1, 3, 100, 2);
			
			date_default_timezone_set("America/New_York");
			$this->reportTime = date("d/m/Y h:i:sa");
		}
	}

	$repeaters = new Repeaters();
	echo '{ "repeaters": ' . json_encode($repeaters) . '}';
?>