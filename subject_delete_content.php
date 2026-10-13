<?php
// subject_delete_content.php — 删除科目（纯内容，无HTML布局）
// 课程设计知识点：数据库 DELETE + 事务

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="alert alert-danger">参数错误：缺少科目ID</div>';
    exit;
}

// 查询科目名称（用于提示）
$stmt = $conn->prepare("SELECT subject_name FROM subject WHERE subject_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$subject) {
    echo '<div class="alert alert-danger">科目不存在，可能已被删除</div>';
    echo '<a href="admin.php?action=subject_list" class="btn btn-primary mt-3">返回科目列表</a>';
    exit;
}

$error   = '';
$success = '';
$deleted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // 开启事务：先删成绩，再删科目
    $conn->begin_transaction();

    try {
        // 删除该科目下的所有成绩
        $stmt1 = $conn->prepare("DELETE FROM score WHERE subject_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $deletedScores = $stmt1->affected_rows;
        $stmt1->close();

        // 删除科目
        $stmt2 = $conn->prepare("DELETE FROM subject WHERE subject_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        $success = "科目【{$subject['subject_name']}】已删除！同时删除了 {$deletedScores} 条成绩记录。";
        $deleted = true;

    } catch (Exception $e) {
        $conn->rollback();
        $error = '删除失败：' . $e->getMessage();
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-trash text-danger"></i> 删除科目
  </h4>

  <?php if ($deleted): ?>
    <?php if ($success): ?>
    <div class="alert alert-success">
      ✅ <?php echo $success; ?>
    </div>
    <?php endif; ?>
    <a href="admin.php?action=subject_list" class="btn btn-primary">
      <i class="fas fa-arrow-left"></i> 返回科目列表
    </a>

  <?php else: ?>
    <?php if ($error): ?>
    <div class="alert alert-danger">
      ❌ <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="alert alert-warning">
      <h5 class="alert-heading">⚠️ 确认删除</h5>
      <p>您即将删除科目：<strong><?php echo esc($subject['subject_name']); ?></strong></p>
      <hr>
      <p class="mb-0">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>警告：</strong>该科目下的所有成绩记录也会被删除，此操作不可恢复！
      </p>
    </div>

    <form method="post" onsubmit="return confirm('最后确认：真的要删除吗？');">
      <input type="hidden" name="confirm_delete" value="1">
      <button type="submit" class="btn btn-danger">
        <i class="fas fa-trash"></i> 确认删除
      </button>
      <a href="admin.php?action=subject_list" class="btn btn-outline-secondary ml-2">
        取消
      </a>
    </form>
  <?php endif; ?>
</div>
