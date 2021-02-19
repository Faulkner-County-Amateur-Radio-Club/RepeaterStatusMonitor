<?php

require "config.php";
require "repeater.class.php";

function RetrievePrevious() {
    $query = "";
    // Create connection

    $conn = NEW mysqli($host_name, $user_name, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT id , State,  Voltage, LastHeard FROM PreviousState";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $rpt = $row["id"];
            ${'previousMailState' . $rpt} = $row["Voltage"];
            ${'previousMailState' . ($rpt+3)} = $row["LastHeard"];
            ${'previousMailState' . ($rpt+6)} = $row["State"];
        }
    }
    else {
        echo "0 results";
    }
    return array($previousMailState1,$previousMailState2);
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
