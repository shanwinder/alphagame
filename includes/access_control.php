<?php
// --- ไฟล์: includes/access_control.php (ฉบับแก้ไข) ---

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    return;
}

require_once __DIR__ . '/db.php';

$settings_result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// ✅ [แก้ไข] นำบรรทัด $conn->close(); ออกจากไฟล์นี้
// เราจะปล่อยให้การเชื่อมต่อเปิดไว้ เพื่อให้ไฟล์หลัก (เช่น dashboard) ใช้งานต่อได้

$status = $settings['system_status'] ?? 'closed';
$allow_access = false;

if ($status === 'open') {
    $allow_access = true;
} elseif ($status === 'homework') {
    $start_str = $settings['homework_start_time'] ?? '';
    $end_str = $settings['homework_end_time'] ?? '';
    if (!empty($start_str) && !empty($end_str)) {
        $timezone = new DateTimeZone('Asia/Bangkok');
        $now = new DateTime('now', $timezone);
        $start_time = new DateTime($start_str, $timezone);
        $end_time = new DateTime($end_str, $timezone);
        if ($now >= $start_time && $now <= $end_time) {
            $allow_access = true;
        }
    }
}

if (!$allow_access) {
    // ต้องแน่ใจว่า path ถูกต้อง
    // หากไฟล์นี้อยู่ใน 'includes' และจะไปที่ 'pages' เราต้องใช้ ../pages/
    $path_to_unavailable = str_replace('\\', '/', __DIR__) . '/../pages/system_unavailable.php';
    if (file_exists($path_to_unavailable)) {
        header("Location: ../pages/system_unavailable.php");
    } else {
        // Fallback ในกรณีที่ path ไม่ถูกต้อง
        die("System is currently unavailable.");
    }
    exit();
}
?>