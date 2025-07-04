// --- ไฟล์: includes/game_header.php ---
<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<div id="top-bar">
    <div class="info-text">
        <span>🧑‍🚀 ผู้เล่น: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'ทดสอบ') ?></strong></span> |
        <span>🧩 บทเรียน: <strong><?= htmlspecialchars($game_title ?? 'ไม่ระบุ') ?></strong></span> |
        <span>🚩 ด่านที่: <strong><?= htmlspecialchars($stage_id ?? 'N/A') ?></strong></span>
    </div>
    <div class="top-bar-buttons"><a href="student_dashboard.php" class="btn-dashboard">🏠 กลับแดชบอร์ด</a></div>
</div>