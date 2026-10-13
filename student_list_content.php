<?php
// student_list_content.php — 学生列表（纯内容，无HTML布局）
// 课程设计知识点：SELECT 查询 + UPDATE 编辑（同步 student + score 两张表）

// ===== 处理编辑操作（POST 方式，Modal 提交）=====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id      = intval($_POST['edit_id']);
    $name    = trim($_POST['name']);
    $sid     = trim($_POST['sid']);
    $gender  = $_POST['gender'];
    $class   = trim($_POST['class']);
    $chinese = floatval($_POST['chinese'] ?? 0);
    $math    = floatval($_POST['math'] ?? 0);
    $english = floatval($_POST['english'] ?? 0);
    $total   = $chinese + $math + $english;
    $average = round($total / 3, 1);

    // PHP 基础知识：条件语句（计算等级）
    if ($average >= 90)      $level = '优秀';
    elseif ($average >= 75) $level = '良好';
    elseif ($average >= 60) $level = '及格';
    else                     $level = '待提高';

    // UPDATE：更新 student 表
    $stmt = $conn->prepare(
        "UPDATE student SET name=?, student_id=?, gender=?, class=?,
         chinese=?, math=?, english=?, total=?, average=?, level=? WHERE id=?"
    );
    $stmt->bind_param("ssssddddssi", $name, $sid, $gender, $class,
                       $chinese, $math, $english, $total, $average, $level, $id);
    if ($stmt->execute()) {
        // ===== 同步更新 score 表（课程设计：数据库 UPDATE）=====
        $stuRow = $conn->query("SELECT student_id FROM student WHERE id=$id")->fetch_assoc();
        $dbSid  = $conn->real_escape_string($stuRow['student_id']);
        $conn->query("REPLACE INTO score(student_id, subject_id, score) VALUES ('$dbSid',1,$chinese)");
        $conn->query("REPLACE INTO score(student_id, subject_id, score) VALUES ('$dbSid',2,$math)");
        $conn->query("REPLACE INTO score(student_id, subject_id, score) VALUES ('$dbSid',3,$english)");

        echo '<div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                ✅ 学生信息和成绩已更新！</div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                ❌ 更新失败：' . htmlspecialchars($conn->error) . '</div>';
    }
    $stmt->close();
}

// ===== 搜索过滤 =====
$search = trim($_GET['q'] ?? '');
$where  = $search
    ? "WHERE name LIKE '%" . $conn->real_escape_string($search) . "%'
        OR student_id LIKE '%" . $conn->real_escape_string($search) . "%'
        OR class LIKE '%" . $conn->real_escape_string($search) . "%'"
    : "";

// ===== 数据库操作：SELECT 查询列表 =====
$sql        = "SELECT * FROM student $where ORDER BY id DESC";
$res        = $conn->query($sql);
$totalCount = $res ? $res->num_rows : 0;

// ===== 先把数据读出来，后面再统一输出（避免 Modal 嵌套在 table 内）=====
$allRows = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $allRows[] = $row;
    }
}
?>

<div class="page-card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-users text-primary"></i> 学生列表</h4>
    <a href="admin.php?action=add_student" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> 添加学生
    </a>
  </div>

  <!-- 搜索框 -->
  <form method="get" class="mb-3">
    <input type="hidden" name="action" value="student_list">
    <div class="input-group">
      <input type="text" name="q" class="form-control" placeholder="搜索姓名/学号/班级..."
             value="<?php echo htmlspecialchars($search); ?>">
      <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
        <?php if ($search): ?>
        <a href="admin.php?action=student_list" class="btn btn-outline-danger">清除</a>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <?php if (count($allRows) > 0): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-sm">
      <thead class="thead-dark">
        <tr>
          <th>学号</th>
          <th>姓名</th>
          <th>性别</th>
          <th>班级</th>
          <th>语文</th>
          <th>数学</th>
          <th>英语</th>
          <th>总分</th>
          <th>平均分</th>
          <th>等级</th>
          <th style="width:100px;">操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($allRows as $row):
        $levelMap   = ['优秀'=>'success','良好'=>'primary','及格'=>'warning','待提高'=>'danger'];
        $levelClass = $levelMap[$row['level']] ?? 'secondary';
      ?>
        <tr>
          <td><?php echo htmlspecialchars($row['student_id']); ?></td>
          <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
          <td><?php echo htmlspecialchars($row['gender']); ?></td>
          <td><?php echo htmlspecialchars($row['class']); ?></td>
          <td><?php echo htmlspecialchars($row['chinese']); ?></td>
          <td><?php echo htmlspecialchars($row['math']); ?></td>
          <td><?php echo htmlspecialchars($row['english']); ?></td>
          <td><strong><?php echo htmlspecialchars($row['total']); ?></strong></td>
          <td><?php echo htmlspecialchars($row['average']); ?></td>
          <td>
            <span class="badge badge-<?php echo $levelClass; ?>">
              <?php echo htmlspecialchars($row['level']); ?>
            </span>
          </td>
          <td>
            <!-- 修改成绩（跳转页面） -->
            <a href="admin.php?action=edit_score&sid=<?php echo $row['id']; ?>"
               class="btn btn-warning btn-sm" title="修改成绩">
              <i class="fas fa-chart-line"></i>
            </a>
            <!-- 编辑基本信息（Modal — Bootstrap JS + jQuery 驱动） -->
            <button class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#editModal<?php echo $row['id']; ?>">
              <i class="fas fa-edit"></i>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted mt-2" style="font-size:0.85rem;">共 <?php echo $totalCount; ?> 条记录</p>
  <?php else: ?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-inbox" style="font-size:3rem;opacity:0.3;"></i>
    <p class="mt-3">暂无学生记录</p>
    <a href="admin.php?action=add_student" class="btn btn-primary">添加第一个学生</a>
  </div>
  <?php endif; ?>
</div>

<!-- ===== 所有编辑 Modal 统一放在 table 外面（Bootstrap 规范要求）===== -->
<?php foreach ($allRows as $row): ?>
<div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✏️ 编辑学生信息</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
          <div class="form-group">
            <label>姓名</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($row['name']); ?>" required>
          </div>
          <div class="form-group">
            <label>学号</label>
            <input type="text" name="sid" class="form-control"
                   value="<?php echo htmlspecialchars($row['student_id']); ?>" required>
          </div>
          <div class="form-row">
            <div class="form-group col-4">
              <label>性别</label>
              <select name="gender" class="form-control">
                <option value="男" <?= $row['gender']=='男'?'selected':'' ?>>男</option>
                <option value="女" <?= $row['gender']=='女'?'selected':'' ?>>女</option>
              </select>
            </div>
            <div class="form-group col-8">
              <label>班级</label>
              <input type="text" name="class" class="form-control"
                     value="<?php echo htmlspecialchars($row['class']); ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-4">
              <label>语文</label>
              <input type="number" step="0.1" name="chinese" class="form-control"
                     min="0" max="100" value="<?php echo htmlspecialchars($row['chinese']); ?>" required>
            </div>
            <div class="form-group col-4">
              <label>数学</label>
              <input type="number" step="0.1" name="math" class="form-control"
                     min="0" max="100" value="<?php echo htmlspecialchars($row['math']); ?>" required>
            </div>
            <div class="form-group col-4">
              <label>英语</label>
              <input type="number" step="0.1" name="english" class="form-control"
                     min="0" max="100" value="<?php echo htmlspecialchars($row['english']); ?>" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> 保存修改
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
