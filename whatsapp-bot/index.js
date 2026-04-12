require('dotenv').config();
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');
const cron = require('node-cron');

const API_URL = process.env.API_URL || 'http://myacademy-laravel.test/api/whatsapp';

// State Machine Storage
const userStates = new Map();

const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: './.wwebjs_auth'
    }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ]
    },
    authTimeoutMs: 60000,
    restartOnAuthFail: true
});

client.on('qr', (qr) => {
    console.log('\n' + '='.repeat(50));
    qrcode.generate(qr, { small: true });
    console.log('='.repeat(50));
    console.log('\n🔄 Scan the QR code above to login to WhatsApp Bot.');
});

client.on('ready', async () => {
    console.log('\n✅ MyAcademy WhatsApp Bot is ready and listening!');
    const info = await client.info;
    console.log('\n📱 BOT WHATSAPP NUMBER:', info.wid.user);
    setupScheduledJobs();
});

// Helper for Conversational States
async function handleStateFlow(msg, phone, text, stateObj) {
    if (text.toLowerCase() === 'cancel') {
        userStates.delete(phone);
        return msg.reply('🚫 Process cancelled. You can ask me anything else!');
    }

    // ---------------- REGISTER FLOW ----------------
    if (stateObj.step === 'EMAIL') {
        stateObj.data.email = text.trim();
        stateObj.step = 'ADMISSION';
        return msg.reply(
            'Got it! ✅\n\n' +
            'If you are a *Parent*, reply with your child\'s *Admission Number*.\n' +
            'If you are *Staff/Admin*, just reply with the word *skip*.'
        );
    }
    
    if (stateObj.step === 'ADMISSION') {
        stateObj.data.admission_number = text.toLowerCase() === 'skip' ? null : text.trim();
        try {
            const res = await axios.post(`${API_URL}/register`, {
                email: stateObj.data.email,
                admission_number: stateObj.data.admission_number,
                phone: phone
            });
            if (res.data.success) {
                stateObj.step = 'OTP';
                return msg.reply(`📩 *Verification Required*\n\nWe found your account! Generated OTP Code: *${res.data.otp}*\n\nPlease reply with the OTP code to verify your WhatsApp.`);
            }
        } catch(e) {
            userStates.delete(phone);
            return msg.reply('❌ Registration failed: ' + (e.response?.data?.message || 'Unknown error') + '\n\nType *register* to try again.');
        }
    }
    
    if (stateObj.step === 'OTP') {
        try {
            const res = await axios.post(`${API_URL}/verify`, {
                phone: phone,
                otp: text.trim()
            });
            userStates.delete(phone);
            if (res.data.user.role === 'parent') {
                return msg.reply(`🎉 Registration Successful!\n\nWelcome! You can now ask me questions naturally like *"Did my child go to school yesterday?"* or *"What was their math score?"*. Give it a try!`);
            } else {
                return msg.reply(`🎉 Staff Registration Successful! Welcome, ${res.data.user.name}.\n\nYou can type *homework* to start assigning tasks to classes.`);
            }
        } catch (e) {
            userStates.delete(phone);
            return msg.reply('❌ Invalid OTP. Type *register* to restart process.');
        }
    }

    // ---------------- HOMEWORK FLOW ----------------
    if (stateObj.step === 'HW_CLASS') {
        stateObj.data.class = text.trim();
        stateObj.step = 'HW_DESC';
        return msg.reply(`Got it. Target Class: *${stateObj.data.class}*.\n\nNow, please send the *Homework Description* (What are the students supposed to do?).`);
    }
    
    if (stateObj.step === 'HW_DESC') {
        stateObj.data.desc = text.trim();
        try {
            const hwRes = await axios.post(`${API_URL}/staff/homework`, {
                user_id: stateObj.data.userId,
                class: stateObj.data.class,
                description: stateObj.data.desc
            });
            userStates.delete(phone);
            return msg.reply(`✅ Success! Homework has been assigned to ${stateObj.data.class} and logged in the system.`);
        } catch(e) {
            userStates.delete(phone);
            return msg.reply(`❌ Failed to send homework: ${e.response?.data?.message || e.message}`);
        }
    }

    // ---------------- BROADCAST FLOW ----------------
    if (stateObj.step === 'BROADCAST_TARGET') {
        stateObj.data.target = text.trim();
        stateObj.step = 'BROADCAST_MSG';
        return msg.reply(`Got it. Target audience: *${stateObj.data.target}*.\n\nNow, please send the *Broadcast Message*.`);
    }

    if (stateObj.step === 'BROADCAST_MSG') {
        stateObj.data.message = text.trim();
        try {
            const res = await axios.post(`${API_URL}/admin/broadcast`, {
                user_id: stateObj.data.userId,
                target: stateObj.data.target,
                message: stateObj.data.message
            });
            const phones = res.data.phones;
            userStates.delete(phone);
            
            if (!phones || phones.length === 0) {
                return msg.reply(`⚠️ No subscribed users found in target audience: ${stateObj.data.target}.`);
            }

            msg.reply(`⏳ Sending broadcast to ${phones.length} users...`);
            let successCount = 0;
            for (const p of phones) {
                try {
                    await client.sendMessage(p + '@c.us', `📢 *Broadcast from Admin*\n\n${stateObj.data.message}`);
                    successCount++;
                } catch(e) {}
            }
            return msg.reply(`✅ Broadcast successfully delivered to ${successCount} out of ${phones.length} users!`);
        } catch(e) {
            userStates.delete(phone);
            return msg.reply('❌ Failed to lookup targets: ' + (e.response?.data?.message || e.message));
        }
    }

    userStates.delete(phone);
    return msg.reply("Workflow cancelled due to an internal error.");
}


client.on('message', async msg => {
    if (msg.fromMe) return;

    const rawText = msg.body || '';
    const text = rawText.trim();
    if (!text) return; // Images/Media aren't processed via AI yet natively here

    const textLower = text.toLowerCase();
    const phone = msg.from.split('@')[0];

    // 1. STATE MACHINE - Workflows intercept ordinary texts
    if (userStates.has(phone)) {
        return await handleStateFlow(msg, phone, text, userStates.get(phone));
    }

    // 2. ENTRY COMMANDS (Strict match)
    if (textLower === 'register') {
        userStates.set(phone, { step: 'EMAIL', data: {} });
        return msg.reply('👋 Welcome! Let\'s hook up your account.\n\nPlease reply with your *Email Address* (or type *cancel* anytime).');
    }

    // 3. FETCH USER CONTEXT
    let userInfo;
    try {
        const res = await axios.get(`${API_URL}/user/${phone}`);
        userInfo = res.data.user;
    } catch(e) {
        // If not registered
        if (['hi', 'hello', 'hey', 'menu', 'start'].includes(textLower)) {
            return msg.reply('👋 Welcome to MyAcademy AI Bot!\n\nYou are not registered yet or I don\'t recognize your number.\n\nReply with *register* to begin the quick setup.');
        }
        return msg.reply('You are not registered. Reply with *register* to link your account.');
    }

    const userId = userInfo.id;
    const userRole = userInfo.role;

    // --- EXACT COMMANDS ---
    if (textLower === 'broadcast' && ['admin', 'superadmin'].includes(userRole)) {
        userStates.set(phone, { step: 'BROADCAST_TARGET', data: { userId: userId } });
        return msg.reply('📢 Let\'s send a broadcast!\n\nWho should receive this? (e.g. *Parents*, *Staff*, or *All*)');
    }

    if (textLower === 'homework' && ['teacher', 'admin', 'superadmin'].includes(userRole)) {
        userStates.set(phone, { step: 'HW_CLASS', data: { userId: userId } });
        return msg.reply('📚 Let\'s assign homework!\n\nWhich *Class* is this for? (e.g. Basic_1)');
    }

    if (textLower === 'menu' || textLower === 'help') {
        if (userRole === 'parent') {
            return msg.reply('🤖 *MyAcademy Assistant*\n\nYou can talk to me naturally! E.g. *"Did my son go to school?"*, *"What are the fees?"*\n\nOr use commands:\n- attendance\n- results\n- contact\n- subscribe');
        } else if (userRole === 'admin' || userRole === 'superadmin') {
            return msg.reply('👑 *Admin Assistant*\n\nCommands:\n- broadcast (Mass message)\n- homework (Interactive Assign)\n- subscribe\n- unsubscribe\n\nOr just chat with me naturally!');
        } else {
            return msg.reply('👨‍🏫 *Staff Assistant*\n\nCommands:\n- homework (Interactive Assign)\n- subscribe\n- unsubscribe\n\nOr just chat with me naturally!');
        }
    }

    if (textLower === 'subscribe') {
        await axios.post(`${API_URL}/subscribe/${userId}`);
        return msg.reply('✅ You are now subscribed to automated push notifications.');
    }
    if (textLower === 'unsubscribe') {
        await axios.post(`${API_URL}/unsubscribe/${userId}`);
        return msg.reply('✅ You have successfully unsubscribed from automated notifications.');
    }
    if (textLower === 'contact') {
        const res = await axios.get(`${API_URL}/contact`);
        return msg.reply(`☎️ School Contact\nPhone: ${res.data.phone || 'N/A'}\nEmail: ${res.data.email || 'N/A'}`);
    }

    // --- PARENT LEGACY EXPLICIT COMMANDS ---
    // Kept as fallbacks just in case AI is fully offline.
    if (userRole === 'parent' && textLower === 'attendance') {
        const res = await axios.get(`${API_URL}/attendance/${userId}`);
        let reply = `📅 Today's Attendance\n\n`;
        res.data.students.forEach(s => {
            const isPresent = s.attendance_marks && s.attendance_marks.length > 0 && ['P', 'L'].includes(s.attendance_marks[0].status);
            reply += `${isPresent ? '✅' : '❌'} ${s.first_name} ${s.last_name} - ${isPresent ? 'Present' : 'Absent'}\n`;
        });
        return msg.reply(reply);
    }
    if (userRole === 'parent' && textLower === 'results') {
        const res = await axios.get(`${API_URL}/results/${userId}`);
        let reply = `📊 Latest Results\n\n`;
        res.data.students.forEach(s => {
            reply += `👨‍🎓 ${s.first_name}\n`;
            if(s.scores?.length > 0) {
                s.scores.forEach(score => reply += `📚 ${score.subject?.name}: ${score.total_score}/100\n`);
            } else {
                reply += `No recent results.\n`;
            }
        });
        return msg.reply(reply);
    }


    // 4. NATURAL LANGUAGE AI FALLBACK (Idea 3)
    // If the message wasn't a strict command or state flow, process it via AI!
    // Parents and Staff get pure AI context!
    try {
        const aiRes = await axios.post(`${API_URL}/ai/ask`, {
            parent_id: userId,
            question: msg.body
        });
        if (aiRes.data.success) {
            return msg.reply(aiRes.data.answer);
        }
    } catch(e) {
        console.error('AI Error:', e.response?.data || e.message);
        return msg.reply('🤷 I didn\'t quite catch that, or the AI service is momentarily busy. Type *menu* for quick commands.');
    }
});

function setupScheduledJobs() {
    // Proactive Alerts (Idea 5)
    // This allows Laravel backend to run a cron push alert for attendance everyday at 9:00 AM (if enabled in settings).
    cron.schedule('0 9 * * 1-5', () => {
        console.log('🔔 Triggering attendance push alerts webhook to Laravel...');
        axios.post(`${API_URL}/cron/attendance-alerts`).catch(e => {
            // Endpoint doesn't strictly have to exist yet, but sets up Idea 5 pipeline!
            console.log('Cron response:', e.response?.status);
        });
    });
} 

process.on('SIGINT', async () => {
    console.log('\n🛑 Shutting down bot gracefully...');
    await client.destroy();
    process.exit(0);
});

client.initialize().catch(err => {
    console.error('❌ Failed to initialize:', err);
});
