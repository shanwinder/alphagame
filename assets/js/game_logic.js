// --- ไฟล์: assets/js/game_logic.js (ฉบับแก้ไข) ---

class GameScene extends Phaser.Scene {
    constructor() {
        super({ key: 'GameScene' });
        this.game_over = false;
        this.livePollingInterval = null;
    }

    preload() {
        // ✅ [เพิ่ม] เพิ่มการโหลดภาพพื้นหลังสำหรับฉากเกมเข้ามา
        this.load.image('background', '../assets/img/login_bg_wide.png');

        this.load.image('alpha', '../assets/img/alpha_front.png');
        this.load.image('signal_triangle', '../assets/img/signal_triangle.png');
        this.load.image('signal_square', '../assets/img/signal_square.png');
        this.load.image('signal_circle', '../assets/img/signal_circle.png');
        this.load.image('signal_star', '../assets/img/signal_star.png');
    }

    create() {
        // เมื่อโหลดภาพ 'background' แล้ว คำสั่งนี้จะทำงานได้ถูกต้อง
        this.add.image(450, 300, 'background');
        this.add.sprite(120, 480, 'alpha').setScale(0.4);

        switch (CURRENT_STAGE_ID) {
            case 1:
                this.createStage1();
                break;
            default:
                this.add.text(450, 300, 'ไม่พบข้อมูลสำหรับด่านนี้', { fontSize: '32px', color: '#ff0000', fontFamily: 'Kanit' }).setOrigin(0.5);
                break;
        }
    }

    // ... ฟังก์ชัน createStage1() และ submitScore() ยังคงเหมือนเดิม ...
    createStage1() {
        this.add.graphics().fillStyle(0xfffbe6, 0.9).fillRoundedRect(300, 40, 300, 180, 20);
        this.add.text(450, 60, 'โจทย์ต้นแบบ', { fontSize: '28px', color: '#b45309', fontFamily: 'Kanit' }).setOrigin(0.5);
        this.add.graphics().fillStyle(0xe0f2fe, 0.9).fillRoundedRect(100, 300, 700, 250, 20);
        this.add.text(450, 320, 'เลือกภาพที่เหมือนกับโจทย์', { fontSize: '24px', color: '#0c4a6e', fontFamily: 'Kanit' }).setOrigin(0.5);
        const all_patterns = ['signal_triangle', 'signal_square', 'signal_circle'];
        const correctAnswerKey = Phaser.Utils.Array.GetRandom(all_patterns);
        this.add.image(450, 150, correctAnswerKey).setScale(0.5);
        let choices = Phaser.Utils.Array.Shuffle([...all_patterns]);
        choices.forEach((choiceKey, i) => {
            const btn = this.add.image(250 + (i * 200), 450, choiceKey).setScale(0.4).setInteractive({ useHandCursor: true });
            btn.on('pointerdown', () => {
                if (this.game_over) return;
                if (choiceKey === correctAnswerKey) {
                    this.game_over = true;
                    btn.setTint(0x00ff00);
                    this.submitScore(100);
                } else {
                    btn.disableInteractive().setAlpha(0.3);
                }
            });
        });
    }
    submitScore(score) {
        if (this.game_over) return;
        this.game_over = true;
        const formData = new FormData();
        formData.append('stage_id', CURRENT_STAGE_ID);
        formData.append('score', score);
        fetch('../api/submit_stage_score.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.showSuccessPopup(data.stars, IS_LIVE_SESSION, NEXT_STAGE_LINK);
                    if (IS_LIVE_SESSION) {
                        this.startLivePolling();
                    }
                } else {
                    alert('Error: ' + data.message);
                    this.game_over = false;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                this.game_over = false;
            });
    }
    startLivePolling() {
        console.log("Live mode: Polling for teacher's command...");
        this.livePollingInterval = setInterval(() => {
            fetch(`../api/live_status.php?code=${LIVE_SESSION_CODE}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Polling... Server Stage ID:", data.current_stage_id, "Current Stage ID:", CURRENT_STAGE_ID);
                    if (data.current_stage_id > CURRENT_STAGE_ID) {
                        console.log("Teacher advanced to the next stage! Redirecting...");
                        clearInterval(this.livePollingInterval);
                        window.location.href = `play.php?stage_id=${data.current_stage_id}`;
                    }
                });
        }, 20000);
    }
}

const config = {
    type: Phaser.AUTO,
    width: 900,
    height: 600,
    backgroundColor: '#ffffff',
    parent: 'game-container',
    scene: [GameScene]
};
const game = new Phaser.Game(config);