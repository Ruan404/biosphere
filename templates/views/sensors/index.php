<?php
    $style = "sensor";
    $title = "station météo";
    $description = "voir les données de la station météo";
?>

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