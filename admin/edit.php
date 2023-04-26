<?php

include '../misc/connect.php';
include '../misc/tool.php';

$user_id = check_auth('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $error = false;
    if (!empty($_POST['tab'])) { // If come from admin panel
        if (!empty($_POST['check']) && mysqli_num_rows($result_student) > 0) {
            $idex_user_id = $_POST['check'];
            $count = count($idex_user_id);
            $result_student = mysqli_query($connect, "SELECT user.*, student.* FROM user, student WHERE user.user_id=student.student_id AND user.user_id='" . $idex_user_id[0] . "' ");
            $row = mysqli_fetch_array($result_student);
        }
    } else if (!(empty($_POST['id']) || empty($_POST['type']) || //Check required informations exits
        //($_POST['type'] == 'teacher' && (empty($_POST['course_title']) || empty($_POST['course_code']) || //Check if teacher have cource details
        //    substr_count($_POST['course_title'], '-') || preg_match('/^[A-Za-z0-9]{1,}$/i', $_POST['course_code']) != 1)) || //Check if course code and title valid
        preg_match('/^[A-Za-z0-9_-]{1,}$/i', $_POST['id']) != 1 || //Check if username valid
        (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)))) { //Check if email valid

        if (!mysqli_query($connect, "UPDATE user SET login_id='" . $_POST['id'] . "' WHERE user_id=" . $_POST['user_id']) === TRUE) {
            echo "Error updating record: " . $connect->error;
        }

        if (!mysqli_query($connect, "UPDATE user SET user_name=" . (!empty($_POST['name']) ? "'" . $_POST['name'] . "'" : "NULL") . " WHERE user_id=" . $_POST['user_id']) === TRUE) {
            echo "Error updating record: " . $connect->error;
        }

        if (!mysqli_query($connect, "UPDATE user SET user_email=" . (!empty($_POST['email']) ? "'" . $_POST['email'] . "'" : "NULL") . " WHERE user_id=" . $_POST['user_id']) === TRUE) {
            echo "Error updating record: " . $connect->error;
        }

        if (!empty($_POST['password']))
            if (!mysqli_query($connect, "UPDATE user SET user_password='" . password_hash($_POST['password'], (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . "' WHERE user_id=" . $_POST['user_id']) === TRUE) {
                echo "Error updating record: " . $connect->error;
            }

        if (count($_FILES) > 0) {
            if (isset($_FILE) && $_FILE['avatar']['error'] === UPLOAD_ERR_OK) {
                $sql_avatar = "UPDATE user SET profile_image=?, image_type=? WHERE user_id=?";
                if ($stmt = mysqli_prepare($connect, $sql_avatar)) {
                    $stmt->bind_param('bsi', $profile_image, $image_type, $user_id);

                    $user_id = $_POST['user_id'];
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
                            $stmt->send_long_data(0, file_get_contents($_FILES['avatar']['tmp_name']));
                        } else {
                            echo 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.';
                        }
                    }
                    if (!mysqli_stmt_execute($stmt)) {
                        die("Could not successfully run query ($sql_avatar) from $db: " . mysqli_error($connect));
                    }
                }
            }

            mysqli_stmt_close($stmt);
            switch (strtolower($_POST['type'])) {
                case 'student':
                    if (!mysqli_query($connect, "UPDATE student SET student_gender=" . (!empty($_POST['gender']) ? "'" . strtolower($_POST['gender'][0]) . "'" : "NULL") . " WHERE student_id=" . $_POST['user_id']) === TRUE) {
                        echo "Error updating record: " . $connect->error;
                    }

                    if (!mysqli_query($connect, "UPDATE student SET student_birthday=" . (!empty($_POST['birthday']) ? "'" . $_POST['birthday'] . " 00:00:00'" : "NULL") . " WHERE student_id=" . $_POST['user_id']) === TRUE) {
                        echo "Error updating record: " . $connect->error;
                    }
                    break;
            }
        } else {
            echo "Error: " . $sql_user . "<br>" . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit <?php echo $_POST['tab'] ?> - Admin Panel - Online Examination System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- page content -->
    <div class="header">
        <h1 class="page-title">
            Administration panel
        </h1>
    </div>
    <div class="content">
        <?php if (!isset($login_id) && isset($count) && $count == 1) { ?>
            <form class="box" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="formReg" enctype="multipart/form-data">
                <input type="hidden" name="type" class="hide" value="<?php echo $_POST['tab'] ?>" readonly>
                <input type="hidden" name="user_id" class="hide" value="<?php echo $idex_user_id[0] ?>" readonly>
                <span class="box-title">Edit <?php echo $_POST['tab'] ?></span>
                <table class="box-form">
                    <tr>
                        <td class="form-label"><label for="id">Login ID</label></td>
                        <td class="form-input"><input type="text" name="id" value="<?php echo $row["login_id"] ?>"></td>
                        <td class="form-msg">
                            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                if (!empty($_POST['tab'])) {
                                    //Expected do nothing
                                } else if (empty($_POST['id'])) {
                                    echo "Please fill in your login ID.";
                                } else if (preg_match('/^[A-Za-z0-9_-]{1,}$/i', $_POST['id']) != 1) {
                                    echo "Please just use alphanumeric characters (A-Z, a-z, 0-9), dash (-), or underscore (_).";
                                }
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="form-label"><label for="name">Nick name</label></td>
                        <td class="form-input"><input type="text" name="name" value="<?php echo $row["user_name"] ?>"></td>
                        <td class="form-msg"></td>
                    </tr>
                    <tr>
                        <td class="form-label"><label for="password">Password</label></td>
                        <td class="form-input"><input type="password" name="password" ?></td>
                        <td class="form-msg">
                            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                if (empty($_POST['tab']) && empty($_POST['password'])) {
                                    echo "Please fill in your password.";
                                }
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="form-label"><label for="email">Email</label></td>
                        <td class="form-input"><input type="email" name="email" value="<?php echo $row["user_email"] ?>"></td>
                        <td class="form-msg">
                            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                if (empty($_POST['tab']) && !empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
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
                                if (empty($_POST['tab']) && count($_FILES) > 0) {
                                    if (is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                                        if (!in_array($fileType, $allowTypes)) {
                                            echo 'Sorry, only JPEG, PNG, GIF, BMP and WEBP files are allowed.';
                                        }
                                    }
                                }
                            } ?>
                        </td>
                    </tr>
                    <?php if ($_POST['tab'] == 'student') { ?>
                        <tr>
                            <td class="form-label"><label for="gender">Gender</label></td>
                            <td class="form-input">
                                <select name="gender" id="gender">
                                    <option></option>
                                    <option <?php
                                            if (!empty($row["student_gender"]) && $row["student_gender"]  == 'm') {
                                                echo ' selected="selected"';
                                            } ?>>
                                        Male </option>
                                    <option <?php
                                            if (!empty($row["student_gender"]) && $row["student_gender"]  == 'f') {
                                                echo ' selected="selected"';
                                            } ?>>Female</option>
                                    <option <?php
                                            if (!empty($row["student_gender"]) && $row["student_gender"]  == 'o') {
                                                echo ' selected="selected"';
                                            } ?>>Other</option>
                                </select>
                            </td>
                            <td class="form-msg"></td>
                        </tr>
                        <tr>
                            <td class="form-label"><label for="birthday">Birthday</label></td>
                            <td class="form-input"><input type="date" id="birthday" name="birthday" value="<?php echo !empty($row["student_birthday"]) ? date('Y-m-d', strtotime($row["student_birthday"])) : "" ?>"></td>
                            <td class="form-msg"></td>
                        </tr>
                    <?php
                    } ?>
                </table>
                <div class="box-button">
                    <button type="reset">Reset</button>
                    <button type="submit" id="btnEdit">Edit</button>
                </div>
            </form>
        <?php
        } else if (isset($count) && $count > 1) { ?>
            <div class="box">
                <span class="box-title">Error</span>
                <div class="box-content">
                    Please select one item. Click <a href="../admin">here back to the panel to select one</a>.
                </div>
            </div>
        <?php
        } else { ?>
            <div class="box">
                <span class="box-title">Edited <?php echo (!empty($_POST['tab'])) ? $_POST['tab'] : $_POST['type'] ?></span>
                <div class="box-content">
                    User, <?php echo (!empty($_POST['name'])) ? $_POST['name'] : $_POST['id'] ?> edited. Click <a href="../admin">here back to the panel</a>.
                </div>
            </div>
        <?php
        } ?>
    </div>
</body>

</html>