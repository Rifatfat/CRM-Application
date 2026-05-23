import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatCurrency, getFormData, showToast, validateNumber, validateRequired } from "./utils.js";

renderLayout({ active: "contracts", title: "Add Deal" });

const contractForm = document.getElementById("add-contract-form");
const serviceForm = document.getElementById("quick-service-form");
const clientSelect = document.getElementById("client_id");
const serviceSelect = document.getElementById("service_id");
const serviceHint = document.getElementById("service-hint");

function options(items, label) {
  return `<option value="">${label}</option>${items.map((item) => `<option value="${item.id}">${escapeHtml(item.company_name || item.name)}${item.base_price ? ` - ${formatCurrency(item.base_price)}` : ""}</option>`).join("")}`;
}

async function loadOptions() {
  try {
    const [clientsResponse, servicesResponse] = await Promise.all([
      api.get("/clients", { loadingMessage: "Preparing contract form..." }),
      api.get("/services"),
    ]);

    const clients = unwrapData(clientsResponse) || [];
    const services = unwrapData(servicesResponse) || [];
    clientSelect.innerHTML = options(clients, "Choose client");
    serviceSelect.innerHTML = options(services, "Choose service");
    serviceHint.textContent = services.length ? `${services.length} services available.` : "No services yet. Create one below before saving a deal.";
  } catch (error) {
    showToast(error.message, "error");
  }
}

serviceForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(serviceForm);

  try {
    validateRequired(data, ["name", "base_price"]);
    validateNumber(data.base_price, "Base price");
    await api.post("/services", data, { loadingMessage: "Creating service..." });
    showToast("Service added and ready for contracts.", "success");
    serviceForm.reset();
    await loadOptions();
  } catch (error) {
    showToast(error.message, "error");
  }
});

contractForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(contractForm);

  try {
    validateRequired(data, ["client_id", "service_id", "contract_value", "start_date", "end_date", "status"]);
    validateNumber(data.contract_value, "Contract value");

    if (new Date(data.end_date) <= new Date(data.start_date)) {
      throw new Error("End date must be after start date.");
    }

    await api.post("/contracts", data, { loadingMessage: "Saving contract..." });
    showToast("Deal created successfully.", "success");
    window.setTimeout(() => {
      window.location.href = "contracts.html";
    }, 650);
  } catch (error) {
    showToast(error.message, "error");
  }
});

loadOptions();
