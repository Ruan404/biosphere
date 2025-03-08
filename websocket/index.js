const WebSocket = require("ws");
const Chat = require("./models/Chat.js");

// Create WebSocket server
const wss = new WebSocket.Server({ port: 8000 });

wss.on("connection", (ws) => {
  console.log("New client connected");

  // Listen for incoming messages
  ws.on("message", (data) => {
    const messageData = JSON.parse(data);
    // Save message in the database
    if (messageData.topic_id) {
      if(new Chat().newChat(messageData)){
        wss.clients.forEach((client) => {
          if (client.readyState === WebSocket.OPEN) {
            client.send(
              JSON.stringify({
                pseudo: messageData.pseudo,
                date: new Date(Date.now()),
                message: messageData.message,
              })
            );
          }
        });
      }
    }
  });

  new Chat(ws).getChat(4, ws); 

  ws.on("error", (error) => {
    console.error("WebSocket error:", error);
  });
});

console.log("WebSocket server running on ws://localhost:8000");
