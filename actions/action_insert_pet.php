<?php

	include_once('../includes/session.php');
	include_once('../database/dogs.php');
    

    if (isset($_FILES['listing_picture'])) {
        $file = $_FILES['listing_picture'];

	if($file['error'] == UPLOAD_ERR_OK){


		$file_name_parts = explode('.', $file['name']);
		$file_ext = strtolower(end($file_name_parts));
	    
		$allowed = array('jpg', 'jpeg', 'png');
	    
		if(in_array($file_ext, $allowed)) {
		      $dog_photo = generate_filename($file_ext);
		      move_uploaded_file($file['tmp_name'], $dog_photo);

			insert_pet($_POST, $dog_photo);
			header('Location: ../pages/profile.php');
			return;
		}
		else{
			$_SESSION['errors'] = array('Wrong extension');
		}

	}
	else if($file['error'] == UPLOAD_ERR_INI_SIZE){
		$_SESSION['errors'] = array('File size if too big should be at max ' . ini_get('upload_max_filesize'));
	}
	else{
		$_SESSION['errors'] = array('Problem with upload');
	}

    }
    else{
		$_SESSION['errors'] = array('Forgot to put picture');
    }
    	
    	
    	set_from_post_in_session('listing_name');
    	set_from_post_in_session('listing_description');
    	set_from_post_in_session('breed_id');
    	set_from_post_in_session('color_id');
    	set_from_post_in_session('age_id');
    	set_from_post_in_session('gender_id');
	header('Location: ../pages/foundpet.php');

?>
