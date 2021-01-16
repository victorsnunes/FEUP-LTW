<?php
	
	require('../database/db_class.php');

	function clear_expired_tokens(){
		$dbc = Database::instance()->db();
		$delete = $dbc->prepare('DELETE FROM auth_tokens WHERE expires<CURRENT_TIMESTAMP');
		$delete->execute();
	}

	/*
	 * Create a selector+validator for the given user
	 */
	function create_user_cookie($id){
		$conn = Database::instance()->db();

		$selector = base64_encode(random_bytes(16));
		$validator = base64_encode(random_bytes(32));


		$stmt = $conn->prepare('INSERT INTO auth_tokens(selector, hashed_validator, user_id) VALUES(?,?,?)');
		$stmt->execute(array($selector, password_hash($validator, PASSWORD_DEFAULT, ['cost' => 12]), $id));

		return $selector . ':' . $validator;
	}

	function create_user($data){


		$guarantee = guarantee_and_escape($data, ['username', 'password', 'csrf'], true);
		if($guarantee == false){
			return;
		}

		if(!test_csrf($guarantee['csrf'], true)){
			return;
		}


		$username = $guarantee['username'];
		$password = $guarantee['password'];


		$dbc = Database::instance()->db();
		clear_expired_tokens();

		header('Content-Type: application/json');


		if ( !preg_match ("/^[a-zA-Z0-9]+$/", $username)) {
			echo json_encode(['errors' => 'Username is using invalid characters']);
			return;
		}

		if(strlen($password) < 8){
			echo json_encode(['errors' => 'Password must have at least 8 chars']);
			return;
		}

		try{
			$options = ['cost' => 12];
			$stmt = $dbc->prepare('INSERT INTO users(username, password) VALUES (?, ?)');
			$stmt->execute(array($username, password_hash($password, PASSWORD_DEFAULT, $options)));

			$stmt_id = $dbc->prepare('SELECT id FROM users WHERE username = ?');
			$stmt_id->execute(array($username));

			$user_id = $stmt_id->fetch()['id'];


			$_SESSION['id'] = $user_id;
			echo json_encode(['status' => 'success']);
		}
		catch(PDOexception $e){
			echo json_encode(['errors' => 'Username is already in use']);
		}
	}

	function login_user($data){


		$guarantee = guarantee_and_escape($data, ['username', 'password', 'remember', 'csrf'], true);
		if($guarantee == false){
			return;
		}

		if(!test_csrf($guarantee['csrf'], true)){
			return;
		}

		$username = $guarantee['username'];
		$password = $guarantee['password'];
		$remember = $guarantee['remember'];

		$dbc = Database::instance()->db();

		clear_expired_tokens();

		header('Content-Type: application/json');


		if ( !preg_match ("/^[a-zA-Z0-9]+$/", $username)) {
			echo json_encode(['errors' => 'Username is using invalid characters']);
			return;
		}

		$stmt = $dbc->prepare('SELECT * FROM users WHERE username = ?');
		$stmt->execute(array($username));
		$user = $stmt->fetch();

		if($user !== false && password_verify($password, $user['password'])){
			$_SESSION['id'] = $user['id'];

			$orig_path = $_SERVER['PHP_SELF'];
			$orig_path_parts = explode('/', $orig_path);

			array_splice($orig_path_parts, -2);
			$path = '/'.implode('/', $orig_path_parts);

			if($remember)
				setcookie('auth', create_user_cookie($user['id']), time()+(3600*24*30), $path);
			echo json_encode(['status' => 'success']);
			return;
		}
		echo json_encode(['errors' => 'Username/Password combination are not valid']);

	}


	/*
	 * $cookie -> user cookie
	 *
	 * If the selector is valid and the validator is wrong then it's deleted from the DB
	 */
	function restore_session($cookie){

		clear_expired_tokens();
		$elements = explode(':', $cookie);

		if(count($elements) == 2){

			$dbc = Database::instance()->db();
			$selector = $elements[0];
			$validator = $elements[1];

			$stmt = $dbc->prepare('SELECT hashed_validator, username, user_id  FROM auth_tokens JOIN users ON users.id=auth_tokens.user_id WHERE expires > CURRENT_TIMESTAMP AND selector = ?');
			$stmt->execute(array($selector));
			$auth_token = $stmt->fetch();


			if($auth_token !== false && password_verify($validator, $auth_token['hashed_validator'])){
				$_SESSION['id'] = $auth_token['user_id'];
			}
			else{
				$delete = $dbc->prepare('DELETE FROM auth_tokens WHERE selector = ?');
				$delete->execute(array($selector));
				setcookie('auth', '', 0);
			}


		}
		else{
			setcookie('auth', '', 0);
		}
	}


	function remove_session(){

		clear_expired_tokens();
		if(!isset($_COOKIE['auth'])){
			return;
		}

		$cookie = $_COOKIE['auth'];
		$elements = explode(':', $cookie);

		if(count($elements) == 2){
			$dbc = Database::instance()->db();
			$selector = $elements[0];
			$delete = $dbc->prepare('DELETE FROM auth_tokens WHERE selector = ?');
			$delete->execute(array($selector));
			setcookie('auth', '', 0);
		}
	}

	function change_user_creds($data){

		header('Content-Type: application/json');

		$guarantee = guarantee_and_escape($data, ['username', 'old_password', 'new_password', 'csrf'], true);
		if($guarantee == false){
			return;
		}

		if(!test_csrf($guarantee['csrf'], true)){
			return;
		}



		if ( !preg_match ("/^[a-zA-Z0-9]+$/", $guarantee['username'])) {
			echo json_encode(['errors' => 'Username is using invalid characters']);
			return;
		}

		$new_password_len = strlen($guarantee['new_password']);
		if($new_password_len > 0 && $new_password_len < 8){
			echo json_encode(['errors' => 'Password must have at least 8 chars']);
			return;
		}

		$dbc = Database::instance()->db();
		$stmt = $dbc->prepare('SELECT username, password, id FROM users WHERE id = ?');
		$stmt->execute(array($_SESSION['id']));
		$user = $stmt->fetch();

		if($user !== false && password_verify($guarantee['old_password'], $user['password'])){

			$dbc->beginTransaction();


			try{

				$stmt = NULL;

				if($new_password_len == 0){
					$stmt = $dbc->prepare('UPDATE users SET username = ? WHERE id = ?');
					$stmt->execute(array($guarantee['username'], $_SESSION['id']));
				}
				else{
					$stmt = $dbc->prepare('UPDATE users SET username = ? , password = ? WHERE id = ?');
					$stmt->execute(array($guarantee['username'], password_hash($guarantee['new_password'], PASSWORD_DEFAULT, ['cost' => 12]), $_SESSION['id']));
				}


				$dbc->commit();


			}
			catch(PDOexception $e){
				$dbc->rollback();
				echo json_encode(['errors' => 'You goofed']);
				return;
			}




			echo json_encode(['status' => 'success']);
			return;
		}
		echo json_encode(['errors' => 'Old password is invalid']);


	}


	function get_number_receiving_proposals(){

		$dbc = Database::instance()->db();
		$stmt = $dbc->prepare('SELECT COUNT(id) as num_proposals FROM proposals WHERE seller_id = ? AND proposal_status = 0 GROUP BY seller_id, proposal_status');
		$stmt->execute(array($_SESSION['id']));

		$fetch = $stmt->fetch();
		if($fetch == false)
			return 0;
		return $fetch['num_proposals'];
	}

?>
