import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatDate, getFormData, renderEmpty, showToast, validateRequired } from "./utils.js";

renderLayout({ active: "communication", title: "Communication" });

const form = document.getElementById("communication-form");
const clientSelect = document.getElementById("client_id");
const tableBody = document.getElementById("communication-body");
const countLabel = document.getElementById("communication-count");

let logs = [];

function renderLogs() {
  countLabel.textContent = `${logs.length} logs`;
  tableBody.innerHTML = logs.length
    ? logs.map((log) => `
      <tr>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(log.client?.company_name || `Client #${log.client_id}`)}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(log.communication_type)}</td>
        <td class="font-medium text-[#0d2a4c]">${formatDate(log.communication_date)}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(log.notes || "-")}</td>
      </tr>
    `).join("")
    : renderEmpty(4, "No communication logs yet.");
}

async function loadPage() {
  try {
    const [clientResponse, logResponse] = await Promise.all([
      api.get("/clients", { loadingMessage: "Loading communication..." }),
      api.get("/communication-logs"),
    ]);

    const clients = unwrapData(clientResponse) || [];
    logs = unwrapData(logResponse) || [];

    clientSelect.innerHTML = `<option value="">Choose client</option>${clients.map((client) => `<option value="${client.id}">${escapeHtml(client.company_name)}</option>`).join("")}`;
    renderLogs();
  } catch (error) {
    clientSelect.innerHTML = `<option value="">Unable to load clients</option>`;
    logs = [];
    renderLogs();
    showToast(error.message, "error");
  }
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(form);

  try {
    validateRequired(data, ["client_id", "communication_type", "communication_date"]);
    await api.post("/communication-logs", data, { loadingMessage: "Saving communication..." });
    showToast("Communication log saved.", "success");
    form.reset();
    await loadPage();
  } catch (error) {
    showToast(error.message, "error");
  }
});

loadPage();
