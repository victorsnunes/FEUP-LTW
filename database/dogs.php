<?php
  include_once('../database/db_class.php');

  /**
   * Returns the list of adopted pets from a certain user.
   */
  function getUserAdoptedPets($username) {
    
    $dbc = Database::instance()->db();
    $stmt = $dbc->prepare('SELECT * FROM adopted_pets WHERE username = ?');
    $stmt->execute(array($username));
    return $stmt->fetchAll(); 
  }

  /**
   * Returns the list of pets for adoption from a certain user.
   */
  function getUserPetsForAdoption($username) {
    
    $dbc = Database::instance()->db();
    $stmt = $dbc->prepare('SELECT * FROM pets_for_adoption WHERE username = ?');
    $stmt->execute(array($username));
    return $stmt->fetchAll(); 
  }

  /**
   * Inserts a new pet for adoption into the database.
   */
  function insert_pet($form, $file_name) {
    
	$guarantee = guarantee_and_escape($form, ['listing_name', 'listing_description', 'breed_id', 'color_id', 'age_id', 'gender_id', 'csrf']);
	if($guarantee == false){
		header('Location: ../pages/foundpet.php');
		return;
	}

	if(!test_csrf($guarantee['csrf'])){
		header('Location: ../pages/foundpet.php');
		return;
	}
    $dbc = Database::instance()->db();

    $stmt1 = $dbc->prepare('INSERT INTO dogs(user_id, listing_name, listing_description, listing_picture, breed_id, color_id, age_id, gender_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt1->execute(array($_SESSION['id'], $guarantee['listing_name'], $guarantee['listing_description'], $file_name,
	    $guarantee['breed_id'],
	    $guarantee['color_id'],
	    $guarantee['age_id'],
	    $guarantee['gender_id']
    ));

  }


  /*
   * Helper fucntion to convert the result into an associative array
   */
  function db_res_id_to_array($db_res, $component_name){

	$res = array();
	foreach($db_res as $entry){
		$res[$entry['id']] = $entry[$component_name];
	}
	return $res;

  }

  function get_component($component_name, $table_name){

    $dbc = Database::instance()->db();

    $query_str = sprintf('SELECT * FROM %s ORDER BY id', $table_name);
    $stmt = $dbc->prepare($query_str);
    $stmt->execute();
    $db_res = $stmt->fetchAll();

    return db_res_id_to_array($db_res, $component_name);
  }

  function get_breeds(){
	  return get_component('breed_name', 'dog_breeds');
  }

  function get_colors(){
	  return get_component('color_name', 'dog_colors');
  }

  function get_ages(){
	  return get_component('age_name', 'dog_ages');
  }

  function get_genders(){
	  return get_component('gender_name', 'dog_genders');
  }


  function get_dog($id){

    $dbc = Database::instance()->db();

    $stmt = $dbc->prepare('SELECT username, dogs.*, color_name, breed_name, age_name, gender_name
	    FROM dogs 
	    JOIN dog_colors ON color_id=dog_colors.id 
	    JOIN dog_breeds ON breed_id=dog_breeds.id 
	    JOIN dog_ages ON age_id=dog_ages.id 
	    JOIN dog_genders ON gender_id=dog_genders.id 
	    JOIN users ON dogs.user_id=users.id 
		WHERE dogs.id = ?');
    $stmt->execute(array($id));
    return $stmt->fetch();
  }

  function update_dog($data){

		header('Content-Type: application/json');
		$guarantee = guarantee_and_escape($data, ['listing_name', 'listing_description', 'breed_id', 'color_id', 'age_id', 'gender_id', 'dog_id', 'csrf'], true);
		if($guarantee == false){
			return;
		}

		if(!test_csrf($guarantee['csrf'], true)){
			return;
		}

		$dbc = Database::instance()->db();
		$stmt = $dbc->prepare('UPDATE dogs SET 
			listing_name = ?,
			listing_description = ?,
			breed_id = ?,
			color_id = ?,
			age_id = ?,
			gender_id = ?
		       	WHERE id = ? AND user_id = ?');
		try{
			$stmt->execute(array(
				$guarantee['listing_name'],
				$guarantee['listing_description'],
				$guarantee['breed_id'],
				$guarantee['color_id'],
				$guarantee['age_id'],
				$guarantee['gender_id'],
				$guarantee['dog_id'],
				$_SESSION['id']
			)
			);


			echo json_encode(['status' => 'success']);
		}
		catch(PDOexception $e){
			error_log($e);
			echo json_encode(['errors' => 'There was an error updating the listing']);
		}

  }

  function get_dogs_of_user(){

	$dbc = Database::instance()->db();
	$stmt = $dbc->prepare('SELECT id, listing_name, listing_picture FROM dogs WHERE user_id = ?');
	$stmt->execute(array($_SESSION['id']));

	return $stmt->fetchAll();
  }

  function update_picture($data, $file_name){
	

	$guarantee = guarantee_and_escape($data, ['listing_id', 'csrf']);
	if($guarantee == false){
		header('Location: ../pages/item.php');
		return;
	}

	if(!test_csrf($guarantee['csrf'])){
		header('Location: ../pages/item.php');
		return;
	}

	$dbc = Database::instance()->db();
	$stmt = $dbc->prepare('UPDATE dogs SET listing_picture = ? WHERE id = ? AND user_id = ?');
	$stmt->execute(array($file_name, $guarantee['listing_id'], $_SESSION['id']));
  }

  function heart_pet($data){
	$guarantee = guarantee_and_escape($data, ['pet_id', 'csrf'], true);
	if($guarantee == false){
		return;
	}

	if(!test_csrf($guarantee['csrf'], true)){
		return;
	}

	  $dbc = Database::instance()->db();
	  $stmt = $dbc->prepare('INSERT INTO favorites(user_id, dog_id) VALUES (?, ?)'); 
	  try{
		  $stmt->execute(array($_SESSION['id'], $guarantee['pet_id']));
	  }
	  catch(PDOException $e){}
  }

  function unheart_pet($data){

	$guarantee = guarantee_and_escape($data, ['pet_id', 'csrf'], true);
	if($guarantee == false){
		return;
	}

	if(!test_csrf($guarantee['csrf'], true)){
		return;
	}

	  $dbc = Database::instance()->db();
	  $stmt = $dbc->prepare('DELETE FROM favorites WHERE user_id = ? AND dog_id = ?'); 
	  try{
		  $stmt->execute(array($_SESSION['id'], $data['pet_id']));
	  }
	  catch(PDOException $e){}

  }

   function comment_question($form){

		$guarantee = guarantee_and_escape($form, ['question_content', 'dog_id', 'csrf']);
		if($guarantee == false){
			return;
		}

		if(!test_csrf($guarantee['csrf'])){
			return;
		}

	   $dbc = Database::instance()->db();
	   $stmt = $dbc->prepare('INSERT INTO comments(user_id, dog_id, question) VALUES (?, ?, ?)');
	   try{
			$stmt->execute(array($_SESSION['id'], $guarantee['dog_id'], $guarantee['question_content']));
	    }
		catch(PDOException $e){}
   }

   function answer_question($form) {


		$guarantee = guarantee_and_escape($form, ['answer_content', 'comment_id', 'csrf']);
		if($guarantee == false){
			header('Location: ../pages/item.php');
			return;
		}

		if(!test_csrf($guarantee['csrf'])){
			header('Location: ../pages/item.php');
			return;
		}



		$dbc = Database::instance()->db();
		$stmt = $dbc->prepare('UPDATE comments SET answer = ? WHERE id = ?');
		try {
			$stmt->execute(array($guarantee['answer_content'], $guarantee['comment_id']));
		}
		catch(PDOException $e){}
   }

   function create_proposal($form) {


		$guarantee = guarantee_and_escape($form, ['proposal_content', 'dog_id', 'csrf'], false);
		if($guarantee == false){
			return;
		}

		if(!test_csrf($guarantee['csrf'], false)){
			return;
		}

		$dbc = Database::instance()->db();
		$stmt = $dbc->prepare('SELECT * FROM dogs WHERE id = ?');
		try {
			$stmt->execute(array($guarantee['dog_id']));
		}
		catch(PDOException $e){}
		$dog = $stmt->fetch();
		$seller_id = $dog['user_id'];

		$stmt2 = $dbc->prepare('INSERT INTO proposals(seller_id, buyer_id, dog_id, proposal_text) VALUES (?, ?, ?, ?)');
		$stmt2->execute(array($seller_id, $_SESSION['id'], $guarantee['dog_id'], $guarantee['proposal_content']));

   }

   /*
    * $data contains the dog id and the answer is the type of response 1-accept 2-reject
    */
   function answer_proposal($data, $answer) {


		$guarantee = guarantee_and_escape($data, ['id', 'csrf']);
		if($guarantee == false){
			echo $_SESSION['errors'] = array(['errors' => 'Missing fields']);
			return;
		}

		if(!test_csrf($guarantee['csrf'], false)){
			return;
		}

		$proposal_id = $data['id'];
	   
	$dbc = Database::instance()->db();
	$dbc->beginTransaction();

	try{

		$stmt = $dbc->prepare('UPDATE proposals SET proposal_status = ? WHERE id = ?');
		$stmt->execute(array($answer, $proposal_id));

		$stmt = $dbc->prepare('SELECT * FROM proposals WHERE id = ?');
		$stmt->execute(array($proposal_id));
		$proposal = $stmt->fetch();

		if ($answer === 1) {
			$dog_id = $proposal['dog_id'];

			$stmt = $dbc->prepare('UPDATE proposals SET proposal_status = 2 WHERE dog_id = ? AND id != ?');
			$stmt->execute(array($dog_id, $proposal_id));

			$stmt = $dbc->prepare('UPDATE dogs SET is_adopted = 1 WHERE id = ?');
			$stmt->execute(array($dog_id));
		}


		$dbc->commit();


	}
	catch(PDOexception $e){
		$dbc->rollback();
		return;
	}

	}

   	/*
	 * Whether user already made a proposal
	 * to the given $dog_id
	 */
	function already_made_proposal($dog_id) {

		$dbc = Database::instance()->db();
		
		$stmt = $dbc->prepare("SELECT count(*) AS num FROM proposals WHERE dog_id = ? AND buyer_id = ?");
		$stmt->execute(array($dog_id, $_SESSION['id']));
		$num = $stmt->fetch()['num'];

		return $num != 0;
	}

?>
  
