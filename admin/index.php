<?php

include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {	//Delete User
	$checkbox = $_POST['check'];
	if (empty($checkbox)) {
		echo "You didn't select any buildings.";
	} else {
		$store_count = count($checkbox);
		for ($i = 0; $i < $store_count; $i++) {
			$del_id = $checkbox[$i];
			mysqli_query($connect, "DELETE FROM `user` WHERE user_id='" . $del_id . "'");
			$message = "Data deleted successfully !";
		}
	}
}

//This is retrieve data from database
$result_student = mysqli_query($connect, "SELECT user.*, student.* FROM user, student WHERE user.user_id=student.student_id AND user.user_type='student' ");
$result_teacher = mysqli_query($connect, "SELECT user.*, course.* FROM user, course WHERE user.user_id=course.teacher_id AND user.user_type='teacher' ");
$result_admin = mysqli_query($connect, "SELECT * FROM user WHERE user_type='admin' ");
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Admin Panel - Online Examination System</title>
	<link rel="stylesheet" href="../css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="fixed-top-right box-button">
		<a href="../misc/logout.php"><button>Logout</button></a>
	</div>
	<div class="header">
		<h1 class="page-title">Administration panel</h1>
		<p class="page-short-text">You can manage users information here. Use
			checkbox to batch delete. Click the row to edit the user information.
			To add new user, click add button</p>
	</div>
	<div class="content">
		<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" class="tab-box" id="formAdmin">
			<input type="radio" name="tab" id="tab-student" value="student" class="tab-radio" checked="checked" />
			<div class="tab">
				<label class="tab-title" for="tab-student">Student</label>
				<!--The Student part-->
				<!--Delete User-->
				<table class="tab-content">
					<tr>
						<th></th>
						<!-- Column for select box -->
						<th>ID</th>
						<!-- user_id / student_id -->
						<th>Avatar</th>
						<!-- profile_image -->
						<th>Login ID</th>
						<!-- login_id -->
						<th>Nickname</th>
						<!-- user_name -->
						<th>Email</th>
						<!-- user_email -->
						<th>Gender</th>
						<!-- student_gender -->
						<th>Birthday</th>
						<!-- student_birthday -->
					</tr>
					<?php
					if (mysqli_num_rows($result_student) > 0) {
					?>
						<?php
						$i = 0;
						while ($row = mysqli_fetch_array($result_student)) {
						?>
							<tr>
								<td><input type="checkbox" id="checkItem" name="check[]" value="<?php echo $row["user_id"]; ?>"></td>
								<!--Delete User-->
								<td><?php echo $row["user_id"] ?></td>
								<td><img class="avatar" src="<?php echo get_image($row['user_id']) ?>"></td>
								<td><?php echo $row["login_id"] ?></td>
								<td><?php echo $row["user_name"] ?></td>
								<td><?php echo $row["user_email"] ?></td>
								<td><?php echo $row["student_gender"] ?></td>
								<td><?php echo $row["student_birthday"] ?></td>
							</tr>
						<?php
							$i++;
						}
						?>
					<?php
					} else {
						echo "<tr><td colspan='8'>No result found</td></tr>";
					}
					?>
				</table>
				<label class="box-button" for="tab-student">
					<button type="submit" formaction="add.php">Add</button>
					<button type="submit" name="delete">Delete</button>
					<!--Delete User-->
					<button type="submit" formaction="edit.php">Edit</button>
					<!--only student Edit button activated-->
				</label>
				<!--Delete User-->
			</div>
			<!--End of Student part-->

			<!--The Teacher part-->
			<input type="radio" name="tab" id="tab-teacher" value="teacher" class="tab-radio" />
			<div class="tab">
				<label class="tab-title" for="tab-teacher">Teacher</label>
				<table class="tab-content">
					<tr>
						<th></th>
						<!-- Column for select box -->
						<th>ID</th>
						<!-- user_id / student_id -->
						<th>Avatar</th>
						<!-- profile_image -->
						<th>Login ID</th>
						<!-- login_id -->
						<th>Nickname</th>
						<!-- user_name -->
						<th>Email</th>
						<!-- user_email -->
						<th>Courses</th>
						<!-- query from course table -->
					</tr>
					<?php
					if (mysqli_num_rows($result_teacher) > 0) {
					?>
						<?php
						$i = 0;
						while ($row = mysqli_fetch_array($result_teacher)) {
						?>
							<tr>
								<td><input type="checkbox" id="checkItem" name="check[]" value="<?php echo $row["user_id"]; ?>"></td>
								<td><?php echo $row["user_id"] ?></td>
								<td><img class="avatar" src="data:<?php echo empty($row['image_type']) ? "image/png" : $row['image_type']; ?>;charset=utf8;base64,<?php echo empty($row['profile_image']) ? "iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAMAAAD04JH5AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACcUExUReTm59nc3cLHycHFyMTIy8XJy7zBw7i9wL7DxcXKy8PHysHGycbKzK60t620t7G3usHGyLW7vb3CxbC2ub/Exq2ztsPHybe8v7G2ubzBxLO5vMfKzcbLzbq+wbW6vauxtdDU1auytbS5vMXKzK+1uLvAwsTIyqyytcLGyLO4vLm+wLa7vqyytquxtLS6vMTHysPIyrC1ucfLzcLHyNxzYOIAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAK9SURBVHhe7Zptd9MwDIWrpGm3LtnChlfWjkFp1433Af//v5HQCyeF1JZdyTk7+PnYE+neyIprOxklEolEIpFIJBKJxPOFsnxcTKbTk9PZGeG3iFBZdTi/GEf1QPULKHe4jGeBrqD5Fy/jWNgv/h7mOoIFglg/c3UHZCB1AKPswKWv7cCtr+uAo9+g5oBeQcHBjZaDMQSc1AgQxv4AdtFpA1ogPYMlYkTJkJyFRgnmyM3iFkGC8DugRaELlkjNJEOYGMw56A9X0iXwasEW6RIUyMsmR6AUr5GXjfBU4NsCVXWHSCG8W6B6g0gh3iItH4NIIfwrUCFSiBxZPUCkEMnA4D3wDJ+CFSKF8J8J3yFSiinyspFeFq6Rl430v+Hg6wHfJtiILwpvkZmJ+Jpw+FXx6A65WawRJIlXG2rsjOgCyRmUiJFl6N2xx2SkdD4wog0EHNwrFYA7G52r6fPaQPOUjONAV989Ctr6rhos1PUbB5a/pW0E/YZDRYhx+zuo7jkznWbR9FuofIDwjjKq+g7Ky8f5+8WH2TqPe/MdKGsgiq1PebH9+GnVmRHM583JsrGCCxRpyv5lBdV/MUbXBI0XjH+jh22uYoIK/sLc5NIeKPc6qm4wks8lzfg330GqCvQ1SL5lImCB6mD5lqdjLdA3ZArFHPVen7w35X2EO+BvBOzMwiwcOfpdgt6p0w2iJQhYKdIEsTJ4OyDfmc+FpwNx/QYfB3SPIEk8aiA8/r/hO6gRIQ335Mz/WJbLdyjY8TmK8YVVgmtcrAGnDfQGoKWAioXDH2pJ4C6BbgEYB3giKwALrhJoF8B5in2Gy/RwfNlwissUsZdgf9OvgvUYVWoVaOMJWr0EvKH1x9YE2g/hL2xNwP5e7hgsBqgy+tgMBLwjDyAZSAYGN/ADaj3EMWCZCiMZODwGycB/aGA0+gkQ90Qn3n0uvgAAAABJRU5ErkJggg==" : base64_encode($row['profile_image']); ?>" alt="Avatar of <?php echo $row["user_name"] ?>"></td>
								<td><?php echo $row["login_id"] ?></td>
								<td><?php echo $row["user_name"] ?></td>
								<td><?php echo $row["user_email"] ?></td>
								<td><?php echo $row["course_name"] ?></td>
							</tr>
						<?php
							$i++;
						}
						?>
					<?php
					} else {
						echo "<tr><td colspan='8'>No result found</td></tr>";
					}
					?>
				</table>
				<label class="box-button" for="tab-student">
					<button type="submit" formaction="add.php">Add</button>
					<button type="submit" name="delete">Delete</button>
					<button type="submit" formaction="edit.php">Edit</button>
				</label>
			</div>
			<!--End of teacher-->

			<!--The Admin part-->
			<input type="radio" name="tab" id="tab-admin" value="admin" class="tab-radio" />
			<div class="tab">
				<label class="tab-title" for="tab-admin">Admin</label>
				<table class="tab-content">
					<tr>
						<th></th>
						<!-- Column for select box -->
						<th>ID</th>
						<!-- user_id / student_id -->
						<th>Avatar</th>
						<!-- profile_image -->
						<th>Login ID</th>
						<!-- login_id -->
						<th>Nickname</th>
						<!-- user_name -->
						<th>Email</th>
						<!-- user_email -->
					</tr>
					<?php
					if (mysqli_num_rows($result_admin) > 0) {
					?>
						<?php
						$i = 0;
						while ($row = mysqli_fetch_array($result_admin)) {
						?>
							<tr>
								<td><input type="checkbox" id="checkItem" name="check[]" value="<?php echo $row["user_id"]; ?>"></td>
								<td><?php echo $row["user_id"] ?></td>
								<td><img class="avatar" src="data:<?php echo empty($row['image_type']) ? "image/png" : $row['image_type']; ?>;charset=utf8;base64,<?php echo empty($row['profile_image']) ? "iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAMAAAD04JH5AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACcUExUReTm59nc3cLHycHFyMTIy8XJy7zBw7i9wL7DxcXKy8PHysHGycbKzK60t620t7G3usHGyLW7vb3CxbC2ub/Exq2ztsPHybe8v7G2ubzBxLO5vMfKzcbLzbq+wbW6vauxtdDU1auytbS5vMXKzK+1uLvAwsTIyqyytcLGyLO4vLm+wLa7vqyytquxtLS6vMTHysPIyrC1ucfLzcLHyNxzYOIAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAK9SURBVHhe7Zptd9MwDIWrpGm3LtnChlfWjkFp1433Af//v5HQCyeF1JZdyTk7+PnYE+neyIprOxklEolEIpFIJBKJxPOFsnxcTKbTk9PZGeG3iFBZdTi/GEf1QPULKHe4jGeBrqD5Fy/jWNgv/h7mOoIFglg/c3UHZCB1AKPswKWv7cCtr+uAo9+g5oBeQcHBjZaDMQSc1AgQxv4AdtFpA1ogPYMlYkTJkJyFRgnmyM3iFkGC8DugRaELlkjNJEOYGMw56A9X0iXwasEW6RIUyMsmR6AUr5GXjfBU4NsCVXWHSCG8W6B6g0gh3iItH4NIIfwrUCFSiBxZPUCkEMnA4D3wDJ+CFSKF8J8J3yFSiinyspFeFq6Rl430v+Hg6wHfJtiILwpvkZmJ+Jpw+FXx6A65WawRJIlXG2rsjOgCyRmUiJFl6N2xx2SkdD4wog0EHNwrFYA7G52r6fPaQPOUjONAV989Ctr6rhos1PUbB5a/pW0E/YZDRYhx+zuo7jkznWbR9FuofIDwjjKq+g7Ky8f5+8WH2TqPe/MdKGsgiq1PebH9+GnVmRHM583JsrGCCxRpyv5lBdV/MUbXBI0XjH+jh22uYoIK/sLc5NIeKPc6qm4wks8lzfg330GqCvQ1SL5lImCB6mD5lqdjLdA3ZArFHPVen7w35X2EO+BvBOzMwiwcOfpdgt6p0w2iJQhYKdIEsTJ4OyDfmc+FpwNx/QYfB3SPIEk8aiA8/r/hO6gRIQ335Mz/WJbLdyjY8TmK8YVVgmtcrAGnDfQGoKWAioXDH2pJ4C6BbgEYB3giKwALrhJoF8B5in2Gy/RwfNlwissUsZdgf9OvgvUYVWoVaOMJWr0EvKH1x9YE2g/hL2xNwP5e7hgsBqgy+tgMBLwjDyAZSAYGN/ADaj3EMWCZCiMZODwGycB/aGA0+gkQ90Qn3n0uvgAAAABJRU5ErkJggg==" : base64_encode($row['profile_image']); ?>" alt="Avatar of <?php echo $row["user_name"] ?>"></td>
								<td><?php echo $row["login_id"] ?></td>
								<td><?php echo $row["user_name"] ?></td>
								<td><?php echo $row["user_email"] ?></td>
							</tr>
						<?php
							$i++;
						}
						?>
					<?php
					} else {
						echo "<tr><td colspan='8'>No result found</td></tr>";
					}
					?>
				</table>
				<label class="box-button" for="tab-admin">
					<button type="submit" formaction="add.php">Add</button>
					<button type="submit" name="delete">Delete</button>
					<button type="submit" formaction="edit.php">Edit</button>
				</label>
				<!--End of Admin part-->
		</form>
	</div>
</body>

</html>