<?php
ini_set('session.save_path', sys_get_temp_dir());
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'conn.php';

// 路由：根据 action 参数加载不同内容
$action = $_GET['action'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理后台 - 学生成绩管理系统</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap 4 依赖 jQuery -->
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <style>
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    }
    .sidebar-sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        height: calc(100vh - 48px);
        padding-top: .5rem;
        overflow-x: hidden;
        overflow-y: auto;
    }
    .sidebar .nav-link {
        font-weight: 500;
        color: #333;
        padding: 12px 16px;
        border-left: 3px solid transparent;
    }
    .sidebar .nav-link:hover {
        color: #007bff;
        background: #f8f9fa;
    }
    .sidebar .nav-link.active {
        color: #007bff;
        border-left-color: #007bff;
        background: #e9ecef;
    }
    .sidebar .nav-link i {
        margin-right: 8px;
        width: 20px;
        text-align: center;
    }
    main {
        padding-top: 20px;
    }
    .page-card {
        background: #fff;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- 侧边栏 -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky pt-3">
                <h5 class="px-3 mb-3 text-muted">
                    <i class="fas fa-graduation-cap"></i> 学生成绩管理系统
                </h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='dashboard'?'active':''; ?>" href="admin.php">
                            <i class="fas fa-tachometer-alt"></i> 系统首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='student_list'?'active':''; ?>" href="admin.php?action=student_list">
                            <i class="fas fa-users"></i> 学生列表
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='subject_list'||$action==='subject_add'?'active':''; ?>" href="admin.php?action=subject_list">
                            <i class="fas fa-book"></i> 科目管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='score_chart'?'active':''; ?>" href="admin.php?action=score_chart">
                            <i class="fas fa-chart-bar"></i> 成绩统计图
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='score_manage'?'active':''; ?>" href="admin.php?action=score_manage">
                            <i class="fas fa-edit"></i> 成绩记录管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='export_txt'?'active':''; ?>" href="admin.php?action=export_txt">
                            <i class="fas fa-file-export"></i> 导出TXT
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='upload_avatar'?'active':''; ?>" href="admin.php?action=upload_avatar">
                            <i class="fas fa-image"></i> 上传头像
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action==='user_manage'?'active':''; ?>" href="admin.php?action=user_manage">
                            <i class="fas fa-user-shield"></i> 用户管理
                        </a>
                    </li>
                </ul>

                <h6 class="px-3 mt-4 mb-2 text-muted" style="font-size:0.75rem;text-transform:uppercase;">
                    账户
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> 退出登录
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- 主内容区 -->
        <main class="col-md-10 ml-sm-auto px-4" role="main">
            <?php
            // 路由分发
            switch ($action) {
                case 'dashboard':
                    include 'dashboard_content.php';
                    break;
                case 'student_list':
                    include 'student_list_content.php';
                    break;
                case 'add_student':
                    include 'add_student_content.php';
                    break;
                case 'edit_score':
                    include 'edit_score_content.php';
                    break;
                case 'subject_list':
                    include 'subject_list_content.php';
                    break;
                case 'subject_add':
                    include 'subject_add_content.php';
                    break;
                case 'subject_edit':
                    include 'subject_edit_content.php';
                    break;
                case 'subject_delete':
                    include 'subject_delete_content.php';
                    break;
                case 'score_chart':
                    include 'score_chart_content.php';
                    break;
                case 'score_manage':
                    include 'score_manage_content.php';
                    break;
                case 'export_txt':
                    include 'export_txt_content.php';
                    break;
                case 'upload_avatar':
                    include 'upload_avatar_content.php';
                    break;
                case 'user_manage':
                    include 'user_manage_content.php';
                    break;
                default:
                    include 'dashboard_content.php';
            }
            ?>
        </main>
    </div>
</div>
</body>
</html>
