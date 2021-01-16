<?php include_once('../includes/form_creator.php'); ?>
<nav class="topnav" id="topnavbar">
  <a href="../index.php" class="navbar no-border"> <i class="fas fa-home"></i> Home </a>
  <?php if (isset($_SESSION['id'])) { 


		require_once('../database/user.php');
		$num_prop = get_number_receiving_proposals();	
	?>
  <a href="../pages/profile.php" class="navbar"> <i class="fas fa-user"></i> Profile </a>
  <a href="../pages/proposals.php" class="navbar <?= ($num_prop > 0) ? 'orange' : '' ?>"> <i class="fa fa-paper-plane"></i></i> Proposals <?= ($num_prop > 0) ? '('.$num_prop.')' : '' ?></a>
  <?php } ?>
  <a href="../pages/pets.php" class="navbar"> <i class="fas fa-dog"></i> Pets </a>
  <?php if (isset($_SESSION['id'])) { ?>
  <a href="../pages/foundpet.php" class="navbar"> <i class="fas fa-child"></i> Found a pet! </a>
  <?php } ?>
  <div style="display:none" id="csrf_token"><?= $_SESSION['csrf'] ?></div>

  <?php if(!isset($_SESSION['id'])){ ?>
  <!-- REGISTER -->
  <button onclick="displayRegisterPopup()" class="navbar right">
    <i class="fa fa-user-plus"></i> Register
  </button>
  <?php
    $register_form = new FormCreator('register-popup', '../actions/action_register.php', true);
    $register_form->add_input("username", "Username", "text", "Enter username", true, NULL, '^[a-zA-Z0-9]+$');
    $register_form->add_input("password", "Password", "password", "Enter password", true, NULL, '^[a-zA-Z0-9]+$');

    $register_form->inline();
  ?>
  <!-- LOGIN -->
  <button onclick="displayLoginPopup()" class="navbar right">
    <i class="fa fa-sign-in"></i> Login
  </button>
	<?php
    $login_form = new FormCreator('login-popup', '../actions/action_login.php', true);
    $login_form->add_input("username", "Username", "text", "Enter username", true, NULL, '^[a-zA-Z0-9]+$');
    $login_form->add_input("password", "Password", "password", "Enter password", true, NULL, '^[a-zA-Z0-9]+$');
    $login_form->add_input("remember", "Remember Me", "checkbox", NULL, false);
    $login_form->inline();
	?>

  <?php } 
  else { ?>
	  <button onclick="fetch('../actions/action_logout.php?csrf=<?=$_SESSION['csrf']?>').then((e)=> { location.reload();});" class="navbar right">
	    <i class="fa fa-users-slash"></i> Logout
	  </button>
	  <a href="../pages/favorites.php" class="navbar favorites right"> <i class="fa fa-heart"> </i> Favorites </a>

  <?php } ?>
  <a href="../pages/pets.php" class="navbar search right"> <i class="fa fa-search"> </i> Search </a>
  <a href="javascript:void(0);" class="icon" onclick="topnavResponsive()"><i class="fa fa-bars"></i></a>
</nav>
