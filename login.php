<?php
ini_set('session.save_path', sys_get_temp_dir());
session_start();
require_once 'conn.php';

// 已登录则跳转
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
if (isset($_SESSION['student_id'])) {
    header('Location: student_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>学生成绩管理系统登录</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-card { width: 380px; border-radius: 10px; }
        .role-btn.active { background-color: #007bff; color: #fff; border-color: #007bff; }
        .role-btn { cursor: pointer; }
    </style>
</head>
<body>
<div class="card login-card shadow">
    <div class="card-body">
        <h3 class="text-center mb-4">学生成绩管理系统</h3>
        <form action="api_login.php" method="post">
            <!-- 隐藏字段：当前角色 -->
            <input type="hidden" name="role" id="role-input" value="admin">

            <!-- 身份切换按钮 -->
            <div class="form-group">
                <div class="d-flex">
                    <button type="button" class="btn btn-outline-primary flex-fill mr-1 role-btn active" id="btn-admin" onclick="switchRole('admin')">管理员</button>
                    <button type="button" class="btn btn-outline-primary flex-fill role-btn" id="btn-student" onclick="switchRole('student')">学生</button>
                </div>
            </div>

            <div class="form-group">
                <label id="label-username">用户名</label>
                <input type="text" name="username" id="input-username" class="form-control" placeholder="请输入用户名" required autofocus>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" id="input-password" class="form-control" placeholder="请输入密码" required>
            </div>
            <div class="form-group">
                <label>验证码</label>
                <div class="d-flex">
                    <input type="text" name="captcha" class="form-control" style="width: 60%;" required>
                    <img src="captcha.php" class="ml-2 border" style="height: 38px; cursor: pointer;" onclick="this.src='captcha.php?'+Math.random()">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">登录</button>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3 py-2"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function switchRole(role) {
    document.getElementById('role-input').value = role;
    
    // 切换按钮样式
    document.getElementById('btn-admin').classList.toggle('active', role === 'admin');
    document.getElementById('btn-student').classList.toggle('active', role === 'student');
    
    // 切换提示文字
    var label = document.getElementById('label-username');
    var inputU = document.getElementById('input-username');
    var inputP = document.getElementById('input-password');
    
    if (role === 'student') {
        label.textContent = '学号';
        inputU.placeholder = '请输入学号';
        inputP.placeholder = '默认密码为学号';
    }
}
</script>
</body>
</html>
