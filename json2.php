<?php
	require "repeaters.class.php";
	$repeaters = new Repeaters();
	echo '{ "repeaters": ' . json_encode($repeaters) . '}';
?>