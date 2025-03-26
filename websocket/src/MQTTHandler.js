require("dotenv").config();
const mqtt = require("mqtt");

class MQTTHandler {
  constructor(wss) {
    this.mqttClient = mqtt.connect(process.env.MQTT_SERVER);
    this.wss = wss;
    this.pendingMessages = []; // Store messages if no WebSocket clients are connected

    this.mqttClient.on("connect", this.onConnect.bind(this));
    this.mqttClient.on("message", this.onMessage.bind(this));

    this.wss.on("connection", (ws) => {
      console.log("WebSocket client connected");

      // Send any pending messages to the new client
      while (this.pendingMessages.length > 0) {
        const messageToSend = this.pendingMessages.shift();
        ws.send(messageToSend);
        console.log("Sent stored message to new client.");
      }

      ws.on("close", () => {
        console.log("WebSocket client disconnected");
      });
    });
  }

  //Handle MQTT connection
  onConnect() {
    console.log("Connected to MQTT broker");

    this.mqttClient.subscribe(process.env.MQTT_TOPIC, (err) => {
      if (err) {
        console.error("Error subscribing to MQTT topic", err);
      } else {
        console.log(`Subscribed to topic: ${process.env.MQTT_TOPIC}`);
      }
    });
  }

  //Handle incoming MQTT messages
  onMessage(topic, message) {
    const msg = JSON.stringify({ topic, message: message.toString() });
    console.log(`MQTT Received: ${msg}`);

    if (this.wss.clients.size > 0) {
      this.wss.clients.forEach((ws) => {
        if (ws.readyState === ws.OPEN) {
          ws.send(msg);
        }
      });
    } else {
      console.log("No WebSocket clients connected. Storing message.");
      this.pendingMessages.push(msg);
    }
  }
}

module.exports = MQTTHandler;
