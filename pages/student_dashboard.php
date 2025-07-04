<?php
// --- ไฟล์: pages/student_dashboard.php (ฉบับแก้ไขลำดับ require) ---

session_start();
// ✅ 1. เรียกใช้ไฟล์ที่จำเป็นสำหรับการเชื่อมต่อและยืนยันตัวตนก่อน
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent(); // ยืนยันว่าเป็นนักเรียน

// ✅ 2. หลังจากยืนยันตัวตนแล้ว จึงเรียกใช้ 'ยามเฝ้าประตู'
require_once '../includes/access_control.php';

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$games = [
    1 => ['code' => 'Logic', 'title' => 'บทที่ 1: เหตุผลเชิงตรรกะ'],
    2 => ['code' => 'Algorithm', 'title' => 'บทที่ 2: อัลกอริทึม'],
    3 => ['code' => 'Text', 'title' => 'บทที่ 3: อัลกอริทึมด้วยข้อความ'],
    4 => ['code' => 'Pseudocode', 'title' => 'บทที่ 4: รหัสจำลอง'],
    5 => ['code' => 'Flowchart', 'title' => 'บทที่ 5: ผังงาน (Flowchart)'],
];

function getGameProgress($conn, $user_id, $chapter_id)
{
    // ... ฟังก์ชันนี้ถูกต้องแล้ว ไม่ต้องแก้ไข ...
    $stmt_stages = $conn->prepare("SELECT id FROM stages WHERE chapter_id = ?");
    $stmt_stages->bind_param("i", $chapter_id);
    $stmt_stages->execute();
    $result_stages = $stmt_stages->get_result();
    $stage_ids = [];
    while ($row = $result_stages->fetch_assoc()) {
        $stage_ids[] = $row['id'];
    }
    $stmt_stages->close();
    if (empty($stage_ids)) {
        return ['passed' => 0, 'total' => 0, 'total_stars' => 0];
    }
    $total_stages_in_chapter = count($stage_ids);
    $placeholders = implode(',', array_fill(0, count($stage_ids), '?'));
    $types = 'i' . str_repeat('i', count($stage_ids));
    $params = array_merge([$user_id], $stage_ids);
    $sql = "SELECT COUNT(id) AS passed_stages, SUM(stars_awarded) AS total_stars FROM progress WHERE user_id = ? AND stage_id IN ($placeholders) AND completed_at IS NOT NULL";
    $stmt_progress = $conn->prepare($sql);
    if ($stmt_progress === false) {
        return ['passed' => 0, 'total' => $total_stages_in_chapter, 'total_stars' => 0];
    }
    $bind_names = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bind_names[] = &$params[$i];
    }
    call_user_func_array([$stmt_progress, 'bind_param'], $bind_names);
    $stmt_progress->execute();
    $result_progress = $stmt_progress->get_result();
    $progress_data = $result_progress->fetch_assoc();
    $stmt_progress->close();
    return [
        'passed' => (int) ($progress_data['passed_stages'] ?? 0),
        'total' => $total_stages_in_chapter,
        'total_stars' => (int) ($progress_data['total_stars'] ?? 0)
    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
</head>

<body>
</body>

</html>