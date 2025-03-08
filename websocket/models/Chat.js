const db = require("../core/database.js")

class Chat {
  constructor(topic_id, message, pseudo) {
    this.pseudo = pseudo;
    this.topic_id = topic_id;
    this.message = message;
  }

  getChat(topic_id, ws) {
    const query = "SELECT * FROM CHAT WHERE topic_id = ?";
    db.query(query, [topic_id], (err, results) => {
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

  newChat(messageData) {
    const query =
      "INSERT INTO chat (pseudo, topic_id, message) VALUES (?, ?, ?)";
    db.query(
      query,
      [messageData.pseudo, messageData.topic_id, messageData.message],
      (err, result) => {
        if (err) throw err;
        console.log("Message saved:", result.insertId);

        // Broadcast message to all connected clients

        return true;
      }
    );
    return true;
  }
}

module.exports = Chat;
