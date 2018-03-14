<nav class="navbar navbar-expand-lg navbar-dark bg-primary" style="background-color:rgba(0,0,0,.6)!important;">
	<a class="navbar-brand" href="/home/">Tempus</a>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav ml-auto">
			<!-- SESSIONS -->
			<?php if (hasPerms($conn, "sessions", 1)): ?>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-toggle="dropdown">
						Sessions
					</a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/home/my_sessions.php">Personal</a>
						<a class="dropdown-item" href="/home/sessions/">Management</a>
					</div>
				</li>
			<?php else: ?>
				<li class="nav-item"><a class="nav-link" href="/home/my_sessions.php">Sessions</a></li>
			<?php endif; ?>

			<!-- PAYMENTS -->
			<?php if (hasPerms($conn, "payments", 1)): ?>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-toggle="dropdown">
						Payments
					</a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/home/my_payments.php">Personal</a>
						<a class="dropdown-item" href="/home/payments/">Management</a>
					</div>
				</li>
			<?php else: ?>
				<li class="nav-item"><a class="nav-link" href="/home/my_payments.php">Payments</a></li>
			<?php endif; ?>

			<!-- TEAM -->
			<?php if (hasPerms($conn, "payments", 1)): ?>
				<li class="nav-item"><a class="nav-link" href="/home/team/">Team</a></li>
			<?php endif; ?>

			<!-- ACCOUNT -->
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-toggle="dropdown">
					<?= $_SESSION['user'] ?>
				</a>
				<div class="dropdown-menu">
					<img src="<?= q($conn, "SELECT icon FROM employee WHERE name = ?", ['args'=>$_SESSION['user']]) ?>" alt="<?= $_SESSION['user'] ?>'s icon'" width="128" height="128">
					<a class="dropdown-item" href="#">Stats</a>
					<a class="dropdown-item" href="#">Profile</a>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="/src/logout.php">Logout</a>
				</div>
			</li>
		</ul>
	</div>
</nav>
