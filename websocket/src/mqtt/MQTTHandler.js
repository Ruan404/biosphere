require("dotenv").config();
const mqtt = require("mqtt");
const fs = require("fs");
const path = require("path");

class MQTTHandler {
  constructor(wss) {
    this.mqttClient = mqtt.connect(process.env.MQTT_SERVER);
    this.wss = wss;
    this.latestValues = {}; // e.g., { weather: {}, monitoring: {} }
    this.topicGroups = this.loadTopicsFromFile();
    this.pendingUpdate = false;

    this.mqttClient.on("connect", this.onConnect.bind(this));
    this.mqttClient.on("message", this.onMessage.bind(this));

    this.wss.on("connection", (ws) => {
      if (Object.keys(this.latestValues).length > 0) {
        ws.send(JSON.stringify(this.latestValues));
      }
    });
  }

  loadTopicsFromFile() {
    try {
      const filePath = path.resolve(__dirname, "mqtt-topics.json");
      const data = fs.readFileSync(filePath, "utf8");
      const parsed = JSON.parse(data);
      return parsed.topics || [];
    } catch (err) {
      console.error("Failed to load MQTT topics:", err);
      return [];
    }
  }

  onConnect() {
    this.topicGroups.forEach(({ topic }) => {
      this.mqttClient.subscribe(topic, (err) => {
        if (err) {
          console.error(`Error subscribing to topic: ${topic}`);
        } else {
          console.log(`Subscribed to topic: ${topic}`);
        }
      });
    });
  }

  onMessage(topic, message) {
    const payload = message.toString();
    const parsed = this.tryParseJSON(payload);

    const group = this.getGroupForTopic(topic) || "default";
    if (!this.latestValues[group]) this.latestValues[group] = {};

    if (typeof parsed === "object" && parsed !== null) {
      Object.assign(this.latestValues[group], parsed);
    } else {
      const key = topic.split("/").pop();
      this.latestValues[group][key] = parsed;
    }

    if (!this.pendingUpdate) {
      this.pendingUpdate = true;
      setTimeout(() => {
        this.broadcastLatestValues();
        this.pendingUpdate = false;
      }, 500);
    }
  }

  getGroupForTopic(topic) {
    for (const { topic: pattern, group } of this.topicGroups) {
      const regex = this.mqttPatternToRegex(pattern);
      if (regex.test(topic)) {
        return group;
      }
    }
    return null;
  }

  mqttPatternToRegex(pattern) {
    // Convert MQTT wildcards to RegExp
    const regexStr = pattern
      .replace(/\+/g, "[^/]+")
      .replace(/#/g, ".+");
    return new RegExp("^" + regexStr + "$");
  }

  broadcastLatestValues() {
    const dataToSend = JSON.stringify(this.latestValues);
    this.wss.clients.forEach((ws) => {
      if (ws.readyState === ws.OPEN) {
        ws.send(dataToSend);
      }
    });
  }

  tryParseJSON(str) {
    try {
      return JSON.parse(str);
    } catch {
      const num = parseFloat(str);
      return isNaN(num) ? str : num;
    }
  }
}

module.exports = MQTTHandler;
