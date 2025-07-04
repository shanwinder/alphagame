<?php
// --- ไฟล์: api/live_status.php (ไฟล์ใหม่) ---
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

$session_code = $_GET['code'] ?? '';
if (empty($session_code)) {
    echo json_encode(['error' => 'Missing session code']);
    exit();
}

// ดึงข้อมูลเซสชั่นปัจจุบัน
$stmt_session = $conn->prepare("SELECT id, status, current_stage_id FROM live_sessions WHERE session_code = ?");
$stmt_session->bind_param("s", $session_code);
$stmt_session->execute();
$session_result = $stmt_session->get_result();
if ($session_result->num_rows === 0) {
    echo json_encode(['error' => 'Session not found']);
    exit();
}
$session_data = $session_result->fetch_assoc();
$session_id = $session_data['id'];
$stmt_session->close();

// ดึงรายชื่อผู้เล่นทั้งหมดในเซสชั่น
$stmt_players = $conn->prepare(
    "SELECT u.name FROM users u 
     JOIN live_session_participants p ON u.id = p.user_id 
     WHERE p.session_id = ?"
);
$stmt_players->bind_param("i", $session_id);
$stmt_players->execute();
$players_result = $stmt_players->get_result();
$players = [];
while ($row = $players_result->fetch_assoc()) {
    $players[] = $row;
}
$stmt_players->close();

// ส่งข้อมูลทั้งหมดกลับไปเป็น JSON
echo json_encode([
    'status' => $session_data['status'],
    'current_stage_id' => $session_data['current_stage_id'],
    'players' => $players
]);
?>