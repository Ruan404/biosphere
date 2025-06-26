const http = require("http");
const path = require("path");
const cookie = require("cookie");
const jwt = require("jsonwebtoken");
require("dotenv").config({ path: path.join(__dirname, "../.env") });

const { wss1, wss2, wss3 } = require("./websocket");

const server = http.createServer();

const JWT_SECRET = process.env.JWT_SECRET;
const CLIENT_ORIGIN = process.env.CLIENT;

server.on("upgrade", function upgrade(request, socket, head) {
  const { pathname, searchParams } = new URL(request.url, process.env.SOCKET_BASE);

  if (request.headers.origin !== CLIENT_ORIGIN) {
    socket.destroy();
    return;
  }

  // Extract JWT from query or Authorization header
  const urlToken = searchParams.get("token");
  const authHeader = request.headers["authorization"];
  let token = urlToken;

  if (!token && authHeader && authHeader.startsWith("Bearer ")) {
    token = authHeader.slice(7);
  }

  // Auth-required route
  if (pathname.match(/^\/message(?:\/[a-zA-Z]+(?:_[a-z]+)*)?\/?$/g)) {
    if (!token) {
      socket.destroy();
      return;
    }

    try {
      const decoded = jwt.verify(token, JWT_SECRET);
      request.user = decoded; // Attach full decoded JWT to request
      
      wss3.handleUpgrade(request, socket, head, function done(ws) {
        wss3.emit("connection", ws, request);
      });
    } catch (err) {
      socket.destroy(); // Invalid or expired token
    }

  // Unauthenticated route: chat
  } else if (pathname.match(/^\/chat(?:\/[a-z]+(?:_[a-zA-Z]+)*)?\/?$/g)) {
    wss1.handleUpgrade(request, socket, head, function done(ws) {
      wss1.emit("connection", ws, request);
    });

  // Unauthenticated route: bar
  } else if (pathname === "/bar") {
    wss2.handleUpgrade(request, socket, head, function done(ws) {
      wss2.emit("connection", ws, request);
    });

  } else {
    socket.destroy();
  }
});

server.listen(3000);
