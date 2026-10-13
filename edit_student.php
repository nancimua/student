<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require_once 'conn.php';

$stuId = $_GET['id'] ?? '';
$message = $error = '';
$student = null;
$scores = [];

// 获取学生信息
$stmt = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt->bind_param("s", $stuId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) {
    die("学生不存在");
}

// 获取所有科目
$subjects = [];
$subRes = $conn->query("SELECT subject_id, subject_name FROM subject ORDER BY subject_id");
while ($row = $subRes->fetch_assoc()) {
    $subjects[$row['subject_id']] = $row['subject_name'];
}

// 获取已有成绩，并用科目ID作为键
$scoreStmt = $conn->prepare("SELECT subject_id, score FROM score WHERE student_id = ?");
$scoreStmt->bind_param("s", $stuId);
$scoreStmt->execute();
$scoreRes = $scoreStmt->get_result();
while ($row = $scoreRes->fetch_assoc()) {
    $scores[$row['subject_id']] = $row['score'];
}

// 如果某些科目没有成绩记录，补一个默认值（0），但不要插入数据库，只在表单显示时使用
// 实际保存时会自动创建缺失的记录

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newId = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $gender = $_POST['gender'];
    $class = trim($_POST['class']);

    // 学号验证（加详细错误信息）
    if (!preg_match("/^2[0-9]{10}$/", $newId)) {
        $error = "学号格式错误！必须是2开头 + 10位数字，总共11位数字！";
    } elseif (!preg_match("/^[\x{4e00}-\x{9fa5}]{2,6}$/u", $name)) {
        $error = "姓名格式错误：必须为2-6位中文";
    } else {
        $conn->begin_transaction();
        try {
            // 更新 student 表
            $upd = $conn->prepare("UPDATE student SET student_id=?, name=?, gender=?, class=? WHERE student_id=?");
            $upd->bind_param("sssss", $newId, $name, $gender, $class, $stuId);
            $upd->execute();
            $upd->close();

            // 如果学号改变了，同步更新 score 表中的 student_id
            if ($newId != $stuId) {
                $conn->query("UPDATE score SET student_id = '$newId' WHERE student_id = '$stuId'");
            }

            // 处理各科成绩：更新或插入
            foreach ($subjects as $sid => $sname) {
                $scoreVal = floatval($_POST["score_$sid"] ?? 0);
                // 检查是否存在记录
                $check = $conn->prepare("SELECT id FROM score WHERE student_id = ? AND subject_id = ?");
                $check->bind_param("si", $newId, $sid);
                $check->execute();
                $exists = $check->get_result()->num_rows > 0;
                $check->close();

                if ($exists) {
                    $updScore = $conn->prepare("UPDATE score SET score = ? WHERE student_id = ? AND subject_id = ?");
                    $updScore->bind_param("dsi", $scoreVal, $newId, $sid);
                    $updScore->execute();
                    $updScore->close();
                } else {
                    $insScore = $conn->prepare("INSERT INTO score (student_id, subject_id, score) VALUES (?, ?, ?)");
                    $insScore->bind_param("sid", $newId, $sid, $scoreVal);
                    $insScore->execute();
                    $insScore->close();
                }
            }

            $conn->commit();
            $message = "修改成功！";
            $stuId = $newId;
            // 刷新当前显示的学生数据
            $student['student_id'] = $newId;
            $student['name'] = $name;
            $student['gender'] = $gender;
            $student['class'] = $class;
            // 刷新成绩数组，用于表单回显
            $newScoreStmt = $conn->prepare("SELECT subject_id, score FROM score WHERE student_id = ?");
            $newScoreStmt->bind_param("s", $newId);
            $newScoreStmt->execute();
            $newScoreRes = $newScoreStmt->get_result();
            $scores = [];
            while ($row = $newScoreRes->fetch_assoc()) {
                $scores[$row['subject_id']] = $row['score'];
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "修改失败：" . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>编辑学生</title>
<style>
body { font-family: 'Microsoft YaHei'; margin-left:220px; padding:20px; background:#ecf0f1; }
.container { background:#fff; padding:20px; border-radius:10px; max-width:600px; }
.form-group { margin-bottom:15px; }
label { display:block; margin-bottom:5px; }
input,select { width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; }
.scores { display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-top:10px; }
button { background:#f39c12; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; }
.msg-success { color:#27ae60; background:#eafaf1; padding:10px; margin-bottom:15px; }
.msg-error { color:#e74c3c; background:#fdedec; padding:10px; margin-bottom:15px; }
 .sidebar { position: fixed; left:0; top:0; width: 220px; height:100vh;
            background: #2c3e50; color: #ecf0f1; padding: 24px 0; }
 .sidebar h2 { color:white;text-align: center; margin-bottom: 30px; font-size: 20px; }
 .sidebar a { display: block; padding: 12px 24px; color: #bdc3c7; text-decoration: none;
               transition: .2s; font-size: 14px; }
 .sidebar a:hover, .sidebar a.active { background: #34495e; color: #fff; }
</style>
</head>
<body>
<div class="sidebar">
    <h2>学生成绩管理系统</h2>
    <a href="admin.php">🏠 系统首页</a>
    <a href="add_student.php">➕ 添加学生</a>
    <a href="admin.php?list=1">📋 学生列表<br>（给学生添加成绩）</a>
    <a href="score_chart.php" target="_blank">📊 成绩统计图<br>（查看学生成绩）</a>
    <a href="export_txt.php">📄 导出学生成绩TXT</a>
    <a href="upload_avatar.php">🖼️ 上传学生头像</a>
    <a href="logout.php" style="margin-top:40px;color:#e74c3c;">🚪 退出登录</a>
</div>
<div class="container">
    <h2>编辑学生成绩</h2>
    <?php if ($message) {
    echo "<div class='msg-success'>$message</div>";
} ?>
    <?php if ($error) {
    echo "<div class='msg-error'>$error</div>";
} ?>
    <form method="post">
        <div class="form-group"><label>学号</label><input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>" required></div>
        <div class="form-group"><label>姓名</label><input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required></div>
        <div class="form-group"><label>性别</label><select name="gender"><option <?= $student['gender']=='男'?'selected':'' ?>>男</option><option <?= $student['gender']=='女'?'selected':'' ?>>女</option></select></div>
        <div class="form-group"><label>班级</label><input type="text" name="class" value="<?= htmlspecialchars($student['class']) ?>"></div>
        <div class="form-group"><label>各科成绩（0-100）</label><div class="scores">
            <?php foreach ($subjects as $sid => $sname): ?>
            <div><label><?= $sname ?></label><input type="number" step="0.5" min="0" max="100" name="score_<?= $sid ?>" value="<?= $scores[$sid] ?? '' ?>"></div>
            <?php endforeach; ?>
        </div></div>
        <button type="submit">保存修改</button>
    </form>
    <a href="admin.php?list=1">← 返回列表</a>
</div>
</body>
</html>
?>