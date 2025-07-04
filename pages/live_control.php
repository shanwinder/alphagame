<?php
// --- ไฟล์: pages/live_control.php (ไฟล์ใหม่) ---
require_once '../includes/db.php';
require_once '../includes/header.php'; // ใช้ header ของ Admin
require_once '../includes/auth.php';
requireAdmin();

// ดึงรหัสห้องจาก URL
$session_code = $_GET['code'] ?? null;
if (!$session_code) {
    header('Location: dashboard.php?error=nocode');
    exit();
}

// ดึงข้อมูลเบื้องต้นของห้อง
$stmt = $conn->prepare("SELECT * FROM live_sessions WHERE session_code = ? AND admin_id = ?");
$stmt->bind_param("si", $session_code, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: dashboard.php?error=session_not_found');
    exit();
}
$session = $result->fetch_assoc();
?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-broadcast-tower"></i> ห้องควบคุม Live Session</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h3>รหัสเข้าร่วม: <span class="badge bg-success fs-3"><?= htmlspecialchars($session_code) ?></span>
                    </h3>
                    <div id="game-status" class="mt-3">
                        <p class="fs-5"><strong>สถานะ:</strong> <span id="status-text"
                                class="fw-bold">กำลังโหลด...</span></p>
                        <p class="fs-5"><strong>ด่านปัจจุบัน:</strong> <span id="stage-text" class="fw-bold">--</span>
                        </p>
                    </div>
                    <hr>
                    <h4><i class="fas fa-gamepad"></i> แผงควบคุม</h4>
                    <div id="controls" class="d-flex flex-wrap gap-2">
                        <button id="btn-start" class="btn btn-lg btn-success" onclick="sendAction('start_game')"><i
                                class="fas fa-play"></i> เริ่มเกม</button>
                        <button id="btn-next" class="btn btn-lg btn-info" onclick="sendAction('next_stage')"><i
                                class="fas fa-forward"></i> ไปด่านถัดไป</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 class="text-center"><i class="fas fa-users"></i> ผู้เข้าร่วม (<span id="player-count">0</span>
                        คน)</h4>
                    <ul id="player-list" class="list-group" style="max-height: 300px; overflow-y: auto;">
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const sessionCode = "<?= $session_code ?>";

    // ฟังก์ชันสำหรับส่งคำสั่งไปที่ API
    function sendAction(action) {
        if (!confirm(`คุณแน่ใจหรือไม่ที่จะ "${action}" ?`)) return;

        const formData = new FormData();
        formData.append('code', sessionCode);
        formData.append('action', action);

        fetch('../api/session_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('ส่งคำสั่งสำเร็จ!');
                    updateSessionView(); // อัปเดตหน้าจอทันที
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            });
    }

    // ฟังก์ชันสำหรับอัปเดตข้อมูลในหน้านี้ (Player List, สถานะ)
    function updateSessionView() {
        fetch(`../api/live_status.php?code=${sessionCode}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // อัปเดตสถานะและด่าน
                document.getElementById('status-text').textContent = data.status;
                document.getElementById('stage-text').textContent = data.current_stage_id || 'ยังไม่เริ่ม';

                // อัปเดตรายชื่อผู้เล่น
                const playerList = document.getElementById('player-list');
                playerList.innerHTML = '';
                document.getElementById('player-count').textContent = data.players.length;
                data.players.forEach(player => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent = `🧑‍🚀 ${player.name}`;
                    playerList.appendChild(li);
                });

                // ซ่อน/แสดงปุ่มตามสถานะ
                const btnStart = document.getElementById('btn-start');
                const btnNext = document.getElementById('btn-next');

                if (data.status === 'waiting') {
                    btnStart.style.display = 'inline-block';
                    btnNext.style.display = 'none';
                } else if (data.status === 'active') {
                    btnStart.style.display = 'none';
                    btnNext.style.display = 'inline-block';
                } else {
                    btnStart.style.display = 'none';
                    btnNext.style.display = 'none';
                }
            });
    }

    // เริ่มการ Polling ทุกๆ 5 วินาทีเพื่ออัปเดตหน้าจอ
    setInterval(updateSessionView, 5000);

    // เรียกครั้งแรกทันทีที่หน้าโหลด
    updateSessionView();
</script>

<?php include '../includes/footer.php'; ?>