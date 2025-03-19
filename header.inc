<?php session_start(); ?>
<header class="bg-white">
	<div class="container">
		<!-- Container box -->
		<div class="logo">
			<img src="styles/images/logo.png" alt="Etech Logo">
		</div>
    	<input type="checkbox" id="menu-toggle">
		<label for="menu-toggle" id="menu-icon">
		&#9776;
		</label>
		<nav>
			<a href="index.php">
			Home
			</a>
			<a href="about.php">
			About Us
			</a>
			<a href="jobs.php">
			Jobs
			</a>
			<?php if (!isset($_SESSION['manager_id'])): ?>
			<a href="apply.php">
			Apply
			</a>
			<?php endif; ?>
			<a href="enhancements.php">
			Enhancements
			</a>
			<?php 
			if (isset($_SESSION['manager_id'])):
			?>
            <a href="manage.php">Manage</a>
            <a href="logout.php">
				<button class='btn btn-logout'>Logout</button>
				<div class="link">Logout</div>
			</a>
            <?php else: ?>
			<a href="login.php">
				<button class='btn btn-login'>Login</button>
				<div class="link">Login</div>
			</a>
			<a href="register.php">
				<button class='btn btn-register'>Register</button>
				<div class="link">Register</div>
			</a>
            <?php endif; ?>
		</nav>
	</div>
</header>