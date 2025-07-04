<?php
// --- ไฟล์: pages/system_unavailable.php (ไฟล์ใหม่) ---
session_start();
require_once '../includes/db.php';

// ดึงข้อมูลการตั้งค่ามาแสดงผลให้นักเรียนทราบ
$settings_result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$conn->close();

$status = $settings['system_status'] ?? 'closed';
$message = 'คุณครูยังไม่เปิดระบบให้เข้าเล่นในขณะนี้ครับ';

if ($status === 'homework') {
    $start_time_str = $settings['homework_start_time'] ?? '';
    if (!empty($start_time_str)) {
        $start_time = new DateTime($start_time_str, new DateTimeZone('Asia/Bangkok'));
        $message = "โหมดการบ้านจะเปิดให้เล่นในวันที่ " . $start_time->format('d/m/Y') . " เวลา " . $start_time->format('H:i') . " น.";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบยังไม่เปิดให้บริการ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; }
        .container { max-width: 600px; }
        .card { border-radius: 15px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="container text-center">
        <div class="card p-4 p-md-5 shadow-sm">
            <h1 class="display-1">⏳</h1>
            <h2 class="mb-3">ระบบยังไม่เปิดให้บริการ</h2>
            <p class="lead text-muted"><?= htmlspecialchars($message) ?></p>
            <hr class="my-4">
            <div class="d-grid gap-2">
                <a href="../logout.php" class="btn btn-primary btn-lg">กลับสู่หน้าแรก</a>
            </div>
        </div>
    </div>
</body>
</html>