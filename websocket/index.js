const http = require("http");
const WebSocketServer = require("ws").Server;

const chat = require("./models/Chat.js");

const server = http.createServer();
const wss1 = new WebSocketServer({ noServer: true });
const wss2 = new WebSocketServer({ noServer: true });
const Chat = new chat();

//chat
wss1.on("connection", function connection(ws, req) {
  ws.on("error", console.error);
  console.log("chat websocket");
  var topic = req.url.substring(req.url.lastIndexOf("/") + 1);

  //send messages chat
  Chat.getChat(topic, ws);

  ws.on("message", (data) => {
    Chat.newChat(JSON.parse(data), wss1)
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
