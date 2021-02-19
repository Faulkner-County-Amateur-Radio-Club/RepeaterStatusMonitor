<?php

require "config.php";

function GetPreviousRepeaterStatus($repeaterName) {
    // Create connection
    $connecton = new mysqli(database::address, database::username, database::password, database::schema);
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
	
    echo "Previous state of $repeaterName was $previousStatePower, $previousStateBattery,$previousStateTime";
}

GetPreviousRepeaterStatus("W5AUU-1");

?>