<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('teacher');

$result_course = mysqli_query($connect, "SELECT * FROM course WHERE teacher_id=$user_id ");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $error = (empty($_POST['course_id']) || empty($_POST['exam_title']) || empty($_POST['question_score']) || empty($_POST['exam_date_start']) || empty($_POST['exam_time_start']) || empty($_POST['exam_date_end']) || empty($_POST['exam_time_end']) || empty($_POST['question_type']) || empty($_POST['question_title']));

    if (!$error) {

        for ($i = 0; $i < count($_POST['question_type']); $i++) {

            $error = empty($_POST['question_title'][$i]) || empty($_POST['question_type'][$i]) || empty($_POST['question_score'][$i]);
            if ($error) break;
            switch ($_POST['question_type'][$i]) {
                case 'choice':

                    $error = empty($_POST['answer_text']) || empty($_POST['answer_text'][$i]) || empty($_POST['is_answer']);
                    if ($error) break;
                    for ($j = 0; $j < count($_POST['answer_text'][$i]); $j++) {

                        $error = empty($_POST['answer_text'][$i][$j]);
                    }
                    break;

                case 'tf':

                    $error = empty($_POST['choice']) || empty($_POST['choice'][$i]);
                    break;

                case 'fill':

                    $error = !preg_match("/((?<!\\\\)(?:\\\\\\\\)*)\\[([\\w ]+)\\]/i", $_POST['question_title'][$i]);
                    break;
            }
        }
    }

    if (!$error) {

        $sql_exam = "insert into exam(course_id,exam_title, exam_date_start, exam_date_end, exam_created_on) values(" . $_POST['course_id'] . ", '" . $_POST['exam_title'] . "', '" . $_POST['exam_date_start'] . ' ' . $_POST['exam_time_start'] . ":00', '" . $_POST['exam_date_end'] . ' ' . $_POST['exam_time_end'] . ":00', NOW())";
        $result_exam = mysqli_query($connect, $sql_exam);
        $exam_id = mysqli_insert_id($connect);

        if (!$result_exam) {
            die("Could not successfully run query ($sql_exam) from $db: " . mysqli_error($connect));
        }

        for ($i = 0; $i < count($_POST['question_type']); $i++) {
            $sql_question = "insert into question(question_title, question_type) values('" . $_POST['question_title'][$i] . "', '" . ($_POST['question_type'][$i] == 'tf' ? 'choice' : $_POST['question_type'][$i]) . "')";
            $result_question = mysqli_query($connect, $sql_question);
            $question_id = mysqli_insert_id($connect);

            if (!$result_question) {
                die("Could not successfully run query ($sql_question) from $db: " . mysqli_error($connect));
            }

            $sql_exam_question = "insert into exam_question(exam_id, question_id, question_score) values(" . $exam_id . ", " . $question_id . ", '" . $_POST['question_score'][$i] . "')";
            $result_exam_question = mysqli_query($connect, $sql_exam_question);

            if (!$result_exam_question) {
                die("Could not successfully run query ($sql_exam_question) from $db: " . mysqli_error($connect));
            }

            switch ($_POST['question_type'][$i]) {
                case 'choice':
                    for ($j = 0; $j < count($_POST['answer_text'][$i]); $j++) {
                        $answer_id = get_answer_id($_POST['answer_text'][$i][$j]);

                        $sql_question_choice = "insert into question_choice(answer_id, question_id, is_answer) values(" . $answer_id . ", " . $question_id . ", " . ((!empty($_POST['is_answer'][$i][$j]) && $_POST['is_answer'][$i][$j] == 'true') ? 1 : 0) . ")";
                        $result_question_choice = mysqli_query($connect, $sql_question_choice);

                        if (!$result_question_choice) {
                            die("Could not successfully run query ($sql_question_choice) from $db: " . mysqli_error($connect));
                        }
                    }
                    break;

                case 'tf':
                    $answer_id_true = get_answer_id("True");

                    $sql_question_choice_true = "insert into question_choice(answer_id, question_id, is_answer) values(" . $answer_id_true . ", " . $question_id . ", " . ((!empty($_POST['choice'][$i]) && $_POST['choice'][$i] == 'true') ? 1 : 0) . ")";
                    $result_question_choice_true = mysqli_query($connect, $sql_question_choice_true);

                    if (!$result_question_choice_true) {
                        die("Could not successfully run query ($sql_question_choice_true) from $db: " . mysqli_error($connect));
                    }

                    $answer_id_false = get_answer_id("False");

                    $sql_question_choice_false = "insert into question_choice(answer_id, question_id, is_answer) values(" . $answer_id_false . ", " . $question_id . ", " . ((empty($_POST['choice'][$i]) || $_POST['choice'][$i] == 'false') ? 1 : 0) . ")";
                    $result_question_choice_false = mysqli_query($connect, $sql_question_choice_false);

                    if (!$result_question_choice_false) {
                        die("Could not successfully run query ($sql_question_choice_false) from $db: " . mysqli_error($connect));
                    }
                    break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Create new exam - Teacher - Online Examination System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/newExam.js"></script>
</head>

<body>
    <!-- page content -->
    <div class="header image">
        <img class="avatar" src="<?php echo get_image($user_id) ?>" alt="Your Avatar">
        <h1 class="page-title">Hi, <?php echo get_name($user_id) ?>!</h1>
        <p class="page-short-text">
            You may create new exams, complete evaluation, or just view result of past exams.
        </p>
    </div>
    <div class="content">
        <?php if ($_SERVER['REQUEST_METHOD'] != 'POST' || $error) { ?>
            <form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
                <span class="box-title">New examination</span>
                <ul class="box-list">
                    <li class="box-item">
                        <p class="item-title">Exam information</p>
                        <table style="width:100%;" class="box-form">
                            <tr>
                                <td class="form-label"><label for="course_id">Course</label></td>
                                <td class="form-input">
                                    <select name="course_id" id="course_id">
                                        <option value="" selected="selected">Please select...</option>
                                        <?php
                                        if (mysqli_num_rows($result_course) > 0) {
                                            while ($row = mysqli_fetch_array($result_course)) {
                                        ?>
                                                <option value="<?php echo $row['course_id']; ?>" <?php echo (!empty($_POST['course_id']) && $_POST['course_id'] == $row['course_id']) ? 'selected="selected"' : "" ?>><?php echo $row['course_name']; ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="form-msg">
                                    <?php
                                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                        if (empty($_POST['course_id'])) {
                                            echo "Please choose a course.";
                                        }
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="form-label"><label for="exam_title">Exam Title</label></td>
                                <td class="form-input"><input type="text" id="exam_title" name="exam_title" value="<?php echo (!empty($_POST['exam_title'])) ? $_POST['exam_title'] : "" ?>" /></td>
                                <td class="form-msg">
                                    <?php
                                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                        if (empty($_POST['exam_title'])) {
                                            echo "Please enter the exam title.";
                                        }
                                    } ?></td>
                            </tr>
                            <tr>
                                <td class="form-label"><label for="exam_date_start">Start date and time</label></td>
                                <td><input type="date" name="exam_date_start" value="<?php echo (!empty($_POST['exam_date_start'])) ? $_POST['exam_date_start'] : "" ?>" /><input type="time" name="exam_time_start" value="<?php echo (!empty($_POST['exam_time_start'])) ? $_POST['exam_time_start'] : "" ?>" /></td>
                                <td class="form-msg">
                                    <?php
                                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                        if (empty($_POST['exam_date_start'])) {
                                            echo "Please choose a start date.";
                                        }
                                        if (empty($_POST['exam_time_start'])) {
                                            echo "Please choose a start time.";
                                        }
                                    } ?></td>
                            </tr>
                            <tr>
                                <td class="form-label"><label for="exam_date_end">End date and time</label></td>
                                <td><input type="date" name="exam_date_end" value="<?php echo (!empty($_POST['exam_date_end'])) ? $_POST['exam_date_end'] : "" ?>" /><input type="time" name="exam_time_end" value="<?php echo (!empty($_POST['exam_time_end'])) ? $_POST['exam_time_end'] : "" ?>" /></td>
                                <td class="form-msg">
                                    <?php
                                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                        if (empty($_POST['exam_date_end'])) {
                                            echo "Please choose a end date.";
                                        }
                                        if (empty($_POST['exam_time_end'])) {
                                            echo "Please choose a end time.";
                                        }
                                    } ?></td>
                            </tr>
                        </table>
                    </li>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['question_type'])) {
                        for ($i = 0; $i < count($_POST['question_type']); $i++) {
                    ?>
                            <li class="box-item question" data-index="<?php echo $i; ?>">
                                <p class="item-title">Question <?php echo $i + 1; ?></p>
                                <table style="width:100%;" class="box-form">
                                    <tr>
                                        <td class="form-label"><label for="question_type[<?php echo $i; ?>]">Type</label></td>
                                        <td class="form-input">
                                            <select name="question_type[<?php echo $i; ?>]" class="question_type" onchange="changeQuestionType(this);">
                                                <option value="" <?php echo (empty($_POST['question_type'][$i])) ? 'selected="selected"' : "" ?>></option>
                                                <option value="tf" <?php echo (!empty($_POST['question_type'][$i]) && $_POST['question_type'][$i] == 'tf') ? 'selected="selected"' : "" ?>>True/False</option>
                                                <option value="choice" <?php echo (!empty($_POST['question_type'][$i]) && $_POST['question_type'][$i] == 'choice') ? 'selected="selected"' : "" ?>>Multiple choice</option>
                                                <option value="text" <?php echo (!empty($_POST['question_type'][$i]) && $_POST['question_type'][$i] == 'text') ? 'selected="selected"' : "" ?>>Short text</option>
                                                <option value="fill" <?php echo (!empty($_POST['question_type'][$i]) && $_POST['question_type'][$i] == 'fill') ? 'selected="selected"' : "" ?>>Fill in the blank</option>
                                            </select>
                                        </td>
                                        <td class="form-msg">
                                            <?php
                                            if (empty($_POST['question_type'][$i])) {
                                                echo "Please choose a question type";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($_POST['question_type'][$i] == "fill") {
                                    ?>
                                        <tr class="type-specific">
                                            <td class="form-label"><label for="question_title[<?php echo $i; ?>]">Question</label></td>
                                            <td class="form-input"><textarea style="min-width: 300px;min-height:80px;" name="question_title[<?php echo $i; ?>]" class="question_title" placeholder="Make a fill in the [blank] question like this. If you want to indicate it is not a blank, you may just use \[this\] type of expression."><?php echo (!empty($_POST['question_title'][$i])) ? $_POST['question_title'][$i] : "" ?></textarea></td>
                                            <td class="form-msg">
                                                <?php
                                                if (empty($_POST['question_title'][$i])) {
                                                    echo "Please enter a title for the exam.";
                                                }
                                                ?></td>
                                        </tr>
                                    <?php
                                    } else if (!empty($_POST['question_type'][$i])) {
                                    ?>
                                        <tr class="type-specific">
                                            <td class="form-label"><label for="question_title[<?php echo $i; ?>]">Question</label></td>
                                            <td class="form-input"><input type="text" name="question_title[<?php echo $i; ?>]" class="question_title" value="<?php echo (!empty($_POST['question_title'][$i])) ? $_POST['question_title'][$i] : "" ?>"></td>
                                            <td class="form-msg">
                                                <?php
                                                if (empty($_POST['question_title'][$i])) {
                                                    echo "Please enter a title for the exam.";
                                                }
                                                ?></td>
                                        </tr>
                                        <?php
                                        if ($_POST['question_type'][$i] == "tf") {
                                        ?>
                                            <tr class="type-specific">
                                                <td class="form-label">Correct answer</td>
                                                <td class="form-input">
                                                    <input type="radio" name="choice[<?php echo $i; ?>]" id="choice[<?php echo $i; ?>]_true" class="choice" value="true" <?php echo (!empty($_POST['choice'][$i]) && $_POST['choice'][$i] == 'true') ? 'checked="checked"' : "" ?>>
                                                    <label for="choice[<?php echo $i; ?>]_true">True</label>
                                                    <input type="radio" name="choice[<?php echo $i; ?>]" id="choice[<?php echo $i; ?>]_false" class="choice" value="false" <?php echo (!empty($_POST['choice'][$i]) && $_POST['choice'][$i] == 'false') ? 'checked="checked"' : "" ?>>
                                                    <label for="choice[<?php echo $i; ?>]_false">False</label>
                                                </td>
                                                <td class="form-msg">
                                                    <?php
                                                    if (empty($_POST['choice'][$i])) {
                                                        echo "Please select a option.";
                                                    }
                                                    ?></td>
                                            </tr>
                                            <?php
                                        } else if ($_POST['question_type'][$i] == "choice") {
                                            for ($j = 0; $j < count($_POST['answer_text'][$i]); $j++) {
                                            ?>
                                                <tr class="type-specific">
                                                    <td class="form-label"><label for="answer_text[<?php echo $i; ?>][<?php echo $j; ?>]" data-index="<?php echo $j; ?>">Option <?php echo $j + 1; ?></label></td>
                                                    <td class="form-input">
                                                        <input type="text" name="answer_text[<?php echo $i; ?>][<?php echo $j; ?>]" class="answer_text" value="<?php echo (!empty($_POST['answer_text'][$i][$j])) ? $_POST['answer_text'][$i][$j] : "" ?>">
                                                        <input type="checkbox" name="is_answer[<?php echo $i; ?>][<?php echo $j; ?>]" id="is_answer[<?php echo $i; ?>][<?php echo $j; ?>]" class="is_answer" <?php echo (!empty($_POST['is_answer'][$i][$j]) && $_POST['is_answer'][$i][$j]) ? 'checked="checked"' : "" ?>>
                                                        <label for="is_answer[<?php echo $i; ?>][<?php echo $j; ?>]">Option <?php echo $j + 1; ?> is answer</label>
                                                    </td>
                                                    <td class="form-msg">
                                                        <?php
                                                        if (empty($_POST['answer_text'][$i][$j])) {
                                                            echo "Please enter the option.";
                                                        }
                                                        if (empty($_POST['is_answer'][$i])) {
                                                            echo "Please choose at least one answer..";
                                                        }
                                                        ?></td>
                                                </tr>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                            <tr class="type-specific" onclick="addOption(this);">
                                                <td colspan="2" style="text-align: center;">+ Add more option</td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class="form-label"><label for="question_score[<?php echo $i; ?>]">Score</label></td>
                                        <td class="form-input"><input type="number" name="question_score[<?php echo $i; ?>]" value="<?php echo (!empty($_POST['question_score'][$i])) ? $_POST['question_score'][$i] : "" ?>" /></td>
                                        <td class="form-msg">
                                            <?php
                                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                                if (empty($_POST['question_score'][$i])) {
                                                    echo "Please enter a score.";
                                                }
                                            } ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="box-button"><button onclick="deleteQuestion(this);">Delete</button></td>
                                    </tr>
                                </table>
                            </li>
                    <?php }
                    }
                    ?>
                    <a class="box-item" id="add_question" onclick="addQuestion(this);">
                        <p class="item-title" onload="addQuestion(this.parentNode);">+ Add new question</p>
                    </a>
                </ul>
                <div class="box-button">
                    <button type="submit">Create</button>
                </div>
            </form>
        <?php } else {?>
                <div class="box">
                    <span class="box-title">Created new examination</span>
                    <div class="box-content">
                        New exam created. Click <a href="../teacher">here back to the panel</a>.
                    </div>
                </div>
        <?php }?>
    </div>


</body>

</html>