<?php
$style = "sensor";
$title = "data";
$description = "voir les données";
?>

<main>
    <div class="sensor-ctn">
        <div class="select-ctn">
            <label for="group-select">Voir :</label>
            <select id="group-select">
                <!-- Groups will be added dynamically -->
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="head">Appareil</th>
                    <th class="head">Valeur</th>
                </tr>
            </thead>
            <tbody id="sensor-table-body">
                <!-- Sensor data rows go here -->
            </tbody>
        </table>
    </div>
</main>

<script>
    const socket = new WebSocket(`${WEBSOCKET_URL}/bar`);
    let latestData = {};

    const groupSelect = document.getElementById("group-select");
    const tbody = document.getElementById("sensor-table-body");

    socket.onmessage = function (event) {
        const data = JSON.parse(event.data);
        latestData = data;

        // Populate group selector
        groupSelect.innerHTML = '';
        Object.keys(data).forEach(group => {
            const option = document.createElement("option");
            option.value = group;
            option.textContent = group;
            groupSelect.appendChild(option);
        });

        // Show the first group by default
        if (groupSelect.options.length > 0) {
            groupSelect.value = groupSelect.options[0].value;
            updateTable(groupSelect.value);
        }
    };

    groupSelect.addEventListener("change", () => {
        updateTable(groupSelect.value);
    });

    function updateTable(group) {
        const groupData = latestData[group] || {};
        tbody.innerHTML = "";

        for (const [sensor, value] of Object.entries(groupData)) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td class="sensor">${sensor}</td>
                <td class="sensor-value">${value}</td>
            `;
            tbody.appendChild(row);
        }
    }
</script>