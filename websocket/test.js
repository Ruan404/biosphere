const mqtt = require("mqtt");
const client = mqtt.connect("mqtt://10.44.18.22");

client.on("connect", () => {
  client.subscribe("/icam/LoRaGateway/sensorPayload", (err) => {
    // if (!err) {
    //   client.publish("presence", "Hello mqtt");
    // }
  });
});

client.on("message", (topic, message) => {
  // message is Buffer
  console.log(message.toString());
  client.end();
});