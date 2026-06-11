const https = require('https');
const querystring = require('querystring');
const crypto = require('crypto');

const CONFIG = {
    APP_KEY: '8FB345B8693CCD00149EBCB96D0EAE85339A22A4105B6558',
    APP_SECRET: '9ce8f4e1fe3b430c8b94f24aa83b809c',
    USERNAME: 'Admin_shiellamarie',
    PASSWORD_MD5: '3406d9a5d03ec8d3c3c7b433eee0a8a7'
};

function getTimestamp() {
    const now = new Date();
    return now.getUTCFullYear() + '-' +
        String(now.getUTCMonth()+1).padStart(2,'0') + '-' +
        String(now.getUTCDate()).padStart(2,'0') + ' ' +
        String(now.getUTCHours()).padStart(2,'0') + ':' +
        String(now.getUTCMinutes()).padStart(2,'0') + ':' +
        String(now.getUTCSeconds()).padStart(2,'0');
}

function generateSignature(params, secret) {
    const keys = Object.keys(params).sort();
    let raw = secret;
    for (const key of keys) {
        if (key !== 'sign' && params[key] !== null && params[key] !== undefined && params[key] !== '') {
            raw += key + params[key];
        }
    }
    raw += secret;
    return crypto.createHash('md5').update(raw).digest('hex').toUpperCase();
}

function apiPost(params) {
    return new Promise((resolve) => {
        const body = querystring.stringify(params);
        const req = https.request({
            hostname: 'hk-open.tracksolidpro.com',
            path: '/route/rest',
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Content-Length': Buffer.byteLength(body) }
        }, (res) => {
            let data = '';
            res.on('data', d => data += d);
            res.on('end', () => { try { resolve(JSON.parse(data)); } catch(e) { resolve({ raw: data }); } });
        });
        req.on('error', () => resolve(null));
        req.write(body);
        req.end();
    });
}

async function delay(ms) { return new Promise(r => setTimeout(r, ms)); }

async function runTest() {
    // 1. GET TOKEN
    const tokenParams = {
        method: 'jimi.oauth.token.get',
        app_key: CONFIG.APP_KEY,
        timestamp: getTimestamp(),
        format: 'json', v: '0.9', sign_method: 'md5', expires_in: '7200',
        user_id: CONFIG.USERNAME, user_pwd_md5: CONFIG.PASSWORD_MD5
    };
    const tokenRes = await apiPost(tokenParams);
    const token = tokenRes?.result?.accessToken;
    if (!token) return console.log('Token error', tokenRes);
    
    // 2. GET VALID IMEI
    await delay(1000);
    const devParams = {
        method: 'jimi.user.device.location.list',
        app_key: CONFIG.APP_KEY, access_token: token, timestamp: getTimestamp(),
        format: 'json', v: '1.0', sign_method: 'md5', target: CONFIG.USERNAME
    };
    devParams.sign = generateSignature(devParams, CONFIG.APP_SECRET);
    const devRes = await apiPost(devParams);
    if (devRes.code !== 0 || !devRes.result) return console.log('No devices');
    const validImei = devRes.result[0].imei;
    console.log('Testing with IMEI:', validImei);
    
    // 3. TEST INSTRUCTION SEND (Relay cutoff simulation)
    await delay(1000);
    const instParamJson = JSON.stringify({
        inst_id: "113", 
        inst_template: "RELAY,1#", 
        params: [], 
        is_cover: "true"
    });
    
    const sendParams = {
        method: 'jimi.open.instruction.send',
        app_key: CONFIG.APP_KEY,
        access_token: token,
        timestamp: getTimestamp(),
        format: 'json',
        v: '1.0',
        sign_method: 'md5',
        imei: validImei,
        inst_param_json: instParamJson
    };
    sendParams.sign = generateSignature(sendParams, CONFIG.APP_SECRET);
    
    console.log('Sending params:', sendParams);
    const sendRes = await apiPost(sendParams);
    console.log('\n--- SEND RESULT ---');
    console.log(JSON.stringify(sendRes, null, 2));
}
runTest();
