// --- ไฟล์: assets/js/game_logic.js (ฉบับสมบูรณ์) ---

// ใช้ Class ในการสร้าง Scene ของเกม ซึ่งเป็นรูปแบบมาตรฐานของ Phaser 3
class GameScene extends Phaser.Scene {
    // ฟังก์ชัน constructor จะทำงานเมื่อ Scene ถูกสร้างขึ้น
    constructor() {
        // เรียก constructor ของคลาสแม่ (Phaser.Scene) และตั้งชื่อ Scene ว่า 'GameScene'
        super({ key: 'GameScene' });
        // สร้างตัวแปร 'game_over' เพื่อใช้ตรวจสอบสถานะของเกม (ป้องกันการคลิกซ้ำ)
        this.game_over = false;
    }

    // ฟังก์ชัน preload: เป็นฟังก์ชันแรกที่จะทำงานเพื่อโหลดทรัพยากรทั้งหมดที่ต้องใช้ในเกม
    preload() {
        // โหลดภาพพื้นหลัง และตั้งชื่ออ้างอิงว่า 'background'
        this.load.image('background', '../assets/img/login_bg_wide.png');
        // โหลดภาพตัวละครอัลฟ่า และตั้งชื่ออ้างอิงว่า 'alpha'
        this.load.image('alpha', '../alpha_front.png');
        // โหลดภาพสัญญาณรูปสามเหลี่ยม
        this.load.image('signal_triangle', '../assets/img/signal_triangle.png');
        // โหลดภาพสัญญาณรูปสี่เหลี่ยม
        this.load.image('signal_square', '../assets/img/signal_square.png');
        // โหลดภาพสัญญาณรูปวงกลม
        this.load.image('signal_circle', '../assets/img/signal_circle.png');
        // โหลดภาพสัญญาณรูปดาว (เผื่อไว้สำหรับด่านอื่น)
        this.load.image('signal_star', '../assets/img/signal_star.png');
    }

    // ฟังก์ชัน create: จะทำงานหลังจากที่ preload โหลดทรัพยากรเสร็จแล้ว ใช้สำหรับสร้างวัตถุต่างๆ ในเกม
    create() {
        // เพิ่มภาพพื้นหลังเข้ามาในฉากที่ตำแหน่งกึ่งกลาง (450, 300)
        this.add.image(450, 300, 'background');
        // เพิ่มภาพตัวละครอัลฟ่าเข้ามาในฉากที่ตำแหน่งด้านซ้ายล่าง และย่อขนาดลง 60%
        this.add.sprite(120, 480, 'alpha').setScale(0.4);

        // ใช้ switch-case เพื่อตรวจสอบว่าตอนนี้กำลังจะสร้างเกมของด่านไหน (CURRENT_STAGE_ID มาจากไฟล์ play.php)
        switch (CURRENT_STAGE_ID) {
            // ถ้าเป็นด่านที่ 1
            case 1:
                // ให้เรียกใช้ฟังก์ชันสำหรับสร้างด่านที่ 1
                this.createStage1();
                break;
            // (ในอนาคต) ถ้าเป็นด่านที่ 2
            // case 2:
            //     this.createStage2();
            //     break;

            // กรณีที่ไม่พบข้อมูลด่านที่ตรงกับ CURRENT_STAGE_ID
            default:
                // ให้แสดงข้อความ Error ขึ้นมากลางจอ
                this.add.text(450, 300, 'ไม่พบข้อมูลสำหรับด่านนี้', { fontSize: '32px', color: '#ff0000', fontFamily: 'Kanit' }).setOrigin(0.5);
                break;
        }
    }

    // ฟังก์ชันสำหรับสร้างองค์ประกอบเฉพาะของด่านที่ 1
    createStage1() {
        // สร้างกล่องสำหรับโจทย์
        this.add.graphics().fillStyle(0xfffbe6, 0.9).fillRoundedRect(300, 40, 300, 180, 20);
        // เพิ่มข้อความ "โจทย์ต้นแบบ"
        this.add.text(450, 60, 'โจทย์ต้นแบบ', { fontSize: '28px', color: '#b45309', fontFamily: 'Kanit' }).setOrigin(0.5);
        // สร้างกล่องสำหรับตัวเลือก
        this.add.graphics().fillStyle(0xe0f2fe, 0.9).fillRoundedRect(100, 300, 700, 250, 20);
        // เพิ่มข้อความ "เลือกภาพที่เหมือนกับโจทย์"
        this.add.text(450, 320, 'เลือกภาพที่เหมือนกับโจทย์', { fontSize: '24px', color: '#0c4a6e', fontFamily: 'Kanit' }).setOrigin(0.5);

        // สร้างคลังของสัญญาณทั้งหมดสำหรับด่านนี้
        const all_patterns = ['signal_triangle', 'signal_square', 'signal_circle'];
        // สุ่มเลือกคำตอบที่ถูกต้องสำหรับด่านนี้ 1 อย่างจากคลัง
        const correctAnswerKey = Phaser.Utils.Array.GetRandom(all_patterns);

        // แสดงภาพโจทย์ (คือภาพที่เป็นคำตอบที่ถูกต้อง)
        this.add.image(450, 150, correctAnswerKey).setScale(0.5);

        // สลับลำดับของ Array สัญญาณทั้งหมด เพื่อใช้เป็นตัวเลือกที่สุ่มตำแหน่งแล้ว
        let choices = Phaser.Utils.Array.Shuffle([...all_patterns]);

        // วนลูปเพื่อสร้างปุ่มตัวเลือก 3 ปุ่ม
        choices.forEach((choiceKey, i) => {
            // กำหนดตำแหน่งของแต่ละปุ่ม
            const btn = this.add.image(250 + (i * 200), 450, choiceKey).setScale(0.4).setInteractive({ useHandCursor: true });

            // สร้าง Event Listener เพื่อดักจับการคลิกที่ปุ่ม
            btn.on('pointerdown', () => {
                // ถ้าเกมจบแล้ว (ตอบถูกไปแล้ว) จะไม่ทำอะไรเลย
                if (this.game_over) return;

                // ตรวจสอบว่าปุ่มที่คลิกเป็นคำตอบที่ถูกต้องหรือไม่
                if (choiceKey === correctAnswerKey) {
                    // ถ้าถูกต้อง
                    this.game_over = true; // ตั้งสถานะว่าเกมจบแล้ว
                    btn.setTint(0x00ff00); // ทำให้ปุ่มที่ถูกเป็นสีเขียว
                    this.submitScore(100); // เรียกใช้ฟังก์ชันส่งคะแนน โดยส่งค่าไป 100
                } else {
                    // ถ้าผิด
                    btn.disableInteractive().setAlpha(0.3); // ทำให้ปุ่มที่ผิดคลิกไม่ได้และจางลง
                }
            });
        });
    }

    // ฟังก์ชันสำหรับส่งคะแนน (สามารถใช้ร่วมกันได้ทุกด่าน)
    submitScore(score) {
        // สร้าง Object FormData เพื่อเตรียมข้อมูลสำหรับส่ง
        const formData = new FormData();
        // เพิ่ม stage_id ปัจจุบันลงในฟอร์ม
        formData.append('stage_id', CURRENT_STAGE_ID);
        // เพิ่มคะแนนที่ได้รับลงในฟอร์ม
        formData.append('score', score);

        // ส่งข้อมูลไปยัง API ด้วย fetch
        fetch('../api/submit_stage_score.php', { method: 'POST', body: formData })
            .then(response => response.json()) // แปลงข้อมูลตอบกลับจากเซิร์ฟเวอร์เป็น JSON
            .then(data => {
                // หลังจากได้รับข้อมูลตอบกลับ
                if (data.status === 'success') {
                    // ถ้าสถานะคือ 'success'
                    // เรียกใช้ฟังก์ชัน Popup จาก game_common.js โดยส่งจำนวนดาวที่ได้จาก API ไปด้วย
                    window.showSuccessPopup(data.stars, IS_LIVE_SESSION, NEXT_STAGE_LINK);
                } else {
                    // ถ้าสถานะเป็นอย่างอื่น ให้แสดงข้อความ error
                    alert('Error: ' + data.message);
                    this.game_over = false; // ให้เล่นใหม่ได้ถ้าบันทึกไม่สำเร็จ
                }
            })
            .catch(error => {
                // กรณีที่การเชื่อมต่อกับเซิร์ฟเวอร์ล้มเหลว
                console.error('Fetch Error:', error); // แสดง error ใน console
                this.game_over = false; // ให้เล่นใหม่ได้
            });
    }
}

// การตั้งค่าหลักของเกม Phaser
const config = {
    type: Phaser.AUTO, // ให้ Phaser เลือกวิธีแสดงผลที่เหมาะสมที่สุด
    width: 900, // ความกว้างของเกม
    height: 600, // ความสูงของเกม
    parent: 'game-container', // บอกให้เกมไปแสดงผลใน div ที่มี id="game-container"
    scene: [GameScene] // ระบุว่า Scene ที่จะใช้ในเกมนี้คือ GameScene ที่เราสร้างไว้
};

// สร้างเกมขึ้นมาใหม่โดยใช้ config ที่ตั้งไว้
const game = new Phaser.Game(config);