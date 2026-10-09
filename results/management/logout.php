<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['exam_admin_logged']);
unset($_SESSION['exam_admin_id']);
unset($_SESSION['exam_admin_user']);
unset($_SESSION['exam_admin_name']);

header('Location: login.php');
exit;
