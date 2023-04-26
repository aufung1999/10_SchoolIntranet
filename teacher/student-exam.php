<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('teacher');

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
    <div class="header">
        <h1 class="page-title" value=''>Student <?php echo get_name($_POST['student_id']) ?> result</h1>
    </div>
    <div class="content">
        <div class="box">
            <span class="box-title">Question</span>
            <ul class="box-list">
                <?php
                $result_per_student_question = mysqli_query($connect, "SELECT question.question_title, question.question_id FROM student_answer, exam_question, question WHERE student_answer.question_id=exam_question.question_id AND question.question_id=exam_question.question_id AND student_answer.student_id=" . $_POST['student_id'] . " GROUP BY question.question_id");
                $result_per_student_selected_ans = mysqli_query($connect, "SELECT answer.answer_text FROM student_answer,answer WHERE student_answer.answer_id=answer.answer_id AND student_answer.student_id=" . $_POST['student_id'] . " ");
                $result_per_student_answer_score = mysqli_query($connect, "SELECT answer_score FROM student_answer WHERE student_answer.student_id=" . $_POST['student_id'] . " ");
                $result_per_student_question_score = mysqli_query($connect, "SELECT question_score FROM student_answer,exam_question,question WHERE student_answer.exam_id=exam_question.exam_id AND exam_question.question_id=question.question_id GROUP BY exam_question.question_id");

                if (mysqli_num_rows($result_per_student_question) > 0) {
                    $i = 0;
                    while ($row_sq = mysqli_fetch_array($result_per_student_question)) {
                        $row_ssa = mysqli_fetch_array($result_per_student_selected_ans);
                        $result_per_student_correct_ans = mysqli_query($connect, "SELECT is_answer,answer_text FROM question_choice,answer WHERE answer.answer_id=question_choice.answer_id AND question_choice.question_id=" . $row_sq['question_id'] . " AND question_choice.is_answer=1");
                        $row_sas = mysqli_fetch_array($result_per_student_answer_score);
                        $row_sqs = mysqli_fetch_array($result_per_student_question_score);
                ?>
                        <li class="box-item question">
                            <p class="item-title">Question</p>
                            <p class="subheading">
                                <?php echo $row_sq['question_title']; ?>
                            </p>
                            <p class="item-title">Selected Answer</p>
                            <p class="subheading">
                                <?php echo $row_ssa['answer_text']; ?>
                            </p>
                            <p class="item-title">Correct Answer</p>
                            <p class="subheading">
                                <?php
                                $count = 0;
                                while ($row_sca = mysqli_fetch_array($result_per_student_correct_ans)) {
                                    echo $row_sca["answer_text"];
                                    $count++;
                                }
                                if ($count == 0) {
                                    echo "**this question needs to be corrected by teachers**" . "<br>";
                                }
                                ?>
                            </p>
                            <p class="item-title">Score</p>
                            <p class="subheading">
                                <?php echo $row_sas["answer_score"] . "/" . $row_sqs["question_score"]; ?>
                            </p>
                        </li>
                <?php
                    }
                }
                ?>
            </ul>
        </div>

        <div class="box">
            <span class="box-title">Info</span>
            <ul class="box-list">
                <li class="box-item">
                    <p class="item-title">Total score</p>
                    <p class="subheading">
                        <?php
                        $result_per_student_total_score = mysqli_query($connect, "SELECT SUM(answer_score) FROM student_answer WHERE student_answer.student_id=" . $_POST['student_id'] . " ");
                        while ($row = mysqli_fetch_array($result_per_student_total_score)) {
                            echo $row["SUM(answer_score)"];
                        }
                        ?>
                    </p>
                </li>
                <li class="box-item">
                    <p class="item-title">Submit Time</p>
                    <p class="subheading">
                        <?php
                        $result_per_student_submit_time = mysqli_query($connect, "SELECT time_submit FROM student_answer,student,student_exam WHERE student.student_id=student_exam.student_id AND student_answer.student_id=student.student_id AND student_answer.student_id=" . $_POST['student_id'] . " GROUP BY  student_answer.student_id");
                        while ($row = mysqli_fetch_array($result_per_student_submit_time)) {
                            echo $row["time_submit"];
                        }
                        ?>
                    </p>
                </li>
        </div>

    </div>

</body>

</html>