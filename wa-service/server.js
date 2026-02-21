const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');
const axios = require('axios');

const app = express();
app.use(express.json());

// Store client status
let clientReady = false;
let qrCode = null;

// Initialize WhatsApp client with session save
const client = new Client({
    authStrategy: new LocalAuth({
        clientId: 'cake-shop-pos'
    }),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

// QR Code event
client.on('qr', (qr) => {
    console.log('📱 SCAN QR CODE INI:');
    qrcode.generate(qr, { small: true });
    qrCode = qr;
    clientReady = false;
});

// Ready event
client.on('ready', () => {
    console.log('✅ WhatsApp CLIENT READY!');
    console.log('🚀 Service running on http://localhost:3000');
    clientReady = true;
    qrCode = null;
});

// Disconnect event
client.on('disconnected', (reason) => {
    console.log('❌ WhatsApp disconnected:', reason);
    clientReady = false;
    // Restart client
    client.initialize();
});

// Message event (untuk testing)
client.on('message', message => {
    console.log(`📨 Message from ${message.from}: ${message.body}`);
    
    // Auto reply untuk testing
    if (message.body.toLowerCase() === 'ping') {
        message.reply('pong dari SweetCake Bot! 🍰');
    }
});

// Initialize client
client.initialize();

// ============= API ENDPOINTS =============

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: clientReady ? 'ready' : 'connecting',
        qr: qrCode
    });
});

// Send message
// Send message endpoint dengan LOGGING
app.post('/send', async (req, res) => {
    const { phone, message } = req.body;
    
    console.log('📨 REQUEST RECEIVED:', { phone, message }); // <-- TAMBAH INI
    
    if (!clientReady) {
        console.log('❌ Client not ready'); // <-- TAMBAH INI
        return res.status(503).json({ 
            error: 'WhatsApp client not ready',
            status: 'connecting'
        });
    }
    
    if (!phone || !message) {
        console.log('❌ Invalid request: missing phone or message'); // <-- TAMBAH INI
        return res.status(400).json({ error: 'Phone and message required' });
    }
    
    try {
        // Format nomor: harus pake @c.us
        let formattedPhone = phone.replace(/[^0-9]/g, '');
        if (!formattedPhone.endsWith('@c.us')) {
            formattedPhone = `${formattedPhone}@c.us`;
        }
        
        console.log('📤 Sending to:', formattedPhone); // <-- TAMBAH INI
        
        await client.sendMessage(formattedPhone, message);
        
        console.log('✅ Message sent successfully!'); // <-- TAMBAH INI
        
        res.json({ 
            success: true, 
            message: 'Message sent successfully' 
        });
        
    } catch (error) {
        console.error('❌ Send error:', error); // <-- TAMBAH INI
        res.status(500).json({ 
            error: 'Failed to send message',
            details: error.message 
        });
    }
});

// Send to multiple
app.post('/send-bulk', async (req, res) => {
    const { recipients } = req.body; // [{phone, message}]
    
    if (!clientReady) {
        return res.status(503).json({ error: 'WhatsApp client not ready' });
    }
    
    const results = [];
    
    for (const item of recipients) {
        try {
            let phone = item.phone.replace(/[^0-9]/g, '') + '@c.us';
            await client.sendMessage(phone, item.message);
            results.push({ phone: item.phone, success: true });
        } catch (error) {
            results.push({ phone: item.phone, success: false, error: error.message });
        }
    }
    
    res.json({ results });
});

// Get QR (buat ditampilkan di web)
app.get('/qr', (req, res) => {
    if (qrCode) {
        res.json({ qr: qrCode });
    } else {
        res.json({ qr: null, status: clientReady ? 'ready' : 'no_qr' });
    }
});

// Status
app.get('/status', (req, res) => {
    res.json({
        ready: clientReady,
        timestamp: new Date().toISOString()
    });
});

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🌐 HTTP Server running on port ${PORT}`);
});
