<?php
// --- ไฟล์: includes/header.php (ฉบับปรับปรุง) ---

// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>ระบบแบบฝึกทักษะวิทยาการคำนวณ ป.4</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background-color: #f8f9fa; /* เปลี่ยนสีพื้นหลังให้อ่านง่ายขึ้น */
      min-height: 100vh;
    }
    .navbar {
        /* เปลี่ยนสี Navbar ให้นุ่มนวลขึ้น */
      background: linear-gradient(to right, #4a90e2, #50e3c2);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar-brand,
    .nav-link {
      color: white !important;
      font-weight: bold;
    }
    .navbar-brand:hover,
    .nav-link:hover {
      text-decoration: none;
      opacity: 0.9;
    }
    h1, h2, h3 {
        color: #343a40;
    }
    .table {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="fas fa-robot"></i> Alpha Game Dashboard</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarAdmin">
        <ul class="navbar-nav ms-auto">
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <span class="nav-link"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['name']); ?> (ผู้ดูแลระบบ)</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">เข้าสู่ระบบ</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">