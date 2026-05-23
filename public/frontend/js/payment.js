import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatCurrency, formatDate, getFormData, renderEmpty, showToast, statusBadge, validateNumber, validateRequired } from "./utils.js";

renderLayout({ active: "payments", title: "Payments" });

const tableBody = document.getElementById("payments-body");
const modal = document.getElementById("payment-modal");
const form = document.getElementById("payment-form");
const contractSelect = document.getElementById("contract_id");
const paymentsShowing = document.getElementById("payments-showing");
const totalRevenue = document.getElementById("payment-total-revenue");
const paidTotal = document.getElementById("payment-paid-total");
const pendingTotal = document.getElementById("payment-pending-total");
const paidBar = document.getElementById("payment-paid-bar");
const pendingBar = document.getElementById("payment-pending-bar");
let payments = [];
let contracts = [];

function initials(value) {
  return String(value || "NA").split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function contractLabel(contract) {
  const client = contract.client?.company_name || `Client #${contract.client_id}`;
  const service = contract.service?.name || `Service #${contract.service_id}`;
  return `${client} - ${service} - Remaining ${formatCurrency(remainingBalance(contract.id, contract.contract_value))}`;
}

function contractFor(payment) {
  return contracts.find((contract) => Number(contract.id) === Number(payment.contract_id)) || payment.contract || {};
}

function paidPayments(contractId) {
  return payments
    .filter((payment) => Number(payment.contract_id) === Number(contractId) && payment.status === "paid")
    .reduce((sum, payment) => sum + Number(payment.amount || 0), 0);
}

function remainingBalance(contractId, contractValue) {
  return Math.max(0, Number(contractValue || 0) - paidPayments(contractId));
}

function reservedPayments(contractId) {
  return payments
    .filter((payment) => Number(payment.contract_id) === Number(contractId) && ["paid", "pending"].includes(payment.status))
    .reduce((sum, payment) => sum + Number(payment.amount || 0), 0);
}

function availableBalance(contractId, contractValue) {
  return Math.max(0, Number(contractValue || 0) - reservedPayments(contractId));
}

function renderPaymentSummary() {
  const paid = payments.filter((payment) => payment.status === "paid").reduce((sum, payment) => sum + Number(payment.amount || 0), 0);
  const pending = payments.filter((payment) => payment.status === "pending").reduce((sum, payment) => sum + Number(payment.amount || 0), 0);
  const tracked = paid + pending;
  const paidPercent = tracked ? Math.round((paid / tracked) * 100) : 0;
  const pendingPercent = tracked ? Math.round((pending / tracked) * 100) : 0;

  totalRevenue.textContent = formatCurrency(paid);
  paidTotal.textContent = formatCurrency(paid);
  pendingTotal.textContent = formatCurrency(pending);
  paidBar.style.width = `${paidPercent}%`;
  pendingBar.style.width = `${pendingPercent}%`;
}

function renderPayments() {
  paymentsShowing.textContent = `Showing ${payments.length} payments`;
  renderPaymentSummary();

  tableBody.innerHTML = payments.length
    ? payments.map((payment) => {
      const contract = contractFor(payment);
      const clientName = contract.client?.company_name || `Contract #${payment.contract_id}`;
      const dealName = contract.service?.name || `Payment #${payment.id}`;

      return `
      <tr>
        <td>
          <div class="flex items-center gap-3">
            <span class="avatar-token">${escapeHtml(initials(clientName))}</span>
            <span class="font-medium text-[#0d2a4c]">${escapeHtml(clientName)}</span>
          </div>
        </td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(dealName)}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(payment.payment_method)}</td>
        <td class="font-medium text-[#0d2a4c]">${formatCurrency(payment.amount)}</td>
        <td class="font-medium text-[#0d2a4c]">${formatDate(payment.payment_date)}</td>
        <td>${statusBadge(payment.status)}</td>
        <td class="text-right"><button class="filter-chip !border-0 !bg-[#fff1f3] !px-3 !text-[10px] !text-[#d92332]" data-delete="${payment.id}" type="button">Delete</button></td>
      </tr>
    `;
    }).join("")
    : renderEmpty(7, "No payments have been recorded yet.");
}

async function loadContracts() {
  try {
    const response = await api.get("/contracts");
    contracts = unwrapData(response) || [];
    contractSelect.innerHTML = `<option value="">Choose contract</option>${contracts.map((contract) => `<option value="${contract.id}">${escapeHtml(contractLabel(contract))}</option>`).join("")}`;
  } catch (error) {
    contracts = [];
    contractSelect.innerHTML = `<option value="">Unable to load contracts</option>`;
    showToast(error.message, "error");
  }
}

async function loadPayments() {
  try {
    const response = await api.get("/payments", { loadingMessage: "Loading payments..." });
    payments = unwrapData(response) || [];
    await loadContracts();
    renderPayments();
  } catch (error) {
    payments = [];
    renderPayments();
    paymentsShowing.textContent = "Unable to load payments";
    tableBody.innerHTML = renderEmpty(7, "Unable to load payments right now.");
    showToast(error.message, "error");
  }
}

document.getElementById("open-payment-modal").addEventListener("click", () => modal.classList.remove("hidden"));
document.getElementById("close-payment-modal").addEventListener("click", () => {
  modal.classList.add("hidden");
  form.reset();
});
document.getElementById("close-payment-modal-secondary").addEventListener("click", () => {
  modal.classList.add("hidden");
  form.reset();
});

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(form);

  try {
    validateRequired(data, ["contract_id", "amount", "payment_date", "payment_method", "status"]);
    validateNumber(data.amount, "Amount");
    const contract = contracts.find((item) => Number(item.id) === Number(data.contract_id));
    const available = contract ? availableBalance(contract.id, contract.contract_value) : 0;
    if (data.status !== "failed" && Number(data.amount) > available) {
      throw new Error(`Amount exceeds available balance (${formatCurrency(available)}).`);
    }
    await api.post("/payments", data, { loadingMessage: "Saving payment..." });
    showToast("Payment recorded.", "success");
    modal.classList.add("hidden");
    form.reset();
    await loadPayments();
  } catch (error) {
    showToast(error.message, "error");
  }
});

tableBody.addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-delete]");
  if (!button || !confirm("Delete this payment?")) return;

  try {
    await api.delete(`/payments/${button.dataset.delete}`, { loadingMessage: "Deleting payment..." });
    showToast("Payment deleted.", "success");
    await loadPayments();
  } catch (error) {
    showToast(error.message, "error");
  }
});

loadPayments();
