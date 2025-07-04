<?php
// --- ไฟล์: pages/join_live.php (ไฟล์ใหม่) ---
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_code = strtoupper(trim($_POST['session_code'])); // แปลงเป็นตัวพิมพ์ใหญ่

    // ตรวจสอบว่ามีรหัสห้องนี้ในระบบหรือไม่
    $stmt = $conn->prepare("SELECT id, status FROM live_sessions WHERE session_code = ?");
    $stmt->bind_param("s", $session_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $session = $result->fetch_assoc();
        if ($session['status'] === 'waiting') {
            // ถ้ารหัสถูกต้องและห้องกำลังรออยู่ ให้ส่งไปที่ Lobby
            $_SESSION['live_session_code'] = $session_code; // เก็บรหัสห้องไว้ใน session
            header("Location: lobby.php");
            exit();
        } else {
            $error_message = "ห้องเรียนนี้เริ่มเล่นไปแล้วหรือไม่พร้อมใช้งาน";
        }
    } else {
        $error_message = "ไม่พบรหัสห้องเรียนนี้";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าร่วมห้องเรียน Live</title>
    </head>
<body style="background-color: #0a192f; color: white; font-family: 'Kanit', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4" style="background-color: rgba(255,255,255,0.1); backdrop-filter: blur(10px); width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">เข้าร่วมห้องเรียน Live</h2>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="session_code" class="form-label">กรอกรหัสเข้าร่วม (4 ตัวอักษร):</label>
                    <input type="text" name="session_code" id="session_code" class="form-control form-control-lg text-center" maxlength="4" required autofocus style="text-transform:uppercase;">
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg">เข้าร่วม!</button>
                <a href="student_dashboard.php" class="btn btn-secondary w-100 mt-2">กลับหน้าหลัก</a>
            </form>
        </div>
    </div>
</body>
</html>