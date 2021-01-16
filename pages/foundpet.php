<?php require_once '../includes/session.php'; ?>
<?php require '../templates/head.php'; default_head('Pet Nexus - Found Pets');
if (!isset($_SESSION['id'])) die(header('Location: main.php')); ?>

<body>
	<?php require '../templates/header.html' ?>
  <?php require '../templates/navbar.php'; ?>
  <section class="row">
    <section class="page">
      <h2 class="center">I have a pet for adoption</h2>
    </section>
  </section>

	<?php 
		if(isset($_SESSION['errors'])) { ?>
			<div class="error-div"> <?php 

			foreach($_SESSION['errors'] as $error){ ?>
				<?= $error ?> 
					<br> <?php 
			}
				unset($_SESSION['errors']);
		} 
	?>
	</div>

	<?php
		require_once '../database/dogs.php';
		$submit = new FormCreator('new-pet', '../actions/action_insert_pet.php', true, false, false, 'multipart/form-data');
		$submit->add_input('listing_name', 'Listing Name', 'text', 'Name', true, read_session_or_null('listing_name'), NULL);
		$submit->add_input('listing_description', 'Description', 'text', 'Description', true, read_session_or_null('listing_description'), NULL);
		$submit->add_select('breed_id', 'Breed', get_breeds(), read_session_or_null('breed_id'));
		$submit->add_select('color_id', 'Color', get_colors(), read_session_or_null('color_id'));
		$submit->add_select('age_id', 'Age', get_ages(), read_session_or_null('age_id'));
		$submit->add_select('gender_id', 'Gender', get_genders(), read_session_or_null('gender_id'));

		$submit->add_input('listing_picture', 'Pet\'s Photo', 'file', NULL, true, NULL, NULL);
		$submit->inline();

		foreach($_SESSION as $key => $value) {
			if(!in_array($key, ['csrf', 'id'])) 
				unset($_SESSION[$key]);
		}
	?>


	<?php require '../templates/footer.html'; ?>
	
</body>
</html>
