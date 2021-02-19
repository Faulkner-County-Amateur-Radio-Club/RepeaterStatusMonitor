<?php

require "config.php";
require "repeater.class.php";

function GetPreviousRepeaterStatus($repeaterName) {
    // Create connection
    $connecton = new mysqli($database::address, $database::username, $database::password, $database::schema);
    if ($connecton->connect_error) {
        die("Database connection failed: " . $connecton->connect_error);
    }
	
    $sql = "Select powerOn,batteryGood,reportingOnTime from repeaterState where repeater='$repeaterName'";
	
    $result = $connecton->query($sql);
	while($row = $result->fetch_assoc()) {
		$previousStatePower = $row["powerOn"];
		$previousStateBattery = $row["batteryGood"];
		$previousStateTime = $row["reportingOnTime"];
	}
	$result -> free_result();
	$connecton -> close();
	
    echo "Previous state of $repeaterName was $previousStatePower,$previousStateBattery,$previousStateTime;"
}


$w5auu1 = new Repeater("W5AUU-1", "146.97 repeater", "146.97", 1, 5, 50, 2);
$w5auu2 = new Repeater("W5AUU-2", "OEM repeater shack", "147.03", 1, 5, 50, 2);
$w5auu3 = new Repeater("W5AUU-3", "Greenbrier repeater shack", "146.625", 1, 3, 100, 2);

?><html>
	<head>
		<title>Repeater Status Monitor</title>
		<style>
			span { 
				color: darkred; 
				animation: blinker 1s linear infinite;
			}

			@keyframes blinker {
			  50% {
				opacity: 0.2;
			  }
			}
		</style>
	</head>
	<body>
<?php 
$w5auu1->toString();
$w5auu2->toString();
$w5auu3->toString();
?>
	</body>
</html>
