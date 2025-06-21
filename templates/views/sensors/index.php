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
    let currentGroup = null;

    const groupSelect = document.getElementById("group-select");
    const tbody = document.getElementById("sensor-table-body");

    socket.onmessage = function (event) {
        const data = JSON.parse(event.data);
        latestData = data;
        console.log(data["surveillance"], Object.keys(data))

        const existingSelection = groupSelect.value;
        const groups = Object.keys(data);

        // Only update options if the group list has changed
        const currentOptions = Array.from(groupSelect.options).map(o => o.value);
        const hasChanged = groups.length !== currentOptions.length ||
            !groups.every((g, i) => g === currentOptions[i]);

        if (hasChanged) {
            groupSelect.innerHTML = '';
            groups.forEach(group => {
                const option = document.createElement("option");
                option.value = group;
                option.textContent = group;
                groupSelect.appendChild(option);
            });

            // Try to restore the previous selection if it still exists
            if (groups.includes(existingSelection)) {
                groupSelect.value = existingSelection;
            } else {
                groupSelect.value = groups[0] || "";
            }
        }

        // Update table only if the selected group is still valid
        const selectedGroup = groupSelect.value;
        if (selectedGroup) {
            currentGroup = selectedGroup;
            updateTable(currentGroup);
        }
    };

    groupSelect.addEventListener("change", () => {
        currentGroup = groupSelect.value;
        updateTable(currentGroup);
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
