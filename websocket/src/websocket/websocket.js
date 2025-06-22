const WebSocket = require("ws");
const MQTTHandler = require("../mqtt/MQTTHandler");
const WebSocketServer = require("ws").Server;

const wss1 = new WebSocketServer({ noServer: true });
const wss2 = new WebSocketServer({ noServer: true });
const wss3 = new WebSocketServer({ noServer: true });

// --- Chat WebSocket ---
wss1.on("connection", function connection(ws, req) {
  ws.on("error", console.error);
  console.log("chat websocket");

  ws.topic = req.url.split("/chat/")[1];

  ws.on("message", (data) => {
    const received = JSON.parse(data);
   
    if (ws.topic === received.topic) {
      wss1.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN && client.topic === ws.topic) {
          client.send(JSON.stringify(received));
        }
      });
    }
  });
});

// --- LoRa WebSocket (MQTT integration) ---
wss2.on("connection", function connection(ws) {
  ws.on("error", console.error);
  new MQTTHandler(wss2);
});

// --- Private Messaging WebSocket ---
wss3.on("connection", function connection(ws, req) {
  ws.on("error", console.error);
  console.log("messagerie websocket");
  ws.user = req.user;

  ws.on("message", (data) => {
    const received = JSON.parse(data);
    
    if(received.action === "delete" && received.messages){
      wss3.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
          client.send(JSON.stringify(received));
        }
      });
    }

    // Validate that the message belongs to this user (sender or recipient)
    if (received.sender === ws.user || received.recipient === ws.user) {
      wss3.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
          client.send(JSON.stringify(received));
        }
      });
    }
  });
});

module.exports = { wss1, wss2, wss3 };
