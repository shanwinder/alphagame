<?php
// --- ไฟล์: pages/play.php (ฉบับปรับปรุง) ---
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

// require_once '../includes/access_control.php'; // จะเปิดใช้งานในอนาคต

$stage_id = isset($_GET['stage_id']) ? (int)$_GET['stage_id'] : 1;

// --- ส่วนดึงข้อมูลด่าน (เหมือนเดิม) ---
// ...

// ✅ [เพิ่ม] ตรวจสอบว่าเป็น Live Session หรือไม่ และดึงรหัสห้อง
$is_live_session = isset($_SESSION['live_session_code']) && !empty($_SESSION['live_session_code']);
$live_session_code = $_SESSION['live_session_code'] ?? '';

$next_stage_id = $stage_id + 1;
$next_stage_link = ($next_stage_id > 50) ? "student_dashboard.php" : "play.php?stage_id=" . $next_stage_id;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    </head>
<body>
    <script>
        // ✅ [แก้ไข] ส่งค่าตัวแปรจาก PHP ไปยัง JavaScript ให้ครบถ้วน
        const CURRENT_STAGE_ID = <?= $stage_id ?>;
        const NEXT_STAGE_LINK = "<?= $next_stage_link ?>";
        const IS_LIVE_SESSION = <?= json_encode($is_live_session) ?>;
        const LIVE_SESSION_CODE = "<?= $live_session_code ?>";
    </script>
    <script src="../assets/js/shared/game_common.js"></script>
    <script src="../assets/js/game_logic.js"></script>
</body>
</html>