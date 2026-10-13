<?php
// subject_list_content.php — 科目列表（纯内容，无HTML布局）
// 课程设计知识点：数据库 SELECT 查询 + 表格展示

$msg    = $_GET['msg'] ?? '';
$error  = $_GET['error'] ?? '';

// 查询所有科目
$result = $conn->query("SELECT subject_id, subject_name FROM subject ORDER BY subject_id");
$total  = $result ? $result->num_rows : 0;
?>

<div class="page-card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-book text-primary"></i> 科目管理</h4>
    <a href="admin.php?action=subject_add" class="btn btn-success btn-sm">
      <i class="fas fa-plus"></i> 添加科目
    </a>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    ✅ <?php echo htmlspecialchars($msg); ?>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    ❌ <?php echo htmlspecialchars($error); ?>
  </div>
  <?php endif; ?>

  <?php if ($result && $result->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="thead-dark">
        <tr>
          <th style="width:80px;">科目ID</th>
          <th>科目名称</th>
          <th style="width:160px;">操作</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><strong><?php echo $row['subject_id']; ?></strong></td>
          <td><?php echo esc($row['subject_name']); ?></td>
          <td>
            <a href="admin.php?action=subject_edit&id=<?php echo $row['subject_id']; ?>"
               class="btn btn-warning btn-sm">
              <i class="fas fa-edit"></i> 修改
            </a>
            <a href="admin.php?action=subject_delete&id=<?php echo $row['subject_id']; ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('确定删除【<?php echo esc($row['subject_name']); ?>】？\n\n⚠️ 该科目下的所有成绩也会被删除！')">
              <i class="fas fa-trash"></i> 删除
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted mt-2" style="font-size:0.85rem;">共 <?php echo $total; ?> 个科目</p>

  <?php else: ?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-book" style="font-size:3rem;opacity:0.3;"></i>
    <p class="mt-3">暂无科目，请先添加</p>
    <a href="admin.php?action=subject_add" class="btn btn-success">添加第一个科目</a>
  </div>
  <?php endif; ?>
</div>
