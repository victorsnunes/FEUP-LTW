<?php
	include_once('../includes/session.php');
	include_once('../database/user.php');



	$guarantee = guarantee_and_escape($_GET, ['csrf']);
	if($guarantee == false){
		header('Location: ../index.php');
		return;
	}

	if(!test_csrf($guarantee['csrf'])){
		header('Location: ../index.php');
		return;
	}


	remove_session();
	session_destroy();
	session_start();

?>
