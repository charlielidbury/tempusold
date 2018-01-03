<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Login</title>
	</head>
	<body>
		<h1><a href="index.php">Tempus</a></h1>
		<h2><a href="login.php">Login</a></h2>

		<p>Please login here; to create an account please contact an admin who can make one for you.</p>

		<form action="process.php" method="POST">
			Username: <input type="text" name="username" /> <br/>
			Password: <input type="password" name="password" /> <br/>

			<input type="submit" value="Login" name="submit" />
		</form>
	</body>
</html>
