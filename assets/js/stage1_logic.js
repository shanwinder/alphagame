// --- ไฟล์: assets/js/stage1_logic.js ---

// ตั้งค่าพื้นฐานของเกม
const config = {
    type: Phaser.AUTO,         // ให้ Phaser เลือกวิธีแสดงผลที่เหมาะสมที่สุด (Canvas หรือ WebGL)
    width: 900,                // ความกว้างของพื้นที่เกม
    height: 600,               // ความสูงของพื้นที่เกม
    parent: 'game-container',  // บอกให้เกมไปแสดงผลใน div ที่มี id="game-container"
    scene: {
        preload: preload,      // ฟังก์ชันสำหรับโหลดทรัพยากร
        create: create         // ฟังก์ชันสำหรับสร้างองค์ประกอบของเกม
    }
};

// สร้างเกม Phaser ขึ้นมาใหม่
const game = new Phaser.Game(config);

// --- ฟังก์ชันของ Scene ---

// 1. ฟังก์ชัน Preload: โหลดภาพและเสียงทั้งหมดที่เราต้องใช้
function preload() {
    // โหลดภาพพื้นหลัง (คุณครูสามารถเปลี่ยน URL ได้)
    this.load.image('background', '../assets/img/bg-st1.png');

    // โหลดภาพอัลฟ่า (จากไฟล์ที่คุณครูอัปโหลด)
    this.load.image('alpha', '../assets/img/alpha_front.png');

    // โหลดภาพสัญญาณ (สมมติว่าเราได้ตัดเป็นไฟล์แยกไว้แล้ว)
    this.load.image('signal_triangle', '../assets/img/signal_triangle.png'); // ใช้ภาพตัวอย่างไปก่อน
    this.load.image('signal_square', '../assets/img/signal_square.png'); // ใช้ภาพตัวอย่างไปก่อน
    this.load.image('signal_circle', '../assets/img/signal_circle.png'); // ใช้ภาพตัวอย่างไปก่อน

    // โหลดเสียง (คุณครูสามารถหาไฟล์เสียง .mp3 มาใส่ได้)
    // this.load.audio('correct_sound', '../assets/sound/correct.mp3');
    // this.load.audio('wrong_sound', '../assets/sound/wrong.mp3');
}

// 2. ฟังก์ชัน Create: สร้างฉากและองค์ประกอบต่างๆ ของเกม
function create() {
    // แสดงภาพพื้นหลัง
    this.add.image(450, 300, 'background').setScale(0.7); // 450, 300 คือจุดกึ่งกลางของจอ

    // แสดงตัวละครอัลฟ่า
    this.add.sprite(150, 450, 'alpha').setScale(0.2);

    // --- ส่วนของ Logic เกม ---

    // สร้างกล่องสำหรับโจทย์
    const questionBox = this.add.graphics();
    questionBox.fillStyle(0xffffff, 0.8); // สีขาวโปร่งแสง
    questionBox.fillRoundedRect(350, 50, 200, 150, 15); // x, y, width, height, radius
    this.add.text(405, 60, 'โจทย์ต้นแบบ', { fontSize: '24px', color: '#000000' });

    // แสดงโจทย์ (สมมติว่าด่านนี้โจทย์คือ "สามเหลี่ยม")
    this.add.image(450, 140, 'signal_triangle').setScale(0.3);

    // สร้างตัวเลือก 3 อัน
    const choices = [
        { key: 'signal_circle', isCorrect: false },
        { key: 'signal_triangle', isCorrect: true },
        { key: 'signal_square', isCorrect: false }
    ];

    // สลับลำดับตัวเลือกเพื่อไม่ให้คำตอบถูกอยู่ตำแหน่งเดิมตลอด
    Phaser.Utils.Array.Shuffle(choices);

    // วนลูปเพื่อสร้างปุ่มตัวเลือก
    for (let i = 0; i < choices.length; i++) {
        let x = 300 + (i * 150);
        let y = 400;
        let choice = choices[i];

        // สร้างปุ่มจากรูปภาพ และทำให้มัน "คลิกได้"
        const btn = this.add.image(x, y, choice.key).setScale(0.3).setInteractive();

        // เมื่อคลิกที่ปุ่ม
        btn.on('pointerdown', () => {
            if (choice.isCorrect) {
                // ถ้าคำตอบถูก
                alert("🎉 เก่งมาก! คุณตอบถูก!");
                // this.sound.play('correct_sound'); // เล่นเสียงเมื่อตอบถูก

                // --- ส่วนเชื่อมต่อกับ Backend ---
                // ส่งคะแนน 100 ไปที่ API
                const formData = new FormData();
                formData.append('stage_id', 1); // ID ของด่านนี้
                formData.append('score', 100);

                fetch('../api/submit_stage_score.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('บันทึกคะแนนสำเร็จ:', data);
                    // อาจจะแสดงปุ่มไปด่านต่อไป หรือกลับหน้าหลัก
                })
                .catch(error => {
                    console.error('เกิดข้อผิดพลาดในการบันทึกคะแนน:', error);
                });

            } else {
                // ถ้าคำตอบผิด
                alert("โอ๊ะ! ลองดูอีกทีนะ");
                // this.sound.play('wrong_sound'); // เล่นเสียงเมื่อตอบผิด
                btn.setAlpha(0.5); // ทำให้ปุ่มที่ตอบผิดจางลง
            }
        });
    }
}