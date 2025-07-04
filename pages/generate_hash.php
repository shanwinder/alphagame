<?php
// --- ไฟล์: pages/generate_hash.php (ใช้สร้างรหัสผ่านใหม่แล้วลบทิ้ง) ---

// ✅ 1. ตั้งรหัสผ่านใหม่ที่คุณครูต้องการที่นี่ (แนะนำให้ใช้ภาษาอังกฤษและตัวเลข)
$new_password = 'alpha_admin_2025';

// 2. ทำการเข้ารหัสรหัสผ่านใหม่
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// 3. แสดงผลรหัสที่เข้ารหัสแล้วบนหน้าจอ
echo "<h3>เครื่องมือสร้างรหัสผ่านใหม่</h3>";
echo "<p>รหัสผ่านใหม่ที่คุณครูตั้งคือ: <strong>" . htmlspecialchars($new_password) . "</strong></p>";
echo "<p>ให้นำข้อความข้างล่างนี้ทั้งหมด ไปอัปเดตในฐานข้อมูลครับ:</p>";
echo '<textarea rows="4" style="width:100%;" readonly>' . htmlspecialchars($hashed_password) . '</textarea>';

?>