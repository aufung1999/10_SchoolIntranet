<?php
$server = "localhost";
$user = "root";
if (!empty($_POST['rootPW']))
    $pw = $_POST['rootPW'];
elseif ($_POST['server'] == "mamp")
    $pw = "root";
else
    $pw = ""; // by default xammp root user has no password 

$db = "exam";

$conn = mysqli_connect($server, $user, $pw);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "DROP DATABASE IF EXISTS " . $db;
if (mysqli_query($conn, $sql)) {
    echo "Database dropped successfully<br/>";
} else {
    echo "Error dropping database: " . mysqli_error($conn);
}

// Create database
$sql = "CREATE DATABASE " . $db;
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br/>";
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

$connect = mysqli_connect($server, $user, $pw, $db);

if (!$connect) {
    die("ERROR: Cannot connect to database $db on server $server 
	using user name $user (" . mysqli_connect_errno() . ", " . mysqli_connect_error() . ")");
}

$createAccount = "GRANT ALL PRIVILEGES ON exam.* TO 'wbip'@'localhost' IDENTIFIED BY 'wbip123' WITH GRANT OPTION";

$result = mysqli_query($connect, $createAccount);

if (!$result) {
    die("Could not successfully run query ($createAccount) from $db: " . mysqli_error($connect));
}

$dropTables = array(
    "DROP TABLE IF EXISTS `exam`.`user`;",
    "DROP TABLE IF EXISTS `exam`.`auth_token` ;",
    "DROP TABLE IF EXISTS `exam`.`student` ;",
    "DROP TABLE IF EXISTS `exam`.`course` ;",
    "DROP TABLE IF EXISTS `exam`.`student_course` ;",
    "DROP TABLE IF EXISTS `exam`.`exam` ;",
    "DROP TABLE IF EXISTS `exam`.`question` ;",
    "DROP TABLE IF EXISTS `exam`.`student_exam` ;",
    "DROP TABLE IF EXISTS `exam`.`question_choice` ;",
    "DROP TABLE IF EXISTS `exam`.`student_answer` ;",
    "DROP TABLE IF EXISTS `exam`.`answer` ;",
    "DROP TABLE IF EXISTS `exam`.`exam_question` ;"
);

$createTables = array(
    "CREATE TABLE IF NOT EXISTS `exam`.`user` (
        `user_id` INT NOT NULL AUTO_INCREMENT,
        `login_id` VARCHAR(32) NOT NULL,
        `user_name` VARCHAR(20) NULL,
        `user_password` CHAR(97) NOT NULL,
        `user_email` VARCHAR(254) NULL,
        `profile_image` BLOB NULL,
        `image_type` VARCHAR(10) NULL,
        `user_type` ENUM('student', 'teacher', 'admin') NOT NULL,
        PRIMARY KEY (`user_id`),
        UNIQUE INDEX `login_id_UNIQUE` (`login_id` ASC))
      ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`auth_token` (
        `token_id` INT(11) NOT NULL AUTO_INCREMENT,
        `selector` CHAR(12),
        `token` CHAR(64),
        `user_id` INT NOT NULL,
        `expires` DATETIME,
        PRIMARY KEY (`token_id`),
        CONSTRAINT `fk_auth_token_user`
          FOREIGN KEY (`user_id`)
          REFERENCES `exam`.`user` (`user_id`)
        ON DELETE CASCADE
          ON UPDATE CASCADE)
      ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`student` (
        `student_id` INT NOT NULL,
        `student_gender` ENUM('m', 'f', 'o') NULL,
        `student_birthday` DATETIME NULL,
        PRIMARY KEY (`student_id`),
        CONSTRAINT `fk_student_user`
          FOREIGN KEY (`student_id`)
          REFERENCES `exam`.`user` (`user_id`)
        ON DELETE CASCADE
          ON UPDATE CASCADE)
      ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`course` (
    `course_id` INT NOT NULL AUTO_INCREMENT,
    `teacher_id` INT NOT NULL,
    `course_name` VARCHAR(45) NOT NULL,
    PRIMARY KEY (`course_id`),
    INDEX `fk_course_teacher_user1_idx` (`teacher_id` ASC),
    CONSTRAINT `fk_course_teacher_user1`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `exam`.`user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
    ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`student_course` (
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    PRIMARY KEY (`student_id`, `course_id`),
    INDEX `fk_student_has_course_course1_idx` (`course_id` ASC),
    INDEX `fk_student_has_course_student1_idx` (`student_id` ASC),
    CONSTRAINT `fk_student_has_course_student1`
    FOREIGN KEY (`student_id`)
    REFERENCES `exam`.`student` (`student_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT `fk_student_has_course_course1`
    FOREIGN KEY (`course_id`)
    REFERENCES `exam`.`course` (`course_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
    ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`exam` (
    `exam_id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `exam_title` VARCHAR(45) NOT NULL,
    `exam_date_start` DATETIME NOT NULL,
    `exam_date_end` DATETIME NOT NULL,
    `exam_created_on` DATETIME NOT NULL,
    PRIMARY KEY (`exam_id`),
    INDEX `fk_exam_course1_idx` (`course_id` ASC),
    CONSTRAINT `fk_exam_course1`
    FOREIGN KEY (`course_id`)
    REFERENCES `exam`.`course` (`course_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
    ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`question` (
        `question_id` INT NOT NULL AUTO_INCREMENT,
        `question_title` VARCHAR(255) NOT NULL,
        `question_type` ENUM('choice', 'text','fill') NOT NULL,
        PRIMARY KEY (`question_id`))
      ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`student_exam` (
    `student_id` INT NOT NULL,
    `exam_id` INT NOT NULL,
    `time_submit` DATETIME NOT NULL,
    PRIMARY KEY (`student_id`, `exam_id`),
    INDEX `fk_student_exam_exam1_idx` (`exam_id` ASC),
    CONSTRAINT `fk_student_exam_student1`
    FOREIGN KEY (`student_id`)
    REFERENCES `exam`.`student` (`student_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT `fk_student_exam_exam1`
    FOREIGN KEY (`exam_id`)
    REFERENCES `exam`.`exam` (`exam_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
    ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`answer` (
    `answer_id` INT NOT NULL AUTO_INCREMENT,
    `answer_text` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`answer_id`),
    UNIQUE INDEX `answer_text_UNIQUE` (`answer_text` ASC))
    ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`question_choice` (
        `answer_id` INT NOT NULL,
        `question_id` INT NOT NULL,
        `is_answer` TINYINT(1) NOT NULL,
        PRIMARY KEY (`answer_id`, `question_id`),
        INDEX `fk_question_choice_question1_idx` (`question_id` ASC),
        CONSTRAINT `fk_question_choice_answers1`
          FOREIGN KEY (`answer_id`)
          REFERENCES `exam`.`answer` (`answer_id`)
          ON DELETE NO ACTION
          ON UPDATE CASCADE,
        CONSTRAINT `fk_question_choice_question1`
          FOREIGN KEY (`question_id`)
          REFERENCES `exam`.`question` (`question_id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE)
      ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`exam_question` (
        `exam_id` INT NOT NULL,
        `question_id` INT NOT NULL,
        `question_score` INT NOT NULL,
        INDEX `fk_exam_has_question_question1_idx` (`question_id` ASC),
        INDEX `fk_exam_has_question_exam1_idx` (`exam_id` ASC),
        PRIMARY KEY (`exam_id`, `question_id`),
        CONSTRAINT `fk_exam_has_question_exam1`
          FOREIGN KEY (`exam_id`)
          REFERENCES `exam`.`exam` (`exam_id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE,
        CONSTRAINT `fk_exam_has_question_question1`
          FOREIGN KEY (`question_id`)
          REFERENCES `exam`.`question` (`question_id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE)
      ENGINE = InnoDB;",
    "CREATE TABLE IF NOT EXISTS `exam`.`student_answer` (
        `student_id` INT NOT NULL,
        `exam_id` INT NOT NULL,
        `question_id` INT NOT NULL,
        `answer_id` INT NOT NULL,
        `answer_score` INT NULL,
        INDEX `fk_student_exam_has_answers_answers1_idx` (`answer_id` ASC),
        PRIMARY KEY (`student_id`, `exam_id`, `question_id`, `answer_id`),
        INDEX `fk_student_answer_exam_question1_idx` (`exam_id` ASC, `question_id` ASC),
        CONSTRAINT `fk_student_exam_has_answers_answers1`
          FOREIGN KEY (`answer_id`)
          REFERENCES `exam`.`answer` (`answer_id`)
          ON DELETE NO ACTION
          ON UPDATE NO ACTION,
        CONSTRAINT `fk_student_answer_student1`
          FOREIGN KEY (`student_id`)
          REFERENCES `exam`.`student` (`student_id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE,
        CONSTRAINT `fk_student_answer_exam_question1`
          FOREIGN KEY (`exam_id` , `question_id`)
          REFERENCES `exam`.`exam_question` (`exam_id` , `question_id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE)
      ENGINE = InnoDB;"
);

foreach ($dropTables as $query) {
    $result = mysqli_query($connect, $query);
    if (!$result) {
        die("Could not successfully run query ($query) from $db: " . mysqli_error($connect));
    }
}

foreach ($createTables as $query) {
    $result = mysqli_query($connect, $query);
    if (!$result) {
        die("Could not successfully run query ($query) from $db: " . mysqli_error($connect));
    }
}

$insertDatas = array(
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (1,"Laurel","Cody","' . password_hash("mole", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","dignissim.lacus@auctornuncnulla.ca","teacher");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (2,"Ifeoma","Evelyn","' . password_hash("tincidunt, nunc ac mattis ornare,", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","auctor.ullamcorper.nisl@ipsumnuncid.net","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (3,"Kenneth","Ramona","' . password_hash("commodo ipsum.", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","Aliquam.nisl@CraspellentesqueSed.edu","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (4,"Blake","Armand","' . password_hash("dui", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","odio.a.purus@mitemporlorem.net","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (5,"Alma","Brynn","' . password_hash("sodal", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","blandit.mattis@velarcuCurabitur.org","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (6,"Jillian","Joseph","' . password_hash("posuere cubilia", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","gravida.Aliquam.tincidunt@scelerisquemollis.net","teacher");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (7,"Harper","Cole","' . password_hash("tristique senectus et", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","Aliquam.tincidunt.nunc@tellusSuspendisse.net","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (8,"Kylie","Nora","' . password_hash("libero at auctor ullamcorper,", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","lectus.pede@metussit.com","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (9,"Lara","Jaquelyn","' . password_hash("malesuada augue", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","eget.odio@enimcommodo.ca","student");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (10,"Rana","Phoebe","' . password_hash("mi. Aliquam", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","Nunc.mauris.elit@aliquam.co.uk","teacher");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (11,"can","Can","' . password_hash("canvacan", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","nunc.risus@loremeu.co.uk","admin");',
    'INSERT INTO `user` (`user_id`,`login_id`,`user_name`,`user_password`,`user_email`,`user_type`) VALUES (12,"admin","Admin","' . password_hash("admin", (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT))) . '","nusus@lomeu.co.uk","admin");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (2,"o","2002-10-13 03:59:56");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (3,"o","1997-09-11 11:14:14");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (4,"m","2003-08-10 20:47:58");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (5,"m","1999-12-23 03:39:11");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (7,"m","2002-05-25 13:36:43");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (8,"o","1998-11-14 07:05:11");',
    'INSERT INTO `student` (`student_id`,`student_gender`,`student_birthday`) VALUES (9,"m","1997-08-09 00:56:06");',
    'INSERT INTO `course` (`course_id`,`teacher_id`,`course_name`) VALUES (1,1,"PHP3324 - Phantom Pet");',
    'INSERT INTO `course` (`course_id`,`teacher_id`,`course_name`) VALUES (2,10,"CPP1322 - Carpet Purchase");',
    'INSERT INTO `course` (`course_id`,`teacher_id`,`course_name`) VALUES (3,6,"RST2211 - Rusty Tree");',
    'INSERT INTO `course` (`course_id`,`teacher_id`,`course_name`) VALUES (4,6,"PYT4132 - Pie You Treat");',
    'INSERT INTO `course` (`course_id`,`teacher_id`,`course_name`) VALUES (5,10,"WEB1241 - Spider and Nature");',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (2,1);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (3,3);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (5,1);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (9,5);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (8,4);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (4,2);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (4,3);',
    'INSERT INTO `student_course` (`student_id`,`course_id`) VALUES (7,2);',
    'INSERT INTO `exam` (`exam_id`,`course_id`,`exam_title`,`exam_date_start`,`exam_date_end`,`exam_created_on`) VALUES (1,1,"Final Assessment","2020-12-15 13:05:00","2020-12-15 15:05:00","2020-04-17 03:54:15");',
    'INSERT INTO `exam` (`exam_id`,`course_id`,`exam_title`,`exam_date_start`,`exam_date_end`,`exam_created_on`) VALUES (2,2,"Mid-term","2020-12-03 16:50:00","2020-12-13 12:05:00","2020-07-01 02:04:30");',
    'INSERT INTO `exam` (`exam_id`,`course_id`,`exam_title`,`exam_date_start`,`exam_date_end`,`exam_created_on`) VALUES (3,3,"Final Exam","2020-12-16 04:50:00","2020-12-19 12:05:00","2019-12-21 22:11:59");',
    'INSERT INTO `exam` (`exam_id`,`course_id`,`exam_title`,`exam_date_start`,`exam_date_end`,`exam_created_on`) VALUES (4,4,"Mid-term","2020-12-27 12:30:00","2020-12-30 12:05:00","2020-09-19 21:06:55");',
    'INSERT INTO `exam` (`exam_id`,`course_id`,`exam_title`,`exam_date_start`,`exam_date_end`,`exam_created_on`) VALUES (5,5,"Final Exam","2020-12-23 09:25:00","2020-12-30 12:05:00","2020-06-18 07:46:16");',
    'INSERT INTO `question` (`question_id`,`question_title`,`question_type`) VALUES (1,"Please write down Pikachu.","text");',
    'INSERT INTO `question` (`question_id`,`question_title`,`question_type`) VALUES (2,"This is a [blank] question, please [fill] it up.","fill");',
    'INSERT INTO `question` (`question_id`,`question_title`,`question_type`) VALUES (3,"Please choose True.","choice");',
    'INSERT INTO `question` (`question_id`,`question_title`,`question_type`) VALUES (4,"Please choose Pikachu","choice");',
    'INSERT INTO `question` (`question_id`,`question_title`,`question_type`) VALUES (5,"Please choose False","choice");',
    'INSERT INTO `question` (`question_id`,`question_title`,`question_type`) VALUES (6,"You are in middle of nowhere. What would you bring? Please explain briefly.","text");',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (1,2,7);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (1,4,10);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (2,1,1);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (3,3,10);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (3,6,4);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (3,2,5);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (3,4,8);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (4,1,9);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (4,4,5);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (4,6,5);',
    'INSERT INTO `exam_question` (`exam_id`,`question_id`,`question_score`) VALUES (5,1,2);',
    'INSERT INTO `answer` (`answer_text`) VALUES ("Eevee");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("False");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("Lucario");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("Mimikyu");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("Pikachu");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("True");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("1st_2nd");',
    'INSERT INTO `answer` (`answer_text`) VALUES ("blank, fill");',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (1,4,0);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (2,3,0);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (2,5,1);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (3,4,0);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (4,4,0);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (5,4,1);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (6,3,1);',
    'INSERT INTO `question_choice` (`answer_id`,`question_id`,`is_answer`) VALUES (6,5,0);',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (2,1,"2020-12-19 14:01:47");',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (5,1,"2020-12-19 14:02:33");',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (4,2,"2020-12-03 17:32:12");',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (7,2,"2020-12-03 17:33:43");',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (3,3,"2020-12-16 10:18:50");',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (8,4,"2020-12-27 13:42:13");',
    'INSERT INTO `student_exam` (`student_id`,`exam_id`,`time_submit`) VALUES (9,5,"2020-12-27 13:40:01");',
    'INSERT INTO `student_answer` (`student_id`,`exam_id`,`question_id`,`answer_id`,`answer_score`) VALUES (2,1,2,7,7);',
    'INSERT INTO `student_answer` (`student_id`,`exam_id`,`question_id`,`answer_id`,`answer_score`) VALUES (5,1,2,8,7);',
    'INSERT INTO `student_answer` (`student_id`,`exam_id`,`question_id`,`answer_id`,`answer_score`) VALUES (2,1,4,5,10);',
    'INSERT INTO `student_answer` (`student_id`,`exam_id`,`question_id`,`answer_id`,`answer_score`) VALUES (5,1,4,1,0);',
);


foreach ($insertDatas as $query) {
    $result = mysqli_query($connect, $query);
    if (!$result) {
        die("Could not successfully run query ($query) from $db: " . mysqli_error($connect));
    }
}

$base64_data = array(
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGL55uDMQApofVJIknomklSTAUYtGLVg1IJRC0YtoAZgFDqWRpKGvwdOk6R+6AfRqAWjFoxaMGrBiLAAEAAA//8BAwdbW59/sgAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGJZIjWPgRTQH+lEknomklSTAUYtGLVg1IJRC0YtoAZgFON8TJKG2KXLSFI/9INo1IJRC0YtGLVgRFgACAAA///eDgVy7/SNpwAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGJhahZiIAUs+KBKknomklSTAUYtGLVg1IJRC0YtoAZgeWSmSJKGyFx/ktQP/SAatWDUglELRi0YERYAAgAA//8HpwTfePCqvQAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGKp232YgRQg2DqDJPVMJKkmA4xaMGrBqAWjFoxaQA3A8mNOAkkaOvZIkKR+6AfRqAWjFoxaMGrBiLAAEAAA//+vvQa/pSBnIAAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGJ5UMnKQApgPFFFknomklSTAUYtGLVg1IJRC0YtoAZgdHmkRJKGjerqJKkf+kE0asGoBaMWjFowIiwABAAA///qkAUqPo+yQgAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARUlEQVR4nGJx3STKQAqws5ImST0TSarJAKMWjFowasGoBaMWUAMw6ms5k6aD3Y8k5UM/iEYtGLVg1IJRC0aEBYAAAAD//9w3AtJdfM0lAAAAAElFTkSuQmCC",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGJJDPNgIAWwbAohST0TSarJAKMWjFowasGoBaMWUAMwXnkjR5KGf4Y7SFI/9INo1IJRC0YtGLVgRFgACAAA//+gqQYQeJW/kAAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAAR0lEQVR4nGI5+HIyAynA5ed9ktQzkaSaDDBqwagFoxaMWjBqATUAo2fKPpI0nD+aQJL6oR9EoxaMWjBqwagFI8ICQAAAAP//BvMH+lvDEsEAAAAASUVORK5CYII=",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAAR0lEQVR4nGJZuvUiAylgWupxktQzkaSaDDBqwagFoxaMWjBqATUAy7/nkSRpiDg2nST1Qz+IRi0YtWDUglELRoQFgAAAAP//maEIJVQ+4/AAAAAASUVORK5CYII=",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGI53bOYgRTw49J7ktQzkaSaDDBqwagFoxaMWjBqATUAY/VTRZI0pMbsJkn90A+iUQtGLRi1YNSCEWEBIAAA///x6gfym7n3pgAAAABJRU5ErkJggg==",
    "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAARklEQVR4nGIxzQxkIAXIF+SRpJ6JJNVkgFELRi0YtWDUglELqAEYNV+Lk6RBIKeZJPVDP4hGLRi1YNSCUQtGhAWAAAAA//+0gwRYXW15rAAAAABJRU5ErkJggg=="
);

for ($x = 1; $x <= 11; $x++) {
    $sql = "UPDATE `user` SET `profile_image`=? , `image_type`='image/png' WHERE `user_id`=?";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        $stmt->bind_param('bi', $content, $id);

        $id = $x;
        $content = null;
        $stmt->send_long_data(0, base64_decode($base64_data[$x - 1]));

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    } else {
        $error = mysqli_error($connect);
        echo $error;
    }
}

print("<html><head><title>MySQL Setup</title></head>
	<body><h1>MySQL Setup: SUCCESS!</h1><p>Created MySQL user <strong>wbip</strong> with
	password <strong>wbip123</strong>, with all privileges on the
	<strong>exam</strong> database.</p><p>Created tables in the
	<strong>exam</strong> database.</p>
	</body></html>");

mysqli_close($connect); // close the connection
