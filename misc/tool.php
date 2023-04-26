<?php
function check_auth(string $user_type): int
{
    require 'connect.php';
    $user_id = 0;
    session_start();
    mysqli_query($connect, "DELETE FROM auth_token WHERE expires < GETDATE()");

    if (empty($_SESSION['selector']) && !empty($_COOKIE['remember'])) {
        list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
        $sql = "SELECT * FROM auth_token WHERE selector = '$selector'";
        if ($result = mysqli_query($connect, $sql)) {
            $row = mysqli_fetch_array($result);
            mysqli_query($connect, "DELETE FROM auth_tokens WHERE token_id='" . $row['token_id'] . "'");
            if (hash_equals($row['token'], hash('sha256', base64_decode($authenticator)))) {
                $selector = base64_encode(random_bytes(9));
                $authenticator = random_bytes(33);

                $_SESSION['selector'] = $selector;
                mysqli_query($connect, "INSERT INTO auth_token (selector, token, user_id, expires) VALUES ('" . $selector . "', '" .
                    hash('sha256', $authenticator) . "', " . $row['user_id'] . ", '" . date('Y-m-d\TH:i:s', time() + 86400) . "')");
                setcookie('remember', $selector . ':' . base64_encode($authenticator), time() + 86400);
            }
            $user_id = $row['user_id'];
        }
    }
    $access = true;
    $return = "../login.php";
    if (empty($_SESSION['selector'])) {
        $access = false;
    } else {
        $sql = "SELECT auth_token.user_id, user.user_type FROM auth_token, user WHERE auth_token.user_id=user.user_id AND auth_token.selector = '" . $_SESSION['selector'] . "'";
        if ($result = mysqli_query($connect, $sql)) {
            $row = mysqli_fetch_array($result);
            if (!empty($row)) {
                switch ($user_type) {
                    case 'admin':
                        if ($row['user_type'] != 'admin') {
                            $access = false;
                            $return = "../" . $row['user_type'] . "/";
                        }
                        break;
                    case 'teacher':
                        if ($row['user_type'] == 'student') {
                            $access = false;
                            $return = "../" . $row['user_type'] . "/";
                        }
                        break;
                    case 'student':
                        break;

                    default:
                        die("Invalid user type");
                        break;
                }
                $user_id = $row['user_id'];
            } else $access = false;
        } else $access = false;
    }
    if (!$access) {
        echo $access . " " . $return;
        header("Location: " . $return);
        die();
    }
    return $user_id;
}

function get_image(int $user_id): string
{
    require 'connect.php';
    $result = mysqli_query($connect, "SELECT image_type, profile_image FROM user WHERE user_id=$user_id;");
    $row = mysqli_fetch_array($result);
    return 'data:' . (empty($row['image_type']) ? "image/png" : $row['image_type']) . ';charset=utf8;base64,' . (empty($row['profile_image']) ? "iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAMAAAD04JH5AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACcUExUReTm59nc3cLHycHFyMTIy8XJy7zBw7i9wL7DxcXKy8PHysHGycbKzK60t620t7G3usHGyLW7vb3CxbC2ub/Exq2ztsPHybe8v7G2ubzBxLO5vMfKzcbLzbq+wbW6vauxtdDU1auytbS5vMXKzK+1uLvAwsTIyqyytcLGyLO4vLm+wLa7vqyytquxtLS6vMTHysPIyrC1ucfLzcLHyNxzYOIAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAK9SURBVHhe7Zptd9MwDIWrpGm3LtnChlfWjkFp1433Af//v5HQCyeF1JZdyTk7+PnYE+neyIprOxklEolEIpFIJBKJxPOFsnxcTKbTk9PZGeG3iFBZdTi/GEf1QPULKHe4jGeBrqD5Fy/jWNgv/h7mOoIFglg/c3UHZCB1AKPswKWv7cCtr+uAo9+g5oBeQcHBjZaDMQSc1AgQxv4AdtFpA1ogPYMlYkTJkJyFRgnmyM3iFkGC8DugRaELlkjNJEOYGMw56A9X0iXwasEW6RIUyMsmR6AUr5GXjfBU4NsCVXWHSCG8W6B6g0gh3iItH4NIIfwrUCFSiBxZPUCkEMnA4D3wDJ+CFSKF8J8J3yFSiinyspFeFq6Rl430v+Hg6wHfJtiILwpvkZmJ+Jpw+FXx6A65WawRJIlXG2rsjOgCyRmUiJFl6N2xx2SkdD4wog0EHNwrFYA7G52r6fPaQPOUjONAV989Ctr6rhos1PUbB5a/pW0E/YZDRYhx+zuo7jkznWbR9FuofIDwjjKq+g7Ky8f5+8WH2TqPe/MdKGsgiq1PebH9+GnVmRHM583JsrGCCxRpyv5lBdV/MUbXBI0XjH+jh22uYoIK/sLc5NIeKPc6qm4wks8lzfg330GqCvQ1SL5lImCB6mD5lqdjLdA3ZArFHPVen7w35X2EO+BvBOzMwiwcOfpdgt6p0w2iJQhYKdIEsTJ4OyDfmc+FpwNx/QYfB3SPIEk8aiA8/r/hO6gRIQ335Mz/WJbLdyjY8TmK8YVVgmtcrAGnDfQGoKWAioXDH2pJ4C6BbgEYB3giKwALrhJoF8B5in2Gy/RwfNlwissUsZdgf9OvgvUYVWoVaOMJWr0EvKH1x9YE2g/hL2xNwP5e7hgsBqgy+tgMBLwjDyAZSAYGN/ADaj3EMWCZCiMZODwGycB/aGA0+gkQ90Qn3n0uvgAAAABJRU5ErkJggg==" : base64_encode($row['profile_image']));
}

function get_name(int $user_id): string
{
    require 'connect.php';
    $result = mysqli_query($connect, "SELECT user_name, login_id FROM user WHERE user_id=$user_id;");
    $row = mysqli_fetch_array($result);
    return (!empty($row['user_name']) ? $row['user_name'] : $row['login_id']);
}

function get_answer_id(string $answer_text): int
{

    require 'connect.php';
    if ($result = mysqli_query($connect, "SELECT answer_id FROM answer WHERE answer_text='$answer_text';")) {

        $row = mysqli_fetch_array($result);
        if (!empty($row['answer_id'])) return $row['answer_id'];
    }

    $sql_answer = "insert into answer(answer_text) values('" . $answer_text . "')";
    $result_answer = mysqli_query($connect, $sql_answer);
    if (!$result_answer) {

        die("Could not successfully run query ($sql_answer) from $db: " . mysqli_error($connect));
    }
    return mysqli_insert_id($connect);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body>
</body>

</html>