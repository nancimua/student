<?php
session_start();

// 会话控制：已登录则跳转到后台，否则跳转到登录页
if (isset($_SESSION['admin'])) {
    header('Location: admin.php');
} else {
    header('Location: login.php');
}
exit;
?>