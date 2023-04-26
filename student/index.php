<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('student');

$result_current_exam = mysqli_query($connect, "SELECT exam_id,exam_title,exam_date_start,exam_date_end ,course_name FROM exam, course WHERE exam.exam_date_start < NOW() AND exam.exam_date_end > NOW() AND exam.course_id IN (SELECT course_id FROM student_course WHERE student_course.student_id = $user_id) AND exam.course_id=course.course_id ");
$result_current_exam_count = mysqli_query($connect, "SELECT COUNT(exam_id) AS count FROM exam, course WHERE exam.exam_date_start < NOW() AND exam.exam_date_end > NOW() AND exam.course_id IN (SELECT course_id FROM student_course WHERE student_course.student_id = $user_id) AND exam.course_id=course.course_id ");

$result_past_exam = mysqli_query($connect, "SELECT * FROM exam, course WHERE exam.exam_id IN (SELECT exam_id FROM student_exam WHERE student_exam.student_id = $user_id) AND exam.exam_date_end < NOW() AND exam.course_id=course.course_id ");
$result_past_exam_count = mysqli_query($connect, "SELECT COUNT(exam_id) AS count FROM exam, course WHERE exam.exam_id IN (SELECT exam_id FROM student_exam WHERE student_exam.student_id = $user_id) AND exam.exam_date_end < NOW() AND exam.course_id=course.course_id ");

$result_upcoming_exam = mysqli_query($connect, "SELECT exam_id,exam_title,exam_date_start,exam_date_end ,course_name FROM exam, course WHERE exam.exam_date_start > NOW() AND exam.course_id IN (SELECT course_id FROM student_course WHERE student_course.student_id = $user_id) AND exam.course_id=course.course_id ");
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Student - Online Examination System</title>
	<link rel="stylesheet" href="../css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="fixed-top-right box-button">
		<a href="update-info.php"><button>Update Information</button></a>
		<a href="../misc/logout.php"><button>Logout</button></a>
	</div>
	<div class="header image">
		<img class="avatar" src="<?php echo get_image($user_id) ?>" alt="Your Avatar">
		<h1 class="page-title">Hi, <?php echo get_name($user_id) ?>!</h1>
		<p class="page-short-text">
			<?php
			if (mysqli_num_rows($result_current_exam_count) > 0) {
				if ($row = mysqli_fetch_array($result_current_exam_count)) {
					echo 'Currently, there are ' . $row['count'] . ' exam' . ($row['count'] > 1 ? 's' : '') . ' you can take. ';
				}
			} else {
				echo 'There are no exam you can take now. ';
			}
			if (mysqli_num_rows($result_past_exam_count) > 0) {
				if ($row = mysqli_fetch_array($result_past_exam_count)) {
					echo 'Or you may see the result of ' . $row['count'] . ' of your past exam' . ($row['count'] > 1 ? 's' : '') . '.';
				}
			} ?>
		</p>
	</div>

	<div class="content">
		<div class="box">
			<span class="box-title">Current exam</span>
			<ul class="box-list">
				<?php
				if (mysqli_num_rows($result_current_exam) > 0) {
					while ($row = mysqli_fetch_array($result_current_exam)) {
				?>
						<li class="box-item">
							<p class="item-title"><?php echo $row['course_name'] . ' ' . $row['exam_title'] ?></p>
							<p class="subheading"><?php echo $row['exam_date_start'] ?> - <?php echo $row['exam_date_end'] ?></p>
							<form class="box-button" method="post" action="take-exam.php">
								<input type="hidden" name="exam_id" class="hide" value="<?php echo $row["exam_id"] ?>" readonly>
								<button onclick="this.parentNode.submit();">Start</button>
							</form>
						</li>
				<?php }
				} else { ?>
					<li class="box-item">
						<p class="item-title">No current exam now.</p>
						<p class="subheading">Please come back to see later.</p>
					</li>
				<?php
				} ?>
			</ul>
		</div>
		<div class="box">
			<span class="box-title">Past exam</span>
			<ul class="box-list">
				<?php
				if (mysqli_num_rows($result_past_exam) > 0) {
					while ($row = mysqli_fetch_array($result_past_exam)) {
				?>
						<li class="box-item">
							<p class="item-title"><?php echo $row['course_name'] . ' ' . $row['exam_title'] ?></p>
							<p class="subheading"><?php echo $row['exam_date_start'] ?> - <?php echo $row['exam_date_end'] ?></p>
							<form class="box-button" method="post" action="view-result.php">
								<input type="hidden" name="exam_id" class="hide" value="<?php echo $row["exam_id"] ?>" readonly>
								<button onclick="this.parentNode.submit();">View Result</button>
							</form>
						</li>
				<?php }
				} else { ?>
					<li class="box-item">
						<p class="item-title">No past exam now.</p>
						<p class="subheading">Please see upcoming exam to take more exam.</p>
					</li>
				<?php
				} ?>
			</ul>
		</div>
		<div class="box">
			<span class="box-title">Upcoming exam</span>
			<ul class="box-list">
				<?php
				if (mysqli_num_rows($result_upcoming_exam) > 0) {
					while ($row = mysqli_fetch_array($result_upcoming_exam)) {
				?>
						<li class="box-item">
							<p class="item-title"><?php echo $row['course_name'] . ' ' . $row['exam_title'] ?></p>
							<p class="subheading"><?php echo $row['exam_date_start'] ?> - <?php echo $row['exam_date_end'] ?></p>
						</li>
					<?php }
				} else { ?>
					<li class="box-item">
						<p class="item-title">No upcoming exam now.</p>
						<p class="subheading">Please come back to see later.</p>
					</li>
				<?php
				} ?>
			</ul>
		</div>
	</div>

</body>

</html>