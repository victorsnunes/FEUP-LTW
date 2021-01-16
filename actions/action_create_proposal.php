<?php

include_once('../includes/session.php');
include_once('../database/dogs.php');

create_proposal($_POST);

header('Location: ../pages/item.php?id=' . $_POST['dog_id']);

?>