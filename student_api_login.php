<?php
// student_api_login.php — 学生登录处理（验证学号 + 密码 + 验证码）
// 确保 Session 可写
ini_set('session.save_path', sys_get_temp_dir());
session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_login.php');
    exit;
}

$student_id   = trim($_POST['student_id'] ?? '');
$password     = $_POST['password'] ?? '';
$captchaInput = trim($_POST['captcha'] ?? '');

// 1. 验证验证码
if (empty($_SESSION['captcha_code']) || strtolower($captchaInput) !== strtolower($_SESSION['captcha_code'])) {
    header('Location: student_login.php?error=' . urlencode('验证码错误'));
    exit;
}
unset($_SESSION['captcha_code']);

// 2. 空值检查
if (empty($student_id) || empty($password)) {
    header('Location: student_login.php?error=' . urlencode('学号和密码不能为空'));
    exit;
}

// 3. PHP 基础知识：预处理查询学生
$stmt = $conn->prepare("SELECT student_id, name, password FROM student WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// 4. 验证学生是否存在及密码
if (!$student || empty($student['password'])) {
    header('Location: student_login.php?error=' . urlencode('学号或密码错误'));
    exit;
}

if (!password_verify($password, $student['password'])) {
    header('Location: student_login.php?error=' . urlencode('学号或密码错误'));
    exit;
}

// 5. 登录成功，存入 Session
$_SESSION['student_id']   = $student['student_id'];
$_SESSION['student_name'] = $student['name'];

// 6. 跳转到学生仪表盘
header('Location: student_dashboard.php');
exit;
