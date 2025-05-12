const titleEl = document.getElementById("tab-title");
const table = document.querySelector(".data-table");
const thead = table.querySelector("thead");
const tbody = table.querySelector("tbody");

let currentTab = "";
let currentTabData = []; // Store current data for action lookup

function showTab(tab, force = false) {
  if (tab === currentTab && force === false) return;
  currentTab = tab;

  const buttons = document.querySelectorAll(".sidebar-menu-button");
  buttons.forEach((btn) => btn.classList.remove("current"));
  document
    .querySelector(`[onclick="showTab('${tab}')"]`)
    ?.classList.add("current");

  fetch(`/admin/${tab}`)
    .then((res) => {
      if (!res.ok) throw new Error(`Erreur HTTP: ${res.status}`);
      return res.json();
    })
    .then((data) => renderTab(data))
    .catch((err) => {
      console.error(err);
      titleEl.textContent = "Erreur de chargement";
      thead.innerHTML = "";
      tbody.innerHTML = `<tr><td colspan="100%">Erreur: ${err.message}</td></tr>`;
    });
}

function renderTab(tab) {
  titleEl.textContent = tab.label || "Données";
  currentTabData = tab.data || [];

  if (!currentTabData.length) {
    tbody.innerHTML = "<tr><td colspan='100%'>Aucune donnée</td></tr>";
    thead.innerHTML = "";
    return;
  }

  // Exclude `actions` and `id` from display
  const keys = Object.keys(currentTabData[0]).filter(
    (k) => k !== "actions" && k !== "id"
  );

  thead.innerHTML = `
        <tr>
            ${keys.map((k) => `<th>${k}</th>`).join("")}
            <th>Actions</th>
        </tr>
    `;

  tbody.innerHTML = "";
  currentTabData.forEach((item) => {
    const row = document.createElement("tr");

    keys.forEach((k) => {
      const td = document.createElement("td");
      td.textContent = item[k];
      row.appendChild(td);
    });

    const actionsTd = document.createElement("td");
    const menu = document.createElement("action-menu");

    // If no 'id', you can generate a synthetic one or use another unique key
    const itemId = item.id ?? item.uuid ?? item.slug;
    menu.setAttribute("item-id", itemId);
    menu.setAttribute(
      "options",
      JSON.stringify(
        (item.actions || []).map((a) => ({ label: a.label, value: a.type }))
      )
    );
    actionsTd.appendChild(menu);
    row.appendChild(actionsTd);

    tbody.appendChild(row);
  });
}

// Handle actions from <action-menu>
table.addEventListener("selected", (e) => {
  const { action, itemId } = e.detail;

  if (!action || !itemId) return;
  

  const currentItem = currentTabData.find((item) => item.id == itemId);
  if (!currentItem) return;

  const actionObj = (currentItem.actions || []).find((a) => a.type === action);
  if (actionObj?.confirm && !confirm("Êtes-vous sûr ?")) return;

  fetch("/admin/action", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      action,
      item: Object.fromEntries(
        Object.entries(currentItem).filter(([k]) => k !== "actions")
      ),
    }),
  })
    .then((res) => res.json())
    .then((res) => {
      if (res.success) {
        alert("Action effectuée !");
        showTab(currentTab, true);
      } else {
        alert("Erreur : " + (res.error || "inconnue"));
      }
    })
    .catch((err) => alert("Erreur: " + err.message));
});

window.addEventListener("load", () => {
  showTab("users"); // Load default tab
});
