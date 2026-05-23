import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatCurrency, formatDate, showToast } from "./utils.js";

renderLayout({ active: "dashboard", title: "Dashboard" });

const totalClients = document.getElementById("total-clients");
const totalContracts = document.getElementById("total-contracts");
const totalPayments = document.getElementById("total-payments");
const activeContracts = document.getElementById("active-contracts");
const pendingContractsLabel = document.getElementById("pending-contracts-label");
const recentBody = document.getElementById("recent-activity-body");
const contractBody = document.getElementById("dashboard-contracts-body");
const communicationBody = document.getElementById("dashboard-communication-body");

function initials(value) {
  return String(value || "NA").split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function customerList(clients) {
  if (!clients.length) {
    return `<div class="rounded-lg border border-dashed border-[#dce3ec] p-5 text-sm font-semibold text-slate-400">No customers yet.</div>`;
  }

  return clients.slice(0, 6).map((client) => `
    <article class="flex items-center justify-between gap-4">
      <div class="flex min-w-0 items-center gap-3">
        <span class="avatar-token">${escapeHtml(initials(client.company_name))}</span>
        <div class="min-w-0">
          <p class="truncate text-sm font-extrabold text-[#0d2a4c]">${escapeHtml(client.company_name)}</p>
          <p class="truncate text-xs font-medium text-[#8aa0b5]">${escapeHtml(client.industry || "Client account")}</p>
        </div>
      </div>
      <a class="filter-chip !border-0 !bg-[#eeecff] !px-3 !text-[10px] !text-[#554cff]" href="clients.html">See details</a>
    </article>
  `).join("");
}

function dealCards(contracts) {
  if (!contracts.length) {
    return `<div class="rounded-lg border border-dashed border-[#dce3ec] p-5 text-sm font-semibold text-slate-400 md:col-span-2">No deals yet. Create a contract to populate this area.</div>`;
  }

  return contracts.slice(0, 4).map((contract, index) => {
    const progress = contract.status === "active" ? 92 : contract.status === "pending" ? 28 : contract.status === "expired" ? 12 : 70;
    const color = index % 3 === 0 ? "#ffb44c" : index % 3 === 1 ? "#554cff" : "#35b764";

    return `
      <article class="rounded-lg border border-[#dfe5ed] bg-white p-4">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-sm font-extrabold leading-5 text-black">${escapeHtml(contract.service?.name || "Service deal")}</h3>
            <p class="mt-1 text-xs font-medium text-[#8aa0b5]">${escapeHtml(contract.client?.company_name || `Client #${contract.client_id}`)}</p>
          </div>
          <p class="whitespace-nowrap text-sm font-extrabold text-black">${formatCurrency(contract.contract_value)}</p>
        </div>
        <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-5 gap-y-2 text-[11px]">
          <dt class="font-medium text-[#8aa0b5]">Status</dt>
          <dd class="text-right text-black">${escapeHtml(contractStatusLabel(contract.status))}</dd>
          <dt class="font-medium text-[#8aa0b5]">Created</dt>
          <dd class="text-right text-black">${formatDate(contract.created_at || contract.start_date)}</dd>
          <dt class="font-medium text-[#8aa0b5]">Deadline</dt>
          <dd class="text-right text-black">${formatDate(contract.end_date)}</dd>
        </dl>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#d9d9d9]">
          <div class="h-full rounded-full" style="width: ${progress}%; background: ${color};"></div>
        </div>
      </article>
    `;
  }).join("");
}

function contractStatusLabel(status) {
  const map = {
    active: "In Progress",
    inactive: "Completed",
    expired: "Expired",
    pending: "Pending",
  };

  return map[status] || status || "Pending";
}

function formatTimestamp(value) {
  if (!value) return "Not set";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return formatDate(value);
  }

  return new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date);
}

function dealNameForLog(log, contracts) {
  const contract = contracts.find((item) => Number(item.client_id) === Number(log.client_id));
  return contract?.service?.name || "No deal linked";
}

function communicationCards(logs, contracts) {
  if (!logs.length) {
    return `<div class="rounded-lg border border-dashed border-[#dce3ec] p-5 text-sm font-semibold text-slate-400 lg:col-span-2">No communication activity yet.</div>`;
  }

  return logs.slice(0, 6).map((log) => {
    const clientName = log.client?.company_name || `Client #${log.client_id}`;
    const dealName = dealNameForLog(log, contracts);
    const message = log.notes || `${log.communication_type || "Communication"} logged`;

    return `
      <article class="rounded-lg border border-[#e7ecf2] bg-white p-4 transition hover:border-[#d7dcff] hover:shadow-sm">
        <div class="flex items-start gap-3">
          <span class="avatar-token !h-9 !w-9 !bg-[#eeecff] !text-[#554cff]">${escapeHtml(initials(clientName))}</span>
          <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
              <h3 class="truncate text-sm font-extrabold text-[#0d2a4c]">${escapeHtml(clientName)}</h3>
              <time class="text-xs font-semibold text-[#8aa0b5]">${formatTimestamp(log.created_at || log.communication_date)}</time>
            </div>
            <p class="mt-1 truncate text-xs font-semibold text-[#554cff]">${escapeHtml(dealName)}</p>
            <p class="mt-3 line-clamp-2 text-sm font-medium leading-6 text-[#536579]">${escapeHtml(message)}</p>
          </div>
        </div>
      </article>
    `;
  }).join("");
}

async function loadDashboard() {
  try {
    const [clientResponse, contractResponse, paymentResponse, communicationResponse] = await Promise.all([
      api.get("/clients", { loadingMessage: "Loading dashboard..." }),
      api.get("/contracts"),
      api.get("/payments"),
      api.get("/communication-logs"),
    ]);

    const clients = unwrapData(clientResponse) || [];
    const contracts = unwrapData(contractResponse) || [];
    const payments = unwrapData(paymentResponse) || [];
    const communicationLogs = unwrapData(communicationResponse) || [];
    const paymentSum = payments
      .filter((payment) => payment.status === "paid")
      .reduce((sum, payment) => sum + Number(payment.amount || 0), 0);

    totalClients.textContent = clients.length;
    totalContracts.textContent = contracts.length;
    totalPayments.textContent = formatCurrency(paymentSum);
    activeContracts.textContent = contracts.length;
    pendingContractsLabel.textContent = `${contracts.filter((contract) => contract.status === "pending").length} Pending`;
    recentBody.innerHTML = customerList(clients);
    contractBody.innerHTML = contracts.length
      ? dealCards(contracts)
      : dealCards([]);
    communicationBody.innerHTML = communicationCards(communicationLogs, contracts);
  } catch (error) {
    recentBody.innerHTML = `<div class="rounded-lg border border-dashed border-[#dce3ec] p-5 text-sm font-semibold text-slate-400">Unable to load customers right now.</div>`;
    contractBody.innerHTML = `<div class="rounded-lg border border-dashed border-[#dce3ec] p-5 text-sm font-semibold text-slate-400 md:col-span-2">Unable to load deals right now.</div>`;
    communicationBody.innerHTML = `<div class="rounded-lg border border-dashed border-[#dce3ec] p-5 text-sm font-semibold text-slate-400 lg:col-span-2">Unable to load communication activity right now.</div>`;
    showToast(error.message, "error");
  }
}

loadDashboard();
