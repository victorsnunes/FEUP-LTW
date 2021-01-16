<?php require_once '../includes/session.php'; ?>
<?php require '../templates/head.php'; default_head('Pet Nexus - Found Pets');

// Verify if user is logged in
if (!isset($_SESSION['id'])){
	die(header('Location: main.php'));
}



require_once("../database/db_class.php");
$dbc = Database::instance()->db();
$stmt = $dbc->prepare('SELECT username FROM users WHERE id = ?');
$stmt->execute(array($_SESSION['id']));

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
					<button onclick="document.getElementById('change-popup').style.display='block'" class="edit-button" aria-label="profile settings">
						<i class="fas fa-edit" aria-hidden="true"></i>
					</button>
				</div>
				<?php
				   $change_form = new FormCreator('change-popup', '../actions/action_change_creds.php', true);
				   $change_form->add_input("username", "Username", "text", "Enter username", true, $username, '^[a-zA-Z0-9]+$');
				   $change_form->add_input("old_password", "Old password", "password", "Enter old password", true);
				   $change_form->add_input("new_password", "New password", "password", "Enter new password", false);
				   $change_form->inline();
				?>
			</div>
		</div>
	</header>

	<main class="row">
		<aside class="left15"></aside>

		<section class="main70 lesspad">
			<style>
				#outgoing-proposals,
				#previous-proposals {
					display: none;
				}

				#incoming-proposals {
					display: block;
				}
			</style>
			<script> 
				let csrf_token = document.getElementById('csrf_token').innerHTML;

				function accept_proposal(proposal_id) {
					window.location.href = '../actions/action_accept_proposal.php?id=' + proposal_id + "&csrf=" + csrf_token
				}

				function deny_proposal(proposal_id) {
					window.location.href = '../actions/action_deny_proposal.php?id=' + proposal_id + "&csrf=" + csrf_token
				}

				function proposals(divName) {
					let incoming = document.getElementById('incoming-proposals');
					let outgoing = document.getElementById('outgoing-proposals');
					let previous = document.getElementById('previous-proposals');
					
					if(divName == 'incoming') {
						incoming.style.display = "block";
						outgoing.style.display = "none";
						previous.style.display = "none";
					}

					else if(divName == 'outgoing') {
						incoming.style.display = "none";
						outgoing.style.display = "block";
						previous.style.display = "none";
					}

					else if(divName == 'previous') {
						incoming.style.display = "none";
						outgoing.style.display = "none";
						previous.style.display = "block";
					}					
				}
			</script>			
			<button onclick="proposals('incoming')">Incoming</button>
			<button onclick="proposals('outgoing')">Outgoing</button>
			<button onclick="proposals('previous')">Previous</button>

			<article id="incoming-proposals" class="proposals">
				<h2 class="center">Incoming Proposals</h2>
				<?php 
					$dbc = Database::instance()->db();
					$stmt = $dbc->prepare("SELECT proposals.id, proposal_text, buyer_id, users.username as buyer_username, listing_picture, listing_name, dog_id
						FROM proposals 
						JOIN users 
						ON buyer_id = users.id
						JOIN dogs
						ON dog_id = dogs.id
						WHERE seller_id = ? AND proposal_status = 0");
					$stmt->execute(array($_SESSION['id']));
					$proposals = $stmt->fetchAll();

					foreach($proposals as $index => $entry) {
				?>

				<figure class="proposal-item">
					<div class="proposal-main">
						<img src="<?=$entry['listing_picture']?>" alt="">
						<div></div>
						<button class="yes" onclick="accept_proposal(<?=$entry['id']?>)">Yes <i class="fas fa-check" aria-hidden="true"></i></button>
						<button class="no" onclick="deny_proposal(<?=$entry['id']?>)">No <i class="fas fa-times" aria-hidden="true"></i></button>
					</div>
					<div class="proposal-description">
						<p><strong>Dog name</strong>: <a href="item.php?id=<?= $entry['dog_id'] ?>"><?=$entry['listing_name']?></a></p>
						<p><strong>Proposal from</strong>: <a href="profile.php?id=<?=$entry['buyer_id']?>"><?=$entry['buyer_username'];?></a></p>
						<p><strong>Proposal description</strong>: <?=$entry['proposal_text']?></p>
					</div>
				</figure>

				<?php } ?>
			</article>

			<article id="outgoing-proposals" class="proposals">
				<h2 class="center">Outgoing Proposals</h2>
				<?php 
					$dbc = Database::instance()->db();
					$stmt = $dbc->prepare("SELECT proposals.id, proposal_text, buyer_id, users.username as seller_username, seller_id, listing_picture, listing_name, dog_id
						FROM proposals 
						JOIN users 
						ON seller_id = users.id
						JOIN dogs
						ON dog_id = dogs.id
						WHERE buyer_id = ? AND proposal_status = 0");
					$stmt->execute(array($_SESSION['id']));
					$proposals = $stmt->fetchAll(); 

					foreach($proposals as $index => $entry) {
				?>

				<figure class="proposal-item">
					<div class="proposal-main">
						<img src="<?=$entry['listing_picture']?>" alt="">
						<div></div>
					</div>
					<div class="proposal-description">
						<p><strong>Dog name</strong>: <a href="item.php?id=<?= $entry['dog_id'] ?>"><?=$entry['listing_name']?></a></p>
						<p><strong>Proposal to</strong>: <a href="profile.php?id=<?= $entry['seller_id'] ?>"><?=$entry['seller_username']?></a></p>
						<p><strong>Proposal description</strong>: <?=$entry['proposal_text']?></p>
					</div>
				</figure>

				<?php } ?>
			</article>

			<article id="previous-proposals" class="proposals">
				<h2 class="center">Previous Proposals</h2>
				<?php 
					$dbc = Database::instance()->db();
					$stmt = $dbc->prepare("SELECT proposals.id, proposal_text, buyer_id, users.username as seller_username, seller_id, listing_picture, listing_name, dog_id, proposal_status
						FROM proposals 
						JOIN users 
						ON seller_id = users.id
						JOIN dogs
						ON dog_id = dogs.id
						WHERE buyer_id = ? AND proposal_status in (1,2)");
					$stmt->execute(array($_SESSION['id']));
					$proposals = $stmt->fetchAll(); 

					foreach($proposals as $index => $entry) {
				?>

				<figure class="proposal-item">
					<div class="proposal-main">
						<img src="<?=$entry['listing_picture']?>" alt="">
						<div></div>
					</div>
					<div class="proposal-description">
						<p><strong>Dog name</strong>: <a href="item.php?id=<?= $entry['dog_id'] ?>"><?=$entry['listing_name']?></a></p>
						<p><strong>Proposal description</strong>: <?=$entry['proposal_text']?></p>
						<p><strong>Proposal to</strong>: <a href="profile.php?id=<?= $entry['seller_id'] ?>"><?=$entry['seller_username']?></a></p>
						<p class="important"><strong>Status: </strong><?= $entry['proposal_status'] == '1' ? 'Accepted' : 'Rejected'; ?></p>
					</div>
				</figure>

				<?php } ?>
			</article>			
		</section>
		

    <aside class="right15"></aside>
	</main>
	<?php require '../templates/footer.html'; ?>

</body>

</html>
