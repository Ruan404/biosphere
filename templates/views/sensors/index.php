<div class="sensor-ctn">
    <table>
        <thead>
            <tr>
                <th class="head">Capteur</th>
                <th class="head">Valeur</th>
            </tr>
        </thead>
        <tbody id="sensor-table-body">

        </tbody>
    </table>
</div>


<script>
    const socket = new WebSocket(`ws://localhost:3000/bar`);

    // When a message is received from the WebSocket server
    socket.onmessage = function (event) {
        const data = JSON.parse(event.data);
        const tbody = document.getElementById("sensor-table-body");
        tbody.innerHTML = ""; // Clear previous entries

        for (const [sensor, value] of Object.entries(data)) {
            const row = document.createElement("tr");
            row.innerHTML = `
            <td class="sensor">${sensor}</td>
            <td class="sensor-value">${value}</td>
            `;
            tbody.appendChild(row);
        }
    }
</script>

<style>
    table,
    tr,
    td,
    th {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .sensor-ctn {
        overflow-x: auto;
    }

    .sensor-value, .head {
        padding: 0.5rem 1rem;
    }
    .head{
        text-align: left;
    }

    .sensor {
        padding: 0.5rem 1rem;
        font-weight: bold;
    }
</style>