// --- ไฟล์: assets/js/stage1_logic.js ---

// 1. ตั้งค่าพื้นฐานของเกม (Configuration)
const config = {
    type: Phaser.AUTO,         // ให้ Phaser เลือกวิธีแสดงผลที่เหมาะสมที่สุด (Canvas หรือ WebGL)
    width: 900,                // ความกว้างของพื้นที่เกม
    height: 600,               // ความสูงของพื้นที่เกม
    backgroundColor: '#ffffff', // เพิ่มสีพื้นหลังเริ่มต้น
    parent: 'game-container',  // บอกให้เกมไปแสดงผลใน div ที่มี id="game-container"
    scene: {
        preload: preload,      // ฟังก์ชันสำหรับโหลดทรัพยากร
        create: create         // ฟังก์ชันสำหรับสร้างองค์ประกอบของเกม
    }
};

// 2. สร้างเกม Phaser ขึ้นมาใหม่
const game = new Phaser.Game(config);


// 3. ฟังก์ชัน Preload: โหลดภาพและเสียงทั้งหมดที่เราต้องใช้ก่อนเริ่มเกม
function preload() {
    // --- โหลดภาพ ---
    // โหลดภาพพื้นหลัง (คุณครูสามารถเปลี่ยน URL ได้)
    this.load.image('background', '../assets/img/bg2-st1.png');

    // โหลดภาพอัลฟ่า (จากไฟล์ที่คุณครูอัปโหลด)
    this.load.image('alpha', '../assets/img/alpha_front.png');

    // โหลดภาพสัญญาณ (สมมติว่าเราได้ตัดเป็นไฟล์แยกไว้แล้ว)
    // **หมายเหตุ:** เพื่อให้โค้ดนี้ทำงานได้ คุณครูต้องมีไฟล์ภาพเหล่านี้ในโฟลเดอร์ assets/img/ ก่อนนะครับ
    this.load.image('signal_triangle', '../assets/img/signal_triangle.png');
    this.load.image('signal_square', '../assets/img/signal_square.png');
    this.load.image('signal_circle', '../assets/img/signal_circle.png');
    this.load.image('signal_star', '../assets/img/signal_star.png'); // โหลดภาพเพิ่มเติม

    // --- โหลดเสียง (ถ้ามี) ---
     this.load.audio('correct_sound', '../assets/sound/correct.mp3');
     this.load.audio('wrong_sound', '../assets/sound/wrong.mp3');
}

// 4. ฟังก์ชัน Create: สร้างฉากและองค์ประกอบต่างๆ ของเกมเมื่อโหลดเสร็จ
function create() {
    // --- ตั้งค่าฉาก ---
    this.add.image(450, 300, 'background').setScale(0.7);
    this.add.sprite(120, 480, 'alpha').setScale(0.4);

    // --- ส่วนของ Logic เกม ---

    // สร้างกล่องสำหรับโจทย์
    this.add.graphics().fillStyle(0xfffbe6, 0.9).fillRoundedRect(300, 40, 300, 180, 20);
    this.add.text(450, 60, 'โจทย์ต้นแบบ', { fontSize: '28px', color: '#b45309', fontFamily: 'Kanit' }).setOrigin(0.5);

    // สร้างกล่องสำหรับตัวเลือก
    this.add.graphics().fillStyle(0xe0f2fe, 0.9).fillRoundedRect(100, 300, 700, 250, 20);
    this.add.text(450, 320, 'เลือกภาพที่เหมือนกับโจทย์', { fontSize: '24px', color: '#0c4a6e', fontFamily: 'Kanit' }).setOrigin(0.5);

    // --- การสร้างโจทย์และตัวเลือกแบบไดนามิก ---

    // คลังของสัญญาณทั้งหมด
    const all_patterns = ['signal_triangle', 'signal_square', 'signal_circle', 'signal_star'];

    // สุ่มเลือกคำตอบที่ถูกต้องสำหรับด่านนี้
    const correctAnswerKey = Phaser.Utils.Array.GetRandom(all_patterns);

    // แสดงโจทย์ (คำตอบที่ถูกต้อง)
    this.add.image(450, 150, correctAnswerKey).setScale(0.5);

    // สร้างชุดตัวเลือก
    let choices = [correctAnswerKey]; // ใส่คำตอบที่ถูกไว้ก่อน
    // วนลูปเพื่อหาคำตอบที่ผิด 2 อันที่ไม่ซ้ำกับคำตอบที่ถูก และไม่ซ้ำกันเอง
    while (choices.length < 3) {
        let randomChoice = Phaser.Utils.Array.GetRandom(all_patterns);
        if (!choices.includes(randomChoice)) {
            choices.push(randomChoice);
        }
    }

    // สลับลำดับตัวเลือกเพื่อไม่ให้คำตอบถูกอยู่ตำแหน่งเดิมตลอด
    Phaser.Utils.Array.Shuffle(choices);

    // วนลูปเพื่อสร้างปุ่มตัวเลือก
    for (let i = 0; i < choices.length; i++) {
        let x = 250 + (i * 200);
        let y = 450;
        let choiceKey = choices[i];

        // สร้างปุ่มจากรูปภาพ และทำให้มัน "คลิกได้"
        const btn = this.add.image(x, y, choiceKey).setScale(0.4).setInteractive({ useHandCursor: true });
        
        // เมื่อคลิกที่ปุ่ม
        btn.on('pointerdown', () => {
            if (this.game_over) return; // ป้องกันการคลิกซ้ำหลังจากเกมจบแล้ว

            if (choiceKey === correctAnswerKey) {
                // ถ้าคำตอบถูก
                this.game_over = true; // ตั้งค่าสถานะว่าเกมจบแล้ว

                // this.sound.play('correct_sound');
                
                // แสดงผลว่าทำถูก
                btn.setTint(0x00ff00); // ทำให้ปุ่มเป็นสีเขียว

                // --- ส่วนเชื่อมต่อกับ Backend ---
                const score = 100; // คะแนนเต็ม
                const formData = new FormData();
                formData.append('stage_id', CURRENT_STAGE_ID); // CURRENT_STAGE_ID มาจากไฟล์ play.php
                formData.append('score', score);

                fetch('../api/submit_stage_score.php', {
                    method: 'POST', body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // เรียกใช้ฟังก์ชัน Popup จาก game_common.js โดยส่งจำนวนดาวที่ได้จาก API ไปด้วย
                        window.showSuccessPopup(data.stars);
                    } else {
                        alert('เกิดข้อผิดพลาดในการบันทึกคะแนน: ' + data.message);
                        this.game_over = false; // ถ้าบันทึกไม่สำเร็จ ให้เล่นต่อได้
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
                    this.game_over = false; // ถ้าเชื่อมต่อไม่ได้ ให้เล่นต่อได้
                });

            } else {
                // ถ้าคำตอบผิด
                // this.sound.play('wrong_sound');
                btn.disableInteractive().setAlpha(0.3); // ทำให้ปุ่มที่ผิดคลิกไม่ได้และจางลง
            }
        });
    }
}