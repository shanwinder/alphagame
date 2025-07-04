<?php
// --- ไฟล์: pages/system_settings.php (ไฟล์ใหม่) ---

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/auth.php';
requireAdmin(); // ตรวจสอบสิทธิ์เฉพาะแอดมินเท่านั้น

$message = '';
$message_type = '';

// --- ส่วนที่ 2: จัดการการบันทึกข้อมูลเมื่อครูกดปุ่ม "บันทึก" ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ใช้ transaction เพื่อให้แน่ใจว่าการอัปเดตทั้งหมดสำเร็จหรือล้มเหลวพร้อมกัน
    $conn->begin_transaction();
    try {
        // ค่าที่ได้รับจากฟอร์ม
        $status = $_POST['system_status'] ?? 'closed';
        $start_time = $_POST['homework_start_time'] ?? null;
        $end_time = $_POST['homework_end_time'] ?? null;

        // อัปเดตสถานะหลัก (open, closed, homework)
        $stmt_status = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'system_status'");
        $stmt_status->bind_param("s", $status);
        $stmt_status->execute();

        // อัปเดตเวลาเริ่มต้นโหมดการบ้าน
        $stmt_start = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'homework_start_time'");
        $stmt_start->bind_param("s", $start_time);
        $stmt_start->execute();

        // อัปเดตเวลาสิ้นสุดโหมดการบ้าน
        $stmt_end = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'homework_end_time'");
        $stmt_end->bind_param("s", $end_time);
        $stmt_end->execute();

        $conn->commit(); // ยืนยันการเปลี่ยนแปลงทั้งหมด
        $message = '✅ บันทึกการตั้งค่าเรียบร้อยแล้ว!';
        $message_type = 'success';

    } catch (Exception $e) {
        $conn->rollback(); // ยกเลิกการเปลี่ยนแปลงทั้งหมดหากมีข้อผิดพลาด
        $message = '❌ เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// --- ส่วนที่ 1: ดึงข้อมูลการตั้งค่าปัจจุบันมาแสดงผล ---
$settings_result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$current_status = $settings['system_status'] ?? 'closed';
$current_start_time = $settings['homework_start_time'] ?? '';
$current_end_time = $settings['homework_end_time'] ?? '';

?>

<div class="container py-4">
    <h1 class="mb-4"><i class="fas fa-cogs"></i> ตั้งค่าระบบเกม</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> กลับไปยังแดชบอร์ด</a>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            สถานะการเข้าเล่นของนักเรียน
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="system_status" id="status_open" value="open" 
                           <?= ($current_status === 'open') ? 'checked' : '' ?> onchange="toggleHomeworkOptions()">
                    <label class="form-check-label" for="status_open">
                        <h4>🟢 เปิดระบบ (Open)</h4>
                        <p class="text-muted">นักเรียนสามารถเข้าเล่นเกมได้ตลอดเวลา</p>
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="system_status" id="status_closed" value="closed" 
                           <?= ($current_status === 'closed') ? 'checked' : '' ?> onchange="toggleHomeworkOptions()">
                    <label class="form-check-label" for="status_closed">
                        <h4>🔴 ปิดระบบ (Closed)</h4>
                        <p class="text-muted">นักเรียนทุกคนจะไม่สามารถเข้าเล่นเกมได้</p>
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="system_status" id="status_homework" value="homework" 
                           <?= ($current_status === 'homework') ? 'checked' : '' ?> onchange="toggleHomeworkOptions()">
                    <label class="form-check-label" for="status_homework">
                        <h4>🟡 โหมดการบ้าน (Homework)</h4>
                        <p class="text-muted">กำหนดช่วงเวลาที่นักเรียนสามารถเข้ามาเล่นเกมได้</p>
                    </label>
                </div>
                
                <div id="homework_options" class="mt-3 p-3 border rounded" style="display: <?= ($current_status === 'homework') ? 'block' : 'none' ?>;">
                    <div class="mb-3">
                        <label for="homework_start_time" class="form-label">เวลาเริ่มต้น:</label>
                        <input type="datetime-local" class="form-control" name="homework_start_time" id="homework_start_time" value="<?= htmlspecialchars($current_start_time) ?>">
                    </div>
                    <div>
                        <label for="homework_end_time" class="form-label">เวลาสิ้นสุด:</label>
                        <input type="datetime-local" class="form-control" name="homework_end_time" id="homework_end_time" value="<?= htmlspecialchars($current_end_time) ?>">
                    </div>
                </div>

                <hr class="my-4">
                <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-save"></i> บันทึกการตั้งค่า</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleHomeworkOptions() {
        // ฟังก์ชันสำหรับซ่อนหรือแสดงกล่องตั้งค่าเวลา
        const homeworkRadio = document.getElementById('status_homework');
        const homeworkOptionsDiv = document.getElementById('homework_options');
        if (homeworkRadio.checked) {
            homeworkOptionsDiv.style.display = 'block';
        } else {
            homeworkOptionsDiv.style.display = 'none';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>