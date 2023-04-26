<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('student');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['fill']) || !empty($_POST['choice']) || !empty($_POST['text'])) {
        foreach ($_POST['fill'] as $question_id => $values) {
            $answer = implode("_", $values);
            $answer_id = get_answer_id($answer);
            $sql_student_answer = "insert into student_answer(exam_id ,student_id, question_id, answer_id) values(" . $_POST['exam_id'] . ", $user_id, $question_id, $answer_id)";

            if (!mysqli_query($connect, $sql_student_answer)) {
                die("Could not successfully run query ($sql_student_answer) from $db: " . mysqli_error($connect));
            }
        }
        foreach ($_POST['choice'] as $question_id => $values) {
            foreach ($values as $answer_id => $value) {
                if ($value) {
                    $sql_student_answer = "insert into student_answer(exam_id ,student_id, question_id, answer_id) values(" . $_POST['exam_id'] . ", $user_id, $question_id, $answer_id)";

                    if (!mysqli_query($connect, $sql_student_answer)) {
                        die("Could not successfully run query ($sql_student_answer) from $db: " . mysqli_error($connect));
                    }
                }
            }
        }
        foreach ($_POST['text'] as $question_id => $answer) {
            $answer_id = get_answer_id($answer);
            $sql_student_answer = "insert into student_answer(exam_id ,student_id, question_id, answer_id) values(" . $_POST['exam_id'] . ", $user_id, $question_id, $answer_id)";

            if (!mysqli_query($connect, $sql_student_answer)) {
                die("Could not successfully run query ($sql_student_answer) from $db: " . mysqli_error($connect));
            }
        }
    }
    $sql_exam = "SELECT * FROM exam WHERE exam_id=" . $_POST['exam_id'] . " AND exam_date_start < NOW() AND exam_date_end > NOW()";
    $result_exam = mysqli_query($connect, $sql_exam);
    $row_exam = mysqli_fetch_array($result_exam);

    $sql_course = "SELECT course_name FROM course WHERE course_id=" . $row_exam['course_id'];
    $result_course = mysqli_query($connect, $sql_course);
    $row_course = mysqli_fetch_array($result_course);
} else {
    header("../student"); //This page is required to be requested by POST
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Take exam - Student - Online Examination System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <!-- page content -->
    <div class="fixed-top-right" id="timer" style="font-size: 1.5rem;padding: .5rem 1rem;border: 1px solid rgba(0,0,0,.12);margin-right: 1rem;border-radius: 2px;transform: translateY(-2px);">
    </div>
    <div class="header image">
        <img class="avatar" src="<?php echo get_image($user_id) ?>" alt="Your Avatar">
        <h1 class="page-title">Hi, <?php echo get_name($user_id) ?>!</h1>
        <p class="page-short-text">
            You are now in <?php echo $row_course['course_name']; ?> <?php echo $row_exam['exam_title']; ?> exam.
        </p>
    </div>
    <div class="content">
        <?php if (empty($_POST['fill']) || empty($_POST['choice']) || empty($_POST['text'])) { ?>
            <form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="formExam">
                <input type="hidden" name="exam_id" class="hide" value="<?php echo $_POST['exam_id']; ?>" readonly>
                <span class="box-title">Examination</span>
                <ul class="box-list">
                    <li class="box-item">
                        <p class="item-title">Exam information</p>
                        <table style="width:100%;" class="box-form">
                            <tr>
                                <td class="form-label">Course</td>
                                <td class="form-input"><?php echo $row_course['course_name']; ?>
                                    </select>
                                </td>
                                <td class="form-msg">
                                </td>
                            </tr>
                            <tr>
                                <td class="form-label">Exam Title</td>
                                <td class="form-input"><?php echo $row_exam['exam_title']; ?></td>
                                <td class="form-msg"></td>
                            </tr>
                            <tr>
                                <td class="form-label">Start date and time</td>
                                <td><?php echo $row_exam['exam_date_start']; ?></td>
                                <td class="form-msg"></td>
                            </tr>
                            <tr>
                                <td class="form-label">End date and time</td>
                                <td><?php echo $row_exam['exam_date_end']; ?></td>
                                <td class="form-msg"></td>
                            </tr>
                        </table>
                    </li>
                    <?php
                    $sql_question = "SELECT * FROM question WHERE question_id IN (SELECT question_id FROM exam_question WHERE exam_id=" . $_POST['exam_id'] . ")";
                    $result_question = mysqli_query($connect, $sql_question);
                    $i = 0;
                    while ($row_question = mysqli_fetch_array($result_question)) {
                    ?>
                        <li class="box-item question" data-index="<?php echo $row_question['question_id']; ?>">
                            <p class="item-title">Question <?php echo $i + 1; ?></p>
                            <table style="width:100%;" class="box-form">
                                <tr>
                                    <td colspan="3">
                                        <?php
                                        echo $row_question['question_title'];
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                switch ($row_question['question_type']) {
                                    case 'fill':
                                        preg_match_all("/((?<!\\\\)(?:\\\\\\\\)*)\\[([\\w ]+)\\]/i", $row_question['question_title'], $matches);
                                        for ($j = 0; $j < count($matches[0]); $j++) { ?>
                                            <tr>
                                                <td class="form-label"><label for="fill[<?php echo $row_question['question_id']; ?>][<?php echo $j; ?>]"></label><?php echo $matches[0][$j]; ?>:</td>
                                                <td class="form-input"><input type="text" name="fill[<?php echo $row_question['question_id']; ?>][<?php echo $j; ?>]" id="fill[<?php echo $row_question['question_id']; ?>][<?php echo $j; ?>]"></td>
                                                <td class="form-msg"></td>
                                            </tr>
                                        <?php }
                                        break;

                                    case 'choice':
                                        $sql_answer = "SELECT * FROM answer WHERE answer_id IN (SELECT answer_id FROM question_choice WHERE question_id=" . $row_question['question_id'] . ")";
                                        $result_answer = mysqli_query($connect, $sql_answer);
                                        while ($row_answer = mysqli_fetch_array($result_answer)) { ?>
                                            <tr>
                                                <td class="form-label"><input type="checkbox" name="choice[<?php echo $row_question['question_id']; ?>][<?php echo $row_answer['answer_id']; ?>]" id="choice[<?php echo $row_question['question_id']; ?>][<?php echo $row_answer['answer_id']; ?>]"></td>
                                                <td class="form-input"><label for="choice[<?php echo $row_question['question_id']; ?>][<?php echo $row_answer['answer_id']; ?>]"></label><?php echo $row_answer['answer_text']; ?></td>
                                                <td class="form-msg"></td>
                                            </tr>
                                        <?php }
                                        break;

                                    case 'text': { ?>
                                            <tr>
                                                <td class="form-label"><label for="text[<?php echo $row_question['question_id']; ?>]"></label></td>
                                                <td class="form-input"><input type="text" name="text[<?php echo $row_question['question_id']; ?>]" id="text[<?php echo $row_question['question_id']; ?>]"></td>
                                                <td class="form-msg"></td>
                                            </tr>
                                        <?php }
                                        break;

                                    default: { ?>
                                            <tr>
                                                <td class="form-label"></td>
                                                <td class="form-input"></td>
                                                <td class="form-msg"></td>
                                            </tr>
                                <?php }
                                        break;
                                }
                                ?>
                            </table>
                        </li>
                    <?php
                        $i++;
                    }
                    ?>
                </ul>
                <div class="box-button">
                    <button type="submit">Submit</button>
                </div>
            </form>
        <?php } else { ?>
            <div class="box">
                <span class="box-title">Submitted</span>
                <div class="box-content">
                    Click <a href="../student">here back to main page</a>.
                </div>
            </div>
        <?php } ?>
    </div>

    <script>
        // Set the date we're counting down to
        var countDownDate = new Date("<?php echo $row_exam['exam_date_end']; ?>").getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="demo"
            document.getElementById("timer").innerHTML = days + "d " + hours + "h " +
                minutes + "m " + seconds + "s ";

            // If the count down is finished, write some text
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("timer").innerHTML = "EXPIRED";
                document.getElementById("formExam").submit();
            }
        }, 1000);
    </script>

</body>

</html>