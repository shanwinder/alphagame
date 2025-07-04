<?php
// --- ไฟล์: pages/lobby.php (ไฟล์ใหม่) ---
session_start();
require_once '../includes/auth.php';
requireStudent();

// ตรวจสอบว่านักเรียนมี session code หรือไม่ ถ้าไม่มีให้กลับไปหน้า join
if (!isset($_SESSION['live_session_code'])) {
    header('Location: join_live.php');
    exit();
}
$session_code = $_SESSION['live_session_code'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Lobby - รอเริ่มเกม</title>
    </head>
<body>
    <div class="container text-center py-5">
        <h1>ห้องเรียน: <?= htmlspecialchars($session_code) ?></h1>
        <p class="lead">รอคุณครูเริ่มเกมสักครู่นะ...</p>
        
        <div id="player-list-container" class="my-4">
            <h3>ผู้เข้าร่วมตอนนี้:</h3>
            <ul id="player-list" class="list-group">
                </ul>
        </div>
    </div>

    <script>
        const sessionCode = "<?= $session_code ?>";

        // ฟังก์ชันสำหรับดึงข้อมูลผู้เล่นและสถานะห้อง
        function checkSessionStatus() {
            fetch(`../api/live_status.php?code=${sessionCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'active') {
                        // ถ้าครูกดเริ่มเกมแล้ว ให้ไปที่ด่านแรก
                        window.location.href = `play.php?stage_id=${data.current_stage_id}`;
                    } else {
                        // อัปเดตรายชื่อผู้เล่น
                        const playerList = document.getElementById('player-list');
                        playerList.innerHTML = ''; // ล้างรายชื่อเดิม
                        data.players.forEach(player => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.textContent = player.name;
                            playerList.appendChild(li);
                        });
                    }
                });
        }

        // เริ่มการ Polling ทุกๆ 5 วินาที
        setInterval(checkSessionStatus, 5000);

        // เรียกครั้งแรกทันทีที่หน้าโหลด
        checkSessionStatus();
    </script>
</body>
</html>