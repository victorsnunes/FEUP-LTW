<?php

	require('../includes/session.php');
	require('../database/dogs.php');

	$json = file_get_contents('php://input');
	$data = json_decode($json, true);

	update_dog($data);
?>
