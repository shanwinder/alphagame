<?php
// --- ไฟล์: pages/student_dashboard.php (ฉบับอธิบายโค้ดทั้งหมด) ---

session_start(); // เริ่มต้นการใช้งาน session เพื่อเก็บข้อมูลผู้ใช้ที่ล็อกอิน
require_once '../includes/db.php'; // เรียกใช้ไฟล์สำหรับเชื่อมต่อฐานข้อมูล
require_once '../includes/auth.php'; // เรียกใช้ไฟล์สำหรับฟังก์ชันตรวจสอบสิทธิ์
requireStudent(); // เรียกใช้ฟังก์ชันเพื่อตรวจสอบว่าผู้ใช้ที่ล็อกอินอยู่เป็นนักเรียนหรือไม่

$user_id = $_SESSION['user_id']; // ดึง user_id ของนักเรียนที่ล็อกอินอยู่จาก session มาเก็บในตัวแปร
$name = $_SESSION['name']; // ดึงชื่อของนักเรียนจาก session มาเก็บในตัวแปร

// สร้าง Array เพื่อเก็บข้อมูลของบทเรียนทั้งหมดในเกม
$games = [
    1 => ['code' => 'Logic', 'title' => 'บทที่ 1: เหตุผลเชิงตรรกะ'], // ข้อมูลบทที่ 1
    2 => ['code' => 'Algorithm', 'title' => 'บทที่ 2: อัลกอริทึม'], // ข้อมูลบทที่ 2
    3 => ['code' => 'Text', 'title' => 'บทที่ 3: อัลกอริทึมด้วยข้อความ'], // ข้อมูลบทที่ 3
    4 => ['code' => 'Pseudocode', 'title' => 'บทที่ 4: รหัสจำลอง'], // ข้อมูลบทที่ 4
    5 => ['code' => 'Flowchart', 'title' => 'บทที่ 5: ผังงาน (Flowchart)'], // ข้อมูลบทที่ 5
];

// ฟังก์ชันสำหรับดึงข้อมูลความคืบหน้าของแต่ละบทเรียน
function getGameProgress($conn, $user_id, $chapter_id)
{
    // เตรียมคำสั่ง SQL เพื่อดึง ID ของด่านทั้งหมดที่อยู่ในบทเรียน (chapter_id) ที่ระบุ
    $stmt_stages = $conn->prepare("SELECT id FROM stages WHERE chapter_id = ?");
    // ผูกค่าตัวแปร $chapter_id เข้ากับเครื่องหมาย ? ในคำสั่ง SQL
    $stmt_stages->bind_param("i", $chapter_id);
    // สั่งให้คำสั่ง SQL ทำงาน
    $stmt_stages->execute();
    // รับผลลัพธ์จากการ query
    $result_stages = $stmt_stages->get_result();
    // สร้าง Array ว่างสำหรับเก็บ ID ของด่าน
    $stage_ids = [];
    // วนลูปเพื่อดึง ID ของแต่ละด่านมาเก็บใน Array $stage_ids
    while ($row = $result_stages->fetch_assoc()) {
        $stage_ids[] = $row['id'];
    }
    // ปิด statement เพื่อคืนทรัพยากร
    $stmt_stages->close();

    // นับจำนวนด่านทั้งหมดในบทเรียนนี้
    $total_stages_in_chapter = count($stage_ids);

    // ถ้าในบทเรียนนี้ยังไม่มีด่าน ให้คืนค่าเริ่มต้นเป็น 0 ทั้งหมด
    if (empty($stage_ids)) {
        return ['passed' => 0, 'total' => $total_stages_in_chapter, 'total_stars' => 0];
    }

    // สร้าง string ของเครื่องหมาย ? ตามจำนวนด่านสำหรับใช้ใน SQL IN clause (เช่น ?,?,?)
    $placeholders = implode(',', array_fill(0, count($stage_ids), '?'));
    // สร้าง string ของประเภทข้อมูลสำหรับ bind_param (เช่น 'iii' ถ้ามี 3 ด่าน)
    $types = 'i' . str_repeat('i', count($stage_ids));
    // รวม user_id และ ID ของด่านทั้งหมดไว้ใน Array เดียวกันเพื่อใช้ bind_param
    $params = array_merge([$user_id], $stage_ids);

    // เตรียมคำสั่ง SQL เพื่อนับจำนวนด่านที่ผ่านและรวมจำนวนดาวทั้งหมด
    $sql = "SELECT COUNT(id) AS passed_stages, SUM(stars_awarded) AS total_stars
            FROM progress
            WHERE user_id = ? AND stage_id IN ($placeholders) AND completed_at IS NOT NULL";

    // เตรียมคำสั่ง SQL กับฐานข้อมูล
    $stmt_progress = $conn->prepare($sql);
    // ตรวจสอบว่าการ prepare สำเร็จหรือไม่ ถ้าไม่สำเร็จให้คืนค่าเริ่มต้น
    if ($stmt_progress === false) {
        return ['passed' => 0, 'total' => $total_stages_in_chapter, 'total_stars' => 0];
    }

    // สร้าง Array สำหรับ bind_param แบบไดนามิก
    $bind_names = [$types];
    // วนลูปเพื่อสร้าง reference ของแต่ละ parameter
    for ($i = 0; $i < count($params); $i++) {
        $bind_names[] = &$params[$i];
    }
    // เรียกใช้ bind_param ด้วย call_user_func_array เพื่อรองรับ parameter แบบไดนามิก
    call_user_func_array([$stmt_progress, 'bind_param'], $bind_names);
    // สั่งให้คำสั่ง SQL ทำงาน
    $stmt_progress->execute();
    // รับผลลัพธ์
    $result_progress = $stmt_progress->get_result();
    // ดึงข้อมูลผลลัพธ์มาเก็บในรูปแบบ Array
    $progress_data = $result_progress->fetch_assoc();
    // ปิด statement
    $stmt_progress->close();

    // คืนค่าผลลัพธ์ในรูปแบบ Array
    return [
        'passed' => (int) ($progress_data['passed_stages'] ?? 0), // จำนวนด่านที่ผ่าน (ถ้าไม่มีให้เป็น 0)
        'total' => $total_stages_in_chapter, // จำนวนด่านทั้งหมดในบท
        'total_stars' => (int) ($progress_data['total_stars'] ?? 0) // จำนวนดาวทั้งหมด (ถ้าไม่มีให้เป็น 0)
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
        /* แท็กเริ่มต้นสำหรับเขียน CSS ภายในไฟล์ HTML */
        body {
            /* กำหนดสไตล์ให้กับแท็ก body ทั้งหมด */
            font-family: 'Kanit', sans-serif;
            /* กำหนดฟอนต์หลัก */
            background-color: #0d1b2a;
            /* กำหนดสีพื้นหลัง */
            color: white;
            /* กำหนดสีตัวอักษรหลัก */
            min-height: 100vh;
            /* กำหนดความสูงขั้นต่ำให้เต็มหน้าจอ */
            position: relative;
            /* กำหนด position เพื่อให้ element ลูกที่เป็น absolute อ้างอิงได้ */
            overflow-x: hidden;
            /* ซ่อน scrollbar แนวนอน */
        }

        body::before {
            /* สร้าง pseudo-element ก่อน body เพื่อทำเป็น Layer พื้นหลัง */
            content: '';
            /* กำหนด content ให้เป็นค่าว่าง */
            position: fixed;
            /* กำหนดตำแหน่งแบบ fixed ไม่เลื่อนตามการ scroll */
            bottom: 0;
            /* ชิดขอบล่าง */
            left: 0;
            /* ชิดขอบซ้าย */
            width: 100%;
            /* ความกว้างเต็ม 100% */
            height: 100%;
            /* ความสูงเต็ม 100% */
            background-image: url('../assets/img/bottom_planet.png');
            /* กำหนดภาพพื้นหลัง */
            background-repeat: no-repeat;
            /* ไม่ให้ภาพพื้นหลังซ้ำ */
            background-position: bottom left;
            /* จัดตำแหน่งภาพพื้นหลังที่มุมซ้ายล่าง */
            background-size: 75%;
            /* กำหนดขนาดภาพพื้นหลัง */
            z-index: 1;
            /* กำหนดลำดับการซ้อน ให้ Layer นี้อยู่หลังเนื้อหา */
            pointer-events: none;
            /* ทำให้ element นี้ไม่ตอบสนองต่อการคลิก */
            opacity: 0.9;
            /* กำหนดความโปร่งใส */
        }

        .star {
            /* กำหนดสไตล์ให้กับ element ที่มีคลาส .star */
            position: absolute;
            /* กำหนดตำแหน่งแบบ absolute */
            background: white;
            /* กำหนดสีพื้นหลัง */
            border-radius: 50%;
            /* ทำให้เป็นวงกลม */
            animation: twinkle 3s linear infinite, drift 200s linear infinite;
            /* กำหนดให้มี 2 animations ทำงานพร้อมกัน */
        }

        @keyframes drift {

            /* กำหนดการเคลื่อนไหวชื่อ drift */
            from {
                transform: translateY(-20vh) translateX(5vw);
            }

            /* จุดเริ่มต้นของ animation */
            to {
                transform: translateY(120vh) translateX(-5vw);
            }

            /* จุดสิ้นสุดของ animation */
        }

        @keyframes twinkle {

            /* กำหนดการเคลื่อนไหวชื่อ twinkle */
            0%,
            100% {
                opacity: 0.7;
            }

            /* จุดเริ่มต้นและสิ้นสุด ให้มีความโปร่งใส 0.7 */
            50% {
                opacity: 1;
                transform: scale(1.2);
            }

            /* จุดกึ่งกลาง ให้มีความโปร่งใส 1 และขยายขนาด */
        }

        .star.type1 {
            width: 1px;
            height: 1px;
            background: #FFF;
            box-shadow: 0 0 6px #FFF;
        }

        /* สไตล์ของดาวประเภทที่ 1 */
        .star.type2 {
            width: 2px;
            height: 2px;
            background: #90e0ef;
            box-shadow: 0 0 8px #90e0ef;
        }

        /* สไตล์ของดาวประเภทที่ 2 */
        .star.type3 {
            width: 3px;
            height: 3px;
            background: #f9c74f;
            box-shadow: 0 0 10px #f9c74f;
        }

        /* สไตล์ของดาวประเภทที่ 3 */

        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        /* กำหนดให้เนื้อหาหลักอยู่บนสุด */
        .welcome {
            font-size: 2.3rem;
            font-weight: 700;
            color: #f9c74f;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.4);
        }

        /* สไตล์ของข้อความต้อนรับ */
        .join-live-btn {
            font-size: 1.2rem;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        /* สไตล์ของปุ่มเข้าร่วม Live */
        .join-live-btn:hover {
            transform: scale(1.05);
        }

        /* สไตล์ของปุ่มเมื่อเอาเมาส์ไปชี้ */
        .game-card {
            /* สไตล์ของ Card แสดงบทเรียน */
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
            /* พื้นหลังไล่ระดับสีและโปร่งแสง */
            backdrop-filter: blur(10px);
            /* เอฟเฟกต์เบลอพื้นหลังของ card */
            border-radius: 20px;
            /* ขอบมน */
            padding: 30px;
            /* ระยะห่างภายใน card */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            /* เงาของ card */
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            /* transition เมื่อมีการเปลี่ยนแปลง */
            text-decoration: none;
            /* ลบเส้นใต้ลิงก์ */
            color: white;
            /* สีข้อความ */
            display: block;
            /* แสดงผลเป็น block */
            border: 1px solid rgba(255, 255, 255, 0.25);
            /* เส้นขอบ */
        }

        .game-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        /* สไตล์ของ card เมื่อเอาเมาส์ไปชี้ */
        .game-card h4 {
            color: #FFDA63;
            margin-top: 0;
            margin-bottom: 1.2rem;
            font-size: 1.8rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* สไตล์ของหัวข้อใน card */
        .progress-info {
            font-size: 1.1rem;
        }

        /* สไตล์ของส่วนข้อมูลความคืบหน้า */
        .progress {
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 25px;
            height: 30px;
            margin-bottom: 1rem;
        }

        /* สไตล์ของแถบ progress bar (พื้นหลัง) */
        .progress-bar {
            background: linear-gradient(to right, #50E3C2, #00C6FF);
            font-weight: bold;
            border-radius: 25px;
            color: #222;
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.3);
        }

        /* สไตล์ของแถบ progress bar (แถบที่เคลื่อนที่) */
        .game-card strong {
            color: #F97F51;
            font-size: 1.2em;
            text-shadow: 0.5px 0.5px 1px rgba(0, 0, 0, 0.5);
        }

        /* สไตล์ของข้อความที่เน้น (ตัวเลข) */
        .game-card .icon-chapter {
            font-size: 2rem;
            margin-right: 15px;
            vertical-align: middle;
            color: #A78BFA;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
        }

        /* สไตล์ของไอคอนบทเรียน */
    </style>
</head>

<body> <?php include '../includes/student_header.php'; ?>
    <div class="content-wrapper">
        <main class="container my-5">
            <div class="welcome text-center mb-4">เลือกบทเรียนเพื่อเริ่มการผจญภัยได้เลย! ✨</div>
            <div class="text-center mb-5"> <a href="join_live.php" class="btn btn-warning join-live-btn shadow"> <i
                        class="fas fa-users me-2"></i> เข้าร่วมห้องเรียน Live </a>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center my-4">
                <h5>หรือเลือกเล่นด้วยตัวเอง:</h5>
            </div>
            <div class="row g-4">
                <?php foreach ($games as $chapter_id => $game): // วนลูป Array $games เพื่อสร้าง Card ของแต่ละบทเรียน ?>
                    <?php // บล็อกโค้ด PHP สำหรับเตรียมข้อมูลในแต่ละรอบของลูป
                        $progress = getGameProgress($conn, $user_id, $chapter_id); // เรียกใช้ฟังก์ชันเพื่อดึงข้อมูลความคืบหน้า
                        $percent = ($progress['total'] > 0) ? round(($progress['passed'] / $progress['total']) * 100) : 0; // คำนวณเปอร์เซ็นต์
                        $first_stage_in_chapter_id = (($chapter_id - 1) * 10) + 1; // คำนวณ ID ของด่านแรกในบท
                        $link_url = "play.php?stage_id={$first_stage_in_chapter_id}"; // สร้าง URL สำหรับลิงก์
                        ?>
                    <div class="col-md-6 col-lg-4"> <a href="<?= $link_url ?>" class="game-card">
                            <h4><i class="fas fa-rocket icon-chapter"></i> <?= htmlspecialchars($game['title']) ?></h4>
                            <div class="progress-info">
                                <p class="mb-2">ความคืบหน้า: <strong><?= $progress['passed'] ?></strong> /
                                    <?= $progress['total'] ?> ด่าน</p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;"
                                        aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= $percent ?>%</div>
                                </div>
                                <p class="mt-2 mb-0"><strong><i class="fas fa-star text-warning me-1"></i> ดาวสะสม:</strong>
                                    <?= $progress['total_stars'] ?> ดวง</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
        <?php include '../includes/student_footer.php'; ?>
    </div>
    <script> /* แท็กเริ่มต้นสำหรับเขียน JavaScript */
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html> 