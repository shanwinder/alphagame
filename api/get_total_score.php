<?php
// --- ไฟล์: api/get_total_score.php (ฉบับแก้ไข) ---
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['score' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ [แก้ไข] เปลี่ยนจากการ SUM(score) เป็น SUM(stars_awarded) ให้ตรงกับฐานข้อมูลล่าสุด
$sql = "SELECT SUM(stars_awarded) as total_score FROM progress WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_score);
$stmt->fetch();
$stmt->close();

// ส่งค่าคะแนนกลับไปเป็น JSON (ถ้าไม่มีคะแนนเลยจะส่งค่า 0)
echo json_encode(['score' => (int) $total_score]);
?>