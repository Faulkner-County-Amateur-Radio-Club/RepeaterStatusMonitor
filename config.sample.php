<?php

// Creating a *static class* to maintain database connection info.
// https://www.w3schools.com/php/php_oop_static_properties.asp

class database {
	public static $address = "localhost";
	public static $username = "user";
	public static $password = "pass";
	public static $schema = "schema";
}

$recipients = [
        "typicalSuspects" => "email@domain.com",
        "w5auu1Power" => "",
        "w5auu2Power" => "email@domain.com",
        "w5auu3Power" => "email@domain.com",
        "testMode" => "email@domain.com"
];

?>