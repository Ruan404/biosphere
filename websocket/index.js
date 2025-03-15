const http = require("http");
const WebSocket = require("ws")
const WebSocketServer = require("ws").Server;

const server = http.createServer();
const wss1 = new WebSocketServer({ noServer: true });
const wss2 = new WebSocketServer({ noServer: true });

//chat
wss1.on("connection", function connection(ws, req) {
  ws.on("error", console.error);
  console.log("chat websocket");

  ws.on("message", (data) => {
    wss1.clients.forEach((client) => {
      if (client !== ws && client.readyState === WebSocket.OPEN) {
        client.send(
          JSON.stringify(JSON.parse(data))
        );
      }
    });
  });
});

wss2.on("connection", function connection(ws) {
  ws.on("error", console.error);

  // ...
});

server.on("upgrade", function upgrade(request, socket, head) {
  const { pathname } = new URL(request.url, "wss://localhost");

  if (pathname.match(/^\/chat\/[a-z]+(?:_[a-z]+)*$/g)) {
    wss1.handleUpgrade(request, socket, head, function done(ws) {
      wss1.emit("connection", ws, request);
    });
  } else if (pathname === "/bar") {
    wss2.handleUpgrade(request, socket, head, function done(ws) {
      wss2.emit("connection", ws, request);
    });
  } else {
    socket.destroy();
  }
});

server.listen(8000);
