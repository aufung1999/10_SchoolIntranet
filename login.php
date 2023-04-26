<?php
include './misc/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	//foreach ($_POST as $k => $i) { echo $k . ":" . $i . "<br/>"; }

	if (!empty($_POST['id']) && !empty($_POST['password'])) { //Check if all field filled
		$sql = "SELECT user_id, user_password, user_type FROM `user` WHERE login_id='" . $_POST['id'] . "'"; 	//Get password hash to check password
		if ($result = mysqli_query($connect, $sql)) {
			$row = mysqli_fetch_array($result);
			if ($row && implode(null, $row) != null) { //Check if login ID exist
				if (password_verify($_POST['password'], $row['user_password'])) {

					session_start();

					$selector = base64_encode(random_bytes(9));
					$authenticator = random_bytes(33);

					$_SESSION['selector'] = $selector;
					if (!mysqli_query($connect, "INSERT INTO auth_token (selector, token, user_id, expires) VALUES ('" . $selector . "', '" .
						hash('sha256', $authenticator) . "', " . $row['user_id'] . ", '" . date('Y-m-d H:i:s', time() + 86400) . "')")) {
						die("Error updating record: " . $connect->error);
					}

					if (!empty($_POST['remember'])) {
						setcookie('remember', $selector . ':' . base64_encode($authenticator), time() + 86400);
					}

					header("Location: ./" . $row['user_type'], true, 302);
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
	<title>Login - Online Examination System</title>
	<link rel="stylesheet" href="css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="header">
		<h1 class="page-title">Examination system</h1>
		<p class="page-short-text">
			Not have a account? <a href="register.php">Register</a>
		</p>
	</div>
	<div class="content">
		<form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="formAuth">
			<span class="box-title">Login</span> <!-- <span> only for adding some css-->
			<table class="box-form">
				<tr>
					<td class="form-label"><label for="id">Login ID</label></td>
					<td class="form-input"><input type="text" name="id" value="<?php echo (!empty($_POST['id'])) ? $_POST['id'] : "" ?>"></td>
					<!--USER type login_ID  MAY NEED USE name & value-->
					<td class="form-msg">
						<?php if ($_SERVER['REQUEST_METHOD'] != 'POST') {
						} else if (empty($_POST['id'])) {
							echo "Please fill in your login ID.";
						} else if (!empty($_POST['id']) && !empty($_POST['password']) && (empty($row) || implode(null, $row) == null)) {
							echo "This login ID not exist.";
						} ?>
					</td>
				</tr>
				<tr>
					<td class="form-label"><label for="password">Password</label></td>
					<td class="form-input"><input type="password" name="password" value="<?php echo (!empty($_POST['password'])) ? $_POST['password'] : "" ?>"></td>
					<!--USER password-->
					<td class="form-msg">
						<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST['password'])) {
							echo "Please fill in your password.";
						} else if (!empty($_POST['id']) && !empty($_POST['password']) && $row && implode(null, $row) != null) {
							echo "Wrong password.";
						} ?>
					</td>
				</tr>
				<tr>
					<td class="form-label" colspan="2">
						<input type="checkbox" name="remember" id="remember" <?php echo (!empty($_POST['remember'])) ? 'checked="checked"' : ''; ?> />
						<label for="remember">Remember Me</label></td>
				</tr>
			</table>
			<div class="box-button"><a href="forget.php">Forget Password?</a><button>Login</button></div>
		</form>
	</div>

</body>

</html>