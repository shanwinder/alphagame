<?php
// --- ไฟล์: pages/play.php ---
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

// ✅ เราจะเรียกใช้ access_control.php ที่นี่ในอนาคตเมื่อพร้อม
// require_once '../includes/access_control.php';

$stage_id = isset($_GET['stage_id']) ? (int)$_GET['stage_id'] : 1;

$stmt = $conn->prepare("SELECT s.title AS stage_title, c.title AS chapter_title, s.instruction, s.id AS stage_id FROM stages s JOIN chapters c ON s.chapter_id = c.id WHERE s.id = ?");
$stmt->bind_param("i", $stage_id);
$stmt->execute();
$result = $stmt->get_result();
$stage_data = $result->fetch_assoc();

if (!$stage_data) {
    header('Location: student_dashboard.php'); exit();
}
$stmt->close();
$conn->close();

$game_title = $stage_data['chapter_title'];
$instruction_text = $stage_data['instruction'] ?? 'ขอให้สนุกกับการแก้ปัญหา!';
$next_stage_id = $stage_id + 1;
$next_stage_link = ($next_stage_id > 50) ? "student_dashboard.php" : "play.php?stage_id=" . $next_stage_id;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($stage_data['stage_title']) ?> - <?= htmlspecialchars($game_title) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/game_header.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        body { font-family: 'Kanit', sans-serif; display: flex; flex-direction: column; align-items: center; background-color: #eef2f3; margin-top: 80px; }
        .content-wrapper { width: 100%; display: flex; flex-direction: column; align-items: center; }
        #game-container { width: 900px; height: 600px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .instruction-box { width: 90%; max-width: 880px; background-color: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 20px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <?php include '../includes/game_header.php'; ?>
        <div class="instruction-box">
            <h4><i class="fas fa-bullseye text-primary"></i> ภารกิจ: <?= htmlspecialchars($stage_data['stage_title']) ?></h4>
            <p><?= htmlspecialchars($instruction_text) ?></p>
        </div>
        <div id="game-container"></div>
        <div id="successModal" class="modal">
            <div class="modal-content">
                <h2 id="modal-title">ภารกิจสำเร็จ!</h2>
                <div id="star-rating" class="star-container"></div>
                <p id="modal-message"></p>
                <a id="nextStageBtn" class="btn-next-stage" style="display:none;">
                    <div class="progress-bar-inner" id="next-progress-fill"></div>
                    <span class="btn-text">▶️ ไปด่านถัดไป (<span id="seconds">10</span>)</span>
                </a>
            </div>
        </div>
        <?php include '../includes/game_footer.php'; ?>
    </div>
    <script> const CURRENT_STAGE_ID = <?= $stage_id ?>; const NEXT_STAGE_LINK = "<?= $next_stage_link ?>"; </script>
    <script src="../assets/js/shared/game_common.js"></script>
    <script src="../assets/js/game_logic.js"></script>
</body>
</html>