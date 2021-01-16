<?php require_once '../includes/session.php'; 
	if(!isset($_GET['id'])) {
		header('Location: ../index.php');
		return;
	}
?>
<?php require '../templates/head.php'; default_head('Pet Nexus - Pets'); ?>

<body>
	<?php 
		require '../templates/header.html';
		require '../templates/navbar.php';
		require '../database/dogs.php';

		$dog_data = get_dog($_GET['id']);
		$is_logged_in = isset($_SESSION['id']);
		$is_author = $is_logged_in && $dog_data['user_id'] === $_SESSION['id'];
	?>

	<main class="row">
		<aside class="left15"> </aside>

		<section class="main70">
			<h2><?= $dog_data['listing_name'] ?></h2>
			<?php if ($is_author) { ?>
				<button onclick="document.getElementById('edit-listing-popup').style.display='block'" class="edit-button">
					<i class="fas fa-edit" aria-hidden="true"></i>
				</button>
			<?php } ?>
      <figure class="item">
				<article class="item-general">
					<section class="item-pic">
						<img src="<?= $dog_data['listing_picture'] ?>" alt="">
						<p> <b>Description:&nbsp;</b> <?= $dog_data['listing_description'] ?> </p>
						<?php
							if($is_logged_in) {
								if ($is_author) { ?>
									<a href="../pages/picture_change.php?id=<?=$dog_data['id']?>">Change Picture</a> <?php 
								}
								else if(!already_made_proposal($dog_data['id'])) { ?>
									<button onclick="displayProposalPopup()"> I want to adopt it! </button> <?php
								} 	
								else { ?> 
									<p class="warning"> You've already sent a proposal to adopt this dog! </p> <?php
								}
							}
						?>

					</section>
					<section class="status">
						<table>
							<tr>
								<th class="table-title" colspan="2">Dog Details</th>
							</tr>
							<tr>
								<th class="table-left">Gender</th>
								<td class="table-right"><?= $dog_data['gender_name'] ?></td>
							</tr>
							<tr>
								<th class="table-left">Breed</th>
								<td class="table-right"><?= $dog_data['breed_name'] ?></td>
							</tr>
							<tr>
								<th class="table-left">Color</th>
								<td class="table-right"><?= $dog_data['color_name'] ?></td>
							</tr>
							<tr>
								<th class="table-left">Age</th>
								<td class="table-right"><?= $dog_data['age_name'] ?></td>
							</tr>
							<tr>
								<th class="table-left">Owner ID</th>
								<td class="table-right">
									<a class="" href="profile.php?id=<?=$dog_data['user_id']?>"><?=$dog_data['username']?></a>
								</td>
							</tr>
						</table>
					</section>
				</article>
				<article class="item-more">
					<?php 
						if($is_logged_in && $is_author) {
							$edit_listing = new FormCreator('edit-listing-popup', '../actions/action_edit_listing.php', true);
							$edit_listing->add_input("listing_name", "Listing Name", "text", "New listing name", true, $dog_data['listing_name']);
							$edit_listing->add_input("listing_description", "Listing description", "text", "New listing description", true, $dog_data['listing_description']);
							$edit_listing->add_select('breed_id', 'Breed', get_breeds(), $dog_data['breed_id']);
							$edit_listing->add_select('color_id', 'Color', get_colors(), $dog_data['color_id']);
							$edit_listing->add_select('age_id', 'Age', get_ages(), $dog_data['age_id']);
							$edit_listing->add_select('gender_id', 'Gender', get_genders(), $dog_data['gender_id']);
							$edit_listing->add_input("dog_id", "", "hidden", "", true, $dog_data['id']);
							$edit_listing->inline();
						} 
				
						if ($is_logged_in && !$is_author) {
							$proposal_form = new FormCreator('proposal-popup', '../actions/action_create_proposal.php', true, false);
							$proposal_form->add_input("proposal_content", "Proposal", "text", "Write a proposal to the person!", true);
							$proposal_form->add_input("dog_id", "", "hidden", "", true, $dog_data['id']);
							$proposal_form->inline();
						}

						if ($is_logged_in) {
							$comments = new FormCreator('comments_section', '../actions/action_make_a_question.php', false, false, false);
							$comments->add_input('question_content', 'Question', 'text', 'Write here your question', true);
							$comments->add_input("dog_id", "", "hidden", "", true, $dog_data['id']);
							$comments->inline();
						} 
							
						require_once("../database/db_class.php");
						$dbc = Database::instance()->db();
				
						$stmt = $dbc->prepare("SELECT comments.*, users.username FROM comments 
							JOIN users ON user_id = users.id WHERE dog_id = ?");

						$stmt->execute(array($dog_data['id']));
						$comments = $stmt->fetchAll();
				
						$num = count($comments);
					?>
	
					<!-- Comments header -->
					<h2 class="comments-header"><?=$num?> Comment<?php if ($num !== 1) echo 's'; ?></h2>
	
					<?php foreach ($comments as $index => $entry) { ?>
						<!-- Question -->
						<div class="qna">
							<p class="qna-header q">Question</p>
							<p class="qna-text"><?=$entry['question']?> -</p>
							<a class="qna-user" href="profile.php?id=<?=$entry['user_id']?>"><?=$entry['username']?></a>
						</div> <?php 
						
							if ($entry['answer'] === NULL && $is_author) {
								$add_answer = new FormCreator('answer', '../actions/action_write_a_answer.php', false, false, false);
								$add_answer->add_input('answer_content', 'Answer', 'text', 'Write a answer for this question', true);
								$add_answer->add_input("comment_id", "", "hidden", "", true, $entry['id']);
								$add_answer->add_input("dog_id", "", "hidden", "", true, $dog_data['id']);
								$add_answer->inline();
							}
							else if ($entry['answer'] !== NULL) { ?>
								<!-- Answer -->
								<div class="qna margin">
									<p class="qna-header a">Answer</p> 
									<p class="qna-text"><?=$entry['answer']?> -</p>
									<a class="qna-user" href="profile.php?id=<?=$dog_data['user_id']?>"><?=$dog_data['username']?></a>
								</div> <?php
							}
						} //close foreach
					?>

				</article> <!-- End item-more  -->
			</figure> <!-- End item  -->
		</section> <!-- End main70 -->
	
		<aside class="right15"></aside>
	</main>
	<?php require '../templates/footer.html'; ?>

</body>
</html>
