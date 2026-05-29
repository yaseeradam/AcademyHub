// Test WhatsApp Bot API without actual WhatsApp
// This simulates what the bot does

const axios = require('axios');

// Set up Axios default headers for Multi-Tenant discovery and API Key authentication
axios.defaults.headers.common['X-WhatsApp-Api-Key'] = 'dev-local-whatsapp-key-change-in-production';
axios.defaults.headers.common['X-Tenant-Slug'] = 'demo';

const API_URL = 'http://myacademy-laravel.test/api/whatsapp';
const TEST_PHONE = '2348012345678'; // Test phone number

async function testBotAPI() {
    console.log('🧪 Testing MyAcademy WhatsApp Bot API\n');

    // Test 1: Get Parent Info
    console.log('Test 1: Get Parent Info');
    try {
        const res = await axios.get(`${API_URL}/parent/${TEST_PHONE}`);
        console.log('✅ Parent found:', res.data.parent.name);
    } catch (e) {
        console.log('❌ Parent not found (expected for new number)');
    }

    // Test 2: Register Parent
    console.log('\nTest 2: Register Parent');
    try {
        const res = await axios.post(`${API_URL}/register`, {
            email: 'testparent@test.com',
            admission_number: 'STU20240001',
            phone: TEST_PHONE
        });
        console.log('✅ Registration response:', res.data);
    } catch (e) {
        console.log('❌ Registration failed:', e.response?.data || e.message);
    }

    // Test 3: Get Attendance
    console.log('\nTest 3: Get Attendance');
    try {
        const res = await axios.get(`${API_URL}/attendance/1`);
        console.log('✅ Attendance data:', res.data);
    } catch (e) {
        console.log('❌ Attendance failed:', e.response?.data || e.message);
    }

    // Test 4: Get Results
    console.log('\nTest 4: Get Results');
    try {
        const res = await axios.get(`${API_URL}/results/1`);
        console.log('✅ Results data:', res.data);
    } catch (e) {
        console.log('❌ Results failed:', e.response?.data || e.message);
    }

    console.log('\n✅ API Testing Complete!');
}

testBotAPI();
