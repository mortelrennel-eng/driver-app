const axios = require('axios');

async function testApiKey() {
    console.log("--- TESTING FIREBASE INSTALLATIONS API WITH API KEY ---");
    const url = "https://firebaseinstallations.googleapis.com/v1/projects/eurotaxi-4c240/installations?key=AIzaSyADek8a9SP9shob9-ccesxI9PQ72e8kacQ";
    const payload = {
        appId: "1:932083063677:android:9e0692a5cda615d3b30a14",
        sdkVersion: "a:17.0.0"
    };

    try {
        const response = await axios.post(url, payload, {
            headers: {
                'Content-Type': 'application/json',
                'User-Agent': 'test-agent'
            }
        });
        console.log("SUCCESS! The API Key is NOT restricted. Response status:", response.status);
        console.log("Data:", response.data);
    } catch (error) {
        console.error("FAILED! Google API Key test failed.");
        if (error.response) {
            console.error("HTTP Status:", error.response.status);
            console.error("Error Response Data:", JSON.stringify(error.response.data, null, 2));
        } else {
            console.error("Error Message:", error.message);
        }
    }
}

testApiKey();
