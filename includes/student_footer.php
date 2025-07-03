<?php
// --- ไฟล์: includes/student_footer.php (ฉบับปรับปรุงธีมอวกาศ) ---
?>
<footer>
    <div class="footer-box">
        <p class="mb-1">
            พัฒนาระบบโดย <strong>นายณัฐดนัย สุวรรณไตรย์</strong><br>
            ครู โรงเรียนบ้านนาอุดม | สังกัดสำนักงานเขตพื้นที่การศึกษาประถมศึกษามุกดาหาร
        </p>
        <p class="text-muted mb-0">
            &copy; <?= date("Y") ?> Developed by Mr. Natdanai Suwannatrai. All rights reserved.
        </p>
    </div>
</footer>

<style>
    footer {
        width: 100%;
        margin-top: auto; /* ดันให้อยู่ล่างสุดเสมอ */
        padding: 20px 0;
        text-align: center;
        position: relative;
        z-index: 2; /* ให้อยู่เหนือดาว */
    }

    .footer-box {
        /* ทำให้พื้นหลังโปร่งใส และไม่มีเงา */
        background: transparent;
        margin: auto;
        padding: 15px 10px;
        border-radius: 0;
        max-width: 800px;
        font-size: 0.9rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2); /* เพิ่มเส้นขีดคั่นบางๆ */
    }

    .footer-box p {
        margin-bottom: 0.25rem;
        color: #94a3b8; /* สีเทาอมฟ้า อ่านง่ายบนพื้นหลังเข้ม */
    }

    .footer-box strong {
        color: #50e3c2; /* สีเขียว Sci-fi */
        font-weight: bold;
    }

    .footer-box .text-muted {
        color: #94a3b8 !important; /* ใช้สีเดียวกันเพื่อให้ดูกลมกลืน */
        opacity: 0.7;
    }
</style>