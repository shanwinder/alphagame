<?php
// --- ไฟล์: pages/stage_logic_1.php (ฉบับแก้ไข) ---

session_start();
require_once '../includes/auth.php';
requireStudent();

$game_title = "บทที่ 1: จับคู่สัญญาณ";
$stage_id = 1;
$next_stage_link = "student_dashboard.php"; // ลิงก์เริ่มต้น ให้กลับไปหน้าเลือกด่านก่อน
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>เกมจับคู่สัญญาณ - การผจญภัยของอัลฟ่า</title>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>
    <link rel="stylesheet" href="../assets/css/game_header.css">
    <link rel="stylesheet" href="../assets/css/game_common.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        body {
            display: flex; flex-direction: column; align-items: center;
            background-color: #f0f8ff; margin-top: 20px;
        }
        .mission-box {
            width: 80%; max-width: 880px; background-color: white;
            border: 2px solid #ddd; border-radius: 10px; padding: 15px;
            margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .mission-box h3 { margin-top: 0; color: #4A90E2; }
        #game-container {
            width: 900px; height: 600px; border: 5px solid #4A90E2;
            border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/game_header.php'; ?>

    <div class="mission-box">
        <h3><i class="fas fa-bullseye"></i> ภารกิจ: จับคู่สัญญาณที่หายไป</h3>
        <p><strong>เงื่อนไขการผ่านด่าน:</strong> ผู้เล่นจะต้องเลือก "สัญญาณ" ที่เหมือนกับ "โจทย์ต้นแบบ" ให้ถูกต้อง</p>
        <p><strong>ทักษะที่ต้องใช้ในด่านนี้:</strong> การสังเกต, การเปรียบเทียบ, การตัดสินใจ</p>
    </div>

    <div id="game-container"></div>

    <div id="successModal" class="modal">
        <div class="modal-content">
            <h2>ภารกิจสำเร็จ!</h2>
            <p>คุณผ่านด่าน "จับคู่สัญญาณ" ได้สำเร็จแล้ว!</p>
            <p>คะแนนที่ได้รับ: <span id="modal-score"></span> คะแนน</p>
            <a id="nextStageButton" href="<?php echo $next_stage_link; ?>" class="modal-button">ไปต่อ</a>
        </div>
    </div>

    <script src="../assets/js/stage1_logic.js"></script>
</body>
</html>