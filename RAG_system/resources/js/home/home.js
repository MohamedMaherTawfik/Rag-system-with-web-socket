import { API_KEY, WS_URL } from "../constants/consts.js";

const api_key = API_KEY;
const token = localStorage.getItem("token");
const userId = localStorage.getItem("userId");

if (!token || !userId) {
    console.warn("Token or User ID missing, redirecting to login");
    window.location.href = "/login";
}

if (!token) {
    window.location.href = "/login";
}

const socket = io(WS_URL, {
    auth: {
        token: token,
    },
});
socket.on("connect", () => {
    console.log("Connected to WebSocket server");
});

socket.on("answer", (answer) => {
    const messagesDiv = document.getElementById("messages");
    messagesDiv.textContent = answer;
});

socket.on("error", (msg) => {
    const messagesDiv = document.getElementById("messages");
    messagesDiv.textContent = "WebSocket Error: " + msg;
});

document.getElementById("chatForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const file = formData.get("file");
    const prompt = formData.get("prompt");
    document.getElementById("messages").textContent = "📤 PDF is uploading...";

    if (!file || !prompt) {
        alert("Please select a PDF and enter a question.");
        return;
    }

    try {
        const uploadRes = await fetch("/api/v1/pdf/upload", {
            method: "POST",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${token}`,
                "x-api-key": api_key,
            },
            body: formData,
        });

        if (!uploadRes.ok) {
            const err = await uploadRes.json();
            console.error("Upload Error Response:", err);
            throw new Error(err.message || "Failed to upload PDF");
        }

        const uploadData = await uploadRes.json();
        document.getElementById("messages").textContent =
            "Processing your question...";
        socket.emit("ask", {
            question: prompt,
            token: token,
            userId: userId,
        });
    } catch (err) {
        document.getElementById("messages").textContent =
            "Upload Error: " + err.message;
        console.error("Upload Exception:", err);
    }
});
