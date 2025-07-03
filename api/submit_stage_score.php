<?php
// --- ไฟล์: api/submit_stage_score.php (ฉบับแก้ไขสมบูรณ์) ---

// # บรรทัดนี้สำคัญมาก เพื่อให้ JavaScript อ่านข้อมูลตอบกลับเป็น JSON ได้ถูกต้อง
header('Content-Type: application/json');

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

// # ตรวจสอบว่าข้อมูลที่จำเป็นถูกส่งมาครบหรือไม่
if (!isset($_SESSION['user_id']) || !isset($_POST['stage_id']) || !isset($_POST['score'])) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลที่ส่งมาไม่ครบถ้วน']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stage_id = intval($_POST['stage_id']);
$score_received = intval($_POST['score']);

// # แปลงคะแนนที่ได้รับ (score) เป็นจำนวนดาว (stars_awarded)
// นี่เป็น Logic ที่เราเพิ่มเข้ามาเพื่อให้สอดคล้องกับฐานข้อมูล
// คุณครูสามารถปรับแก้เงื่อนไขนี้ได้ในอนาคต เช่น ตามเวลาที่ใช้
$stars_awarded = 0;
if ($score_received >= 100) {
    $stars_awarded = 3; // คะแนนเต็มได้ 3 ดาว
} elseif ($score_received >= 70) {
    $stars_awarded = 2; // คะแนน 70-99 ได้ 2 ดาว
} elseif ($score_received > 0) {
    $stars_awarded = 1; // แค่ผ่านก็ได้ 1 ดาว
}

// # กำหนดเวลาที่ผ่านด่าน ถ้าได้ดาวมากกว่า 0
$completed_at = ($stars_awarded > 0) ? date('Y-m-d H:i:s') : null;

try {
    // # ใช้คำสั่ง INSERT ... ON DUPLICATE KEY UPDATE เพื่อจัดการข้อมูลในตาราง `progress`
    // ถ้ายังไม่มีข้อมูลของ user และ stage นี้: จะทำการ INSERT แถวใหม่
    // ถ้ามีข้อมูลอยู่แล้ว: จะทำการ UPDATE แถวเดิม
    $stmt = $conn->prepare(
        "INSERT INTO progress (user_id, stage_id, stars_awarded, attempts, completed_at)
         VALUES (?, ?, ?, 1, ?)
         ON DUPLICATE KEY UPDATE
         stars_awarded = GREATEST(stars_awarded, VALUES(stars_awarded)), -- # อัปเดตเฉพาะเมื่อดาวที่ได้ใหม่สูงกว่าเดิม
         attempts = attempts + 1,
         completed_at = VALUES(completed_at)"
    );

    // # ส่งค่า parameter ให้ตรงกับเครื่องหมาย ? ใน SQL
    // "iiis" หมายถึง Integer, Integer, Integer, String
    $stmt->bind_param("iiis", $user_id, $stage_id, $stars_awarded, $completed_at);
    $stmt->execute();

    // # (สำคัญที่สุด) ส่งสถานะ "success" และ "จำนวนดาว" กลับไปให้ JavaScript
    echo json_encode(['status' => 'success', 'stars' => $stars_awarded, 'message' => 'บันทึกข้อมูลสำเร็จ!']);

} catch (Exception $e) {
    // # หากเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล ให้ส่งสถานะ "error" กลับไป
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>