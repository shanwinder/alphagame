<?php
// --- ไฟล์: pages/lobby.php (ฉบับอัปเกรดดีไซน์) ---
session_start();
require_once '../includes/auth.php';
requireStudent();

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
    <title>Lobby: <?= htmlspecialchars($session_code) ?> - รอเริ่มเกม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        /* ✅ ใช้ CSS ทั้งหมดจากหน้า student_dashboard เพื่อให้ธีมสอดคล้องกัน */
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #0d1b2a;
            color: white;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../assets/img/bottom_planet.png');
            background-repeat: no-repeat;
            background-position: bottom left;
            background-size: 50%;
            z-index: 1;
            pointer-events: none;
            opacity: 0.9;
        }

        .star {
            /* ... CSS ดาวเหมือนเดิม ... */
        }

        /* ✅ ออกแบบกล่อง Lobby ใหม่ทั้งหมด */
        .lobby-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 700px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.25);
            text-align: center;
        }

        .lobby-header h1 {
            color: #50e3c2;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .lobby-header .lead {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .waiting-animation {
            /* Animation สำหรับข้อความ "รอ..." */
            font-size: 1.5rem;
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.5;
            }
        }

        #player-list-container h3 {
            margin-top: 2rem;
            color: #f9c74f;
        }

        #player-list {
            max-height: 200px;
            overflow-y: auto;
            border-radius: 10px;
        }

        .list-group-item {
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>

    <div class="background-container"></div>

    <div class="lobby-container">
        <div class="lobby-header">
            <h1>รหัสห้องเรียน: <?= htmlspecialchars($session_code) ?></h1>
            <p class="lead">เมื่อเพื่อนๆ เข้ามาครบแล้ว คุณครูจะเริ่มการผจญภัย!</p>
            <p class="waiting-animation">กำลังรอผู้เล่นคนอื่น...</p>
        </div>

        <div id="player-list-container">
            <h3><i class="fas fa-users"></i> ผู้เข้าร่วมตอนนี้: <span id="player-count">0</span> คน</h3>
            <ul id="player-list" class="list-group list-group-flush">
            </ul>
        </div>
    </div>

    <script>
        const sessionCode = "<?= $session_code ?>";
        const playerCountSpan = document.getElementById('player-count');

        function checkSessionStatus() {
            // โค้ด fetch เดิมถูกต้องแล้ว แต่เราจะเพิ่มการอัปเดตจำนวนผู้เล่น
            fetch(`../api/live_status.php?code=${sessionCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'active') {
                        window.location.href = `play.php?stage_id=${data.current_stage_id}`;
                    } else {
                        const playerList = document.getElementById('player-list');
                        playerList.innerHTML = '';
                        playerCountSpan.textContent = data.players.length; // อัปเดตจำนวน
                        data.players.forEach(player => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.textContent = `🧑‍🚀 ${player.name}`;
                            playerList.appendChild(li);
                        });
                    }
                });
        }

        setInterval(checkSessionStatus, 5000);
        checkSessionStatus();
    </script>
</body>

</html>