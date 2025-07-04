<?php
// --- ไฟล์: pages/play.php (ฉบับแก้ไขให้สอดคล้องกับ Header) ---

session_start();
require_once '../includes/auth.php';
requireStudent();
require_once '../includes/db.php';

$stage_id_from_url = isset($_GET['stage_id']) ? (int) $_GET['stage_id'] : 1;

$stmt = $conn->prepare(
    "SELECT s.title AS stage_title, c.title AS chapter_title, s.instruction, s.id AS stage_id
     FROM stages s
     JOIN chapters c ON s.chapter_id = c.id
     WHERE s.id = ?"
);
$stmt->bind_param("i", $stage_id_from_url);
$stmt->execute();
$result = $stmt->get_result();
$stage_data = $result->fetch_assoc();

if (!$stage_data) {
    header('Location: student_dashboard.php');
    exit();
}
$stmt->close();
$conn->close();

// ✅ [แก้ไข] เปลี่ยนชื่อตัวแปรให้ตรงกับที่ game_header.php ต้องการ
$game_title = $stage_data['chapter_title'];
$stage_id = $stage_data['stage_id']; // <--- แก้ไขจาก $stage_id_display เป็น $stage_id
$instruction_text = $stage_data['instruction'] ?? 'ขอให้สนุกกับการแก้ปัญหา!';

$next_stage_id = $stage_id + 1;
$next_stage_link = ($next_stage_id > 50) ? "student_dashboard.php" : "play.php?stage_id=" . $next_stage_id;

// ✅ [เพิ่ม] ตรวจสอบว่าเป็น Live Session หรือไม่ จาก PHP Session
$is_live_session = isset($_SESSION['live_session_code']) && !empty($_SESSION['live_session_code']);
$live_session_code = $_SESSION['live_session_code'] ?? '';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($stage_data['stage_title']) ?> - <?= htmlspecialchars($game_title) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>
    <link rel="stylesheet" href="../assets/css/game_header.css">
    <link rel="stylesheet" href="../assets/css/game_common.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f0f8ff;
            margin-top: 20px;
            font-family: 'Kanit', sans-serif;
        }

        #game-container {
            width: 900px;
            height: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .instruction-box {
            width: 90%;
            max-width: 880px;
            background-color: #fffde7;
            border: 2px dashed #fbc02d;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 30px;
            border: 5px solid #50e3c2;
            border-radius: 20px;
            width: 60%;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation-name: animatetop;
            animation-duration: 0.4s;
        }

        @keyframes animatetop {
            from {
                top: -300px;
                opacity: 0
            }

            to {
                top: 0;
                opacity: 1
            }
        }

        .modal-content h2 {
            color: #28a745;
            font-size: 2.5em;
        }

        .modal-content p {
            font-size: 1.2em;
            color: #333;
        }

        .star-container {
            font-size: 4rem;
            margin: 15px 0;
        }

        .star-icon {
            transition: color 0.3s ease;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-next-stage {
            position: relative;
            padding: 10px 20px;
            font-size: 1.1rem;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            background-color: #28a745;
            overflow: hidden;
        }

        .btn-next-stage .progress-bar-inner {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transition: width 10s linear;
        }

        .btn-next-stage .btn-text {
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body>

    <?php include '../includes/game_header.php'; ?>

    <div class="instruction-box">
        <h4><i class="fas fa-info-circle"></i> ภารกิจ: <?= htmlspecialchars($stage_data['stage_title']) ?></h4>
        <p><?= htmlspecialchars($instruction_text) ?></p>
    </div>
    <div id="game-container"></div>
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title">ภารกิจสำเร็จ!</h2>
            <div id="star-rating" class="star-container">
                <span class="star-icon">☆</span>
                <span class="star-icon">☆</span>
                <span class="star-icon">☆</span>
            </div>
            <p id="modal-message">คุณผ่านด่าน "<?= htmlspecialchars($stage_data['stage_title']) ?>" ได้สำเร็จ!</p>
            <a href="<?= $next_stage_link ?>" id="nextStageBtn" class="btn-next-stage">
                <div class="progress-bar-inner" id="next-progress-fill"></div>
                <span class="btn-text">▶️ ไปด่านถัดไป (<span id="seconds">10</span>)</span>
            </a>
        </div>
    </div>

    <script>
        const CURRENT_STAGE_ID = <?= $stage_id ?>;

        // ✅ [เพิ่ม] ส่งค่าตัวแปรจาก PHP ไปยัง JavaScript
        const CURRENT_STAGE_ID = <?= $stage_id ?>;
        const IS_LIVE_SESSION = <?= json_encode($is_live_session) ?>;
        const LIVE_SESSION_CODE = "<?= $live_session_code ?>";
    </script>
    <script src="../assets/js/shared/game_common.js"></script>
    <script src="../assets/js/stage1_logic.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            updateTotalScore();
        });
    </script>
</body>

</html>