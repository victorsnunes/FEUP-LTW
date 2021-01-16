<?php

include_once('../includes/session.php');
include_once('../database/dogs.php');

answer_question($_POST);

header('Location: ../pages/item.php?id=' . $_POST['dog_id']);

?>