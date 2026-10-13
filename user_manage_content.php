<?php
// user_manage_content.php — 学生账号管理（添加、修改密码、删除、批量生成密码）
// 课程设计知识点：password_hash 密码哈希 + INSERT + UPDATE + DELETE + 自动 ALTER TABLE

$error   = '';
$success = '';

// ---------- 自动检测并添加 password 字段（如果不存在） ----------
$checkCol = $conn->query("SHOW COLUMNS FROM student LIKE 'password'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE student ADD COLUMN password VARCHAR(255) DEFAULT NULL COMMENT '登录密码（哈希）'");
}

// ---------- 1. 处理添加学生账号（POST） ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id']);
    $name       = trim($_POST['name']);
    $gender     = $_POST['gender'] ?? '男';
    $class      = trim($_POST['class']);
    $password   = $_POST['password'] ?? '';

    if (empty($student_id) || empty($name) || empty($class)) {
        $error = '学号、姓名、班级均为必填项！';
    } elseif (!preg_match("/^2[0-9]{10}$/", $student_id)) {
        $error = '学号格式错误！必须是 2 开头 + 10 位数字，共 11 位（如：24103120101）';
    } elseif (!preg_match("/^[\x{4e00}-\x{9fa5}]{2,6}$/u", $name)) {
        $error = '姓名必须为 2-6 位中文！';
    } elseif (strlen($password) < 4) {
        $error = '密码至少 4 位！';
    } else {
        // 检查学号是否已存在
        $check = $conn->prepare("SELECT student_id FROM student WHERE student_id = ?");
        $check->bind_param("s", $student_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = '该学号已存在！';
        } else {
            // INSERT：密码用 password_hash 加密存储
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO student (student_id, name, gender, class, password, chinese, math, english, total, average, level)
                 VALUES (?, ?, ?, ?, ?, 0, 0, 0, 0, 0, '待提高')"
            );
            $stmt->bind_param("sssss", $student_id, $name, $gender, $class, $hashed);
            if ($stmt->execute()) {
                $success = "学生账号添加成功！学号：{$student_id}，姓名：{$name}，默认密码：{$password}";
            } else {
                $error = '添加失败：' . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// ---------- 2. 批量生成密码（默认密码为学号） ----------
if (isset($_GET['batch_gen_pwd'])) {
    $students = $conn->query("SELECT student_id FROM student WHERE password IS NULL OR password = ''");
    $count = 0;
    while ($row = $students->fetch_assoc()) {
        $defaultPwd = $row['student_id']; // 密码默认 = 学号本身
        $hashed = password_hash($defaultPwd, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE student SET password = ? WHERE student_id = ?");
        $stmt->bind_param("ss", $hashed, $row['student_id']);
        if ($stmt->execute()) $count++;
        $stmt->close();
    }
    $success = "批量生成成功！共为 {$count} 名学生设置了密码（默认密码 = 学号）。";
}

// ---------- 3. 处理修改密码 ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pwd'])) {
    $student_id = trim($_POST['student_id']);
    $new_pwd    = $_POST['new_password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if (empty($student_id)) {
        $error = '学生ID不能为空';
    } elseif (strlen($new_pwd) < 4) {
        $error = '密码长度至少4位';
    } elseif ($new_pwd !== $confirm) {
        $error = '两次密码输入不一致';
    } else {
        $hashed = password_hash($new_pwd, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE student SET password = ? WHERE student_id = ?");
        $stmt->bind_param("ss", $hashed, $student_id);
        if ($stmt->execute()) {
            $success = "密码更新成功（学号：{$student_id}）";
        } else {
            $error = "更新失败：" . $stmt->error;
        }
        $stmt->close();
    }
}

// ---------- 4. 处理删除学生（POST 方式，防止误触发） ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $student_id = trim($_POST['delete_student']);
    if (!empty($student_id)) {
        if ($conn->begin_transaction()) {
            try {
                $stmt1 = $conn->prepare("DELETE FROM score WHERE student_id = ?");
                $stmt1->bind_param("s", $student_id);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $conn->prepare("DELETE FROM student WHERE student_id = ?");
                $stmt2->bind_param("s", $student_id);
                $stmt2->execute();
                $stmt2->close();

                $conn->commit();
                $success = "学生（学号：{$student_id}）已删除！";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "删除失败：" . $e->getMessage();
            }
        } else {
            // MyISAM 降级
            $stmt1 = $conn->prepare("DELETE FROM score WHERE student_id = ?");
            $stmt1->bind_param("s", $student_id);
            $stmt1->execute();
            $stmt1->close();
            $stmt2 = $conn->prepare("DELETE FROM student WHERE student_id = ?");
            $stmt2->bind_param("s", $student_id);
            $stmt2->execute();
            $stmt2->close();
            $success = "学生（学号：{$student_id}）已删除！";
        }
    }
}

// ---------- 5. 查询所有学生 ----------
$result = $conn->query("SELECT student_id, name, class, password FROM student ORDER BY student_id");
?>
<div class="page-card">
  <h4 class="mb-4">
    <i class="fas fa-user-cog text-primary"></i> 学生账号管理
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

  <!-- 工具栏 -->
  <div class="mb-3 d-flex flex-wrap gap-2">
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addStudentModal">
      <i class="fas fa-user-plus"></i> 添加学生账号
    </button>
    <a href="?action=user_manage&batch_gen_pwd=1" class="btn btn-info btn-sm"
       onclick="return confirm('将为所有尚未设置密码的学生设置密码（密码为学号），确认执行？')">
      <i class="fas fa-sync-alt"></i> 批量生成密码（默认学号）
    </a>
  </div>

  <?php if ($result && $result->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="thead-dark">
        <tr>
          <th>学号</th>
          <th>姓名</th>
          <th>班级</th>
          <th>密码状态</th>
          <th style="width:220px;">操作</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($row['student_id']); ?></strong></td>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['class']); ?></td>
          <td>
            <?php if (!empty($row['password'])): ?>
            <span class="badge badge-success">已设置</span>
            <?php else: ?>
            <span class="badge badge-secondary">未设置</span>
            <?php endif; ?>
          </td>
          <td>
            <button class="btn btn-warning btn-sm" data-toggle="modal"
                    data-target="#pwdModal<?php echo $row['student_id']; ?>">
              <i class="fas fa-key"></i> 修改密码
            </button>
            <form method="post" style="display:inline;"
                  onsubmit="return confirm('确认删除学生【<?php echo htmlspecialchars($row['name']); ?>】？\n⚠️ 该生的所有成绩也会被删除！')">
              <input type="hidden" name="delete_student" value="<?php echo htmlspecialchars($row['student_id']); ?>">
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i> 删除
              </button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-users" style="font-size:3rem;opacity:0.3;"></i>
    <p class="mt-3">暂无学生数据，请先添加学生账号。</p>
  </div>
  <?php endif; ?>
</div>

<!-- 添加学生账号 Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">➕ 添加学生账号</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="add_student" value="1">
          <div class="form-group">
            <label>学号 <span class="text-danger">*</span></label>
            <input type="text" name="student_id" class="form-control" required
                   pattern="2[0-9]{10}" placeholder="2开头 + 10位数字，共11位">
          </div>
          <div class="form-group">
            <label>姓名 <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="2-6位中文姓名">
          </div>
          <div class="form-group">
            <label>性别</label>
            <select name="gender" class="form-control">
              <option value="男">男</option>
              <option value="女">女</option>
            </select>
          </div>
          <div class="form-group">
            <label>班级 <span class="text-danger">*</span></label>
            <input type="text" name="class" class="form-control" required
                   placeholder="如：324计算机应用技术1班">
          </div>
          <div class="form-group">
            <label>密码 <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required
                   minlength="4" placeholder="至少4位">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 确认添加
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 修改密码 Modal（每个学生一个） -->
<?php
$result2 = $conn->query("SELECT student_id, name FROM student ORDER BY student_id");
if ($result2) {
    while ($row = $result2->fetch_assoc()) {
?>
<div class="modal fade" id="pwdModal<?php echo $row['student_id']; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🔑 修改密码 - <?php echo htmlspecialchars($row['name']); ?></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
          <div class="form-group">
            <label>新密码（至少4位）</label>
            <input type="password" name="new_password" class="form-control" required minlength="4">
          </div>
          <div class="form-group">
            <label>确认密码</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="4">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
          <button type="submit" name="change_pwd" class="btn btn-warning">
            <i class="fas fa-save"></i> 更新密码
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
    }
}
?>