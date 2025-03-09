const db = require("../core/database.js");

class Topic {
  constructor(id, name) {
    this.id = id;
    this.name = name;
  }

  getTopicByName(topic) {
    return new Promise((resolve, reject) => {
      const query = "SELECT * FROM `topic` WHERE topic.name = ?";
      db.query(query, [topic], (err, results) => {
        if (err){
            reject(null)
        }
        else{
            resolve(results[0])
        }
      });
    });
  }
}

module.exports = Topic;
