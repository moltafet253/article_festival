<?php
include_once __DIR__ . '/config/connection.php';
include_once __DIR__ . '/build/php/functions.php';
session_start();

$urlofthispage = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

//type=1 => rater
//type=2 => header
//type=3 => admin
//type=4 => super-admin
//type=5 => journal-admin
//approved=0 => کاربر غیر فعال شده
$dateforinsertloglogins = $year . '/' . $month . '/' . $day . ' ' . $hour . ':' . $min . ':' . $sec;
function convertPersianNumbersToEnglish($text)
{
    $persianToEnglish = array(
        '۰' => '0',
        '۱' => '1',
        '۲' => '2',
        '۳' => '3',
        '۴' => '4',
        '۵' => '5',
        '۶' => '6',
        '۷' => '7',
        '۸' => '8',
        '۹' => '9'
    );
    $englishText = str_replace(array_keys($persianToEnglish), $persianToEnglish, $text);
    return $englishText;
}
if (isset($_POST) & !empty($_POST)) {
    if ($_POST['captcha'] == $_SESSION['captcha']) {
        $user = convertPersianNumbersToEnglish($_POST['username']);
        $pass = convertPersianNumbersToEnglish($_POST['password']);
        if ((!isset($_POST['submit']) and empty($user)) or empty($pass)) {
            $operation = "LoginError";
            logsend($operation, $urlofthispage, $connection_maghalat);
            header("location:index?error");
        } else {
            $result = mysqli_query($connection_maghalat, "select * from users where username='$user'");
            foreach ($result as $rows) {
            }
            if (empty($rows)) {
                $operation = "NotFoundUser - Entered User=$user";
                logsend($operation, $urlofthispage, $connection_maghalat);
                header("location:index?NotFoundUser-UserWrong");
            } elseif (!password_verify($pass, $rows['password'])) {
                $operation = "WrongPassword - Entered User=$user";
                logsend($operation, $urlofthispage, $connection_maghalat);
                header("location:index?WrongPassword-UserWrong");
            } else {
                if ($rows['approved'] == 0) {
                    $operation = "NotApproved - Entered User=$user";
                    logsend($operation, $urlofthispage, $connection_maghalat);
                    header("location:index?NotApprovedUser");
                } else {
                    if ($user == $rows['username']) {
                        $_SESSION['username'] = $rows['username'];
                        $_SESSION['head'] = $rows['type'];
                        $_SESSION['islogin'] = true;
                        $_SESSION['id'] = $rows['id'];
                        $_SESSION['start'] = time();
                        $_SESSION['end'] = $_SESSION['start'] + (36000);
                        if ($rows['type'] == 1) {
                            $_SESSION['group'] = $rows['scientific_group'];
                            $operation = "RaterLoginSuccess";
                            logsend($operation, $urlofthispage, $connection_maghalat);
                            header("location:panel");
                        } elseif ($rows['type'] == 2) {
                            $operation = "AdminLoginSuccess";
                            logsend($operation, $urlofthispage, $connection_maghalat);
                            header("location:panel");
                        } elseif ($rows['type'] == 3) {
                            $operation = "HeaderLoginSuccess";
                            logsend($operation, $urlofthispage, $connection_maghalat);
                            header("location:panel");
                        } elseif ($rows['type'] == 4) {
                            $operation = "SuperAdminLoginSuccess";
                            logsend($operation, $urlofthispage, $connection_maghalat);
                            header("location:panel");
                        } elseif ($rows['type'] == 5) {
                            $operation = "JournalAdminLoginSuccess";
                            logsend($operation, $urlofthispage, $connection_maghalat);
                            header("location:panel");
                        } elseif ($rows['type'] == 6) {
                            $operation = "SorterLoginSuccess";
                            logsend($operation, $urlofthispage, $connection_maghalat);
                            header("location:panel");
                        } else {
                            header("location:index?error");
                        }
                    }
                }
            }
        }
    } else {
        $captcha = $_POST['captcha'];
        $operation = "InvalidCaptcha(CaptchaEntered=$captcha)";
        logsend($operation, $urlofthispage, $connection_maghalat);
        header("location:index?invalidcaptcha");
    }
} else {
    header("location:index?error");
}
