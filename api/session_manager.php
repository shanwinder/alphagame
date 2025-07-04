<?php
// --- ไฟล์: api/session_manager.php (ไฟล์ใหม่) ---
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

$session_code = $_POST['code'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($session_code) || empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

// เริ่มเกม: ตั้งค่าสถานะเป็น active และกำหนดด่านแรก
if ($action === 'start_game') {
    $first_stage_id = 1; // เริ่มที่ด่าน 1 เสมอ
    $stmt = $conn->prepare("UPDATE live_sessions SET status = 'active', current_stage_id = ? WHERE session_code = ?");
    $stmt->bind_param("is", $first_stage_id, $session_code);
    $stmt->execute();
}

// ไปด่านถัดไป: เพิ่ม current_stage_id ขึ้น 1
if ($action === 'next_stage') {
    $stmt = $conn->prepare("UPDATE live_sessions SET current_stage_id = current_stage_id + 1 WHERE session_code = ? AND current_stage_id < 50"); // ป้องกันไม่ให้เกินด่าน 50
    $stmt->bind_param("s", $session_code);
    $stmt->execute();
}
// เพิ่ม action อื่นๆ ได้ที่นี่ เช่น pause, finish

echo json_encode(['status' => 'success', 'message' => 'คำสั่ง ' . $action . ' ถูกส่งแล้ว']);
?>