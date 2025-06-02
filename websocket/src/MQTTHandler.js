require("dotenv").config();
const mqtt = require("mqtt");

class MQTTHandler {
  constructor(wss) {
    this.mqttClient = mqtt.connect(process.env.MQTT_SERVER);
    this.wss = wss;
    this.latestValues = {}; // { temp: 25, humidity: 55 }

    this.mqttClient.on("connect", this.onConnect.bind(this));
    this.mqttClient.on("message", this.onMessage.bind(this));

    this.wss.on("connection", (ws) => {
      console.log("WebSocket client connected");

      // Send the latest values on client connect
      if (Object.keys(this.latestValues).length > 0) {
        ws.send(JSON.stringify(this.latestValues));
      }

      ws.on("close", () => {
        console.log("WebSocket client disconnected");
      });
    });
  }

  onConnect() {
    console.log("Connected to MQTT broker");

    const topicToSubscribe = `${process.env.MQTT_TOPIC}/#`;
    this.mqttClient.subscribe(topicToSubscribe, (err) => {
      if (err) {
        console.error("Error subscribing to MQTT topic");
      } else {
        console.log(`Subscribed to topic: ${topicToSubscribe}`);
      }
    });
  }

  onMessage(topic, message) {
    const subtopic = topic.replace(`${process.env.MQTT_TOPIC}/`, '');
    const value = this.parseValue(message.toString());

    this.latestValues[subtopic] = value;

    console.log(`Updated ${subtopic}: ${value}`);

    // Send updated values to all WebSocket clients
    const payload = JSON.stringify(this.latestValues);
    this.wss.clients.forEach((ws) => {
      if (ws.readyState === ws.OPEN) {
        ws.send(payload);
      }
    });
  }

  parseValue(value) {
    // Try to parse numeric, fallback to string
    const num = parseFloat(value);
    return isNaN(num) ? value : num;
  }
}

module.exports = MQTTHandler;
