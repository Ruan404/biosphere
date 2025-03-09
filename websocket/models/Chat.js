const db = require("../core/database.js");
const WebSocket = require("ws")
const topic = require("./Topic.js");

const Topic = new topic();

class Chat {
  constructor(topic_id, message, pseudo) {
    this.pseudo = pseudo;
    this.topic_id = topic_id;
    this.message = message;
  }

  getChat(topic, ws) {
    const query =
      "SELECT * FROM `chat` JOIN `topic` ON chat.topic_id = topic.id WHERE topic.name = ?";
    db.query(query, [topic], (err, results) => {
      if (err) return null;
      results.forEach((result) => {
        ws.send(
          JSON.stringify({
            pseudo: result.pseudo,
            message: result.message,
            date: result.date,
          })
        );
      });
    });
  }

  newChat(messageData, wss1) {
    const query =
      "INSERT INTO chat (pseudo, topic_id, message) VALUES (?, ?, ?)";

    Topic.getTopicByName(messageData.topic)
      .then((data) => {
        db.query(
          query,
          [messageData.pseudo, data[0].id, messageData.message],
          (err, result) => {
            if (err) throw err;
            console.log("Message saved:", result.insertId);

            // Broadcast message to all connected clients

            wss1.clients.forEach((client) => {
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
        );
      })
      .catch(() => {
        return null;
      });
  }
}

module.exports = Chat;
