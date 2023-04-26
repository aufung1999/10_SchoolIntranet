<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('teacher');

$result_course = mysqli_query($connect, "SELECT * FROM course WHERE teacher_id=$user_id ");
//$result_question = mysqli_query($connect, "SELECT question.question_title, question.question_type FROM question, question_choice, answer, student_answer WHERE question.question_id=question_choice.question_id AND question_choice.answer_id=answer.answer_id AND answer.answer_id=student_answer.answer_id");
$result_exam_info = mysqli_query($connect, "SELECT course.* , exam.* FROM course, exam WHERE course.course_id=exam.course_id AND course.teacher_id=$user_id ");
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Teacher - Online Examination System</title>
	<link rel="stylesheet" href="../css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="fixed-top-right box-button">
		<a href="../misc/logout.php"><button>Logout</button></a>
	</div>
	<div class="header image">
		<img class="avatar" src="<?php echo get_image($user_id) ?>" alt="Your Avatar">
		<h1 class="page-title">Hi, <?php echo get_name($user_id) ?>!</h1>
		<p class="page-short-text">
			You may create new exams, complete evaluation, or just view result of past exams.
		</p>
	</div>
	<div class="content">
		<div class="box">
			<span class="box-title">Examinations</span>
			<ul class="box-list">
				<a href="new-exam.php" class="box-item">
					<p class="item-title">+ Create new examination</p>
				</a>
				<?php
				if (mysqli_num_rows($result_exam_info) > 0) {
					$i = 0;
					while ($row = mysqli_fetch_array($result_exam_info)) {
					?>
						<li class="box-item">
							<p class="item-title"><?php echo $row["course_name"] . " " . $row["exam_title"] ?></p>
							<p class="subheading"><?php echo "Exam date: " . $row["exam_date_start"] . " - " . $row["exam_date_end"] ?></p>
							<div class="box-button">
								<form method="post" action="list-exam.php">
									<input type="hidden" name="exam_id" class="hide" value="<?php echo $row["exam_id"] ?>" readonly>
									<button onclick="this.parentNode.submit();">View</button>
								</form>
							</div>

						</li>
					<?php
						$i++;
					}
				}
				?>


			</ul>
			<div class="box-button"><a href="course.php"><button>Course assign</button></a></div>
		</div>
	</div>


</body>

</html>