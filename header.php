<nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse">
  <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
	<span class="navbar-toggler-icon"></span>
  </button>
  <a class="navbar-brand" href="/">Tempus</a>

  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
	<ul class="navbar-nav mr-auto">
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
    </ul>

	<ul class="my-2 my-md-0">
		<li class="nav-item dropdown">
		  <a class="nav-link dropdown-toggle nav-item" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $_SESSION['user'] ?></a>
		  <div class="dropdown-menu" aria-labelledby="dropdown01">
			  <img src="<?= q($conn, "SELECT icon FROM employee WHERE name = ?", ['args'=>$_SESSION['user']]) ?>" alt="<?= $_SESSION['user'] ?>'s icon'" width="128" height="128">
  			<a class="dropdown-item" href="/home/team/view_user.php?user=<?= $_SESSION['user'] ?>">Stats</a>
  			<a class="dropdown-item" href="/home/team/edit_user.php?user=<?= $_SESSION['user'] ?>">Profile</a>
  			<div class="dropdown-divider"></div>
  			<a class="dropdown-item" href="/src/logout.php">Logout</a>
		  </div>
		</li>
	<ul>
  </div>
</nav>
