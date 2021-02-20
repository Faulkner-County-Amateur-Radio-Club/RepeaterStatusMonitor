<?php 
	require "repeater.class.php";
		
	class Repeaters {
		public $w5auu1;
		public $w5auu2;
		public $w5auu3;
		
		function __construct() {
			$this->w5auu1 = new Repeater("W5AUU-1", "146.97 repeater", "146.97", 1, 5, 50, 2);
			$this->w5auu2 = new Repeater("W5AUU-2", "OEM repeater shack", "147.03", 1, 5, 50, 2);
			$this->w5auu3 = new Repeater("W5AUU-3", "Greenbrier repeater shack", "146.625", 1, 3, 100, 2);
		}
		
		function toJson() {
			$json = json_encode($this);
		}
	}

	$repeaters = new Repeaters();
	echo $repeaters->toJson();
?>