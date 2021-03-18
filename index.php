<?php
// Written by George Carroll
 
// set up database for previous condition record
include 'config.php';
$query = "";
// Create connection
$conn = NEW mysqli($host_name, $user_name, $password, $database);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// Read the JSON data
function setmsg($urli) {
    $data = file_get_contents($urli);  // load contents of the page 
    $object = json_decode($data, true); // parse the JSON into an object
    $var = substr($data, strpos($data,"telemetry")+10,5);
    $var2 = substr($data, strpos($data,"lasttime")+11,10);
    $volts = floatval($var);
    return array($volts, $var2);
}

//Read the data into variables 
$sql = "SELECT id , Voltage, LastHeard, State FROM PreviousState";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rpt = $row["id"];
        if ($rpt < 4){
        ${'psendmail' . $rpt} = $row["Voltage"];
        ${'psendmail' . ($rpt+3)} = $row["LastHeard"];
        ${'psendmail' . ($rpt+6)} = $row["State"];}
    }
} else {
    echo "0 results";
}

// Initialize varables 
$volts = 0.0;
$message1 = "";
$message2 = "";
$message3 = "";
$message4 = "";
$message5 = "";
$message6 = "";
$messagr7 = "";
$message8 = "";
$message9 = "";

$sendmail = 1;
$sendmail1 = 1;
$sendmail2 = 1;
$sendmail3 = 1;
$sendmail4 = 1;
$sendmail5 = 1;
$sendmail6 = 1;
$sendmail7 = 1;
$sendmail8 = 1;
$sendmail9 = 1;
//Set or clear $test bit
$test =0;

//This will read the content of aprs.fi/a/w5auu-* JSON file
/*$url1 = "https://api.aprs.fi/api/get?name=W5AUU-1&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";
$url2 = "https://api.aprs.fi/api/get?name=W5AUU-2&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";
$url3 = "https://api.aprs.fi/api/get?name=W5AUU-3&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";

// path to the Telemitry page
$url4 = "https://aprs.fi/telemetry/W5AUU-1&key=100665.Mj8HjUvXqEHYjrV6";
$url5 = "https://aprs.fi/telemetry/W5AUU-2&key=100665.Mj8HjUvXqEHYjrV6";
$url6 = "https://aprs.fi/telemetry/W5AUU-3&key=100665.Mj8HjUvXqEHYjrV6";*/

// Read repeaterTelemitry.json

$readJSONFile = file_get_contents("./repeaterTelemetry.json",true);
$array = json_decode($readJSONFile,true);
$i = 1;
$currentTime = date("Y-m-d\TH:i:s.vP");
for($i = 1; $i <= 3; $i++){
    $timeDif = "timeDif" . $i;
    $lastTimeHeard = $array["W5AUU-"."$i"]["lastUpdated"];
    $$timeDif = strtotime($currentTime) - strtotime($lastTimeHeard);
    $volts = "volts" . $i;
    $$volts = $array["W5AUU-"."$i"]["telemetry1"] / 10;
    $ponoff = "ponoff" . $i;
    $$ponoff = $array["W5AUU-"."$i"]["telemetry3"];
    if ($i<3){
        $$ponoff = $array["W5AUU-"."$i"]["telemetry5"];
        }
} 

// Set voltage test minimum point
$tripPoint = 11.0;

// Read JSON pages ****************************************************

/*// read W5AUU-1 jason page 
$readJSONFile = file_get_contents($url1, true);
$array = json_decode($readJSONFile,true);
$timeHeard1 = $array[entries][0][lasttime];
$timeDif1 = (time() - $timeHeard1)/60; 

// read W5AUU-2 json page
$readJSONFile = file_get_contents($url2, true);
$array = json_decode($readJSONFile,true);
$timeHeard2 = $array[entries][0][lasttime];
$timeDif2 = (time() - $timeHeard2)/60;

// read W5AUU-2 json page
$readJSONFile = file_get_contents($url3, true);
$array = json_decode($readJSONFile,true);
$timeHeard3 = $array[entries][0][lasttime];
$timeDif3 = (time() - $timeHeard3)/60;

// read analog ch1 and ch5 W5AUU-1 from telemitry page **********************
$power = file_get_contents($url4);
//Channel 1
$regex = "/Channel\s1\:\s[0-9]+/";//Regex for Channel 1
preg_match($regex, $power,$match);
$hold = $match[0];
$volts1 = number_format(substr($hold,11)) / 10;

//Channel 5
$regex = "/Channel\s5\:\s[0-9]+/";//Regex for Channel 5
preg_match($regex, $power,$match);
$hold = $match[0];
//sets number for condition on off 
$ponoff1 = number_format(substr($hold,11));

// read analog ch 1 and ch5 W5AUU-2 from telemitry page
$power = file_get_contents($url5);
//Channel 1
$regex = "/Channel\s1\:\s[0-9]+/";//Regex for Channel 1
preg_match($regex, $power,$match);
$hold = $match[0];
$volts2 = number_format(substr($hold,11)) / 10;

//Channel 5
$regex = "/Channel\s5\:\s[0-9]+/";//Regex for Channel 5
preg_match($regex, $power,$match);
$hold = $match[0];
$ponoff2 = number_format(substr($hold,11));

// read analog ch 1 and ch5 W5AUU-3 from telemitry page
$power = file_get_contents($url6);
//Channel 1
$regex = "/Channel\s1\:\s[0-9]+/";//Regex for Channel 1
preg_match($regex, $power,$match);
$hold = $match[0];
$volts3 = number_format(substr($hold,11)) / 10;

//Channel 3
$regex = "/Channel\s3\:\s[0-9]+/";//Regex for Channel 3
preg_match($regex, $power,$match);
$hold = $match[0];
$ponoff3 = number_format(substr($hold,11));
*/

// message section
// Test if W5AUU-1 battery voltage below 11 volts
if ($volts1 < $tripPoint) {$message1 = "The voltage at W 5 A U U - 1 is " . $volts1 . " volts.<br>  ";$sendmail1 = 0;}

// Test if W5AUU-2 battery voltage below 11 volts
if ($volts2 < $tripPoint) {$message2 = "The voltage at W 5 A U U - 2 is " . $volts2 . " volts.<br>  ";$sendmail2 = 0;}

// Test if W5AUU-3 battery voltage below 11 volts
if ($volts3 < $tripPoint) {$message3 = "The voltage at W 5 A U U - 3 is " . $volts3 . " volts.<br>  ";$sendmail3 = 0;}

// Test W5AUU-1 last heard 6hrs
if ($timeDif1 > 21600) {$message4 = "The last time W 5 A U U - 1 heard was " . $timeDif1 . "minuits ago.<br> ";$sendmail4 = 0;}
// Test W5AUU-2 last heard 6hrs
if ($timeDif2 > 21600) {$message5 = "The last time W 5 A U U - 2 heard was " . $timeDif2 . "minuits ago.<br> ";$sendmail5 = 0;}
// Test W5AUU-3 Last heard 6hrs
if ($timeDif3 > 21600) {$message6 = "The last time W 5 A U U - 3 heard was " . $timeDif3 . "minuits ago.<br> ";$sendmail6 = 0;}

//test W5AUU-1 for power condition
if ($ponoff1 < 50) {$message7 = "The power at W 5 A U U - 1  off. #" . $ponoff1 . "<br> ";$sendmail7 = 0;}
//test W5AUU-2 for power condition
if ($ponoff2 < 50) {$message8 = "The power at W 5 A U U - 2  off. #" . $ponoff2 . "<br> ";$sendmail8 = 0;}
// test W5AUU-3 for power condition
if ($ponoff3 > 100) {$message9 = "The power at W 5 A U U - 3  off. #" . $ponoff3 . "<br> ";$sendmail9 = 0;}

// puts $test bit in the correct position
$sendmail10 = $test;
// sets previous conditions in database 
$repeater = 1;
$query = "REPLACE INTO PreviousState (id , State,  Voltage, LastHeard) VALUES ('" . $repeater . "', '" . $sendmail7 . "', '" . $sendmail1 . "', '" . $sendmail4 . "') ;";

if (mysqli_query($conn, $query)){
            echo "Records inserted successfully.";
        } else{
            echo "ERROR: Could not execute $query. " . mysqli_error($conn);
        }

$repeater = 2;
$query = "REPLACE INTO PreviousState (id , State,  Voltage, LastHeard) VALUES ('" . $repeater . "', '" . $sendmail8 . "', '" . $sendmail2 . "', '" . $sendmail5 . "') ;";

if (mysqli_query($conn, $query)) {
	echo "Records inserted successfully.";
} else{
	echo "ERROR: Could not execute $query. " . mysqli_error($conn);
}

$repeater = 3;
$query = "REPLACE INTO PreviousState (id , State,  Voltage, LastHeard) VALUES ('" . $repeater . "', '" . $sendmail9 . "', '" . $sendmail3 . "', '" . $sendmail6 . "') ;";

if (mysqli_query($conn, $query)) {
	echo "Records inserted successfully.";
} else{
	echo "ERROR: Could not execute $query. " . mysqli_error($conn);
}

$query = "REPLACE INTO PreviousState (id , State,  Voltage, LastHeard) VALUES ('4', '" . $test . "', '10', '10') ;";

if (mysqli_query($conn, $query)) {
	echo "Records inserted successfully.";
} else{
	echo "ERROR: Could not execute $query. " . mysqli_error($conn);
}

// Send text to George, Eric, and Pat if condition has changed from normal range
/*$subject = 'Warning';
$headers = 'from: $sendFrom';//ddse.net';*/
for ($i = 1; $i<=9; $i++) { 
/*$i=1;
echo "<br> ";
do {
    echo $i . "  " . ${"sendmail" . $i} . " - " .${"psendmail" . $i} . "  <br>";
    $i++;
} while ($i<10);*/
    if (${'sendmail' . $i} != ${'psendmail' . $i}) {
        if (${'sendmail' . $i} < ${'psendmail' . $i}) {
            if (mail($recipient, $subject, ${'message' . $i}, $headers))
                {
                    echo "Message accepted";
                }
            //special text for Mark on power off condition only
            if ($sendmail8 < $psendmail8) {
                if (mail($mark, $subject, "The power is off at the OEM repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }
            if ($sendmail9 < $psendmail9) {
                if (mail($david, $subject, "The power is off at the repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }
        }

        // If condition clears send text
        elseif (${'sendmail' . $i} > ${'psendmail' . $i}) {
            if (mail($recipient, $subject, 'Condition Cleared', $headers))
            {
                echo "Message accepted";
            }

            // If power back on send text to Mark
            if ($sendmail8 < $psendmail8) {
                if (mail($mark, $subject, "The power is back ON at the OEM repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }
            // If power back on send text to David
            if ($sendmail9 < $psendmail9) {
                if (mail($david, $subject, "The power is back ON at the repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }


        }
    }
} 
//for loop 

// Test for new user
if ($sendmail10 < $psendmail10) {
        if (mail('number@txt.att.net' , $subject, 'I have just reset the TNC and the arduino. Thanks.', $headers))
        {
            echo "Test Message accepted";
        }
    }
?>


