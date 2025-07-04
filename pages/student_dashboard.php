<?php
// --- ไฟล์: pages/student_dashboard.php (ฉบับสมบูรณ์และสอดคล้องกับฐานข้อมูลปัจจุบัน) ---

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireStudent();
require_once '../includes/access_control.php'; // ✅ เพิ่มบรรทัดนี้เข้ามา

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$games = [
    1 => ['code' => 'Logic', 'title' => 'บทที่ 1: เหตุผลเชิงตรรกะ'],
    2 => ['code' => 'Algorithm', 'title' => 'บทที่ 2: อัลกอริทึม'],
    3 => ['code' => 'Text', 'title' => 'บทที่ 3: อัลกอริทึมด้วยข้อความ'],
    4 => ['code' => 'Pseudocode', 'title' => 'บทที่ 4: รหัสจำลอง'],
    5 => ['code' => 'Flowchart', 'title' => 'บทที่ 5: ผังงาน (Flowchart)'],
];

// ฟังก์ชันดึงข้อมูลความคืบหน้า (ฉบับเสถียรและทบทวนแล้ว)
function getGameProgress($conn, $user_id, $chapter_id)
{
    // ดึง ID ของด่านทั้งหมดในบทนั้นๆ
    $stmt_stages = $conn->prepare("SELECT id FROM stages WHERE chapter_id = ?");
    $stmt_stages->bind_param("i", $chapter_id);
    $stmt_stages->execute();
    $result_stages = $stmt_stages->get_result();
    $stage_ids = [];
    while ($row = $result_stages->fetch_assoc()) {
        $stage_ids[] = $row['id'];
    }
    $stmt_stages->close();

    $total_stages_in_chapter = count($stage_ids);

    if (empty($stage_ids)) {
        return ['passed' => 0, 'total' => $total_stages_in_chapter, 'total_stars' => 0];
    }

    // เตรียม Query สำหรับดึงข้อมูลความคืบหน้า
    $placeholders = implode(',', array_fill(0, count($stage_ids), '?'));
    $types = 'i' . str_repeat('i', count($stage_ids));
    $params = array_merge([$user_id], $stage_ids);

    // ✅ ใช้ชื่อคอลัมน์ `stars_awarded` ที่ถูกต้องตามฐานข้อมูล
    $sql = "SELECT COUNT(id) AS passed_stages, SUM(stars_awarded) AS total_stars
            FROM progress
            WHERE user_id = ? AND stage_id IN ($placeholders) AND completed_at IS NOT NULL";

    $stmt_progress = $conn->prepare($sql);

    // ตรวจสอบว่า prepare statement สำเร็จหรือไม่
    if ($stmt_progress === false) {
        // สามารถเพิ่มการจัดการ error ตรงนี้ได้ถ้าต้องการ
        return ['passed' => 0, 'total' => $total_stages_in_chapter, 'total_stars' => 0];
    }

    $bind_names = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bind_names[] = &$params[$i];
    }
    call_user_func_array([$stmt_progress, 'bind_param'], $bind_names);
    $stmt_progress->execute();
    $result_progress = $stmt_progress->get_result();
    $progress_data = $result_progress->fetch_assoc();
    $stmt_progress->close();

    return [
        'passed' => (int) ($progress_data['passed_stages'] ?? 0),
        'total' => $total_stages_in_chapter,
        'total_stars' => (int) ($progress_data['total_stars'] ?? 0)
    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แดชบอร์ดนักเรียน - การผจญภัยของอัลฟ่า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        /* ... CSS ทั้งหมดสำหรับธีมดาวยังคงเหมือนเดิม ... */
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #0a192f;
            color: white;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle linear infinite, drift linear infinite;
        }

        @keyframes drift {
            from {
                transform: translateY(-10vh);
            }

            to {
                transform: translateY(110vh);
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
            box-shadow: 0 0 6px #FFF;
        }

        .star.type2 {
            width: 2px;
            height: 2px;
            background: #90e0ef;
            box-shadow: 0 0 8px #90e0ef;
        }

        .star.type3 {
            width: 3px;
            height: 3px;
            background: #f9c74f;
            box-shadow: 0 0 10px #f9c74f;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        .welcome {
            font-size: 2.3rem;
            font-weight: 700;
            color: #f9c74f;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.4);
        }

        .game-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            text-decoration: none;
            color: white;
            display: block;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .game-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .game-card h4 {
            color: #50e3c2;
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .progress-info {
            font-size: 1rem;
        }

        .progress {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            height: 25px;
        }

        .progress-bar {
            background: linear-gradient(to right, #50e3c2, #00c6ff);
            font-weight: bold;
        }

        .game-card strong {
            color: #f5a623;
            font-size: 1.1em;
        }
    </style>
</head>

<body>
    <?php include '../includes/student_header.php'; ?>
    <div class="content-wrapper">
        <main class="container my-5">
            <div class="welcome text-center mb-5">เลือกบทเรียนเพื่อเริ่มการผจญภัยได้เลย! ✨</div>
            <div class="row g-4">
                <?php foreach ($games as $chapter_id => $game):
                    $progress = getGameProgress($conn, $user_id, $chapter_id);
                    $percent = ($progress['total'] > 0) ? round(($progress['passed'] / $progress['total']) * 100) : 0;
                    $first_stage_in_chapter_id = (($chapter_id - 1) * 10) + 1;
                    $link_url = "play.php?stage_id={$first_stage_in_chapter_id}";
                ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="<?= $link_url ?>" class="game-card">
                            <h4><i class="fas fa-rocket me-2 text-info"></i> <?= htmlspecialchars($game['title']) ?></h4>
                            <div class="progress-info">
                                <p class="mb-1">ความคืบหน้า: <strong><?= $progress['passed'] ?></strong> / <?= $progress['total'] ?> ด่าน</p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"><?= $percent ?>%</div>
                                </div>
                                <p class="mt-2 mb-0"><strong><i class="fas fa-star text-warning me-1"></i> ดาวสะสม:</strong> <?= $progress['total_stars'] ?> ดวง</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
        <?php include '../includes/student_footer.php'; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... JavaScript สำหรับสร้างดาว (เหมือนเดิม) ...
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>