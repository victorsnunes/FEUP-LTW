<?php

include_once('../includes/session.php');
include_once('../database/dogs.php');

answer_proposal($_GET, 2);

header('Location: ../pages/proposals.php');

?>
