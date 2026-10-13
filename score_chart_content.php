<?php
// score_chart_content.php — 成绩统计图（纯内容，无HTML布局）
// 课程设计知识点：Chart.js 前端图表 + PHP 数据输出

$stuId = $_GET['id'] ?? '';
$stuList = $conn->query("SELECT student_id, name FROM student ORDER BY student_id");

// 如果选择了学生，查询成绩
$student   = null;
$subjects  = [];
$scores    = [];
$totalScore = 0;
$avgScore   = 0;
$gradeText  = '';
$commentText = '';

if ($stuId !== '') {
    $stmt = $conn->prepare("SELECT name FROM student WHERE student_id = ?");
    $stmt->bind_param("s", $stuId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($r) {
        $student = $r;

        $sql = "SELECT s.subject_name, sc.score
                FROM score sc
                JOIN `subject` s ON sc.subject_id = s.subject_id
                WHERE sc.student_id = ?
                ORDER BY s.subject_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $stuId);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $subjects[] = $row['subject_name'];
            $scores[]   = (float)$row['score'];
        }
        $stmt->close();

        $count = count($scores);
        if ($count > 0) {
            $totalScore = array_sum($scores);
            $avgScore   = $totalScore / $count;

            // 等级评定
            if ($avgScore >= 90) $gradeText = 'A (卓越)';
            elseif ($avgScore >= 80) $gradeText = 'B (优良)';
            elseif ($avgScore >= 70) $gradeText = 'C (中等)';
            elseif ($avgScore >= 60) $gradeText = 'D (及格)';
            else $gradeText = 'F (待提升)';

            // 智能评语
            $strong = []; $weak = [];
            foreach ($scores as $i => $s) {
                if ($s >= 85) $strong[] = $subjects[$i];
                if ($s < 70)  $weak[]   = $subjects[$i];
            }
            if ($avgScore >= 90) {
                $commentText = "综合表现卓著！";
                if ($strong) $commentText .= "尤其" . implode('、', $strong) . "非常亮眼。";
            } elseif ($avgScore >= 80) {
                $commentText = "成绩优良，基础扎实。";
                if ($strong) $commentText .= implode('、', $strong) . "是优势科目。";
                if ($weak)   $commentText .= "建议加强" . implode('、', $weak) . "。";
            } elseif ($avgScore >= 70) {
                $commentText = "整体中等偏上，有提升空间。";
                if ($weak) $commentText .= "薄弱科目：" . implode('、', $weak) . "。";
            } elseif ($avgScore >= 60) {
                $commentText = "成绩处于及格线，潜力待开发。";
                if ($weak) $commentText .= implode('、', $weak) . "需重点加强。";
            } else {
                $commentText = "目前成绩落后，请从基础抓起。";
                if ($weak) $commentText .= "重点关注：" . implode('、', $weak) . "。";
            }
        }
    }
}
?>

<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-chart-bar text-info"></i> 成绩统计图与评分系统
  </h4>

  <!-- 选择学生 -->
  <form method="get" class="mb-4" style="max-width:400px;">
    <input type="hidden" name="action" value="score_chart">
    <div class="form-group">
      <label>请选择学生</label>
      <select name="id" class="form-control" required onchange="this.form.submit()">
        <option value="">-- 请选择 --</option>
        <?php if ($stuList): while ($row = $stuList->fetch_assoc()): ?>
        <option value="<?php echo htmlspecialchars($row['student_id']); ?>"
                <?php echo $stuId === $row['student_id'] ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($row['name']); ?>
          (<?php echo htmlspecialchars($row['student_id']); ?>)
        </option>
        <?php endwhile; endif; ?>
      </select>
    </div>
  </form>

  <?php if ($stuId && !$student): ?>
  <div class="alert alert-danger">学生不存在（学号：<?php echo htmlspecialchars($stuId); ?>）</div>

  <?php elseif ($stuId && $student && count($scores) === 0): ?>
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    学生 <strong><?php echo htmlspecialchars($student['name']); ?></strong> 暂无成绩数据。
    <br>请先在「学生列表」中为学生录入成绩。
  </div>

  <?php elseif ($stuId && $student && count($scores) > 0): ?>
  <div class="row">
    <!-- 左侧：图表 -->
    <div class="col-md-8">
      <div class="card p-3 mb-3">
        <h5 class="text-center mb-3">
          <strong><?php echo htmlspecialchars($student['name']); ?></strong> 的各科成绩
        </h5>
        <canvas id="scoreChart" height="300"></canvas>
      </div>
    </div>

    <!-- 右侧：评分面板 -->
    <div class="col-md-4">
      <div class="card p-3 mb-3">
        <h5 class="mb-3"><i class="fas fa-graduation-cap"></i> 成绩评分卡</h5>
        <div class="mb-2">
          <span class="text-muted">📚 总分</span>
          <h3 class="mb-0"><strong><?php echo number_format($totalScore, 0); ?></strong></h3>
        </div>
        <div class="mb-2">
          <span class="text-muted">⚖️ 平均分</span>
          <h3 class="mb-0"><strong><?php echo number_format($avgScore, 1); ?></strong><small class="text-muted"> /100</small></h3>
        </div>
        <div class="mb-2">
          <span class="text-muted">🏅 学业等级</span>
          <span class="badge badge-warning" style="font-size:1rem;padding:8px 16px;">
            <?php echo htmlspecialchars($gradeText); ?>
          </span>
        </div>
        <div class="alert alert-light mt-3" style="border-left:3px solid #ff8c42;">
          💬 <?php echo htmlspecialchars($commentText); ?>
        </div>
      </div>

      <!-- 各科详情 -->
      <div class="card p-3">
        <h6 class="mb-3">📖 各科分数详情</h6>
        <?php foreach ($subjects as $idx => $sub):
          $val = $scores[$idx];
          $tag = $val >= 90 ? '优秀' : ($val >= 80 ? '良好' : ($val >= 70 ? '中等' : ($val >= 60 ? '及格' : '待提升')));
          $tagClass = $val >= 90 ? 'success' : ($val >= 80 ? 'primary' : ($val >= 70 ? 'info' : ($val >= 60 ? 'warning' : 'danger')));
        ?>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span><?php echo htmlspecialchars($sub); ?></span>
          <span>
            <strong><?php echo $val; ?></strong>
            <span class="badge badge-<?php echo $tagClass; ?>" style="font-size:0.7rem;"><?php echo $tag; ?></span>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Chart.js 渲染 -->
  <script src="https://cdn.bootcdn.net/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script>
    var ctx = document.getElementById('scoreChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_values($subjects)); ?>,
        datasets: [{
          label: '成绩',
          data: <?php echo json_encode(array_values($scores)); ?>,
          backgroundColor: [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
          ],
          borderWidth: 2,
          borderRadius: 8,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, max: 100, title: { display: true, text: '分数' } },
          x: { title: { display: true, text: '科目' } }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  </script>

  <?php else: ?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-chart-bar" style="font-size:3rem;opacity:0.3;"></i>
    <p class="mt-3">请从上方选择一个学生查看成绩图表</p>
  </div>
  <?php endif; ?>

  <div class="text-center mt-3">
    <small class="text-muted">
      💡 评分规则：A≥90 | B≥80 | C≥70 | D≥60 | F<60
    </small>
  </div>
</div>
