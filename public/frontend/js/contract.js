import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatCurrency, formatDate, renderEmpty, showToast, statusBadge } from "./utils.js";

renderLayout({ active: "contracts", title: "Deals" });

const tableBody = document.getElementById("contracts-body");
const statusFilter = document.getElementById("contract-status-filter");
const searchInput = document.getElementById("contract-search");
const contractsTotal = document.getElementById("contracts-total");
const contractsShowing = document.getElementById("contracts-showing");
let contracts = [];

function initials(value) {
  return String(value || "NA").split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function statusLabel(status) {
  const map = {
    active: "In Progress",
    inactive: "Completed",
    expired: "Expired",
    pending: "Pending",
  };

  return map[status] || status || "Pending";
}

function renderContracts() {
  const status = statusFilter.value;
  const query = searchInput.value.trim().toLowerCase();
  const filtered = (status === "all" ? contracts : contracts.filter((contract) => contract.status === status))
    .filter((contract) => [
      contract.client?.company_name,
      contract.service?.name,
      contract.contract_value,
      contract.status,
    ].some((value) => String(value || "").toLowerCase().includes(query)));

  contractsTotal.textContent = `Total: ${contracts.length} Deals`;
  contractsShowing.textContent = `Showing ${filtered.length} of ${contracts.length} deals`;

  tableBody.innerHTML = filtered.length
    ? filtered.map((contract) => `
      <tr>
        <td>
          <div class="flex items-center gap-3">
            <span class="avatar-token">${escapeHtml(initials(contract.client?.company_name))}</span>
            <span class="font-medium text-[#0d2a4c]">${escapeHtml(contract.client?.company_name || "Unknown client")}</span>
          </div>
        </td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(contract.service?.name || "Unknown service")}</td>
        <td>
          <div class="font-medium text-[#0d2a4c]">${formatDate(contract.start_date)}</div>
          <div class="text-xs font-medium text-[#8aa0b5]">Deadline : ${formatDate(contract.end_date)}</div>
        </td>
        <td class="font-medium text-[#0d2a4c]">${formatCurrency(contract.contract_value)}</td>
        <td>${statusBadge(statusLabel(contract.status).toLowerCase())}</td>
        <td class="text-right">
          <button class="filter-chip !border-0 !bg-[#fff1f3] !px-3 !text-[10px] !text-[#d92332]" data-delete="${contract.id}" type="button">Delete</button>
        </td>
      </tr>
    `).join("")
    : renderEmpty(6, "No contracts found for this view.");
}

async function loadContracts() {
  try {
    const response = await api.get("/contracts", { loadingMessage: "Loading contracts..." });
    contracts = unwrapData(response) || [];
    renderContracts();
  } catch (error) {
    contractsTotal.textContent = "Total: 0 Deals";
    contractsShowing.textContent = "Unable to load deals";
    tableBody.innerHTML = renderEmpty(6, "Unable to load deals right now.");
    showToast(error.message, "error");
  }
}

statusFilter.addEventListener("change", renderContracts);
searchInput.addEventListener("input", renderContracts);

tableBody.addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-delete]");
  if (!button || !confirm("Delete this contract?")) return;

  try {
    await api.delete(`/contracts/${button.dataset.delete}`, { loadingMessage: "Deleting contract..." });
    showToast("Contract deleted.", "success");
    await loadContracts();
  } catch (error) {
    showToast(error.message, "error");
  }
});

loadContracts();
