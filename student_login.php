<?php
// student_login.php — 学生登录页面
ini_set('session.save_path', sys_get_temp_dir());
session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: student_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>学生登录 - 成绩查询系统</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-card { width: 380px; border-radius: 12px; }
        .login-card .card-body { padding: 2rem; }
    </style>
</head>
<body>
<div class="card login-card shadow">
    <div class="card-body">
        <h3 class="text-center mb-1">📘 学生成绩查询</h3>
        <p class="text-muted text-center small mb-4">使用学号和密码登录查看成绩</p>

        <form action="student_api_login.php" method="post">
            <div class="form-group">
                <label>学号</label>
                <input type="text" name="student_id" class="form-control" required autofocus
                       placeholder="请输入学号（如 24103120101）">
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" class="form-control" required
                       placeholder="默认密码为学号">
            </div>
            <div class="form-group">
                <label>验证码</label>
                <div class="d-flex align-items-center">
                    <input type="text" name="captcha" class="form-control" style="width:55%;" required placeholder="输入验证码">
                    <img src="captcha.php" class="ml-2 border rounded" style="height:38px;cursor:pointer;"
                         onclick="this.src='captcha.php?'+Math.random()" title="点击刷新验证码">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-4">
                <i class="fas fa-sign-in-alt"></i> 登录
            </button>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger mt-3 py-2 small">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
            <?php endif; ?>
        </form>

        <div class="text-center mt-3 border-top pt-3">
            <a href="login.php" class="text-muted small">🔐 管理员登录入口</a>
        </div>
    </div>
</div>
</body>
</html>
