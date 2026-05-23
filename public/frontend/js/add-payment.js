import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatCurrency, getFormData, showToast, validateNumber, validateRequired } from "./utils.js";

renderLayout({ active: "payments", title: "Add Payment" });

const form = document.getElementById("add-payment-form");
const contractSelect = document.getElementById("contract_id");
let contracts = [];
let payments = [];

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

function contractLabel(contract) {
  return `${contract.client?.company_name || `Client #${contract.client_id}`} - ${contract.service?.name || `Service #${contract.service_id}`} - Remaining ${formatCurrency(remainingBalance(contract.id, contract.contract_value))}`;
}

async function loadContracts() {
  try {
    const [contractResponse, paymentResponse] = await Promise.all([
      api.get("/contracts", { loadingMessage: "Loading contracts..." }),
      api.get("/payments"),
    ]);
    contracts = unwrapData(contractResponse) || [];
    payments = unwrapData(paymentResponse) || [];
    contractSelect.innerHTML = `<option value="">Choose contract</option>${contracts.map((contract) => `<option value="${contract.id}">${escapeHtml(contractLabel(contract))}</option>`).join("")}`;
  } catch (error) {
    showToast(error.message, "error");
  }
}

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
    window.setTimeout(() => {
      window.location.href = "payments.html";
    }, 650);
  } catch (error) {
    showToast(error.message, "error");
  }
});

loadContracts();
