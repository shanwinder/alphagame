<?php
// --- ไฟล์: pages/login.php (ฉบับปรับปรุงธีมสดใส) ---

session_start();
require_once '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, student_id, name, password, role FROM users WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $message = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $message = "❌ ไม่พบชื่อผู้ใช้ในระบบ";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - การผจญภัยของอัลฟ่า</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            font-family: 'Kanit', sans-serif;
            /* ✅ ใช้ภาพพื้นหลังที่เราเคยสร้าง */
            background-image: url('../assets/img/login_bg_wide.png');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            position: relative;
            max-width: 420px;
            width: 100%;
        }

        .login-box {
            background-color: rgba(255, 255, 255, 0.95); /* พื้นหลังสีขาวโปร่งแสงเล็กน้อย */
            padding: 40px 30px;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid white;
        }

        .login-box h2 {
            color: #4A90E2; /* สีฟ้าสดใส */
            margin-bottom: 25px;
            font-size: 2rem;
            text-align: center;
            font-weight: 700;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1.1rem;
        }

        .btn-login {
            background: linear-gradient(to right, #F5A623, #F8D32A); /* ไล่สีส้ม-เหลือง */
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 166, 35, 0.4);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 166, 35, 0.5);
        }

        /* ✅ ตกแต่งด้วยตัวละครอัลฟ่า */
        .alpha-character {
            position: absolute;
            top: -80px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translate(-50%, 0px); }
            50% { transform: translate(-50%, -15px); }
            100% { transform: translate(-50%, 0px); }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="../alpha_front.png" alt="Alpha Character" class="alpha-character">

        <div class="login-box">
            <h2 class="mt-5">ยินดีต้อนรับ!</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger text-center"><?= $message; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">ชื่อผู้ใช้ (รหัสนักเรียน)</label>
                    <input type="text" class="form-control" name="username" id="username" required autofocus autocomplete="username">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <input type="password" class="form-control" name="password" id="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-login w-100 mt-3">
                    <i class="fas fa-rocket"></i> เข้าสู่การผจญภัย
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>