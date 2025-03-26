<div class="sensors">
    <div class="sensor-ctn">
    </div>
</div>


<script>
    const socket = new WebSocket(`ws://localhost:8000/bar`);

    // When a message is received from the WebSocket server
    socket.onmessage = function (event) {
        const data = JSON.parse(event.data);
        console.log(data)
        // document.querySelector(".sensor-ctn").innerHTML += `<h2>Topic : ${data.topic}</h2>    <div class="sensor-data"></div>`

        if (data.message) {
            var message = JSON.parse(data.message)
            console.log(message)
            document.querySelector(".sensor-ctn").innerHTML += `
                <div class="sensor-info">
                    <p>${new Date(message["received_time"]).toLocaleString()}</p>
                </div>
                <div class="sensor-data"></div>
            `

            if (message.payload) {
                var payload = JSON.parse(message.payload)

                for (var key in payload) {
                    document.querySelector(".sensor-data").innerHTML += `
                    <div class="sensor">
                        <p class="sensor-key">${key}</p>
                        <p class="sensor-value">${payload[key]}</p>
                    </div>
                `
                }
            }
        }

    }
</script>

<style>
    .sensor-ctn {
        display: grid;
        border-radius: 1rem;
        background: rgb(var(--bg-2));
        width: fit-content;
        overflow: hidden;
    }

    .sensor-info{
        background: rgb(var(--bg-3));
        color: rgb(var(--fg-2));
        padding: 1rem 1.5rem;
    }

    .sensor-data {
        border-bottom-right-radius: 1rem;
        border-bottom-left-radius: 1rem;
        overflow: hidden;
        border: 1px solid rgb(var(--bg-3));
    }

    .sensor {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 1.5rem 0.5rem;
        border-top: 1px solid rgb(var(--bg-3));
    }
</style>