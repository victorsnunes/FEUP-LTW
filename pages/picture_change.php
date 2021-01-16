<?php require_once '../includes/session.php'; 

	if(!isset($_SESSION['id'])){
		header('Location: ../index.php');
		return;
	}

	require '../templates/head.php'; 
	default_head('Pet Nexus - Found Pets');

	if (!isset($_SESSION['id']))
		die(header('Location: ../index.php'));
?>

<body>
	<?php require '../templates/header.html'; ?>
	<?php require '../templates/navbar.php'; ?>
	
	<main class="row">
		<aside class="left15">
		</aside>

		<section class="main70">
			<?php 
				if(isset($_SESSION['errors'])) { ?>
					<div class="error-div">
					<?php 
						foreach($_SESSION['errors'] as $error) {
							echo $error;
						}
						unset($_SESSION['errors']);
					?>
					</div>
				<?php
				}

					require_once '../database/dogs.php';
					$dogs = get_dogs_of_user();
					$pictures = db_res_id_to_array($dogs, 'listing_picture');
				?>

			<h2>Change picture of <slot id="dog_name"></slot></h2>
			<figure class="item">
				<article class="item-general">
					<div class="item-pic">
						<img id="picture-showcase" style="display:none" src="" alt="">
					</div>
						<?php
							$submit = new FormCreator('picture-change', '../actions/action_update_picture.php', true, false, false,'multipart/form-data');
							$submit->add_select('listing_id', 'Listings', db_res_id_to_array($dogs, 'listing_name'), read_session_or_null('listing_id'));
							$submit->add_input('listing_picture', 'Pet\'s New Photo', 'file', NULL, true, NULL, NULL);
							$submit->inline();
							unset($_SESSION['listing_id']);
						?>
						<script>
							let picture = document.getElementById('picture-showcase');
							let select = document.getElementsByName('listing_id')[0];
							let dogName = document.getElementById('dog_name');
							console.log(dogName);

							let images = {
								<?php
									foreach($pictures as $key => $value)
										echo '\''.$key . '\' : \'' . $value . '\', ';
								?>
							};

							<?php 
								if(isset($_GET['id'])) { ?>
									picture.src = images[<?= $_GET['id']?>];
									select.value = <?= $_GET['id']?>;
									dogName.innerHTML = select.options[select.selectedIndex].text;
							<?php
								}
								else { ?>  
									let lastKey = Object.keys(images)[Object.keys(images).length-1];
									picture.src = images[lastKey];
									select.value  = lastKey;
									dogName.innerHTML = select.options[select.selectedIndex].text;
							<?php } ?>
							
								picture.style.display = 'block';
								select.onchange = (event) => {
									dogName.innerHTML = select.options[select.selectedIndex].text;
									picture.src = images[event.target.value];
								};
						</script>
					</div>

					<div class="status"> </div>
				</article>
			</figure> <!-- End item  -->
		</section> <!-- End main70 -->

		<aside class="right15"> </aside>
	</main>
  <?php require '../templates/footer.html'; ?>
</body>
</html>
