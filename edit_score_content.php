<?php
// edit_score_content.php — 修改成绩（纯内容，无HTML布局）
// 课程设计知识点：数据库 UPDATE/REPLACE INTO + JOIN查询

$id = intval($_GET['sid'] ?? 0);
if ($id <= 0) {
    echo '<div class="alert alert-danger">参数错误：缺少学生ID</div>';
    exit;
}

// 查询学生信息
$stmt = $conn->prepare("SELECT id, student_id, name, class, chinese, math, english FROM student WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo '<div class="alert alert-danger">学生不存在</div>';
    exit;
}

// 查询该学生在 score 表中的各科成绩
$scoreData = [];
$scoreResult = $conn->query(
    "SELECT subject_id, score FROM score WHERE student_id = '{$student['student_id']}'"
);
if ($scoreResult) {
    while ($r = $scoreResult->fetch_assoc()) {
        $scoreData[$r['subject_id']] = $r['score'];
    }
}

// 如果 score 表没数据，用 student 表的冗余字段
$chinese = $scoreData[1] ?? $student['chinese'] ?? 0;
$math    = $scoreData[2] ?? $student['math'] ?? 0;
$english = $scoreData[3] ?? $student['english'] ?? 0;

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chinese = floatval($_POST['chinese'] ?? 0);
    $math    = floatval($_POST['math'] ?? 0);
    $english = floatval($_POST['english'] ?? 0);

    // 分数合法性检查
    if ($chinese < 0 || $chinese > 100 || $math < 0 || $math > 100 || $english < 0 || $english > 100) {
        $error = '分数必须在 0-100 之间！';
    } else {
        $total   = $chinese + $math + $english;
        $average = round($total / 3, 1);

        // 计算等级
        if ($average >= 90)      $level = '优秀';
        elseif ($average >= 75)  $level = '良好';
        elseif ($average >= 60)  $level = '及格';
        else                     $level = '待提高';

        // 1. 更新 student 表
        $stmt = $conn->prepare(
            "UPDATE student SET chinese=?, math=?, english=?, total=?, average=?, level=? WHERE id=?"
        );
        $stmt->bind_param("dddddsi", $chinese, $math, $english, $total, $average, $level, $id);
        $stmt->execute();
        $stmt->close();

        // 2. 同步 score 表（REPLACE INTO，需要唯一索引）
        $sid = $conn->real_escape_string($student['student_id']);
        $conn->query("REPLACE INTO score(student_id, subject_id, score) VALUES ('$sid',1,$chinese)");
        $conn->query("REPLACE INTO score(student_id, subject_id, score) VALUES ('$sid',2,$math)");
        $conn->query("REPLACE INTO score(student_id, subject_id, score) VALUES ('$sid',3,$english)");

        $success = "成绩修改成功！总分：{$total}，平均分：{$average}，等级：{$level}";
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-chart-line text-warning"></i> 修改成绩
  </h4>

  <div class="alert alert-info">
    学生：<strong><?php echo esc($student['name']); ?></strong>
    （<?php echo esc($student['student_id']); ?>）
    — <?php echo esc($student['class']); ?>
  </div>

  <?php if ($success): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    ✅ <?php echo $success; ?>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    ❌ <?php echo $error; ?>
  </div>
  <?php endif; ?>

  <form method="post" style="max-width:500px;">
    <div class="form-row">
      <div class="form-group col-md-4">
        <label>语文</label>
        <input type="number" step="0.1" name="chinese" class="form-control"
               min="0" max="100" value="<?php echo $chinese; ?>" required>
      </div>
      <div class="form-group col-md-4">
        <label>数学</label>
        <input type="number" step="0.1" name="math" class="form-control"
               min="0" max="100" value="<?php echo $math; ?>" required>
      </div>
      <div class="form-group col-md-4">
        <label>英语</label>
        <input type="number" step="0.1" name="english" class="form-control"
               min="0" max="100" value="<?php echo $english; ?>" required>
      </div>
    </div>

    <!-- 实时预览 -->
    <div class="alert alert-light" id="preview">
      总分：<strong id="total"><?php echo $chinese + $math + $english; ?></strong> |
      平均分：<strong id="avg"><?php echo round(($chinese + $math + $english) / 3, 1); ?></strong> |
      等级：<strong id="level"><?php
        $a = ($chinese + $math + $english) / 3;
        if ($a >= 90) echo '优秀'; elseif ($a >= 75) echo '良好'; elseif ($a >= 60) echo '及格'; else echo '待提高';
      ?></strong>
    </div>

    <button type="submit" class="btn btn-warning">
      <i class="fas fa-save"></i> 保存成绩
    </button>
    <a href="admin.php?action=student_list" class="btn btn-outline-secondary ml-2">
      <i class="fas fa-arrow-left"></i> 返回列表
    </a>
  </form>
</div>

<script>
// JS 实时计算总分/平均分/等级
function calc() {
    var c = parseFloat(document.querySelector('[name=chinese]').value) || 0;
    var m = parseFloat(document.querySelector('[name=math]').value) || 0;
    var e = parseFloat(document.querySelector('[name=english]').value) || 0;
    var t = c + m + e;
    var a = (t / 3).toFixed(1);
    var l = a >= 90 ? '优秀' : a >= 75 ? '良好' : a >= 60 ? '及格' : '待提高';
    document.getElementById('total').textContent = t;
    document.getElementById('avg').textContent = a;
    document.getElementById('level').textContent = l;
}
document.querySelectorAll('input[name=chinese],input[name=math],input[name=english]').forEach(function(el) {
    el.addEventListener('input', calc);
});
</script>
