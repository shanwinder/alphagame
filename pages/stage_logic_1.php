<?php
// --- ไฟล์: pages/stage_logic_1.php ---

// เริ่ม session และตรวจสอบการล็อกอินของนักเรียน
session_start();
require_once '../includes/auth.php';
requireStudent(); // ตรวจสอบว่าเป็นนักเรียน

// ข้อมูลสำหรับ Game Header
$game_title = "บทที่ 1: จับคู่สัญญาณ";
$stage_id = 1; // ID ของด่านนี้ในฐานข้อมูล
// ลิงก์ไปยังด่านถัดไป (ถ้ามี)
// $next_stage_link = "stage_logic_2.php";
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
    <style>
        /* CSS สำหรับจัดหน้าเกมให้อยู่กึ่งกลาง */
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f0f8ff; /* สีพื้นหลังอ่อนๆ */
            margin-top: 80px; /* เว้นที่สำหรับ Header */
        }
        #game-container {
            width: 900px;
            height: 600px;
            border: 5px solid #4A90E2;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <?php include '../includes/game_header.php'; // เรียกใช้ Header ของเกม ?>

    <h3 style="margin: 20px 0;">ภารกิจ: สังเกตและเลือกสัญญาณที่เหมือนกับต้นแบบ!</h3>

    <div id="game-container"></div>

    <script src="../assets/js/stage1_logic.js"></script>

    <?php include '../includes/student_footer.php'; // เรียกใช้ Footer ของนักเรียน ?>
</body>

</html>