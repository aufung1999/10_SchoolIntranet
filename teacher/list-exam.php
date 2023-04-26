<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('teacher');

$result_student = mysqli_query($connect, "SELECT * FROM student_exam WHERE exam_id=" . $_POST['exam_id'] . " ");
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Exam overview - Teacher - Online Examination System</title>
	<link rel="stylesheet" href="../css/style.css">
	<!-- <script src="js/script.js"></script> -->
</head>

<body>
	<!-- page content -->
	<div class="header">
		<h1 class="page-title">Overview</h1>
		<p class="page-short-text">
			This is a overview for
			<?php
			$sql_exam = "SELECT * FROM exam, course WHERE exam_id=" . $_POST['exam_id'] . " AND course.course_id = exam.course_id";
			$result_exam = mysqli_query($connect, $sql_exam);
			$row_exam = mysqli_fetch_array($result_exam);
			echo $row_exam['course_name'] . " " . $row_exam['exam_title'];
			?>
		</p>
	</div>
	<div class="content">

		<div class="box">
			<span class="box-title">Overview</span>
			<ul class="box-list">
				<li class="box-item">
					<p class="item-title">Max score</p>
					<p class="subheading">
						<?php
						$result_overall = mysqli_query($connect, "SELECT *, sum(answer_score) AS 'total answer score' FROM student_answer GROUP BY student_id ORDER BY 'total answer score' DESC LIMIT 1 ");

						if (mysqli_num_rows($result_overall) > 0) {
							while ($row = mysqli_fetch_array($result_overall)) {
								echo $row['total answer score'];
							}
						}
						?>
					</p>
				</li>
				<li class="box-item">
					<p class="item-title">Min score</p>
					<p class="subheading">
						<?php
						$result_overall = mysqli_query($connect, "SELECT *, sum(answer_score) AS 'total answer score' FROM student_answer GROUP BY student_id,answer_score ORDER BY 'total answer score' ASC LIMIT 1");

						if (mysqli_num_rows($result_overall) > 0) {
							while ($row = mysqli_fetch_array($result_overall)) {
								echo $row["total answer score"];
							}
						}
						?>
					</p>
				</li>
				<li class="box-item">
					<p class="item-title">Median</p>
					<p class="subheading">
						<?php
						$result_overall = mysqli_query($connect, " SELECT *,sum(answer_score) from student_answer GROUP BY student_id ");
						$array = array();
						$count = -1;
						if (mysqli_num_rows($result_overall) > 0) {
							while ($row = mysqli_fetch_array($result_overall)) {
								$array[] = $row;
								$row["sum(answer_score)"];
								$count++;
							}
						}
						if (($count % 2) != 0) {
							$median_even = ($array[$count / 2]["sum(answer_score)"] + ($array[($count / 2) + 1]["sum(answer_score)"])) / 2;
							echo $median_even;
						} else {
							$median_odd = $array[round($count / 2)]["sum(answer_score)"];
							echo $median_odd;
						}
						?>
					</p>
				</li>
				<li class="box-item">
					<p class="item-title">Average</p>
					<p class="subheading">
						<?php
						$result_overall = mysqli_query($connect, " SELECT *,sum(answer_score) from student_answer GROUP BY student_id ");
						$array = array();
						$count_for_database_position = -1;
						$count_for_calculation = 0;
						$store_value = 0;
						if (mysqli_num_rows($result_overall) > 0) {
							while ($row = mysqli_fetch_array($result_overall)) {
								$array[] = $row;
								$row["sum(answer_score)"];
								$count_for_database_position++;
								$count_for_calculation++;
							}
							for ($i = 0; $i <= $count_for_database_position; $i++) {
								$store_value += $array[$i]["sum(answer_score)"];
							}
							echo $store_value / $count_for_calculation;
						}
						?>
					</p>
				</li>
				<?php
				$count_student = 0;
				$correct_answer = 0;
				$student_answer_score = 0;
				$question_answer_score = 0;
				$result_overall = mysqli_query($connect, "SELECT question_score,answer_score,count(student_answer.student_id),sum(is_answer) FROM question_choice,answer,student_answer,exam_question WHERE student_answer.answer_id=answer.answer_id AND answer.answer_id=question_choice.answer_id AND student_answer.question_id=exam_question.question_id  GROUP BY student_answer.question_id ");

				if (mysqli_num_rows($result_overall) > 0) {
					while ($row = mysqli_fetch_array($result_overall)) {
						$count_student = $row["count(student_answer.student_id)"];
						$correct_answer = $row["sum(is_answer)"];
						$student_answer_score = $row["answer_score"];
						$question_answer_score = $row["question_score"];
					}
					$Percentage = $correct_answer / $count_student * 100 . "%";
					$Average_marks = $student_answer_score * $correct_answer / $count_student . " Marks";
				}
				?>

				<li class="box-item">
					<p class="item-title">Correct answer percentage/question</p>
					<p class="subheading">
						<?php echo $Percentage; ?>
					</p>
				</li>
				<li class="box-item">
					<p class="item-title">Average score/question</p>
					<p class="subheading">
						<?php echo $Average_marks; ?>
					</p>
				</li>
			</ul>
		</div>
		<div class="box">
			<span class="box-title">Student List</span>
			<ul class="box-list">
				<?php
				$result_overall = mysqli_query($connect, "SELECT *,sum(answer_score) from student_answer, user WHERE user.user_id=student_answer.student_id GROUP BY student_id");

				if (mysqli_num_rows($result_overall) > 0) {
					while ($row = mysqli_fetch_array($result_overall)) {
				?>
						<li class="box-item">
							<p class="item-title">Name: <?php echo $row['user_name'] ?></p>
							<p class="subheading">Total score:<?php echo $row['sum(answer_score)'] ?></p>
							<form class="box-button" method="post" action="student-exam.php">
								<input type="hidden" name="student_id" class="hide" value="<?php echo $row["student_id"] ?>" readonly>
								<button onclick="this.parentNode.submit();">View Result</button>
							</form>
						</li>
				<?php
					}
				}
				?>
			</ul>
		</div>

	</div>

</body>

</html>