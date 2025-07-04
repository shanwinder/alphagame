<?php
// --- ไฟล์: pages/join_live.php (ฉบับออกแบบใหม่) ---
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_code = strtoupper(trim($_POST['session_code']));

    if (!empty($session_code)) {
        $stmt = $conn->prepare("SELECT id, status, current_stage_id FROM live_sessions WHERE session_code = ?");
        $stmt->bind_param("s", $session_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            if ($session['status'] === 'waiting') {
                // ✅ [แก้ไข] เพิ่มโค้ดส่วนนี้เพื่อบันทึกผู้เข้าร่วม
                $user_id = $_SESSION['user_id'];
                $session_id = $session['id'];
                $stmt_join = $conn->prepare("INSERT INTO live_session_participants (session_id, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id=user_id");
                $stmt_join->bind_param("ii", $session_id, $user_id);
                $stmt_join->execute();
                $stmt_join->close();
                // จบส่วนที่แก้ไข

                $_SESSION['live_session_code'] = $session_code;
                header("Location: lobby.php");
                exit();
            } elseif ($session['status'] === 'active') {
                $_SESSION['live_session_code'] = $session_code;
                header("Location: play.php?stage_id=" . $session['current_stage_id']);
                exit();
            } else {
                $error_message = "ห้องเรียนนี้จบการเล่นไปแล้วหรือไม่พร้อมใช้งานครับ";
            }
        } else {
            $error_message = "ไม่พบรหัสห้องเรียนนี้ในระบบ";
        }
        $stmt->close();
    } else {
        $error_message = "กรุณากรอกรหัสเข้าร่วมห้อง";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>เข้าร่วมห้องเรียน Live - การผจญภัยของอัลฟ่า</title>
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
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s linear infinite, drift 200s linear infinite;
        }

        @keyframes drift {
            from {
                transform: translateY(-20vh) translateX(5vw);
            }

            to {
                transform: translateY(120vh) translateX(-5vw);
            }
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.7;
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        .star.type1 {
            width: 1px;
            height: 1px;
            background: #FFF;
        }

        .star.type2 {
            width: 2px;
            height: 2px;
            background: #90e0ef;
        }

        .star.type3 {
            width: 3px;
            height: 3px;
            background: #f9c74f;
        }

        /* ✅ ออกแบบกล่องสำหรับ Join Live โดยเฉพาะ */
        .join-box-container {
            position: relative;
            z-index: 2;
        }

        .join-box {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.25);
            text-align: center;
        }

        .join-box h2 {
            color: #50e3c2;
            font-weight: 700;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .form-label {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        #session_code {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5em;
            /* ทำให้ตัวอักษรห่างกัน */
            padding: 10px;
            text-transform: uppercase;
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        #session_code::placeholder {
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: normal;
        }

        .btn-join {
            font-size: 1.3rem;
            font-weight: bold;
            padding: 12px;
            border-radius: 10px;
            background: linear-gradient(to right, #f5a623, #f8d32a);
            border: none;
            color: #333;
        }

        .btn-back {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }

        .btn-back:hover {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="background-container"></div>

    <div class="join-box-container">
        <div class="join-box">
            <h2><i class="fas fa-satellite-dish"></i> เข้าร่วมห้องเรียน Live</h2>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="join_live.php" novalidate>
                <div class="mb-3">
                    <label for="session_code" class="form-label">รหัสเข้าร่วมจากคุณครู</label>
                    <input type="text" name="session_code" id="session_code" class="form-control" maxlength="4" required
                        autofocus placeholder="A B C D">
                </div>
                <button type="submit" class="btn btn-join w-100 mt-3">ยืนยัน</button>
            </form>
            <a href="student_dashboard.php" class="btn-back">กลับหน้าหลัก</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () { // สั่งให้โค้ดทำงานเมื่อหน้าเว็บโหลดเสร็จสมบูรณ์
            const starContainer = document.body; // อ้างอิงถึง element body
            const numberOfStars = 200; // กำหนดจำนวนดาวที่ต้องการ
            const starTypes = ['type1', 'type2', 'type3']; // กำหนดประเภทของดาว (ซึ่งจะอ้างอิงกับคลาสใน CSS)
            for (let i = 0; i < numberOfStars; i++) { // วนลูปเพื่อสร้างดาวตามจำนวนที่กำหนด
                let star = document.createElement('div'); // สร้าง element <div> ใหม่ในแต่ละรอบ
                star.classList.add('star'); // เพิ่มคลาส .star ให้กับ div
                star.classList.add(starTypes[Math.floor(Math.random() * starTypes.length)]); // สุ่มเพิ่มคลาส type1, type2 หรือ type3
                star.style.left = Math.random() * 100 + 'vw'; // สุ่มตำแหน่งแนวนอน (0-100% ของความกว้างจอ)
                star.style.top = -10 + 'vh'; // กำหนดให้ดาวทุกดวงเริ่มจากด้านบนสุดของจอ
                const twinkleDelay = (Math.random() * 5) + 's'; // สุ่มเวลาหน่วงของการกระพริบ
                const driftDelay = (Math.random() * 10) + 's'; // สุ่มเวลาหน่วงของการลอย
                const twinkleDuration = (2 + Math.random() * 3) + 's'; // สุ่มระยะเวลาการกระพริบ
                const driftDuration = (40 + Math.random() * 60) + 's'; // สุ่มระยะเวลาการลอย
                star.style.animation = `twinkle ${twinkleDuration} linear ${twinkleDelay} infinite, drift ${driftDuration} linear ${driftDelay} infinite`; // กำหนด animation ให้กับดาว
                starContainer.insertBefore(star, starContainer.firstChild); // นำดาวที่สร้างเสร็จไปแสดงผลในหน้าเว็บ (โดยแทรกไว้เป็น element แรกของ body)
            }
        });
    </script>
</body>

</html>