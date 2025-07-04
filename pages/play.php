<?php
// --- ไฟล์: pages/play.php (ฉบับยกเครื่องใหม่ทั้งหมด) ---
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

$stage_id = isset($_GET['stage_id']) ? (int) $_GET['stage_id'] : 1;

// ดึงข้อมูลของด่านและบทเรียนจากฐานข้อมูล
$stmt = $conn->prepare(
    "SELECT s.title AS stage_title, c.title AS chapter_title, s.instruction 
     FROM stages s
     JOIN chapters c ON s.chapter_id = c.id 
     WHERE s.id = ?"
);
$stmt->bind_param("i", $stage_id);
$stmt->execute();
$result = $stmt->get_result();
$stage_data = $result->fetch_assoc();

if (!$stage_data) {
    header('Location: student_dashboard.php?error=notfound');
    exit();
}
$stmt->close();
$conn->close();

// เตรียมตัวแปรสำหรับใช้ในหน้าเว็บ
$game_title = $stage_data['chapter_title'];
$instruction_text = $stage_data['instruction'] ?? 'ขอให้สนุกกับการแก้ปัญหา!';
$next_stage_id = $stage_id + 1;
$next_stage_link = ($next_stage_id > 50) ? "student_dashboard.php" : "play.php?stage_id=" . $next_stage_id;

// ตรวจสอบว่าเป็น Live Session หรือไม่
$is_live_session = isset($_SESSION['live_session_code']) && !empty($_SESSION['live_session_code']);
$live_session_code = $_SESSION['live_session_code'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>ด่านที่ <?= $stage_id ?>: <?= htmlspecialchars($stage_data['stage_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/game_header.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link rel="stylesheet" href="../assets/css/game_play.css">
</head>

<body>

    <div class="game-page-wrapper">
        <?php include '../includes/game_header.php'; ?>

        <main class="game-main-content">
            <div class="hud-panel">
                <h4><i class="fas fa-bullseye text-primary"></i> ภารกิจ</h4>
                <p><strong>ชื่อด่าน:</strong> <?= htmlspecialchars($stage_data['stage_title']) ?></p>
                <hr>
                <p><?= htmlspecialchars($instruction_text) ?></p>
            </div>
            <div id="game-container"></div>
        </main>
    </div>

    <div id="successModal" class="modal game-modal">
        <div class="modal-content">
            <h2 id="modal-title">ภารกิจสำเร็จ!</h2>
            <div id="star-rating" class="star-container"></div>
            <p id="modal-message">คุณผ่านด่านนี้สำเร็จ!</p>
            <a id="nextStageBtn" href="#" class="btn btn-primary mt-3" style="display:none;">
                <div class="progress-bar-inner" id="next-progress-fill"></div>
                <span class="btn-text">▶️ ไปด่านถัดไป (<span id="seconds">10</span>)</span>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>
    <script>
        const CURRENT_STAGE_ID = <?= $stage_id ?>;
        const NEXT_STAGE_LINK = "<?= $next_stage_link ?>";
        const IS_LIVE_SESSION = <?= json_encode($is_live_session) ?>;
        const LIVE_SESSION_CODE = "<?= $live_session_code ?>";
    </script>
    <script src="../assets/js/shared/game_common.js"></script>
    <script src="../assets/js/game_logic.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('total-score')) {
                updateTotalScore();
            }
        });
    </script>

</body>

</html>