<?php
// --- ไฟล์: api/submit_stage_score.php (ฉบับแก้ไขสมบูรณ์) ---
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

if (!isset($_SESSION['user_id']) || !isset($_POST['stage_id']) || !isset($_POST['score'])) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลที่ส่งมาไม่ครบถ้วน']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stage_id = intval($_POST['stage_id']);
$score_received = intval($_POST['score']);

$stars_awarded = 0;
if ($score_received >= 100) {
    $stars_awarded = 3;
} elseif ($score_received > 0) {
    $stars_awarded = 1;
}

$completed_at = ($stars_awarded > 0) ? date('Y-m-d H:i:s') : null;

try {
    $stmt = $conn->prepare(
        "INSERT INTO progress (user_id, stage_id, stars_awarded, attempts, completed_at)
         VALUES (?, ?, ?, 1, ?)
         ON DUPLICATE KEY UPDATE
         stars_awarded = GREATEST(stars_awarded, VALUES(stars_awarded)),
         attempts = attempts + 1,
         completed_at = VALUES(completed_at)"
    );

    $stmt->bind_param("iiis", $user_id, $stage_id, $stars_awarded, $completed_at);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'stars' => $stars_awarded, 'message' => 'บันทึกข้อมูลสำเร็จ!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>