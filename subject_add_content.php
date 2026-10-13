<?php
// subject_add_content.php — 添加科目（纯内容，无HTML布局）
// 课程设计知识点：数据库 INSERT + 表单处理

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    // PHP 基础知识：空值检查
    if (empty($name)) {
        $error = '科目名称不能为空！';
    } elseif (mb_strlen($name, 'UTF-8') > 20) {
        $error = '科目名称不能超过20个字符！';
    } else {
        // 数据库操作：INSERT（预处理防注入）
        $stmt = $conn->prepare("INSERT INTO subject (subject_name) VALUES (?)");
        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            $success = '科目添加成功！';
            $name = ''; // 清空表单
        } else {
            // 检查是否是唯一键冲突（科目名称重复）
            if ($conn->errno === 1062) {
                $error = '该科目名称已存在！';
            } else {
                $error = '添加失败：' . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-plus-circle text-success"></i> 添加科目
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
      <label>科目名称 <span class="text-danger">*</span></label>
      <input type="text" name="name" class="form-control"
             value="<?php echo esc($name ?? ''); ?>"
             placeholder="如：高等数学" required maxlength="20">
      <small class="text-muted">最多20个字符</small>
    </div>

    <button type="submit" class="btn btn-success">
      <i class="fas fa-save"></i> 保存
    </button>
    <a href="admin.php?action=subject_list" class="btn btn-outline-secondary ml-2">
      <i class="fas fa-arrow-left"></i> 返回列表
    </a>
  </form>
</div>
