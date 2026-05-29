require('dotenv').config();
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');
const cron = require('node-cron');

const API_URL = process.env.API_URL || 'http://myacademy-laravel.test/api/whatsapp';

// Set up Axios default headers for API Key authentication
const WHATSAPP_API_KEY = process.env.WHATSAPP_API_KEY || 'dev-local-whatsapp-key-change-in-production';

axios.defaults.headers.common['X-WhatsApp-Api-Key'] = WHATSAPP_API_KEY;

// State Machine Storage
const userStates = new Map();
const chatHistories = new Map();

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
    webVersionCache: {
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/{version}.html',
        strict: false
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
    console.log('\n✅ HubGenie is ready and listening!');
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

    // ---------------- LOGIN FLOW ----------------
    if (stateObj.step === 'LOGIN_SCHOOL') {
        stateObj.data.school = text.trim().toLowerCase();
        stateObj.step = 'LOGIN_IDENTIFIER';
        return msg.reply(
            `Got it. School Code: *${stateObj.data.school}*\n\n` +
            '👤 Now enter your *login identifier*:\n\n' +
            '• *Students:* Admission Number (e.g. STU20240001)\n' +
            '• *Staff/Admin/Parents:* Email Address'
        );
    }

    if (stateObj.step === 'LOGIN_IDENTIFIER') {
        stateObj.data.identifier = text.trim();
        stateObj.step = 'LOGIN_PASSWORD';
        return msg.reply('🔑 Now enter your *password*:');
    }

    if (stateObj.step === 'LOGIN_PASSWORD') {
        stateObj.data.password = text.trim();
        try {
            const res = await axios.post(`${API_URL}/login`, {
                identifier: stateObj.data.identifier,
                password: stateObj.data.password,
                phone: phone
            }, {
                headers: {
                    'X-Tenant-Slug': stateObj.data.school
                }
            });
            userStates.delete(phone);
            if (res.data.success) {
                const role = res.data.user.role;
                const name = res.data.user.name || res.data.user.first_name || 'User';
                if (role === 'parent') {
                    return msg.reply(`✅ *Logged in successfully!*\n\nWelcome, ${name}! You can now ask me things like:\n• _"Did my child attend school today?"_\n• _"What are my child's latest results?"_\n\nType *menu* for all commands.`);
                } else if (role === 'student') {
                    return msg.reply(`✅ *Logged in successfully!*\n\nWelcome, ${name}! Type *menu* to see what I can help you with.`);
                } else {
                    return msg.reply(`✅ *Logged in successfully!*\n\nWelcome, ${name}! Type *menu* for available commands.`);
                }
            }
        } catch(e) {
            userStates.delete(phone);
            let errMsg = 'An unexpected error occurred. Please try again.';
            if (e.response) {
                errMsg = e.response.data?.message || `Server error (${e.response.status})`;
            } else if (e.request) {
                errMsg = 'Could not connect to the school server. Please verify that your School Code is correct and the server is online.';
            } else {
                errMsg = e.message;
            }
            return msg.reply(`❌ *Login failed:* ${errMsg}\n\nType *login* to try again.`);
        }
    }

    // ---------------- HOMEWORK FLOW ----------------
    if (stateObj.step === 'HW_CLASS') {
        const inputClass = text.trim();
        const inputClassLower = inputClass.toLowerCase();

        if (stateObj.data.validClasses && !stateObj.data.validClasses.includes(inputClassLower)) {
            return msg.reply(`❌ *Class not found!* The class must be one of the school's active classes.\n\nPlease type one of the exact class names listed above (or type *cancel* to stop):`);
        }

        stateObj.data.class = inputClass;
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
            }, {
                headers: {
                    'X-Tenant-Slug': stateObj.data.tenantSlug || 'demo'
                }
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
            }, {
                headers: {
                    'X-Tenant-Slug': stateObj.data.tenantSlug || 'demo'
                }
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

    // Send seen/read receipt to show double blue ticks on WhatsApp
    try {
        await client.sendSeen(msg.from);
    } catch (e) {
        console.log('Info: Could not send read receipt (seen):', e.message);
    }

    // 1. AUDIO / VOICE NOTE TRANSCRIPTION HANDLER
    if (msg.hasMedia && (msg.type === 'audio' || msg.type === 'ptt')) {
        const phone = msg.from.split('@')[0];
        let userInfo;
        try {
            const res = await axios.get(`${API_URL}/user/${phone}`);
            userInfo = res.data.user;
        } catch(e) {
            return msg.reply('🤷 I don\'t recognize your number yet. Please type *login* to connect your account first.');
        }

        const witToken = process.env.WIT_AI_TOKEN;
        const assemblyKey = process.env.ASSEMBLYAI_API_KEY;
        const openAiKey = process.env.OPENAI_API_KEY;

        if (!witToken && !assemblyKey && !openAiKey) {
            return msg.reply(
                '🎙️ *Voice Mode Info:* I received your voice message, but conversational voice recognition is currently offline.\n\n' +
                '💡 *Free Setup Guide for Administrators:*\n' +
                'To enable free voice features, ask your school developer to configure one of the following free API keys in the system Settings/`.env` file:\n\n' +
                '• *WIT_AI_TOKEN* (Recommended): Meta\'s Wit.ai Speech API is *100% FREE forever* with unlimited transcription! Get a free token at https://wit.ai\n' +
                '• *ASSEMBLYAI_API_KEY*: Get a free API key with *100 hours of free transcription/month* at https://assemblyai.com\n' +
                '• *OPENAI_API_KEY*: Use OpenAI Whisper (requires a paid API account).'
            );
        }

        try {
            await msg.react('🎙️');
            const replyMsg = await msg.reply('⏳ *HubGenie is listening...* Transcribing your voice message, please wait...');

            const media = await msg.downloadMedia();
            const audioBuffer = Buffer.from(media.data, 'base64');
            let transcribedText = '';

            if (witToken) {
                // Method 1: Use 100% Free Meta Wit.ai Speech API
                const response = await axios({
                    method: 'POST',
                    url: `https://api.wit.ai/speech?v=20240304`,
                    headers: {
                        'Authorization': `Bearer ${witToken}`,
                        'Content-Type': media.mimetype
                    },
                    data: audioBuffer
                });

                const responseText = response.data;
                if (typeof responseText === 'string') {
                    const lines = responseText.split('\n');
                    for (const line of lines) {
                        if (line.trim()) {
                            try {
                                const parsed = JSON.parse(line);
                                if (parsed.text) transcribedText = parsed.text;
                                else if (parsed._text) transcribedText = parsed._text;
                            } catch (e) {}
                        }
                    }
                } else if (responseText && typeof responseText === 'object') {
                    transcribedText = responseText.text || responseText._text || '';
                }
            } else if (assemblyKey) {
                // Method 2: Use AssemblyAI Free Tier (100 hours/month)
                const uploadRes = await axios.post('https://api.assemblyai.com/v2/upload', audioBuffer, {
                    headers: {
                        'Authorization': assemblyKey,
                        'Content-Type': 'application/octet-stream'
                    }
                });
                const uploadUrl = uploadRes.data.upload_url;

                const transRes = await axios.post('https://api.assemblyai.com/v2/transcript', {
                    audio_url: uploadUrl
                }, {
                    headers: {
                        'Authorization': assemblyKey,
                        'Content-Type': 'application/json'
                    }
                });
                const transId = transRes.data.id;

                while (true) {
                    await new Promise(resolve => setTimeout(resolve, 1500));
                    const pollRes = await axios.get(`https://api.assemblyai.com/v2/transcript/${transId}`, {
                        headers: { 'Authorization': assemblyKey }
                    });
                    if (pollRes.data.status === 'completed') {
                        transcribedText = pollRes.data.text;
                        break;
                    } else if (pollRes.data.status === 'failed') {
                        throw new Error('AssemblyAI transcription failed: ' + (pollRes.data.error || 'Unknown error'));
                    }
                }
            } else if (openAiKey) {
                // Method 3: Use OpenAI Whisper
                const formData = new FormData();
                formData.append('file', new Blob([audioBuffer], { type: media.mimetype }), 'voice.ogg');
                formData.append('model', 'whisper-1');

                const whisperRes = await axios.post('https://api.openai.com/v1/audio/transcriptions', formData, {
                    headers: {
                        'Authorization': `Bearer ${openAiKey}`,
                        'Content-Type': 'multipart/form-data'
                    }
                });
                transcribedText = whisperRes.data?.text;
            }
            if (!transcribedText || transcribedText.trim() === '') {
                await replyMsg.delete(true).catch(() => {});
                return msg.reply('🤷 I heard some sound, but could not transcribe any clear words. Please try speaking a bit louder or closer to your microphone.');
            }

            // Update the status message to show transcription
            await replyMsg.edit(`🎙️ *Voice Transcription:* "${transcribedText}"\n\n_HubGenie is preparing your response..._`);

            // Query the natural language API with the transcribed text!
            const userId = userInfo.id;
            const tenantSlug = userInfo.tenant?.slug || 'demo';
            const userHistory = chatHistories.get(phone) || [];

            const aiRes = await axios.post(`${API_URL}/ai/ask`, {
                parent_id: userId,
                question: transcribedText,
                history: userHistory
            }, {
                headers: { 'X-Tenant-Slug': tenantSlug }
            });

            if (aiRes.data.success) {
                // Update history
                let updatedHistory = [...userHistory];
                updatedHistory.push({ role: 'user', text: transcribedText });
                updatedHistory.push({ role: 'assistant', text: aiRes.data.answer });
                if (updatedHistory.length > 6) {
                    updatedHistory = updatedHistory.slice(-6);
                }
                chatHistories.set(phone, updatedHistory);

                // Edit the status message with the final response
                return replyMsg.edit(`🎙️ *Voice Transcription:* "${transcribedText}"\n\n🤖 *HubGenie response:*\n\n${aiRes.data.answer}`);
            }
        } catch (err) {
            console.error('Voice Note transcription failed:', err.response?.data || err.message);
            return msg.reply('❌ Sorry, I encountered an error while listening to your voice note. Please try a text query.');
        }
        return;
    }

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
    if (textLower === 'login') {
        userStates.set(phone, { step: 'LOGIN_SCHOOL', data: {} });
        return msg.reply(
            '👋 *Welcome to HubGenie!*\n\n' +
            'Please enter your *School Code* first (e.g. _demo_, _yis_):\n\n' +
            '_(Type *cancel* anytime to stop)_'
        );
    }

    if (textLower === 'logout') {
        try {
            await axios.post(`${API_URL}/logout`, { phone: phone });
            userStates.delete(phone);
            chatHistories.delete(phone);
            return msg.reply('🔒 *Logged out successfully!*\n\nYour account has been disconnected from this WhatsApp number. You can type *login* anytime to connect again.');
        } catch (e) {
            userStates.delete(phone);
            chatHistories.delete(phone);
            return msg.reply('🔒 *Logged out successfully!* Your session has been cleared.');
        }
    }

    // 3. FETCH USER CONTEXT
    let userInfo;
    try {
        const res = await axios.get(`${API_URL}/user/${phone}`);
        userInfo = res.data.user;
    } catch(e) {
        // If not registered/logged in
        if (['hi', 'hello', 'hey', 'menu', 'start', 'help'].includes(textLower)) {
            return msg.reply(
                '👋 *Welcome to HubGenie!*\n\n' +
                'I don\'t recognize your number yet.\n\n' +
                'Type *login* to connect your account using the same username and password you use on the school website.'
            );
        }
        return msg.reply('You are not logged in. Type *login* to connect your account.');
    }

    const userId = userInfo.id;
    const userRole = userInfo.role;

    // --- EXACT COMMANDS ---
    if (textLower === 'broadcast' && ['admin', 'superadmin'].includes(userRole)) {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        userStates.set(phone, { step: 'BROADCAST_TARGET', data: { userId: userId, tenantSlug: tenantSlug } });
        return msg.reply('📢 Let\'s send a broadcast!\n\nWho should receive this? (e.g. *Parents*, *Staff*, or *All*)');
    }

    if (textLower === 'homework' && ['teacher', 'admin', 'superadmin'].includes(userRole)) {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        try {
            const classesRes = await axios.get(`${API_URL}/classes`, {
                headers: { 'X-Tenant-Slug': tenantSlug }
            });
            const classes = classesRes.data.classes;
            if (classes && classes.length > 0) {
                const classList = classes.map(c => `• *${c.name}*`).join('\n');
                userStates.set(phone, { 
                    step: 'HW_CLASS', 
                    data: { 
                        userId: userId, 
                        tenantSlug: tenantSlug, 
                        validClasses: classes.map(c => c.name.toLowerCase()) 
                    } 
                });
                return msg.reply(
                    `📚 *Let's assign homework!*\n\n` +
                    `Here are the available classes in your school:\n${classList}\n\n` +
                    `Please type the exact name of the *Class* to assign homework to:`
                );
            }
        } catch (e) {
            console.error('Failed to fetch classes:', e.message);
        }
        
        userStates.set(phone, { step: 'HW_CLASS', data: { userId: userId, tenantSlug: tenantSlug } });
        return msg.reply('📚 Let\'s assign homework!\n\nWhich *Class* is this for? (e.g. JSS 1)');
    }

    if (textLower === 'menu' || textLower === 'help') {
        if (userRole === 'parent') {
            return msg.reply(
                `====================================\n` +
                `       🎒  *ACADEMYHUB PORTAL*      \n` +
                `====================================\n\n` +
                `🤖 *HubGenie Parent Assistant*\n\n` +
                `You can chat with me naturally, or use these keywords:\n\n` +
                `📅 *attendance* - View today's child attendance\n` +
                `📊 *results*    - View latest term scores\n` +
                `📄 *report*     - Download Official Term 1 PDF Report Card\n` +
                `☎️ *contact*     - Get school address and details\n` +
                `🔔 *subscribe*   - Opt-in to automatic notifications\n` +
                `🔕 *unsubscribe* - Opt-out of notifications\n` +
                `🔒 *logout*      - Disconnect your account\n\n` +
                `------------------------------------\n` +
                `💡 _E.g., try asking: \"Is Abdullahi Bala in school today?\"_`
            );
        } else if (userRole === 'admin' || userRole === 'superadmin') {
            return msg.reply(
                `====================================\n` +
                `    👑  *ACADEMYHUB ADMIN CONSOLE*  \n` +
                `====================================\n\n` +
                `🤖 *HubGenie Admin Assistant*\n\n` +
                `You can ask me questions naturally, or use these commands:\n\n` +
                `📢 *broadcast*   - Send announcement to Parents/Staff\n` +
                `📚 *homework*    - Assign new homework interactively\n` +
                `🔔 *subscribe*   - Opt-in to automated alerts\n` +
                `🔕 *unsubscribe* - Opt-out of alerts\n` +
                `🔒 *logout*      - Disconnect your admin session\n\n` +
                `------------------------------------\n` +
                `💡 _E.g., try asking: \"How many students are in the school?\"_`
            );
        } else {
            return msg.reply(
                `====================================\n` +
                `      👨‍🏫  *ACADEMYHUB FACULTY*     \n` +
                `====================================\n\n` +
                `🤖 *HubGenie Faculty Assistant*\n\n` +
                `You can ask me questions naturally, or use these commands:\n\n` +
                `📚 *homework*    - Assign new homework interactively\n` +
                `🔔 *subscribe*   - Opt-in to automated alerts\n` +
                `🔕 *unsubscribe* - Opt-out of alerts\n` +
                `🔒 *logout*      - Disconnect your faculty session\n\n` +
                `------------------------------------\n` +
                `💡 _E.g., try asking: \"Give me the JSS 1 student counts\"_`
            );
        }
    }

    if (textLower === 'subscribe') {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        await axios.post(`${API_URL}/subscribe/${userId}`, {}, {
            headers: { 'X-Tenant-Slug': tenantSlug }
        });
        return msg.reply('✅ You are now subscribed to automated push notifications.');
    }
    if (textLower === 'unsubscribe') {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        await axios.post(`${API_URL}/unsubscribe/${userId}`, {}, {
            headers: { 'X-Tenant-Slug': tenantSlug }
        });
        return msg.reply('✅ You have successfully unsubscribed from automated notifications.');
    }
    if (textLower === 'contact') {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        const res = await axios.get(`${API_URL}/contact`, {
            headers: { 'X-Tenant-Slug': tenantSlug }
        });
        return msg.reply(`☎️ School Contact\nPhone: ${res.data.phone || 'N/A'}\nEmail: ${res.data.email || 'N/A'}`);
    }

    // --- PARENT LEGACY EXPLICIT COMMANDS ---
    // Kept as fallbacks just in case AI is fully offline.
    if (userRole === 'parent' && textLower === 'attendance') {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        const res = await axios.get(`${API_URL}/attendance/${userId}`, {
            headers: { 'X-Tenant-Slug': tenantSlug }
        });
        let reply = `📅 Today's Attendance\n\n`;
        res.data.students.forEach(s => {
            const isPresent = s.attendance_marks && s.attendance_marks.length > 0 && ['P', 'L'].includes(s.attendance_marks[0].status);
            reply += `${isPresent ? '✅' : '❌'} ${s.first_name} ${s.last_name} - ${isPresent ? 'Present' : 'Absent'}\n`;
        });
        return msg.reply(reply);
    }
    if (userRole === 'parent' && textLower === 'results') {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        const res = await axios.get(`${API_URL}/results/${userId}`, {
            headers: { 'X-Tenant-Slug': tenantSlug }
        });
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

    // --- DIRECT PDF REPORT CARD GENERATION & NATIVE SENDING ---
    const isRequestingReport = textLower.includes('report card') || textLower.includes('download report') || textLower === 'report' || textLower === 'pdf';
    if (userRole === 'parent' && isRequestingReport) {
        const students = userInfo.students;
        if (!students || students.length === 0) {
            return msg.reply('⚠️ No students linked to your parent account.');
        }

        msg.reply('⏳ Request received! Compiling and generating PDF report card. Please wait a moment...');

        const tenantSlug = userInfo.tenant?.slug || 'demo';
        const apiKey = process.env.WHATSAPP_API_KEY || 'dev-local-whatsapp-key-change-in-production';

        for (const s of students) {
            try {
                // Fetch the generated PDF directly from our secure backend endpoint in binary format
                const reportUrl = `${API_URL}/report-card/${s.id}?key=${apiKey}&term=1`;
                const response = await axios.get(reportUrl, {
                    responseType: 'arraybuffer',
                    headers: { 'X-Tenant-Slug': tenantSlug }
                });

                // Convert the PDF binary data into base64 format for WhatsApp Web client
                const base64Data = Buffer.from(response.data, 'binary').toString('base64');
                const media = new MessageMedia(
                    'application/pdf', 
                    base64Data, 
                    `report-card-${s.first_name}-${s.last_name}.pdf`
                );

                // Send the PDF document directly to the parent natively
                await client.sendMessage(msg.from, media, {
                    caption: `📊 Official Term 1 Report Card for *${s.first_name} ${s.last_name}*`
                });
            } catch (err) {
                console.error('Failed to compile and send PDF report card:', err.message);
                msg.reply(`❌ Failed to compile report card for ${s.first_name}. Please contact the school admin.`);
            }
        }
        return;
    }


    // 4. NATURAL LANGUAGE AI FALLBACK (Idea 3)
    // If the message wasn't a strict command or state flow, process it via AI!
    // Parents and Staff get pure AI context!
    try {
        const tenantSlug = userInfo.tenant?.slug || 'demo';
        const userHistory = chatHistories.get(phone) || [];
        
        const aiRes = await axios.post(`${API_URL}/ai/ask`, {
            parent_id: userId,
            question: msg.body,
            history: userHistory
        }, {
            headers: { 'X-Tenant-Slug': tenantSlug }
        });
        if (aiRes.data.success) {
            // Update rolling conversational history cache
            let updatedHistory = [...userHistory];
            updatedHistory.push({ role: 'user', text: msg.body });
            updatedHistory.push({ role: 'assistant', text: aiRes.data.answer });
            
            // Limit to last 6 messages (3 turns) to keep context concise and highly focused
            if (updatedHistory.length > 6) {
                updatedHistory = updatedHistory.slice(-6);
            }
            chatHistories.set(phone, updatedHistory);

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

// =========================================================================
// 🚀 PROACTIVE WEBHOOK SERVER (Bridge to Laravel)
// =========================================================================
const http = require('http');

const WEBHOOK_PORT = process.env.BOT_PORT || 3000;
const server = http.createServer(async (req, res) => {
    res.setHeader('Content-Type', 'application/json');

    // Handle Proactive Message dispatch from Laravel
    if (req.method === 'POST' && req.url === '/webhook/send') {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', async () => {
            try {
                const data = JSON.parse(body);

                // Shared Secret Authentication
                const authHeader = req.headers['authorization'] || req.headers['x-whatsapp-api-key'];
                if (authHeader !== WHATSAPP_API_KEY) {
                    res.statusCode = 401;
                    return res.end(JSON.stringify({ success: false, message: 'Unauthorized: Invalid key' }));
                }

                const { phone, message, mediaUrl, filename, caption } = data;

                if (!phone || (!message && !mediaUrl)) {
                    res.statusCode = 400;
                    return res.end(JSON.stringify({ success: false, message: 'Missing phone or content fields' }));
                }

                const chatId = phone.includes('@c.us') ? phone : `${phone}@c.us`;

                if (mediaUrl) {
                    // Fetch the PDF or image file as binary data
                    const response = await axios.get(mediaUrl, { responseType: 'arraybuffer' });
                    const base64Data = Buffer.from(response.data, 'binary').toString('base64');
                    
                    // Determine mimetype or default to PDF
                    const contentType = response.headers['content-type'] || 'application/pdf';
                    const media = new MessageMedia(contentType, base64Data, filename || 'document.pdf');
                    
                    await client.sendMessage(chatId, media, { caption: caption || '' });
                } else {
                    // Send standard text message
                    await client.sendMessage(chatId, message);
                }

                res.statusCode = 200;
                res.end(JSON.stringify({ success: true, message: 'Message successfully delivered proactive alert' }));
            } catch (err) {
                console.error('Webhook Send Proactive Error:', err.message);
                res.statusCode = 500;
                res.end(JSON.stringify({ success: false, error: err.message }));
            }
        });
    } else {
        res.statusCode = 404;
        res.end(JSON.stringify({ success: false, message: 'Endpoint Not Found' }));
    }
});

server.listen(WEBHOOK_PORT, () => {
    console.log(`🤖 HubGenie Webhook Server is running on local port ${WEBHOOK_PORT}`);
});

process.on('SIGINT', async () => {
    console.log('\n🛑 Shutting down bot gracefully...');
    await client.destroy();
    process.exit(0);
});

client.initialize().catch(err => {
    console.error('❌ Failed to initialize:', err);
});
