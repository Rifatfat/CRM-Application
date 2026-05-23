import { api } from "./api.js";
import { renderLayout } from "./layout.js";
import { getFormData, showToast, validateRequired } from "./utils.js";

renderLayout({ active: "clients", title: "Add Client" });

const form = document.getElementById("add-client-form");

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(form);

  try {
    validateRequired(data, ["company_name", "industry", "address"]);
    await api.post("/clients", data, { loadingMessage: "Saving client..." });
    showToast("Client created successfully.", "success");
    window.setTimeout(() => {
      window.location.href = "clients.html";
    }, 650);
  } catch (error) {
    showToast(error.message, "error");
  }
});
