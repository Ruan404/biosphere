const WebSocket = require("ws");
const MQTTHandler = require("./MQTTHandler");
const WebSocketServer = require("ws").Server;

const wss1 = new WebSocketServer({ noServer: true });
const wss2 = new WebSocketServer({ noServer: true });

//chat
wss1.on("connection", function connection(ws, req) {
  ws.on("error", console.error);
  console.log("chat websocket");

  ws.on("message", (data) => {
    const received = JSON.parse(data)
   
    if(received.action === "delete" && received.messages){
      wss1.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
          client.send(JSON.stringify(received));
        }
      });
    }
    wss1.clients.forEach((client) => {
      if (client !== ws && client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify(JSON.parse(data)));
      }
    });
  });
});

//lora
wss2.on("connection", function connection(ws) {
  ws.on("error", console.error);
  new MQTTHandler(wss2);
});

module.exports = {wss1, wss2}