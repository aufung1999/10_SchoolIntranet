<?php
include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('teacher');

$result_course = mysqli_query($connect, "SELECT * FROM course WHERE teacher_id=$user_id ");
$result_student = mysqli_query($connect, "SELECT * FROM student, user WHERE student_id=user_id");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $error = (empty($_POST['course_id']) || empty($_POST['student_id']));

    if (!$error) {

        $sql_exam = "insert into student_course(student_id, course_id) values(" . $_POST['student_id'] . ", " . $_POST['course_id'] . ")";
        $result_exam = mysqli_query($connect, $sql_exam);
        $exam_id = mysqli_insert_id($connect);

        if (!$result_exam) {
            echo "Could not successfully run query ($sql_exam) from $db: " . mysqli_error($connect);
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
            You may assign user to course here.
        </p>
    </div>
    <div class="content">
        <?php if ($_SERVER['REQUEST_METHOD'] != 'POST' || $error) { ?>
            <form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
                <span class="box-title">Assign student to course</span>
                <ul class="box-list">
                    <li class="box-item">
                        <p class="item-title"></p>
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
                                <td class="form-label"><label for="student_id">Student</label></td>
                                <td class="form-input">
                                    <select name="student_id" id="student_id">
                                        <option value="" selected="selected">Please select...</option>
                                        <?php
                                        if (mysqli_num_rows($result_student) > 0) {
                                            while ($row = mysqli_fetch_array($result_student)) {
                                        ?>
                                                <option value="<?php echo $row['student_id']; ?>" <?php echo (!empty($_POST['student_id']) && $_POST['student_id'] == $row['student_id']) ? 'selected="selected"' : "" ?>><?php echo $row['user_name']; ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="form-msg">
                                    <?php
                                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                        if (empty($_POST['student_id'])) {
                                            echo "Please choose a student.";
                                        }
                                    } ?>
                                </td>
                            </tr>
                        </table>
                    </li>
                </ul>
                <div class="box-button">
                    <button type="submit">Assign</button>
                </div>
            </form>
        <?php } else {?>
                <div class="box">
                    <span class="box-title">User assigned</span>
                    <div class="box-content">
                        User assigned. Click <a href="../teacher">here back to the panel</a>.
                    </div>
                </div>
        <?php }?>
    </div>


</body>

</html>