<?php
// student_logout.php — 学生退出登录
ini_set('session.save_path', sys_get_temp_dir());
session_start();
session_destroy();
header('Location: login.php');
exit;
