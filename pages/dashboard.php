<?php
// --- ไฟล์: pages/dashboard.php (ฉบับปรับปรุง) ---

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/auth.php';
requireAdmin();

// ✅ ปรับปรุง SQL Query ให้ดึงข้อมูลความคืบหน้าของนักเรียนมาด้วย
// โดยการ LEFT JOIN กับตาราง progress และนับจำนวนด่านที่ผ่าน (stars_awarded > 0)
$sql = "SELECT
            u.id,
            u.student_id,
            u.name,
            u.class_level,
            u.created_at,
            COUNT(p.id) AS completed_stages
        FROM
            users u
        LEFT JOIN
            progress p ON u.id = p.user_id AND p.stars_awarded > 0
        WHERE
            u.role = 'student'
        GROUP BY
            u.id
        ORDER BY
            u.class_level, u.name";

$result = $conn->query($sql);
$total_stages = 50; // จำนวนด่านทั้งหมดของเกม
?>

<div class="container py-4">

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            ✅ ลบผู้ใช้ <strong><?= htmlspecialchars(urldecode($_GET['deleted'])) ?></strong> เรียบร้อยแล้ว
        </div>
    <?php elseif (isset($_GET['deleted_count'])): ?>
        <div class="alert alert-success">
            ✅ ลบผู้ใช้จำนวน <strong><?= (int)$_GET['deleted_count'] ?></strong> คนเรียบร้อยแล้ว
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            ❌ เกิดข้อผิดพลาด: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> แดชบอร์ดผู้ดูแลระบบ</h1>

    <div class="d-flex gap-2 mb-3">
        <a href="add_user.php" class="btn btn-success"><i class="fas fa-user-plus"></i> เพิ่มนักเรียน</a>
        <a href="import_students.php" class="btn btn-primary"><i class="fas fa-file-csv"></i> นำเข้านักเรียน</a>
    </div>

    <form method="post" action="delete_multiple_users.php" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้ที่เลือก?');">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light text-center">
          <tr>
            <th><input type="checkbox" id="select_all"></th>
            <th>ลำดับ</th>
            <th>ชื่อ - สกุล</th>
            <th>ชั้นเรียน</th>
            <th style="width: 35%;">ความคืบหน้า (<?= $total_stages ?> ด่าน)</th> <th>การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && $result->num_rows > 0) {
            $index = 1;
            while ($row = $result->fetch_assoc()) {
                // ✅ คำนวณเปอร์เซ็นต์ความคืบหน้า
                $completed_stages = (int)$row['completed_stages'];
                $percentage = ($completed_stages / $total_stages) * 100;
          ?>
              <tr>
                <td class="text-center"><input type='checkbox' name='user_ids[]' value='<?= $row['id'] ?>'></td>
                <td class="text-center"><?= $index++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td class="text-center"><?= htmlspecialchars($row['class_level']) ?></td>
                <td>
                  <div class="progress" role="progressbar" aria-label="Student progress" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="height: 25px; font-size: 1rem;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: <?= $percentage ?>%"><?= round($percentage) ?>%</div>
                  </div>
                  <div class="text-center small text-muted mt-1"><?= $completed_stages ?>/<?= $total_stages ?> ด่าน</div>
                </td>
                <td class="text-center">
                  <a href='edit_user.php?id=<?= $row['id'] ?>' class='btn btn-warning btn-sm' title="แก้ไข"><i class="fas fa-edit"></i></a>
                  <a href='delete_user.php?id=<?= $row['id'] ?>' class='btn btn-danger btn-sm' title="ลบ" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้คนนี้?');"><i class="fas fa-trash"></i></a>
                </td>
              </tr>
          <?php
            }
          } else {
            echo "<tr><td colspan='6' class='text-center'>ยังไม่มีข้อมูลนักเรียนในระบบ</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
    <button type="submit" class="btn btn-danger mt-2"><i class="fas fa-trash-alt"></i> ลบผู้ใช้ที่เลือก</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
  document.getElementById('select_all').addEventListener('change', function() {
    const isChecked = this.checked;
    document.querySelectorAll('input[name="user_ids[]"]').forEach(checkbox => {
      checkbox.checked = isChecked;
    });
  });
</script>

<?php include '../includes/footer.php'; ?>