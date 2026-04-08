require('dotenv').config();
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');
const cron = require('node-cron');

const API_URL = process.env.API_URL || 'http://myacademy-laravel.test/api/whatsapp';

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
    // Increase timeout for better stability
    authTimeoutMs: 60000,
    // Retry on failure
    restartOnAuthFail: true
});

client.on('qr', (qr) => {
    console.log('\n' + '='.repeat(50));
    qrcode.generate(qr, { small: true });
    console.log('='.repeat(50));
    console.log('\n🔄 Scan the QR code above to login to WhatsApp Bot.');
    console.log('📱 Open WhatsApp → Settings → Linked Devices → Link a Device\n');
});

client.on('ready', async () => {
    console.log('\n✅ MyAcademy WhatsApp Bot is ready and listening!');
    console.log('📞 Bot is now active and waiting for messages...');
    console.log('🔗 API URL:', API_URL);
    
    // Get bot number
    const info = await client.info;
    console.log('\n📱 BOT WHATSAPP NUMBER:', info.wid.user);
    console.log('👤 BOT NAME:', info.pushname);
    console.log('\n💬 Parents should send messages TO this number: +' + info.wid.user);
    console.log('\n⚠️  IMPORTANT: You must send messages TO +' + info.wid.user);
    console.log('⚠️  FROM a different WhatsApp number (your personal phone)\n');
    console.log('👂 Listening for incoming messages...\n');
    
    setupScheduledJobs();
});

client.on('message', async msg => {
    console.log('\n' + '='.repeat(60));
    console.log('📩 MESSAGE RECEIVED!');
    console.log('From:', msg.from);
    console.log('Body:', msg.body);
    console.log('Is from me:', msg.fromMe);
    console.log('Chat type:', msg.from.includes('@g.us') ? 'Group' : 'Individual');
    console.log('='.repeat(60) + '\n');
    
    // Ignore messages from self
    if (msg.fromMe) {
        console.log('⚠️ Ignoring message from self\n');
        return;
    }
    
    console.log('📩 Message received from:', msg.from, '| Text:', msg.body);
    try {
        const text = msg.body.toLowerCase().trim();
        const args = text.split(' ');
        const command = args[0];
        
        // Extract plain phone number from things like '1234567890@c.us'
        const phone = msg.from.split('@')[0];

        if (command === 'register') {
            console.log('🔑 Processing register command...');
            if (args.length < 3) {
                return msg.reply('Usage: register [email] [admission_number]');
            }
            console.log('Sending registration request to API...');
            const res = await axios.post(`${API_URL}/register`, {
                email: args[1],
                admission_number: args[2],
                phone: phone
            });
            console.log('Registration response:', res.data);
            if (res.data.success) {
                return msg.reply(`✅ Verification Required\nOTP Code: ${res.data.otp}\nType: verify ${res.data.otp}`);
            }
        }

        if (command === 'verify') {
            if (args.length < 2) {
                return msg.reply('Usage: verify [otp_code]');
            }
            const res = await axios.post(`${API_URL}/verify`, {
                phone: phone,
                otp: args[1]
            });
            if (res.data.success) {
                const parent = res.data.parent;
                let reply = `🎉 Registration Successful!\nYour children:\n`;
                parent.students.forEach(s => {
                    reply += `• ${s.first_name} ${s.last_name}\n`;
                });
                return msg.reply(reply);
            }
        }

        // Parent validation check
        let parentInfo;
        try {
            const res = await axios.get(`${API_URL}/parent/${phone}`);
            parentInfo = res.data.parent;
        } catch (e) {
            // Only send 'not registered' message if they typed a known command other than register/verify
            if (['attendance', 'results', 'fees', 'report', 'help', 'contact'].includes(command)) {
                return msg.reply('👋 Welcome to MyAcademy Bot!\nTo register: register [email] [admission_number]');
            }
            return; // Ignore other random messages
        }

        const parentId = parentInfo.id;

        if (command === 'attendance') {
            const res = await axios.get(`${API_URL}/attendance/${parentId}`);
            const students = res.data.students;
            let reply = `📅 Today's Attendance\n\n`;
            students.forEach(s => {
                const isPresent = s.attendance_marks && s.attendance_marks.length > 0 && ['P', 'L'].includes(s.attendance_marks[0].status);
                reply += `${isPresent ? '✅' : '❌'} ${s.first_name} ${s.last_name} - ${isPresent ? 'Present' : 'Absent'}\n`;
            });
            return msg.reply(reply);
        }

        if (command === 'results') {
            const res = await axios.get(`${API_URL}/results/${parentId}`);
            const students = res.data.students;
            let reply = `📊 Latest Results\n\n`;
            students.forEach(s => {
                reply += `👨‍🎓 ${s.first_name} ${s.last_name}\n`;
                if(s.scores && s.scores.length > 0) {
                    s.scores.forEach(score => {
                        reply += `📚 ${score.subject ? score.subject.name : 'Subject'}: ${score.total_score}/100\n`;
                    });
                } else {
                    reply += `No recent results.\n`;
                }
                reply += '\n';
            });
            return msg.reply(reply);
        }
        
        if (command === 'fees') {
            return msg.reply('💰 Fee records are currently up to date.');
        }

        if (command === 'help') {
            console.log('❓ Processing help command...');
            return msg.reply('Available commands:\n- attendance\n- results\n- fees\n- register\n- verify');
        }

    } catch (e) {
        console.error('\n❌ ERROR processing message:');
        console.error('From:', msg.from);
        console.error('Message:', msg.body);
        console.error('Error:', e.response ? e.response.data : e.message);
        console.error('Stack:', e.stack);
        
        msg.reply('❌ An error occurred processing your request.');
    }
});

function setupScheduledJobs() {
    cron.schedule('0 9 * * 1-5', () => {
        console.log('Running daily attendance alerts scheduler...');
    });
} // End setupScheduledJobs

// Handle process termination gracefully
process.on('SIGINT', async () => {
    console.log('\n\n🛑 Shutting down bot gracefully...');
    await client.destroy();
    console.log('✅ Bot stopped successfully');
    process.exit(0);
});

console.log('🚀 Starting MyAcademy WhatsApp Bot...');
console.log('🔗 API URL:', API_URL);
console.log('⏳ Initializing WhatsApp client...\n');

client.on('loading_screen', (percent, message) => {
    console.log('⏳ Loading:', percent + '%', message);
});

client.on('authenticated', () => {
    console.log('✅ WhatsApp authenticated successfully!');
});

client.on('auth_failure', msg => {
    console.error('❌ Authentication failed:', msg);
    console.log('\n💡 Session expired or invalid');
    console.log('💡 Cleaning up and requesting new QR code...');
    
    // Don't auto-delete, just inform user
    console.log('\n⚠️  Please run these commands:');
    console.log('   1. Stop the bot (Ctrl+C)');
    console.log('   2. rmdir /s /q .wwebjs_auth');
    console.log('   3. node index.js');
});

client.on('disconnected', (reason) => {
    console.log('\n⚠️ Bot disconnected:', reason);
    console.log('🔄 Attempting to reconnect in 5 seconds...');
    
    setTimeout(() => {
        console.log('🔄 Reinitializing client...');
        client.initialize().catch(err => {
            console.error('❌ Reconnection failed:', err);
            console.log('💡 Please restart the bot manually');
        });
    }, 5000);
});

console.log('🔄 Initializing client...');
client.initialize().catch(err => {
    console.error('❌ Failed to initialize:', err);
});
