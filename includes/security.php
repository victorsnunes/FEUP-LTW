<?php

	/*
	 *
	 * $data -> untrusted user data
	 * $guarantee -> fields to guarantee and escape
	 * $ajax -> whether the request was ajax
	 */

	function guarantee_and_escape($data, $guarantee, $ajax=false){

		$res = array();
		foreach($guarantee as $guaranteed){

			if(!isset($data[$guaranteed])){

				if($ajax){
					echo json_encode(['errors' => 'Missing fields ' . $guaranteed]);
				}
				else{
					$_SESSION['errors'] = array('Missing fields ' . $guaranteed);
				}
				return false;
			}

			$res[$guaranteed] = htmlentities($data[$guaranteed]);
		}

		return $res;
	}

	/*
	 * $csrf -> received token
	 * $ajax -> whether the request was ajax
	 */
	function test_csrf($csrf, $ajax=false) {

		$res = $_SESSION['csrf'] == $csrf;

		if($res == false){
			if($ajax){
				echo json_encode(['errors' => 'Invalid CSRF']);
			}
			else{
				$_SESSION['errors'] = array('Invalid CSRF');
			}
		}

		return $res;
	}

?>
