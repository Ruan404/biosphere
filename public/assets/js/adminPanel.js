const table = document.querySelector(".data-table");
const tableHead = document.querySelector(".tab-content-head");
const thead = table.querySelector("thead");
const tbody = table.querySelector("tbody");
let popupFormCtn;
let popupForm;

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
    });
}

function renderTab(data) {
  currentTabData = data;
  if ("content" in document.createElement("template")) {
    var template = document.querySelector(`#table-${currentTab}-head`);

    tableHead.innerHTML = "";
    if (template) {
      var clone = document.importNode(template.content, true);
      tableHead.appendChild(clone);
    } else {
      tableHead.innerHTML = "<h3>Données</h3>";
    }
  }
  handleSubmit();

  popupFormCtn = document.querySelector(".form-popup-ctn");
  popupForm = document.querySelector(".form-popup");

  if (currentTabData.length <= 0) {
    tbody.innerHTML = "<tr><td colspan='100%'>Aucune donnée</td></tr>";
    thead.innerHTML = "";
    return;
  }

  // Exclude `actions` and `slug` from display
  const keys = Object.keys(currentTabData[0]).filter(
    (k) => k !== "actions" && k !== "slug"
  );

  thead.innerHTML = `
    <tr>
        <th><input type="checkbox" id="select-all" /></th>
        ${keys.map((k) => `<th>${k}</th>`).join("")}
        <th>Actions</th>
    </tr>
`;

  tbody.innerHTML = "";
  currentTabData.forEach((item) => {
    const row = document.createElement("tr");

    // Add the checkbox cell
    const checkboxTd = document.createElement("td");
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.classList.add("row-checkbox");
    checkbox.dataset.slug = item.slug; // Store the item ID
    checkboxTd.appendChild(checkbox);
    row.appendChild(checkboxTd);

    // Add data cells
    keys.forEach((k) => {
      const td = document.createElement("td");
      td.textContent = item[k];
      row.appendChild(td);
    });

    // Add action menu
    const actionsTd = document.createElement("td");
    const menu = document.createElement("action-menu");

    menu.setAttribute("item-id", item.slug);
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

  handleBulkActions()
}

// Handle actions from <action-menu>
table.addEventListener("selected", (e) => {
  const { action, itemId } = e.detail;

  if (!action || !itemId) return;

  const currentItem = currentTabData.find((item) => item.slug == itemId);

  if (!currentItem) return;

  const actionObj = (currentItem.actions || []).find((a) => a.type === action);
  if (actionObj?.confirm && !confirm("Êtes-vous sûr ?")) return;

  var formdata = new FormData();

  formdata.append("action", action);
  formdata.append("slug", itemId);

  fetch("/admin/action", {
    method: "POST",
    body: formdata,
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
table.addEventListener("change", (e) => {
  if (e.target.id === "select-all") {
    const checked = e.target.checked;
    document.querySelectorAll(".row-checkbox").forEach((cb) => {
      cb.checked = checked;
    });
  }
});

window.addEventListener("load", () => {
  showTab("users"); // Load default tab
});

function showPopuForm() {
  popupForm.classList.add("show");

  document.body.classList.add("no-overflow");

  setTimeout(() => {
    popupFormCtn.classList.add("show");
  }, 100);
}

function hidePopupForm() {
  popupFormCtn.classList.remove("show");

  setTimeout(() => {
    popupForm.classList.remove("show");
    document.body.classList.remove("no-overflow");
  }, 300);
}

function handleSubmit() {
  var forms = document.getElementsByTagName("form");

  for (let i = 0; i < forms.length; i++) {
    forms[i].addEventListener("submit", (ev) => {
      ev.preventDefault();

      const formdata = new FormData(forms[i]);

      fetch("/admin/action", {
        method: "POST",
        body: formdata,
      })
        .then((response) => response.json())
        .then((res) => {
          if (res.success === true) {
            showTab(currentTab, true);
          }

          hidePopupForm();
        })
        .finally(() => forms[i].reset());
    });
  }
}

function getSelectedSlugs() {
  return Array.from(document.querySelectorAll(".row-checkbox:checked")).map(
    (cb) => cb.dataset.slug
  );
}

function handleBulkActions() {
  document.querySelectorAll(".bulk-action").forEach((btn) => {
    btn.addEventListener("click", () => {
      const selectedSlugs = Array.from(
        document.querySelectorAll(".row-checkbox:checked")
      ).map((cb) => cb.dataset.slug);

      if (selectedSlugs.length === 0) {
        alert("Aucune ligne sélectionnée.");
        return;
      }

      const action = btn.dataset.action;

      if (!confirm(`Confirmer l'action: ${action}?`)) return;

      const formdata = new FormData();
      formdata.append("action", action);
      selectedSlugs.forEach((slug) => formdata.append("slugs[]", slug));

      fetch("/admin/action", {
        method: "POST",
        body: formdata,
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
  });
}
