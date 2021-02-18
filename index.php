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
$sql = "SELECT id , State,  Voltage, LastHeard FROM PreviousState";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rpt = $row["id"];
        ${'psendmail' . $rpt} = $row["Voltage"];
        ${'psendmail' . ($rpt+3)} = $row["LastHeard"];
        ${'psendmail' . ($rpt+6)} = $row["State"];
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

$sendmail = 0;
$sendmail1 = 0;
$sendmail2 = 0;
$sendmail3 = 0;
$sendmail4 = 0;
$sendmail5 = 0;
$sendmail6 = 0;
$sendmail7 = 0;
$sendmail8 = 0;
$sendmail9 = 0;
//Set or clear $test bit
$test =0;

//This will read the content of aprs.fi/a/w5auu-* JSON file
$url1 = "https://api.aprs.fi/api/get?name=W5AUU-1&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";
$url2 = "https://api.aprs.fi/api/get?name=W5AUU-2&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";
$url3 = "https://api.aprs.fi/api/get?name=W5AUU-3&what=loc&apikey=100665.Mj8HjUvXqEHYjrV6&format=json";

// path to the Telemitry page
$url4 = "https://aprs.fi/telemetry/W5AUU-1&key=100665.Mj8HjUvXqEHYjrV6";
$url5 = "https://aprs.fi/telemetry/W5AUU-2&key=100665.Mj8HjUvXqEHYjrV6";
$url6 = "https://aprs.fi/telemetry/W5AUU-3&key=100665.Mj8HjUvXqEHYjrV6";

// Set voltage test minimum point
$tripPoint = 11.0;

// Read JSON pages ****************************************************

// read W5AUU-1 jason page 
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


// message section
// Test if W5AUU-1 battery voltage below 11 volts
if ($volts1 < $tripPoint) {$message1 = "The voltage at W 5 A U U - 1 is " . $volts1 . " volts.\n  ";$sendmail1 = 1;}

// Test if W5AUU-2 battery voltage below 11 volts
if ($volts2 < $tripPoint) {$message2 = "The voltage at W 5 A U U - 2 is " . $volts2 . " volts.\n  ";$sendmail2 = 1;}

// Test if W5AUU-3 battery voltage below 11 volts
if ($volts3 < $tripPoint) {$message3 = "The voltage at W 5 A U U - 3 is " . $volts3 . " volts.\n  ";$sendmail3 = 1;}

// Test W5AUU-1 last heard 6hrs
if ($timeDif1 > 360) {$message4 = "The last time W 5 A U U - 1 heard was " . $timeDif1 . "minuits ago.\n ";$sendmail4 = 1;}
// Test W5AUU-2 last heard 6hrs
if ($timeDif2 > 360) {$message5 = "The last time W 5 A U U - 2 heard was " . $timeDif2 . "minuits ago.\n ";$sendmail5 = 1;}
// Test W5AUU-3 Last heard 6hrs
if ($timeDif3 > 360) {$message6 = "The last time W 5 A U U - 3 heard was " . $timeDif3 . "minuits ago.\n ";$sendmail6 = 1;}

//test W5AUU-1 for power condition
if ($ponoff1 < 50) {$message7 = "The power at W 5 A U U - 1  off. #" . $ponoff1 . "\n ";$sendmail7 = 1;}
//test W5AUU-2 for power condition
if ($ponoff2 < 50) {$message8 = "The power at W 5 A U U - 2  off. #" . $ponoff2 . "\n ";$sendmail8 = 1;}
// test W5AUU-3 for power condition
if ($ponoff3 > 100) {$message9 = "The power at W 5 A U U - 3  off. #" . $ponoff3 . "\n ";$sendmail9 = 1;}

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

if (mysqli_query($conn, $query)){
            echo "Records inserted successfully.";
        } else{
            echo "ERROR: Could not execute $query. " . mysqli_error($conn);
        }

$repeater = 3;
$query = "REPLACE INTO PreviousState (id , State,  Voltage, LastHeard) VALUES ('" . $repeater . "', '" . $sendmail9 . "', '" . $sendmail3 . "', '" . $sendmail6 . "') ;";

if (mysqli_query($conn, $query)){
            echo "Records inserted successfully.";
        } else{
            echo "ERROR: Could not execute $query. " . mysqli_error($conn);
        }

$query = "REPLACE INTO PreviousState (id , State,  Voltage, LastHeard) VALUES ('4', '" . $test . "', '0', '0') ;";

if (mysqli_query($conn, $query)){
            echo "Records inserted successfully.";
        } else{
            echo "ERROR: Could not execute $query. " . mysqli_error($conn);
        }
//get recipients for text message
include 'recipients.php';

// Send text to George, Eric, and Pat if condition has changed from normal range
$subject = 'Warning';
$headers = 'from: $sendFrom';//ddse.net';
for ($i = 1; $i<=9; $i++) { 
    echo ${"sendmail" . $i}," - ",${"psendmail" . $i}, "  ";
    if (${'sendmail' . $i} != ${'psendmail' . $i}) {
        if (${'sendmail' . $i} > ${'psendmail' . $i}) {
            if (mail($recipient, $subject, ${'message' . $i}, $headers))
                {
                    echo "Message accepted";
                }
            //special text for Mark on power off condition only
            if ($sendmail8 > $psendmail8) {
                if (mail($mark, $subject, "The power is off at the OEM repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }
            if ($sendmail9 > $psendmail9) {
                if (mail($david, $subject, "The power is off at the repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }
        }

        // If condition clears send text
        elseif (${'sendmail' . $i} < ${'psendmail' . $i}) {
            if (mail($recipient, $subject, 'Condition Cleared', $headers))
            {
                echo "Message accepted";
            }

            // If power back on send text to Mark
            if ($sendmail8 > $psendmail8) {
                if (mail($mark, $subject, "The power is back ON at the OEM repeater shack.", $headers))
                {
                    echo "Message accepted";
                }
            }
            // If power back on send text to David
            if ($sendmail9 > $psendmail9) {
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
if ($sendmail10 > $psendmail10) {
        if (mail('number@txt.att.net' , $subject, 'I have just reset the TNC and the arduino. Thanks.', $headers))
        {
            echo "Test Message accepted";
        }
    }
?>
