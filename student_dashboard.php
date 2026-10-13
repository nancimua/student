<?php
// student_dashboard.php — 学生登录查看页面
ini_set('session.save_path', sys_get_temp_dir());
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'conn.php';

$student_id = $_SESSION['student_id'];

// 查询学生基本信息
$stmt = $conn->prepare("SELECT name, class, avatar FROM student WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header('Location: student_login.php?error=' . urlencode('学生信息不存在'));
    exit;
}

// 查询各科成绩（JOIN subject 表）
$sql = "SELECT s.subject_name, sc.score
        FROM score sc
        JOIN `subject` s ON sc.subject_id = s.subject_id
        WHERE sc.student_id = ?
        ORDER BY s.subject_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
$scores   = [];
$total    = 0;
$count    = 0;
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row['subject_name'];
    $scores[]   = $row['score'];
    $total     += $row['score'];
    $count++;
}
$stmt->close();

$avgScore = $count > 0 ? round($total / $count, 1) : 0;
$level    = $avgScore >= 90 ? '优秀' : ($avgScore >= 75 ? '良好' : ($avgScore >= 60 ? '及格' : '待提高'));
$levelBadge = $avgScore >= 90 ? 'success' : ($avgScore >= 75 ? 'info' : ($avgScore >= 60 ? 'warning' : 'danger'));

// 头像首字
$firstChar = mb_substr($student['name'], 0, 1, 'UTF-8');
$avatarUrl = !empty($student['avatar']) ? htmlspecialchars($student['avatar']) : null;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的成绩 - <?php echo htmlspecialchars($student['name']); ?></title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #ecf0f1; padding: 30px; font-family: 'Microsoft YaHei','Segoe UI',sans-serif; }
        .score-card { max-width: 700px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.08); overflow: hidden; }
        .header-bg { background: linear-gradient(135deg, #3498db, #2980b9); padding: 30px; color: #fff; text-align: center; }
        .header-bg .avatar { width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold; margin: 0 auto 12px; border: 3px solid rgba(255,255,255,.4); }
        .header-bg .avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .body-content { padding: 24px; }
        .score-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
        .score-row:last-child { border-bottom: none; }
        .stat-card { background: #f8f9fa; border-radius: 12px; padding: 16px; }
        .stat-item { text-align: center; }
        .stat-item .value { font-size: 1.8rem; font-weight: 800; }
        .stat-item .label { color: #6c757d; font-size: .85rem; }
        .footer-links { padding: 16px 24px; background: #f8f9fa; border-top: 1px solid #eee; text-align: center; }
    </style>
</head>
<body>
<div class="score-card">
    <!-- 头部 -->
    <div class="header-bg">
        <div class="avatar">
            <?php if ($avatarUrl): ?>
                <img src="<?php echo $avatarUrl; ?>" alt="头像">
            <?php else: ?>
                <?php echo $firstChar; ?>
            <?php endif; ?>
        </div>
        <h4 class="mb-1"><?php echo htmlspecialchars($student['name']); ?> 同学</h4>
        <p class="mb-0" style="opacity:.85;">
            <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($student_id); ?>
            ｜ <i class="fas fa-users"></i> <?php echo htmlspecialchars($student['class'] ?? '未分配'); ?>
        </p>
    </div>

    <!-- 内容 -->
    <div class="body-content">
        <h5 class="mb-3"><i class="fas fa-chart-simple text-primary"></i> 我的各科成绩</h5>

        <?php if ($count > 0): ?>
            <?php foreach ($subjects as $i => $sub):
                $s    = $scores[$i];
                $tag  = $s >= 90 ? '优秀' : ($s >= 80 ? '良好' : ($s >= 70 ? '中等' : ($s >= 60 ? '及格' : '待提升')));
                $tagC = $s >= 90 ? 'success' : ($s >= 80 ? 'info' : ($s >= 70 ? 'primary' : ($s >= 60 ? 'warning' : 'danger')));
            ?>
            <div class="score-row">
                <span><strong><?php echo htmlspecialchars($sub); ?></strong></span>
                <span>
                    <span class="badge badge-<?php echo $tagC; ?>" style="font-size:.9rem;padding:5px 12px;">
                        <?php echo $s; ?> 分
                    </span>
                    <span class="badge badge-light ml-1"><?php echo $tag; ?></span>
                </span>
            </div>
            <?php endforeach; ?>

            <!-- 统计 -->
            <div class="stat-card mt-3">
                <div class="row">
                    <div class="col-4 stat-item">
                        <div class="value text-primary"><?php echo $total; ?></div>
                        <div class="label">总分</div>
                    </div>
                    <div class="col-4 stat-item">
                        <div class="value text-info"><?php echo $avgScore; ?></div>
                        <div class="label">平均分</div>
                    </div>
                    <div class="col-4 stat-item">
                        <div class="value">
                            <span class="badge badge-<?php echo $levelBadge; ?>" style="font-size:1.1rem;padding:6px 16px;">
                                <?php echo $level; ?>
                            </span>
                        </div>
                        <div class="label">等级</div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                暂无成绩数据，请联系老师录入。
            </div>
        <?php endif; ?>
    </div>

    <!-- 底部 -->
    <div class="footer-links">
        <a href="student_logout.php" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-sign-out-alt"></i> 退出登录
        </a>
    </div>
</div>
</body>
</html>
