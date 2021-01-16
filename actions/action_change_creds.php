<?php

	include_once('../includes/session.php');
	include_once('../database/user.php');


	$json = file_get_contents('php://input');
	$data = json_decode($json, true);


	change_user_creds($data);
?>
