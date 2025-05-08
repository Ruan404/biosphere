const titleEl = document.getElementById("tab-title");
const table = document.querySelector(".data-table");
const thead = table.querySelector("thead");
const tbody = table.querySelector("tbody");

let currentTab = "";

function showTab(tab) {
    if (tab === currentTab) return;
    currentTab = tab;

    const buttons = document.querySelectorAll(".sidebar-menu-button");
    buttons.forEach(btn => btn.classList.remove("current"));
    document.querySelector(`[onclick="showTab('${tab}')"]`)?.classList.add("current");

    fetch(`/admin/${tab}`)
        .then(res => {
            if (!res.ok) throw new Error(`Erreur HTTP: ${res.status}`);
            return res.json();
        })
        .then(data => renderTab(data))
        .catch(err => {
            console.error(err);
            titleEl.textContent = "Erreur de chargement";
            thead.innerHTML = "";
            tbody.innerHTML = `<tr><td colspan="100%">Erreur: ${err.message}</td></tr>`;
        });
}

function renderTab(tab) {
    titleEl.textContent = tab.label || "Données";

    if (!tab.data || !tab.data.length) {
        tbody.innerHTML = "<tr><td colspan='100%'>Aucune donnée</td></tr>";
        thead.innerHTML = "";
        return;
    }

    const keys = Object.keys(tab.data[0]).filter(k => k !== 'actions');
    thead.innerHTML = `
        <tr>
            ${keys.map(k => `<th>${k}</th>`).join('')}
            <th>Actions</th>
        </tr>
    `;

    tbody.innerHTML = tab.data.map(item => {
        const row = keys.map(k => `<td>${item[k]}</td>`).join('');
        const actions = (item.actions || []).map(action => {
            const payload = {
                action: action.type,
                item: Object.fromEntries(Object.entries(item).filter(([k]) => k !== 'actions'))
            };
            const encoded = encodeURIComponent(JSON.stringify(payload));
            return `<button class="action-btn" data-action="${encoded}" ${action.confirm ? 'data-confirm="1"' : ''}>${action.label}</button>`;
        }).join(' ');
        return `<tr>${row}<td>${actions}</td></tr>`;
    }).join('');
}

document.addEventListener("click", (e) => {
    const btn = e.target.closest("button[data-action]");
    if (!btn) return;

    if (btn.dataset.confirm && !confirm("Êtes-vous sûr ?")) return;

    const decoded = JSON.parse(decodeURIComponent(btn.dataset.action));
    fetch("/admin/action", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(decoded)
    })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert("Action effectuée !");
                showTab(currentTab); // Refresh current tab
            } else {
                alert("Erreur : " + (res.error || "inconnue"));
            }
        })
        .catch(err => alert("Erreur: " + err.message));
});

window.addEventListener("load", () => {
    showTab("users"); // Load default tab
});
