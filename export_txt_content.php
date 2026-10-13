<?php
// export_txt_content.php — 导出成绩TXT（纯内容，无HTML布局）
// 课程设计知识点：文件操作（fopen, fwrite, fclose）

$error   = '';
$success = '';
$fileUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    // 查询所有学生成绩
    $result = $conn->query(
        "SELECT s.student_id, s.name, s.class, s.gender,
                AVG(sc.score) as avg_score,
                SUM(sc.score) as total_score
         FROM student s
         LEFT JOIN score sc ON s.student_id = sc.student_id
         GROUP BY s.student_id
         ORDER BY avg_score DESC"
    );

    if ($result && $result->num_rows > 0) {
        // 生成文件名（带时间戳）
        $filename = 'student_scores_' . date('Ymd_His') . '.txt';
        $filepath = __DIR__ . '/' . $filename;

        // 打开文件（w模式：写入，不存在则创建）
        $fp = fopen($filepath, 'w');

        if ($fp) {
            // 写入 BOM（UTF-8 with BOM，解决中文乱码）
            fwrite($fp, "\xEF\xBB\xBF");

            // 写入标题
            fwrite($fp, "========================================\r\n");
            fwrite($fp, "      学生成绩导出 - " . date('Y-m-d H:i:s') . "\r\n");
            fwrite($fp, "========================================\r\n\r\n");

            // 写入表头
            fwrite($fp, str_pad("学号", 12) . str_pad("姓名", 10) . str_pad("班级", 20) . str_pad("总分", 8) . str_pad("平均分", 8) . "\r\n");
            fwrite($fp, str_repeat("-", 60) . "\r\n");

            // 写入数据
            while ($row = $result->fetch_assoc()) {
                $line = str_pad($row['student_id'], 12) .
                        str_pad($row['name'], 10, ' ', STR_PAD_RIGHT) .
                        str_pad(mb_substr($row['class'], 0, 10, 'UTF-8'), 20, ' ', STR_PAD_RIGHT) .
                        str_pad($row['total_score'] ?? 0, 8) .
                        str_pad(round($row['avg_score'] ?? 0, 1), 8) . "\r\n";
                fwrite($fp, $line);
            }

            fwrite($fp, str_repeat("=", 60) . "\r\n");
            fwrite($fp, "共导出 " . $result->num_rows . " 条记录\r\n");

            // 关闭文件
            fclose($fp);

            $success = '导出成功！';
            $fileUrl = $filename;
        } else {
            $error = '无法创建文件，请检查目录权限！';
        }
    } else {
        $error = '没有学生数据可导出！';
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-file-export text-info"></i> 导出成绩TXT
  </h4>

  <?php if ($success): ?>
  <div class="alert alert-success">
    ✅ <?php echo $success; ?>
  </div>
  <p>文件已生成，点击下方链接下载：</p>
  <a href="<?php echo $fileUrl; ?>" class="btn btn-primary btn-lg" download>
    <i class="fas fa-download"></i> 下载 <?php echo $fileUrl; ?>
  </a>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger">
    ❌ <?php echo $error; ?>
  </div>
  <?php endif; ?>

  <div class="alert alert-info">
    <h5 class="alert-heading"><i class="fas fa-info-circle"></i> 功能说明</h5>
    <ul class="mb-0">
      <li>导出所有学生的成绩信息到 TXT 文件</li>
      <li>包含：学号、姓名、班级、总分、平均分</li>
      <li>按平均分从高到低排序</li>
      <li>文件格式：UTF-8（支持中文）</li>
    </ul>
  </div>

  <form method="post">
    <button type="submit" name="export" class="btn btn-info btn-lg">
      <i class="fas fa-file-download"></i> 导出成绩
    </button>
  </form>
</div>
