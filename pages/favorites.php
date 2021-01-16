<?php require_once '../includes/session.php'; ?>
<?php require '../templates/head.php'; default_head('Pet Nexus - Pets'); ?>

<body>
	<?php require '../templates/header.html' ?>
	<?php require '../templates/navbar.php' ?>
	<?php require '../includes/social.php' ?>
	<main class="grid-gallery">
		<h2 class="center pink">Favorites</h2>
		<article class="posts">      
			<?php
				require_once("../database/db_class.php");
				$dbc = Database::instance()->db();

				$stmt = $dbc->prepare("SELECT dogs.*, favorites.id as favorite_id FROM dogs 
					JOIN favorites ON dogs.id = favorites.dog_id 
					WHERE favorites.user_id = ?");

				$stmt->execute(array($_SESSION['id']));
				$pets = $stmt->fetchAll();
				$i = 0;
				$dog_socials = get_dogs_socials($pets);
				
				foreach ($pets as $index => $entry) { 
					$i++;
					draw_pet_card($entry, $dog_socials, $i);
				}  
			?>
		</article>
	</main>
	<?php require '../templates/footer.html'; ?>
	
</body>

</html>
