<?php
// score_manage_content.php — 成绩记录管理（查看历史、删除错误记录）
// 课程设计知识点：多表 JOIN 查询 + DELETE 删除 + GROUP BY 分组

$success = '';
$error = '';

// ===== 处理删除单条成绩记录 =====
if (isset($_GET['del_score'])) {
    $del_id = intval($_GET['del_score']);
    $stmt = $conn->prepare("DELETE FROM score WHERE id=?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $success = "成绩记录 #{$del_id} 已删除！";
    } else {
        $error = "删除失败：" . $stmt->error;
    }
    $stmt->close();
}

// ===== 处理批量删除某学生的重复成绩（只保留最新一条） =====
if (isset($_POST['clean_duplicates']) && isset($_POST['student_id'])) {
    $sid = $conn->real_escape_string($_POST['student_id']);
    // 查出该学生每个科目的最新成绩 ID
    $sql = "SELECT MAX(id) as max_id FROM score WHERE student_id='$sid' GROUP BY subject_id";
    $result = $conn->query($sql);
    $keepIds = [];
    while ($row = $result->fetch_assoc()) {
        $keepIds[] = $row['max_id'];
    }
    if (count($keepIds) > 0) {
        $keepList = implode(',', $keepIds);
        $conn->query("DELETE FROM score WHERE student_id='$sid' AND id NOT IN ($keepList)");
        $affected = $conn->affected_rows;
        $success = "已清理 {$affected} 条重复成绩记录（学号：{$sid}）";
    } else {
        $error = "该学生没有成绩记录";
    }
}

// ===== 查询所有成绩记录（按学生+科目分组显示） =====
$sql = "SELECT sc.id, sc.student_id, st.name, st.class, 
               su.subject_name, sc.score
        FROM score sc
        JOIN student st ON sc.student_id = st.student_id
        JOIN `subject` su ON sc.subject_id = su.subject_id
        ORDER BY sc.student_id, sc.subject_id, sc.id DESC";
$result = $conn->query($sql);

// ===== 按学生分组 =====
$grouped = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $key = $row['student_id'];
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'name' => $row['name'],
                'class' => $row['class'],
                'scores' => []
            ];
        }
        $grouped[$key]['scores'][] = $row;
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-edit text-warning"></i> 成绩记录管理
    <small class="text-muted ml-2" style="font-size:0.7em;">查看历史 · 删除错误记录</small>
  </h4>

  <?php if ($success): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    ✅ <?php echo htmlspecialchars($success); ?>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    ❌ <?php echo htmlspecialchars($error); ?>
  </div>
  <?php endif; ?>

  <?php if (empty($grouped)): ?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-inbox" style="font-size:3rem;opacity:0.3;"></i>
    <p class="mt-3">暂无成绩记录</p>
  </div>
  <?php else: ?>

  <?php foreach ($grouped as $sid => $info): 
    // 统计每个科目的记录数量
    $subjectCounts = [];
    foreach ($info['scores'] as $s) {
        $subj = $s['subject_name'];
        if (!isset($subjectCounts[$subj])) $subjectCounts[$subj] = 0;
        $subjectCounts[$subj]++;
    }
    $hasDuplicates = max($subjectCounts) > 1;
  ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <strong><?php echo htmlspecialchars($info['name']); ?></strong>
        <span class="text-muted ml-2"><?php echo htmlspecialchars($sid); ?></span>
        <span class="badge badge-info ml-2"><?php echo htmlspecialchars($info['class']); ?></span>
      </div>
      <?php if ($hasDuplicates): ?>
      <form method="post" style="display:inline;">
        <input type="hidden" name="clean_duplicates" value="1">
        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($sid); ?>">
        <button type="submit" class="btn btn-warning btn-sm"
                onclick="return confirm('确认清理该学生的重复成绩？只保留最新一条！')">
          <i class="fas fa-broom"></i> 清理重复
        </button>
      </form>
      <?php endif; ?>
    </div>
    <div class="card-body p-0">
      <table class="table table-sm table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:40px;">#</th>
            <th>科目</th>
            <th>成绩</th>

            <th style="width:80px;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $idx = 1;
          foreach ($info['scores'] as $sc): 
            $isDuplicate = $subjectCounts[$sc['subject_name']] > 1;
          ?>
          <tr class="<?php echo $isDuplicate ? 'table-warning' : ''; ?>">
            <td><?php echo $idx++; ?></td>
            <td>
              <?php echo htmlspecialchars($sc['subject_name']); ?>
              <?php if ($isDuplicate): ?>
              <span class="badge badge-danger badge-pill">重复</span>
              <?php endif; ?>
            </td>
            <td>
              <strong><?php echo $sc['score']; ?></strong> 分
            </td>

            <td>
              <a href="?action=score_manage&del_score=<?php echo $sc['id']; ?>"
                 class="btn btn-outline-danger btn-sm"
                 onclick="return confirm('确认删除这条成绩记录？')">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>
</div>
