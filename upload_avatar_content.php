<?php
// upload_avatar_content.php — 上传头像（纯内容，无HTML布局）
// 课程设计知识点：文件上传（$_FILES, move_uploaded_file）+ 文件类型验证

$error   = '';
$success = '';
$preview = '';

// 查询所有学生（用于选择）
$students = $conn->query("SELECT student_id, name FROM student ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $stuId  = trim($_POST['student_id'] ?? '');
    $file   = $_FILES['avatar'];

    // 1. 检查学号
    if (empty($stuId)) {
        $error = '请选择学生！';
    }
    // 2. 检查上传错误
    elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = '上传失败（错误码：' . $file['error'] . '）';
    }
    // 3. 检查文件大小（最大2MB）
    elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = '文件太大！最大允许2MB';
    }
    else {
        // 4. 检查文件类型（MIME类型验证）
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            $error = '只允许上传 JPG / PNG / GIF 图片！';
        } else {
            // 5. 生成新文件名
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'avatar_' . $stuId . '_' . time() . '.' . $ext;
            $uploadDir = 'uploads/avatars/';
            $uploadPath = $uploadDir . $newName;

            // 6. 创建目录
            if (!is_dir(__DIR__ . '/' . $uploadDir)) {
                mkdir(__DIR__ . '/' . $uploadDir, 0777, true);
            }

            // 7. 移动文件
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $uploadPath)) {
                // 8. 更新数据库
                $stmt = $conn->prepare("UPDATE student SET avatar = ? WHERE student_id = ?");
                $stmt->bind_param("ss", $uploadPath, $stuId);

                if ($stmt->execute()) {
                    $success = '头像上传成功！';
                    $preview = $uploadPath;
                } else {
                    $error = '数据库更新失败：' . $conn->error;
                }
                $stmt->close();
            } else {
                $error = '文件移动失败，请检查目录权限！';
            }
        }
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-image text-warning"></i> 上传头像
  </h4>

  <?php if ($success): ?>
  <div class="alert alert-success">
    ✅ <?php echo $success; ?>
  </div>
  <?php if ($preview): ?>
  <div class="text-center mb-3">
    <img src="<?php echo $preview; ?>" style="max-width:200px;border-radius:50%;">
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger">
    ❌ <?php echo $error; ?>
  </div>
  <?php endif; ?>

  <div class="alert alert-info">
    <h5 class="alert-heading"><i class="fas fa-info-circle"></i> 上传说明</h5>
    <ul class="mb-0">
      <li>支持格式：JPG / PNG / GIF</li>
      <li>文件大小：最大 2MB</li>
      <li>建议尺寸：200x200 像素</li>
    </ul>
  </div>

  <form method="post" enctype="multipart/form-data" style="max-width:500px;">
    <div class="form-group">
      <label>选择学生</label>
      <select name="student_id" class="form-control" required>
        <option value="">-- 请选择学生 --</option>
        <?php if ($students): while ($row = $students->fetch_assoc()): ?>
        <option value="<?php echo $row['student_id']; ?>">
          <?php echo esc($row['name']); ?>（<?php echo $row['student_id']; ?>）
        </option>
        <?php endwhile; endif; ?>
      </select>
    </div>

    <div class="form-group">
      <label>选择头像</label>
      <input type="file" name="avatar" class="form-control-file"
             accept="image/jpeg,image/png,image/gif" required>
    </div>

    <button type="submit" class="btn btn-warning btn-block">
      <i class="fas fa-upload"></i> 上传头像
    </button>
  </form>
</div>
