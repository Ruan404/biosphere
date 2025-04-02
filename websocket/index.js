const http = require("http");
const { wss1, wss2 } = require("./src/websocket");
const server = http.createServer();

server.on("upgrade", function upgrade(request, socket, head) {
  const { pathname } = new URL(request.url, "ws://localhost");
  if (request.headers.origin == 'http://localhost:8000') {
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
  }
  else{
    console.log("error")
  }
});

server.listen(3000);
