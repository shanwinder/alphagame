<?php
// --- ไฟล์: includes/game_header.php (ฉบับสมบูรณ์) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div id="top-bar">
    <div class="info-text">
        👦 ผู้เล่น: <strong><?= $_SESSION['name'] ?? 'ทดสอบ' ?></strong> |
        🧩 เกม: <strong><?= $game_title ?? 'ไม่ระบุ' ?></strong> |
        🧠 ด่านที่: <strong><?= $stage_id ?? 'N/A' ?></strong> |
        🌟 คะแนนรวม: <strong id="total-score">0</strong>
    </div>

    <div class="top-bar-buttons">
        <a href="student_dashboard.php" class="btn-dashboard">🏠 กลับแดชบอร์ด</a>
    </div>
</div>