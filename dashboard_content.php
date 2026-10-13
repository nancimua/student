<?php
// dashboard_content.php — 系统首页（纯内容，无HTML布局）
// 课程设计知识点：数据库聚合查询（COUNT, AVG, MAX）

// 统计学生总数
$studentCount = $conn->query("SELECT COUNT(*) as total FROM student")->fetch_assoc()['total'] ?? 0;

// 统计科目总数
$subjectCount = $conn->query("SELECT COUNT(*) as total FROM subject")->fetch_assoc()['total'] ?? 0;

// 统计成绩记录数
$scoreCount = $conn->query("SELECT COUNT(*) as total FROM score")->fetch_assoc()['total'] ?? 0;

// 平均分
$avgScore = $conn->query("SELECT AVG(score) as avg FROM score")->fetch_assoc()['avg'] ?? 0;

// 最高分学生
$topStudent = $conn->query(
    "SELECT s.name, s.class, AVG(sc.score) as avg_score
     FROM student s
     LEFT JOIN score sc ON s.student_id = sc.student_id
     GROUP BY s.student_id
     ORDER BY avg_score DESC
     LIMIT 1"
)->fetch_assoc();

// 最近添加的学生
$recentStudents = $conn->query(
    "SELECT name, student_id, class FROM student ORDER BY id DESC LIMIT 5"
);
?>

<div class="page-card">
  <h2 class="mb-4">
    <i class="fas fa-tachometer-alt text-primary"></i> 系统首页
  </h2>

  <!-- 统计卡片 -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-white bg-primary">
        <div class="card-body">
          <h6 class="card-title"><i class="fas fa-users"></i> 学生总数</h6>
          <h2 class="mb-0"><?php echo $studentCount; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success">
        <div class="card-body">
          <h6 class="card-title"><i class="fas fa-book"></i> 科目总数</h6>
          <h2 class="mb-0"><?php echo $subjectCount; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-warning">
        <div class="card-body">
          <h6 class="card-title"><i class="fas fa-chart-line"></i> 成绩记录</h6>
          <h2 class="mb-0"><?php echo $scoreCount; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-info">
        <div class="card-body">
          <h6 class="card-title"><i class="fas fa-star"></i> 平均分</h6>
          <h2 class="mb-0"><?php echo round($avgScore, 1); ?></h2>
        </div>
      </div>
    </div>
  </div>

  <!-- 最高分学生 -->
  <?php if ($topStudent): ?>
  <div class="alert alert-success">
    <h5 class="alert-heading"><i class="fas fa-trophy"></i> 最高分学生</h5>
    <p class="mb-0">
      <strong><?php echo esc($topStudent['name']); ?></strong>
      （<?php echo esc($topStudent['class']); ?>）
      — 平均分：<strong><?php echo round($topStudent['avg_score'], 1); ?></strong>
    </p>
  </div>
  <?php endif; ?>

  <!-- 最近添加的学生 -->
  <?php if ($recentStudents && $recentStudents->num_rows > 0): ?>
  <h5 class="mb-3"><i class="fas fa-clock"></i> 最近添加的学生</h5>
  <table class="table table-sm table-hover">
    <thead>
      <tr>
        <th>姓名</th>
        <th>学号</th>
        <th>班级</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $recentStudents->fetch_assoc()): ?>
      <tr>
        <td><?php echo esc($row['name']); ?></td>
        <td><?php echo esc($row['student_id']); ?></td>
        <td><?php echo esc($row['class']); ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- 快捷操作 -->
  <hr>
  <h5 class="mb-3">快捷操作</h5>
  <div class="row">
    <div class="col-md-4 mb-2">
      <a href="admin.php?action=add_student" class="btn btn-outline-primary btn-block">
        <i class="fas fa-user-plus"></i> 添加学生
      </a>
    </div>
    <div class="col-md-4 mb-2">
      <a href="admin.php?action=student_list" class="btn btn-outline-success btn-block">
        <i class="fas fa-list"></i> 查看列表
      </a>
    </div>
    <div class="col-md-4 mb-2">
      <a href="admin.php?action=score_chart" class="btn btn-outline-info btn-block">
        <i class="fas fa-chart-bar"></i> 成绩图表
      </a>
    </div>
  </div>
</div>
