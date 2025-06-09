const http = require("http");
const fs = require("fs");
const path = require("path");
const cookie = require("cookie");
const { unserializeSession } = require("php-unserialize");
require("dotenv").config();

const { wss1, wss2, wss3 } = require("./websocket");

const server = http.createServer();

const sessionDir = "../../../tmp/"; // Change if different
const PHPSESSID_PREFIX = "sess_";

server.on("upgrade", function upgrade(request, socket, head) {
  const { pathname } = new URL(request.url, process.env.SOCKET_BASE);

  if (request.headers.origin !== process.env.CLIENT) {
    console.log("Invalid origin:", request.headers.origin);
    socket.destroy();
    return;
  }

  // Chat route
  if (pathname.match(/^\/chat(?:\/[a-z]+(?:_[a-zA-Z]+)*)?\/?$/g)) {
    wss1.handleUpgrade(request, socket, head, function done(ws) {
      wss1.emit("connection", ws, request);
    });

  // Message route — validate session
  } else if (pathname.match(/^\/message(?:\/[a-zA-Z]+(?:_[a-z]+)*)?\/?$/g)) {
    const cookies = cookie.parse(request.headers.cookie || "");
    const sessionId = cookies.PHPSESSID;

    if (!sessionId) {
      socket.destroy();
      return;
    }

    const sessionFile = path.join(sessionDir, PHPSESSID_PREFIX + sessionId);

    fs.readFile(sessionFile, "utf8", (err, data) => {
      if (err || !data) {
        socket.destroy();
        return;
      }

      try {
        const session = unserializeSession(data);
        const user = session.username;
        if (!user) {
          socket.destroy();
          return;
        }
        request.user = user; // attach user to request

        wss3.handleUpgrade(request, socket, head, function done(ws) {
          wss3.emit("connection", ws, request);
        });
      } catch (e) {
        socket.destroy();
      }
    });

  // LoRa or other route
  } else if (pathname === "/bar") {
    wss2.handleUpgrade(request, socket, head, function done(ws) {
      wss2.emit("connection", ws, request);
    });
  } else {
    socket.destroy();
  }
});

server.listen(3000);
