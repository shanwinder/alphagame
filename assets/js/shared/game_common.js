// --- ไฟล์: assets/js/shared/game_common.js (ฉบับสมบูรณ์) ---

// ฟังก์ชันสำหรับอัปเดตคะแนนรวมบน Top Bar (ไม่ต้องแก้ไข)
window.updateTotalScore = function() {
    // ... โค้ดส่วนนี้เหมือนเดิม ...
};

// ✅ ฟังก์ชันแสดง Popup และเริ่มนับถอยหลัง
window.showSuccessPopup = function(stars_awarded) {
    const modal = document.getElementById('successModal');
    const starIcons = document.querySelectorAll('#star-rating .star-icon');
    
    // แสดงดาวตามที่ได้รับ
    starIcons.forEach((star, index) => {
        if (index < stars_awarded) {
            star.innerHTML = '★'; // ดาวเต็ม
            star.style.color = '#ffc107';
        } else {
            star.innerHTML = '☆'; // ดาวว่าง
            star.style.color = '#ccc';
        }
    });

    modal.style.display = 'block'; // แสดง Popup

    // เรียกใช้ฟังก์ชันนับถอยหลัง
    triggerAutoNextStage();
};

// ✅ ฟังก์ชันปุ่มนับถอยหลัง (นำของเดิมกลับมา)
window.triggerAutoNextStage = function() {
    const nextBtn = document.getElementById("nextStageBtn");
    const secondsSpan = document.getElementById("seconds");
    const overlay = document.getElementById("next-progress-fill");

    if (!nextBtn || !secondsSpan || !overlay) return;

    nextBtn.style.display = 'inline-block'; // แสดงปุ่ม
    let count = 10;
    secondsSpan.textContent = count;

    // Reset progress bar
    overlay.style.transition = 'none';
    overlay.style.width = '0%';
    void overlay.offsetWidth; 

    // Start new transition
    overlay.style.transition = 'width 10s linear';
    overlay.style.width = '100%';

    const timer = setInterval(() => {
        count--;
        secondsSpan.textContent = count;
        if (count <= 0) {
            clearInterval(timer);
            if (nextBtn.href) {
                window.location.href = nextBtn.href;
            }
        }
    }, 1000);
};

// ... โค้ดอื่นๆ ที่อาจจะมี ...