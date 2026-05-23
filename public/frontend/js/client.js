import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, getFormData, renderEmpty, showToast, validateEmail, validateRequired } from "./utils.js";

renderLayout({ active: "clients", title: "Clients" });

const tableBody = document.getElementById("clients-body");
const modal = document.getElementById("client-modal");
const form = document.getElementById("client-form");
const modalTitle = document.getElementById("client-modal-title");
const searchInput = document.getElementById("client-search");
const contactForm = document.getElementById("contact-form");
const contactFormTitle = document.getElementById("contact-form-title");
const contactSubmitButton = document.getElementById("contact-submit-button");
const cancelContactEditButton = document.getElementById("cancel-contact-edit");
const contactClientSelect = document.getElementById("contact_client_id");
const contactsBody = document.getElementById("contacts-body");
const clientsTotal = document.getElementById("clients-total");
const clientsShowing = document.getElementById("clients-showing");
const detailModal = document.getElementById("client-detail-modal");
const detailContent = document.getElementById("client-detail-content");
let clients = [];
let contacts = [];
let editingId = null;
let editingContactId = null;

function clientStatus(client) {
  const hasNotes = Boolean(client.notes);
  return hasNotes
    ? '<span class="status-badge bg-[#c6edcf] text-[#2dad58]">Active</span>'
    : '<span class="status-badge bg-[#ffb9c2] text-[#e12635]">New</span>';
}

function initials(value) {
  return String(value || "NA").split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function primaryContact(clientId) {
  return contacts.find((contact) => Number(contact.client_id) === Number(clientId)) || {};
}

function renderClients() {
  const query = searchInput.value.trim().toLowerCase();
  const filtered = clients.filter((client) => {
    const contact = primaryContact(client.id);
    return [
      client.company_name,
      client.industry,
      client.address,
      contact.name,
      contact.email,
      contact.phone,
    ].some((value) => String(value || "").toLowerCase().includes(query));
  });

  clientsTotal.textContent = `Total: ${clients.length} Clients`;
  clientsShowing.textContent = `Showing ${filtered.length} of ${clients.length} Clients`;

  tableBody.innerHTML = filtered.length
    ? filtered.map((client) => {
      const contact = primaryContact(client.id);

      return `
      <tr>
        <td>
          <div class="flex items-center gap-3">
            <span class="avatar-token">${escapeHtml(initials(client.company_name))}</span>
            <span class="font-semibold text-[#0d2a4c]">${escapeHtml(client.company_name)}</span>
          </div>
        </td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(contact.name || "-")}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(contact.phone || "-")}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(contact.email || "-")}</td>
        <td>${clientStatus(client)}</td>
        <td class="text-right">
          <div class="flex justify-end gap-2 whitespace-nowrap">
            <button class="filter-chip !border-0 !bg-[#f4f7fb] !px-3 !text-[10px] !text-[#0d2a4c]" data-action="view" data-id="${client.id}" type="button">Detail</button>
            <button class="filter-chip !border-0 !bg-[#eeecff] !px-3 !text-[10px] !text-[#554cff]" data-action="edit" data-id="${client.id}" type="button">Edit</button>
            <button class="filter-chip !border-0 !bg-[#fff1f3] !px-3 !text-[10px] !text-[#d92332]" data-action="delete" data-id="${client.id}" type="button">Delete</button>
          </div>
        </td>
      </tr>
    `;
    }).join("")
    : renderEmpty(6, "No matching clients found.");
}

function renderContacts() {
  const clientNameById = new Map(clients.map((client) => [Number(client.id), client.company_name]));

  contactsBody.innerHTML = contacts.length
    ? contacts.map((contact) => `
      <tr>
        <td>
          <div class="font-semibold text-[#0d2a4c]">${escapeHtml(contact.name)}</div>
          <div class="text-xs font-medium text-[#8aa0b5]">${escapeHtml(contact.position || "Contact person")}</div>
        </td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(clientNameById.get(Number(contact.client_id)) || `Client #${contact.client_id}`)}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(contact.email)}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(contact.phone)}</td>
        <td class="text-right">
          <button class="filter-chip !border-0 !bg-[#eeecff] !px-3 !text-[10px] !text-[#554cff]" data-contact-action="edit" data-id="${contact.id}" type="button">Edit</button>
        </td>
      </tr>
    `).join("")
    : renderEmpty(5, "No contacts saved yet.");
}

function syncContactClientOptions() {
  contactClientSelect.innerHTML = `<option value="">Choose client</option>${clients.map((client) => `<option value="${client.id}">${escapeHtml(client.company_name)}</option>`).join("")}`;
}

function openModal(client = null) {
  editingId = client?.id || null;
  modalTitle.textContent = editingId ? "Edit client" : "Create client";
  form.company_name.value = client?.company_name || "";
  form.industry.value = client?.industry || "";
  form.address.value = client?.address || "";
  form.notes.value = client?.notes || "";
  modal.classList.remove("hidden");
}

function closeModal() {
  modal.classList.add("hidden");
  form.reset();
  editingId = null;
}

function closeDetailModal() {
  detailModal.classList.add("hidden");
  detailContent.innerHTML = "";
}

async function openDetail(clientId) {
  try {
    const [clientResponse, contactsResponse, contractsResponse, documentsResponse] = await Promise.all([
      api.get(`/clients/${clientId}`, { loadingMessage: "Loading client detail..." }),
      api.get(`/clients/${clientId}/contacts`),
      api.get(`/clients/${clientId}/contracts`),
      api.get(`/clients/${clientId}/documents`),
    ]);

    const client = unwrapData(clientResponse);
    const clientContacts = unwrapData(contactsResponse) || [];
    const clientContracts = unwrapData(contractsResponse) || [];
    const clientDocuments = unwrapData(documentsResponse) || [];
    const contact = clientContacts[0] || {};

    detailContent.innerHTML = `
      <div>
        <p class="crm-label">Company</p>
        <p class="font-extrabold text-[#0d2a4c]">${escapeHtml(client.company_name)}</p>
      </div>
      <div>
        <p class="crm-label">Contact Person</p>
        <p class="font-semibold text-[#0d2a4c]">${escapeHtml(contact.name || "-")}</p>
      </div>
      <div>
        <p class="crm-label">Phone</p>
        <p class="font-semibold text-[#0d2a4c]">${escapeHtml(contact.phone || "-")}</p>
      </div>
      <div>
        <p class="crm-label">Email</p>
        <p class="font-semibold text-[#0d2a4c]">${escapeHtml(contact.email || "-")}</p>
      </div>
      <div>
        <p class="crm-label">Industry</p>
        <p class="font-semibold text-[#0d2a4c]">${escapeHtml(client.industry || "-")}</p>
      </div>
      <div>
        <p class="crm-label">Related Records</p>
        <p class="font-semibold text-[#0d2a4c]">${clientContracts.length} deals - ${clientDocuments.length} documents</p>
      </div>
      <div class="md:col-span-2">
        <p class="crm-label">Address</p>
        <p class="font-semibold text-[#0d2a4c]">${escapeHtml(client.address || "-")}</p>
      </div>
      <div class="md:col-span-2">
        <p class="crm-label">Notes</p>
        <p class="font-semibold text-[#0d2a4c]">${escapeHtml(client.notes || "-")}</p>
      </div>
    `;

    detailModal.classList.remove("hidden");
  } catch (error) {
    showToast(error.message, "error");
  }
}

function resetContactForm() {
  editingContactId = null;
  contactForm.reset();
  contactFormTitle.textContent = "Add client contact";
  contactSubmitButton.textContent = "Save contact";
  cancelContactEditButton.classList.add("hidden");
}

function editContact(contact) {
  editingContactId = contact.id;
  contactFormTitle.textContent = "Edit client contact";
  contactSubmitButton.textContent = "Update contact";
  cancelContactEditButton.classList.remove("hidden");
  contactForm.client_id.value = contact.client_id;
  contactForm.name.value = contact.name || "";
  contactForm.position.value = contact.position || "";
  contactForm.email.value = contact.email || "";
  contactForm.phone.value = contact.phone || "";
  contactForm.scrollIntoView({ behavior: "smooth", block: "start" });
}

async function loadClients() {
  try {
    const response = await api.get("/clients", { loadingMessage: "Loading clients..." });
    clients = unwrapData(response) || [];
    renderClients();
    syncContactClientOptions();
    renderContacts();
  } catch (error) {
    clientsTotal.textContent = "Total: 0 Clients";
    clientsShowing.textContent = "Unable to load clients";
    tableBody.innerHTML = renderEmpty(6, "Unable to load clients right now.");
    showToast(error.message, "error");
  }
}

async function loadContacts() {
  try {
    const response = await api.get("/contacts");
    contacts = unwrapData(response) || [];
    renderContacts();
    renderClients();
  } catch (error) {
    contactsBody.innerHTML = renderEmpty(4, "Unable to load contacts right now.");
    showToast(error.message, "error");
  }
}

document.getElementById("open-client-modal").addEventListener("click", () => openModal());
document.getElementById("close-client-modal").addEventListener("click", closeModal);
document.getElementById("close-client-modal-secondary").addEventListener("click", closeModal);
document.getElementById("close-client-detail").addEventListener("click", closeDetailModal);
cancelContactEditButton.addEventListener("click", resetContactForm);
searchInput.addEventListener("input", renderClients);

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(form);

  try {
    validateRequired(data, ["company_name", "industry", "address"]);

    if (editingId) {
      await api.put(`/clients/${editingId}`, data, { loadingMessage: "Updating client..." });
      showToast("Client updated successfully.", "success");
    } else {
      await api.post("/clients", data, { loadingMessage: "Creating client..." });
      showToast("Client created successfully.", "success");
    }

    closeModal();
    await loadClients();
  } catch (error) {
    showToast(error.message, "error");
  }
});

contactForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(contactForm);

  try {
    validateRequired(data, ["client_id", "name", "email", "phone"]);
    validateEmail(data.email);
    if (editingContactId) {
      await api.put(`/contacts/${editingContactId}`, data, { loadingMessage: "Updating contact..." });
      showToast("Contact updated successfully.", "success");
    } else {
      await api.post("/contacts", data, { loadingMessage: "Saving contact..." });
      showToast("Contact saved successfully.", "success");
    }

    resetContactForm();
    await loadContacts();
  } catch (error) {
    showToast(error.message, "error");
  }
});

tableBody.addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-action]");
  if (!button) return;

  const id = Number(button.dataset.id);
  const client = clients.find((item) => item.id === id);

  if (button.dataset.action === "edit" && client) {
    openModal(client);
  }

  if (button.dataset.action === "view") {
    await openDetail(id);
  }

  if (button.dataset.action === "delete" && confirm("Delete this client and related data?")) {
    try {
      await api.delete(`/clients/${id}`, { loadingMessage: "Deleting client..." });
      showToast("Client deleted.", "success");
      await Promise.all([loadClients(), loadContacts()]);
    } catch (error) {
      showToast(error.message, "error");
    }
  }
});

contactsBody.addEventListener("click", (event) => {
  const button = event.target.closest("button[data-contact-action='edit']");
  if (!button) return;

  const contact = contacts.find((item) => Number(item.id) === Number(button.dataset.id));
  if (contact) editContact(contact);
});

Promise.all([loadClients(), loadContacts()]);
