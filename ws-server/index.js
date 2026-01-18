require("dotenv").config();
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const axios = require("axios");

const app = express();
const server = http.createServer(app);

const io = new Server(server, {
  cors: {
    origin: "*",
  },
});

/**
 * =========================
 *  AUTH MIDDLEWARE (WS)
 * =========================
 */
io.use(async (socket, next) => {
  const token = socket.handshake.auth?.token;
  const ip = socket.handshake.address;

  if (!token) {
    console.warn("WS BLOCKED: Missing token", { ip });
    return next(new Error("Unauthenticated"));
  }

  if (!process.env.API_KEY) {
    console.error("API_KEY is missing in environment variables");
    return next(new Error("Server misconfiguration"));
  }

  try {
    const res = await axios.get("http://127.0.0.1:8080/api/v1/users/profile", {
      headers: {
        Authorization: `Bearer ${token}`,
        "x-api-key": process.env.API_KEY,
        Accept: "application/json",
      },
      timeout: 60000,
    });

    if (!res.data?.data?.user?.id) {
      console.error("Invalid profile response structure", {
        ip,
        data: res.data,
      });
      return next(new Error("Invalid user data"));
    }

    socket.user = res.data.data.user;
    console.log("WS AUTHORIZED:", socket.user.id, "| IP:", ip);

    next();
  } catch (err) {
    const status = err.response?.status;
    const message = err.message || "Unknown error";

    console.error("WS BLOCKED: Invalid token", {
      ip,
      status,
      message,
    });

    if (status === 401) {
      return next(new Error("Unauthorized"));
    } else if (status === 403) {
      return next(new Error("Forbidden"));
    } else {
      return next(new Error("Authentication service unavailable"));
    }
  }
});

/**
 * =========================
 *  CONNECTION
 * =========================
 */
io.on("connection", (socket) => {
  if (!socket.user || !socket.user.id) {
    console.error("Connected socket without valid user – closing");
    return socket.disconnect(true);
  }

  console.log(`WS CONNECTED | socket=${socket.id} | user=${socket.user.id}`);

  socket.on("ask", async ({ question }) => {
    if (!question || typeof question !== "string" || !question.trim()) {
      return socket.emit(
        "error",
        "Question is required and must be a non-empty string",
      );
    }

    try {
      const res = await axios.post(
        "http://127.0.0.1:8080/api/v1/chat",
        { question: question.trim() },
        {
          headers: {
            Authorization: `Bearer ${socket.handshake.auth.token}`,
            "x-api-key": process.env.API_KEY,
            Accept: "application/json",
          },
          timeout: 60000,
        },
      );

      // تحقق من وجود answer في الرد
      if (res.data?.answer == null) {
        return socket.emit("error", "Invalid response from chat service");
      }

      socket.emit("answer", res.data.answer);
    } catch (err) {
      console.error("WS CHAT ERROR", {
        userId: socket.user.id,
        status: err.response?.status,
        message: err.message,
      });

      socket.emit("error", "Chat service error");
    }
  });

  socket.on("disconnect", (reason) => {
    console.log(
      `WS DISCONNECTED | socket=${socket.id} | user=${socket.user.id} | reason=${reason}`,
    );
  });
});

const PORT = process.env.WS_PORT || 3001;
server.listen(PORT, "0.0.0.0", () => {
  console.log(`WebSocket server running on ws://0.0.0.0:${PORT}`);
});
