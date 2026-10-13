<?php
// api_login.php — 统一登录处理（管理员 + 学生）
ini_set('session.save_path', sys_get_temp_dir());
session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username     = trim($_POST['username'] ?? '');
$password     = $_POST['password'] ?? '';
$captchaInput = trim($_POST['captcha'] ?? '');
$role         = $_POST['role'] ?? 'admin'; // 默认管理员

// 1. 验证验证码
if (empty($_SESSION['captcha_code']) || strtolower($captchaInput) !== strtolower($_SESSION['captcha_code'])) {
    header('Location: login.php?error=' . urlencode('验证码错误'));
    exit;
}
unset($_SESSION['captcha_code']);

// 2. 空值检查
if (empty($username) || empty($password)) {
    header('Location: login.php?error=' . urlencode('用户名/学号或密码不能为空'));
    exit;
}

// 3. 根据角色查不同表
if ($role === 'student') {
    // 学生登录：用学号查 student 表，password_verify 验证
    $stmt = $conn->prepare("SELECT student_id, name, password FROM student WHERE student_id = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();

    if (!$student || empty($student['password'])) {
        header('Location: login.php?error=' . urlencode('学号或密码错误'));
        exit;
    }

    if (!password_verify($password, $student['password'])) {
        header('Location: login.php?error=' . urlencode('学号或密码错误'));
        exit;
    }

    // 登录成功
    $_SESSION['student_id']   = $student['student_id'];
    $_SESSION['student_name'] = $student['name'];
    $_SESSION['role']         = 'student';
    header('Location: student_dashboard.php');
    exit;

} else {
    // 管理员登录：查 admin 表
    // 初始化默认管理员（如果不存在）
    $check = $conn->query("SELECT id FROM admin WHERE username='admin'");
    if ($check->num_rows == 0) {
        $hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES ('admin', ?)");
        $stmt->bind_param('s', $hash);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if (!$admin) {
        header('Location: login.php?error=' . urlencode('用户名或密码错误'));
        exit;
    }

    // 管理员密码用 password_verify 验证（兼容旧的 SHA256 和新的 bcrypt）
    if (password_verify($password, $admin['password'])) {
        // bcrypt 验证成功
    } elseif (hash('sha256', $password) === $admin['password']) {
        // 旧 SHA256 验证成功，升级为 bcrypt
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newHash, $admin['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        header('Location: login.php?error=' . urlencode('用户名或密码错误'));
        exit;
    }

    // 登录成功
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin']    = $admin['username'];
    $_SESSION['role']     = 'admin';
    header('Location: admin.php');
    exit;
}
