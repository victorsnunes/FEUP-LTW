<?php require_once '../includes/session.php'; 

if(!isset($_SESSION['id']) && !isset($_GET['id'])){
  header('Location: ../index.php');
  return;
}

$id = null;

if(isset($_GET['id'])){
	$id = $_GET['id'];
}
else {
  $id = $_SESSION['id'];
}

require '../templates/head.php'; default_head('Pet Nexus - Found Pets');

require_once("../database/db_class.php");
require_once("../includes/social.php");
$dbc = Database::instance()->db();
$stmt = $dbc->prepare('SELECT username FROM users WHERE id = ?');
$stmt->execute(array($id));

$username = $stmt->fetch()['username'];
?>

<body>
	<?php require '../templates/header.html' ?>
	<?php require '../templates/navbar.php' ?>
	<header>
		<div class="grid-gallery">
			<div class="profile">
				<div class="profile-image">
					<img src="../assets/img/logo.png" alt="profile-img">
				</div>
				
				<div class="profile-header">
					<code class="profile-user-name"> <?=$username?> </code>
					<?php 
						if($id == $_SESSION['id']) {
							
					?>
						<button onclick="document.getElementById('change-popup').style.display='block'" class="edit-button">
							<i class="fas fa-edit" aria-hidden="true"></i>
						</button>
					<?php
							$change_form = new FormCreator('change-popup', '../actions/action_change_creds.php', true);
							$change_form->add_input("username", "Username", "text", "Enter username", true, $username, '^[a-zA-Z0-9]+$');
							$change_form->add_input("old_password", "Old password", "password", "Enter old password", true);
							$change_form->add_input("new_password", "New password", "password", "Enter new password", false);
							$change_form->inline();
						}
					?>
				</div>
			</div>
		</div>
	</header>

	<main class="grid-gallery">
		<h2 class="center"><slot><?=$username?>'s</slot> Listed Pets</h2>
		<article class="posts">
			<?php
				$stmt = $dbc->prepare('SELECT dogs.*, favorites.id as favorite_id 
					FROM dogs LEFT JOIN favorites ON dogs.id=dog_id AND (favorites.user_id = dogs.user_id OR favorites.user_id IS NULL) 
					WHERE dogs.user_id = ? AND is_adopted=0 ORDER BY id DESC');
				$stmt->execute(array($id));
				$pets = $stmt->fetchAll();
				$dog_socials = get_dogs_socials($pets);
				$i = 0;
				foreach ($pets as $index => $entry) { 
					$i++;
					draw_pet_card($entry, $dog_socials, $i);
			} ?>
		</article>
	</main>
	<?php require '../templates/footer.html'; ?>
</body>

</html>
