<?php include("includes/header.php") ?>

<?php include("includes/navbar.php") ?>
	
<div class="jumbotron">
	<h1 class="text-center"><?php if(logged_in()){
         echo "You are logged in";
	}else{
		header('Location: index.php');
	} ?></h1>
</div>

<?php include("includes/footer.php") ?>

