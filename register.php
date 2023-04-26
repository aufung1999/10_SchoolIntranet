<?php
include './misc/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	//foreach ($_POST as $k => $i) {echo $k . ":" . $i . "<br/>";}

	$error = false;
	if (!(empty($_POST['id']) || empty($_POST['password']) || empty($_POST['type']) || $_POST['type'] == "Please select..." || //Check required informations exits
		($_POST['type'] == 'teacher' && (empty($_POST['course_title']) || empty($_POST['course_code']) || //Check if teacher have cource details
			substr_count($_POST['course_title'], '-') || preg_match('/^[A-Za-z0-9]{1,}$/i', $_POST['course_code']) != 1)) || //Check if course code and title valid
		preg_match('/^[A-Za-z0-9_-]{1,}$/i', $_POST['id']) != 1 || //Check if username valid
		(!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)))) { //Check if email valid
		$sql_user = "insert into user(login_id,user_name,user_password,user_email,profile_image,image_type,user_type) values(?, ?, ?, ?, ?, ?, ?)";

		$sql = 'SELECT login_id FROM `user` WHERE login_id="' . $_POST['id'] . '"';
		$result = mysqli_query($connect, $sql);

		$row = mysqli_fetch_array($result);
		if (!($row) || implode(null,$row) == null) { //Check if login ID not exist
			if ($stmt = mysqli_prepare($connect, $sql_user)) {
				$stmt->bind_param('ssssbss', $login_id, $user_name, $user_password, $user_email, $profile_image, $image_type, $user_type);

				$login_id = $_POST['id'];

				if (!empty($_POST['name'])) $user_name = $_POST['name'];
				$user_password = password_hash($_POST['password'], (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT)));
				if (!empty($_POST['email'])) $user_email = $_POST['email'];

				if (count($_FILES) > 0) {
					if (is_uploaded_file($_FILES['avatar']['tmp_name'])) {
						if (empty($_FILES['avatar']['type'])) { // From PHP docs, mime not gurantee to exist
							$fileType = pathinfo(basename($_FILES["image"]["name"]), PATHINFO_EXTENSION);
							switch ($fileType) {
								case 'gif':
									$fileType = 'image/gif';
									break;
								case 'png':
									$fileType = 'image/png';
									break;
								case 'jpg':
								case 'jpeg':
									$fileType = 'image/jpeg';
									break;
								case 'bmp':
									$fileType = 'image/bmp';
									break;
								case 'webp':
									$fileType = 'image/webp';
									break;
							}
						} else {
							$fileType = $_FILES['avatar']['type'];
						}

						$allowTypes = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/webp');
						if (in_array($fileType, $allowTypes)) {
							$image_type = $fileType;
							$stmt->send_long_data(4, file_get_contents($_FILES['avatar']['tmp_name']));
						}
					}
				}

				$user_type = strtolower($_POST['type']);

				mysqli_stmt_execute($stmt);

				$user_id = mysqli_insert_id($connect);
				mysqli_stmt_close($stmt);
				switch ($user_type) {
					case 'student':
						$sql_second = "insert into student(student_id,student_gender,student_birthday) values(?, ?, ?)";
						if ($stmt = mysqli_prepare($connect, $sql_second)) {
							$stmt->bind_param('iss', $student_id, $student_gender, $student_birthday);
							$student_id = $user_id;
							if (!empty($_POST['gender'])) $student_gender = strtolower($_POST['gender'][0]);
							if (!empty($_POST['birthday'])) $student_birthday = $_POST['birthday'];
							mysqli_stmt_execute($stmt);
							mysqli_stmt_close($stmt);
						} else {
							die("Could not successfully run query ($sql_second) from $db: " . mysqli_error($connect));
						}
						break;

					case 'teacher':
						$sql_second = "insert into course(teacher_id,course_name) values(?, ?)";
						if ($stmt = mysqli_prepare($connect, $sql_second)) {
							$stmt->bind_param('is', $teacher_id, $course_name);
							$teacher_id = $user_id;
							$course_name = $_POST['course_code'] . " - " . $_POST['course_title'];
							mysqli_stmt_execute($stmt);
							mysqli_stmt_close($stmt);
						} else {
							die("Could not successfully run query ($sql_second) from $db: " . mysqli_error($connect));
						}
						break;
				}
			} else {
				echo "Error: " . $sql_user . "<br>" . mysqli_error($connect);
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Register - Online Examination System</title>
	<link rel="stylesheet" href="css/style.css">

	<script src="jsdataVal.js"></script>
</head>

<body>

	<!-- page content -->
	<div class="header">
		<h1 class="page-title">Examination system</h1>
		<?php if (!isset($login_id)) { ?>
			<p class="page-short-text">
				Already have a account? <a href="login.php">Login</a>
			</p>
		<?php } ?>
	</div>
	<div class="content">
		<?php if (!isset($login_id)) { ?>
			<form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="formAuth" enctype="multipart/form-data">
				<span class="box-title">Register</span>
				<table class="box-form">
					<tr>
						<td class="form-label"><label for="id">Login ID</label></td>
						<td class="form-input"><input type="text" name="id" value="<?php echo (!empty($_POST['id'])) ? $_POST['id'] : "" ?>"></td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (empty($_POST['id'])) {
									echo "Please fill in your login ID.";
								} else if (preg_match('/^[A-Za-z0-9_-]{1,}$/i', $_POST['id']) != 1) {
									echo "Please just use alphanumeric characters (A-Z, a-z, 0-9), dash (-), or underscore (_).";
								} else if (!isset($login_id)) {
									echo "This login ID is already in use.";
								}
							} ?>
						</td>
					</tr>
					<tr>
						<td class="form-label"><label for="name">Nick name</label></td>
						<td class="form-input"><input type="text" name="name" value="<?php echo (!empty($_POST['name'])) ? $_POST['name'] : "" ?>"></td>
						<td class="form-msg"></td>
					</tr>
					<tr>
						<td class="form-label"><label for="password">Password</label></td>
						<td class="form-input"><input type="password" name="password" value="<?php echo (!empty($_POST['password'])) ? $_POST['password'] : "" ?>"></td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (empty($_POST['password'])) {
									echo "Please fill in your password.";
								}
							} ?>
						</td>
					</tr>
					<tr>
						<td class="form-label"><label for="email">Email</label></td>
						<td class="form-input"><input type="email" name="email" value="<?php echo (!empty($_POST['email'])) ? $_POST['email'] : "" ?>"></td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
									echo "Please input a valid email.";
								}
							} ?>
						</td>
					</tr>
					<tr>
						<td class="form-label"><label for="avatar">Profile Image</label></td>
						<td class="form-input"><input type="file" name="avatar"></td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (count($_FILES) > 0) {
									if (is_uploaded_file($_FILES['avatar']['tmp_name'])) {
										if (!in_array($fileType, $allowTypes)) {
											echo 'Sorry, only JPEG, PNG, GIF, BMP and WEBP files are allowed.';
										}
									}
								}
							} ?>
						</td>
					</tr>
					<tr>
						<td class="form-label"><label for="type">User type</label></td>
						<td class="form-input">
							<select name="type" id="type">
								<option>Please select...</option>
								<?php
								echo "<option";
								if (!empty($_POST['type']) && strtolower($_POST['type']) == 'student') {
									echo ' selected="selected"';
								}
								echo ">Student</option>";
								echo "<option";
								if (!empty($_POST['type']) && strtolower($_POST['type']) == 'teacher') {
									echo ' selected="selected"';
								}
								echo ">Teacher</option>";
								?>
							</select>
						</td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (empty($_POST['type']) || $_POST['type'] == "Please select...") {
									echo "Please choose your account type.";
								}
							} ?>
						</td>
					</tr>
					<tr>
						<td class="form-label"><label for="gender">Gender</label></td>
						<td class="form-input">
							<select name="gender" id="gender">
								<option></option>
								<?php
								echo "<option";
								if (!empty($_POST['gender']) && $_POST['gender'] == 'Male') {
									echo ' selected="selected"';
								}
								echo ">Male</option>";
								echo "<option";
								if (!empty($_POST['gender']) && $_POST['gender'] == 'Female') {
									echo ' selected="selected"';
								}
								echo ">Female</option>";
								echo "<option";
								if (!empty($_POST['gender']) && $_POST['gender'] == 'Other') {
									echo ' selected="selected"';
								}
								echo ">Other</option>";
								?>
							</select>
						</td>
						<td class="form-msg"></td>
					</tr>
					<tr>
						<td class="form-label"><label for="birthday">Birthday</label></td>
						<td class="form-input"><input type="date" id="birthday" name="birthday" value="<?php echo (!empty($_POST['birthday'])) ? $_POST['birthday'] : "" ?>"></td>
						<td class="form-msg"></td>
					</tr>
					<tr>
						<td class="form-label"><label for="course">Course Code</label></td>
						<td class="form-input"><input type="text" name="course_code" id="course_code" value="<?php echo (!empty($_POST['course_code'])) ? $_POST['course_code'] : "" ?>"></td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (strtolower($_POST['type']) == 'teacher') {
									if (empty($_POST['course_code'])) {
										echo "Please fill in your course code.";
									} else if (preg_match('/^[A-Za-z0-9]{1,}$/i', $_POST['course_code']) != 1) { //Check if course code and title valid
										echo "Please just use alphanumeric characters (A-Z, a-z, 0-9).";
									}
								}
							} ?>
						</td>
					</tr>
					<tr>
						<td class="form-label"><label for="course">Course Title</label></td>
						<td class="form-input"><input type="text" name="course_title" id="course_title" value="<?php echo (!empty($_POST['course_title'])) ? $_POST['course_title'] : "" ?>"></td>
						<td class="form-msg">
							<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
								if (strtolower($_POST['type']) == 'teacher') {
									if (empty($_POST['course_title'])) {
										echo "Please fill in your course title.";
									} else if (substr_count($_POST['course_title'], '-')) { //Check if course code and title valid
										echo "Please do not use dash/hyphen (-).";
									}
								}
							} ?>
						</td>
					</tr>
				</table>
				<div class="box-button">
					<button type="reset">Reset</button>
					<button type="submit" id="btnReg">Register</button>
				</div>
			</form>
		<?php } else { ?>
			<div class="content">
				<div class="box">
					<span class="box-title">Registration completed</span>
					<div class="box-content">
						Hi, <?php echo (!empty($_POST['name'])) ? $_POST['name'] : $_POST['id'] ?>! Thank you for your registration. We are looking forward to your exploration on our site! Please <a href="login.php">log in</a> to continue your jorney!
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<script src="js/register.js"></script>
</body>

</html>