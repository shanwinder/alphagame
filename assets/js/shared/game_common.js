// --- ไฟล์: assets/js/shared/game_common.js (ฉบับสมบูรณ์) ---

// ฟังก์ชันสำหรับอัปเดตคะแนนรวมที่แสดงบน Game Header
window.updateTotalScore = function () {
    // ค้นหา element ที่ใช้แสดงคะแนนรวมจาก ID
    const scoreEl = document.getElementById('total-score');
    // ถ้าไม่พบ element นี้ ให้จบการทำงานของฟังก์ชันทันที
    if (!scoreEl) return;

    // ส่งคำขอไปยัง API เพื่อดึงคะแนนรวมล่าสุด
    fetch('../api/get_total_score.php')
        // แปลงข้อมูลตอบกลับจากเซิร์ฟเวอร์เป็น JSON
        .then(res => res.json())
        // หลังจากได้ข้อมูล JSON แล้ว
        .then(data => {
            // นำคะแนนที่ได้ (หรือ 0 ถ้าไม่มี) มาแสดงผลใน element
            scoreEl.textContent = data.score || 0;
        });
};

// ฟังก์ชันสำหรับแสดง Popup เมื่อผ่านด่าน
// รับ parameter: จำนวนดาวที่ได้, สถานะว่าเป็นโหมด Live หรือไม่, และลิงก์สำหรับด่านถัดไป
window.showSuccessPopup = function (stars_awarded, isLive = false, nextStageLink = '#') {
    // ค้นหา element ของ Modal, ส่วนแสดงดาว, และปุ่มไปด่านถัดไป
    const modal = document.getElementById('successModal');
    const starRatingDiv = document.getElementById('star-rating');
    const nextBtn = document.getElementById("nextStageBtn");
    const modalMessage = document.getElementById("modal-message");

    // ตรวจสอบว่าหา element ทั้งหมดเจหรือไม่ ถ้าไม่เจอให้จบการทำงาน
    if (!modal || !starRatingDiv || !nextBtn || !modalMessage) return;

    // ล้างดาวเก่าทิ้งก่อน
    starRatingDiv.innerHTML = '';
    // สร้างดาวตามจำนวนที่ได้รับ
    for (let i = 0; i < 3; i++) {
        const star = document.createElement('span'); // สร้าง element span
        star.className = 'star-icon'; // กำหนดคลาสให้
        if (i < stars_awarded) {
            star.innerHTML = '★'; // ถ้าเป็นดาวที่ได้ ให้เป็นดาวเต็ม
            star.style.color = '#ffc107'; // กำหนดสีเหลืองทอง
        } else {
            star.innerHTML = '☆'; // ถ้าเป็นดาวที่ไม่ได้ ให้เป็นดาวว่าง
            star.style.color = '#ccc'; // กำหนดสีเทา
        }
        starRatingDiv.appendChild(star); // นำดาวไปแสดงใน div
    }

    // กำหนดลิงก์ให้ปุ่มไปด่านต่อไป
    nextBtn.href = nextStageLink;

    // แสดง Modal ขึ้นมา
    modal.style.display = 'block';

    // ตรวจสอบว่าเป็นโหมด Live หรือไม่
    if (isLive) {
        // ถ้าเป็นโหมด Live
        nextBtn.style.display = 'none'; // ซ่อนปุ่มไปด่านถัดไป
        // เพิ่มข้อความ "รอคุณครู"
        modalMessage.innerHTML += "<br><strong class='text-primary'>ยอดเยี่ยมมาก! รอสัญญาณจากคุณครูเพื่อไปต่อพร้อมเพื่อนๆ นะครับ...</strong>";
    } else {
        // ถ้าเป็นโหมดเล่นคนเดียว
        // เรียกใช้ฟังก์ชันนับถอยหลังหลังจากแสดงดาวเสร็จ (หน่วงเวลาเล็กน้อย)
        setTimeout(triggerAutoNextStage, 1000);
    }
};

// ฟังก์ชันสำหรับปุ่มนับถอยหลัง 10 วินาที
window.triggerAutoNextStage = function () {
    // ค้นหา element ของปุ่ม, ตัวเลขวินาที, และแถบ progress
    const nextBtn = document.getElementById("nextStageBtn");
    const secondsSpan = document.getElementById("seconds");
    const overlay = document.getElementById("next-progress-fill");

    // ถ้าหา element ไม่เจอ ให้จบการทำงาน
    if (!nextBtn || !secondsSpan || !overlay) return;

    // แสดงปุ่มขึ้นมา
    nextBtn.style.display = 'inline-block';
    // กำหนดเวลานับถอยหลังเริ่มต้น
    let count = 10;
    // แสดงตัวเลขเริ่มต้น
    secondsSpan.textContent = count;

    // Reset แถบ Progress bar เพื่อให้ animation เริ่มใหม่ทุกครั้ง
    overlay.style.transition = 'none'; // ล้าง transition เก่าทิ้ง
    overlay.style.width = '0%'; // ตั้งค่าความกว้างเป็น 0%
    void overlay.offsetWidth; // บังคับให้เบราว์เซอร์ re-render

    // เริ่ม Transition ใหม่ ให้แถบ progress ค่อยๆ เต็มใน 10 วินาที
    overlay.style.transition = 'width 10s linear';
    overlay.style.width = '100%';

    // เริ่มต้นการนับเวลาถอยหลัง (ทำงานทุกๆ 1 วินาที)
    const timer = setInterval(() => {
        count--; // ลดค่าเวลานับถอยหลังลง 1
        secondsSpan.textContent = count; // อัปเดตตัวเลขที่แสดง
        if (count < 0) { // เมื่อนับถึง 0 แล้ว
            clearInterval(timer); // หยุดการนับ
            if (nextBtn.href) { // ตรวจสอบว่าปุ่มมีลิงก์หรือไม่
                window.location.href = nextBtn.href; // เปลี่ยนหน้าไปยังลิงก์นั้น
            }
        }
    }, 1000); // 1000 milliseconds = 1 วินาที
};

// Event Listener ที่จะทำงานเมื่อหน้าเว็บโหลดเสร็จสมบูรณ์
document.addEventListener('DOMContentLoaded', () => {
    // ตรวจสอบว่ามี element สำหรับแสดงคะแนนรวมหรือไม่ ถ้ามีให้อัปเดต
    if (document.getElementById('total-score')) {
        updateTotalScore();
    }
});