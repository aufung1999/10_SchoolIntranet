<?php
include './misc/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	foreach ($_POST as $k => $i) { echo $k . ":" . $i . "<br/>"; }

	if (!empty($_POST['id']) && !empty($_POST['password'])) { //Check if all field filled
		echo 'a';
		$sql = "SELECT user_id, user_password, user_type FROM `user` WHERE login_id='" . $_POST['id'] . "'"; 	//Get password hash to check password
		if ($result = mysqli_query($connect, $sql)) {
			$row = mysqli_fetch_array($result);
			echo 'a';
			if ($row && implode(null, $row) != null) { //Check if login ID exist
				echo 'a';
				if ($_POST['password'] == $_POST['confirmed_password']) {
					echo 'a';
					mysqli_query($connect, "DELETE FROM auth_tokens WHERE user_id='" . $row['user_id'] . "'");
					if (!mysqli_query($connect, "UPDATE user SET user_password='" . password_hash($_POST['password'], (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . "' WHERE user_id=" . $row['user_id']) === TRUE) {
						echo "Error updating record: " . $connect->error;
					}
				}
			}
		} else {
			echo "Error updating record: " . $connect->error;
		}
	}
}
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Forget password - Online Examination System</title>
	<link rel="stylesheet" href="css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="header">
		<h1 class="page-title">Forget Password</h1>
	</div>
	<div class="content">
		<form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
			<span class="box-title">Reset</span>
			<table class="box-form">
				<tr>
					<td class="form-label"><label for="id">Login ID:</label></td>
					<td class="form-input"><input type="text" name="id"></td>
				</tr>
				<tr>
					<td class="form-label"><label for="password"></label>Password:</td>
					<td class="form-input"><input type="password" name="password"></td>
				</tr>
				<tr>
					<td class="form-label"><label for="confirmed_password"></label>Confirmed
						Password:</td>
					<td class="form-input"><input type="password" name="confirmed_password"></td>
				</tr>
			</table>
			<div class="box-button">
				<button>Submit</button>
			</div>
		</form>
	</div>

</body>

</html>