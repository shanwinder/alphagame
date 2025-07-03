<?php
// --- ไฟล์: includes/student_header.php (ฉบับปรับปรุงธีมอวกาศ) ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../pages/login.php");
    exit();
}
?>

<style>
    .student-navbar {
        /* ทำให้พื้นหลังโปร่งแสงและเบลอฉากหลัง */
        background: rgba(10, 25, 47, 0.85); /* สีน้ำเงินเข้มโปร่งแสง */
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px); /* สำหรับ Safari */
        font-family: 'Kanit', sans-serif;
        padding: 0.5rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* เส้นขอบบางๆ */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .student-navbar .navbar-brand {
        font-size: 1.5rem;
        color: #50e3c2; /* สีเขียวอมฟ้า Sci-fi */
        font-weight: bold;
        display: flex;
        align-items: center;
    }

    .student-navbar .navbar-brand .emoji {
        font-size: 1.8rem;
        margin-right: 8px;
    }

    .student-navbar .nav-link {
        color: #e2e8f0; /* สีขาวอมเทา */
        font-size: 1.05rem;
        margin-left: 15px;
        transition: color 0.2s;
    }

    .student-navbar .nav-link:hover {
        color: #f5a623; /* สีส้มเมื่อ hover */
    }

    .navbar-profile {
        background: rgba(255, 255, 255, 0.1); /* พื้นหลังโปรไฟล์โปร่งแสง */
        border-radius: 12px;
        padding: 5px 12px;
        margin-left: 20px;
        font-size: 0.95rem;
        color: #e2e8f0; /* สีตัวอักษรโปรไฟล์ */
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .navbar-profile .profile-emoji {
        font-size: 1.3rem;
    }

    @media (max-width: 768px) {
        .student-navbar .navbar-brand {
            font-size: 1.2rem;
        }

        .student-navbar .nav-link {
            font-size: 1rem;
        }

        .navbar-profile {
            margin-top: 10px;
        }
    }
</style>

<nav class="navbar navbar-expand-lg student-navbar sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="student_dashboard.php">
            <span class="emoji">🎯</span> เกมแบบฝึกทักษะวิทยาการคำนวณ ป.4
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbar" aria-controls="studentNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="studentNavbar">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="student_dashboard.php">🏠 หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_learn.php">📖 ทบทวนบทเรียน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php" onclick="return confirm('คุณต้องการออกจากระบบใช่ไหม?')">🚪 ออกจากระบบ</a>
                </li>
                <li class="nav-item navbar-profile">
                    <span class="profile-emoji">👦</span> <?php echo $_SESSION['name']; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>