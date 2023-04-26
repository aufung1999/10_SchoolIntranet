<?php

include '../misc/connect.php';
session_start();
mysqli_query($connect, "DELETE FROM auth_token WHERE expires < GETDATE()");

//print_r($_SESSION);

if (!empty($_COOKIE['remember'])) {
    list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
    mysqli_query($connect, "DELETE FROM auth_tokens WHERE selector='" . $selector . "'");
}

setcookie('remember', '', time() - 3600); // Remove the cookie

if (!empty($_SESSION['selector'])) {
    mysqli_query($connect, "DELETE FROM auth_tokens WHERE selector='" . $_SESSION['selector'] . "'");
}

session_unset();
session_destroy(); // Destroy the session

header("Location: ../login.php");
die();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Log out - Online Examination System</title>
	<link rel="stylesheet" href="../css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="header">
		<h1 class="page-title">Log out</h1>
	</div>
	<div class="content">
        You have logged out. Please click <a href="../login.php">here back to login page</a>.
	</div>
</body>

</html>