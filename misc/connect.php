<?php
$server = "localhost";
$user = "root";
$pw = ""; // by default xammp root user has no password

$db = "exam";

$connect = mysqli_connect($server, $user, $pw, $db);

if (!$connect) {
    die("ERROR: Cannot connect to database $db on server $server 
	using user name $user (" . mysqli_connect_errno() . ", " . mysqli_connect_error() . ")");
}

//This is retrieve data from database
$result_student = mysqli_query($connect, "SELECT user.*, student.* FROM user, student WHERE user.user_id=student.student_id AND user.user_type='student' ");
$result_teacher = mysqli_query($connect, "SELECT user.*, course.* FROM user, course WHERE user.user_id=course.teacher_id AND user.user_type='teacher' ");
$result_admin = mysqli_query($connect, "SELECT * FROM user WHERE user_type='admin' ");
?>