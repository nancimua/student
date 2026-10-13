<?php
// subject_edit_content.php — 修改科目（纯内容，无HTML布局）
// 课程设计知识点：数据库 UPDATE + SELECT

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo '<div class="alert alert-danger">参数错误：缺少科目ID</div>';
    exit;
}

// 查询当前科目
$stmt = $conn->prepare("SELECT subject_id, subject_name FROM subject WHERE subject_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$subject) {
    echo '<div class="alert alert-danger">科目不存在（ID=' . $id . '）</div>';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = '科目名称不能为空！';
    } elseif (mb_strlen($name, 'UTF-8') > 20) {
        $error = '科目名称不能超过20个字符！';
    } else {
        // 数据库操作：UPDATE（预处理）
        $stmt = $conn->prepare("UPDATE subject SET subject_name = ? WHERE subject_id = ?");
        $stmt->bind_param("si", $name, $id);

        if ($stmt->execute()) {
            $success = '修改成功！';
            $subject['subject_name'] = $name; // 刷新显示
        } else {
            if ($conn->errno === 1062) {
                $error = '该科目名称已存在！';
            } else {
                $error = '修改失败：' . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-edit text-warning"></i> 修改科目
  </h4>

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

  <form method="post" style="max-width:400px;">
    <div class="form-group">
      <label>科目ID</label>
      <input type="text" class="form-control" value="<?php echo $subject['subject_id']; ?>" readonly
             style="background:#f5f6fa;">
    </div>

    <div class="form-group">
      <label>科目名称 <span class="text-danger">*</span></label>
      <input type="text" name="name" class="form-control"
             value="<?php echo esc($subject['subject_name']); ?>"
             placeholder="如：高等数学" required maxlength="20">
    </div>

    <button type="submit" class="btn btn-warning">
      <i class="fas fa-save"></i> 保存修改
    </button>
    <a href="admin.php?action=subject_list" class="btn btn-outline-secondary ml-2">
      <i class="fas fa-arrow-left"></i> 返回列表
    </a>
  </form>
</div>
