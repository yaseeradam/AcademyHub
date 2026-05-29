const fs = require('fs');
const path = require('path');

const directories = ['./.wwebjs_auth', './.wwebjs_cache'];

console.log('🧹 Clearing HubGenie WhatsApp session cache...');

directories.forEach(dir => {
    const fullPath = path.resolve(__dirname, dir);
    if (fs.existsSync(fullPath)) {
        try {
            fs.rmSync(fullPath, { recursive: true, force: true });
            console.log(`✅ Deleted: ${dir}`);
        } catch (err) {
            console.error(`❌ Failed to delete ${dir}:`, err.message);
        }
    } else {
        console.log(`ℹ️ Already clean: ${dir}`);
    }
});

console.log('🎉 Cache clearing completed successfully!');
