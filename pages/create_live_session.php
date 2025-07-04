<?php
// --- ไฟล์: pages/create_live_session.php (ไฟล์ใหม่) ---
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

// ฟังก์ชันสำหรับสร้างรหัสห้องแบบสุ่ม (เช่น A4B1)
function generateSessionCode($length = 4) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

$admin_id = $_SESSION['user_id'];
$session_code = generateSessionCode();

// เตรียมคำสั่ง SQL เพื่อเพิ่มห้องใหม่ลงในตาราง live_sessions
// สถานะเริ่มต้นคือ 'waiting' (รอผู้เล่น)
$stmt = $conn->prepare(
    "INSERT INTO live_sessions (session_code, admin_id, status) VALUES (?, ?, 'waiting')"
);
$stmt->bind_param("si", $session_code, $admin_id);

// พยายามเพิ่มข้อมูล
if ($stmt->execute()) {
    // ถ้าสำเร็จ ให้ส่งผู้ใช้ไปยังหน้าควบคุมห้อง Live พร้อมกับรหัสห้อง
    header("Location: live_control.php?code=" . $session_code);
    exit();
} else {
    // ถ้าเกิดข้อผิดพลาด (อาจจะเพราะรหัสซ้ำ ซึ่งโอกาสน้อยมาก)
    // ให้กลับไปที่หน้า dashboard พร้อมข้อความ error
    header("Location: dashboard.php?error=ไม่สามารถสร้างห้องได้");
    exit();
}
?>