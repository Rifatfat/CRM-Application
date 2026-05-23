import { api, unwrapData } from "./api.js";
import { renderLayout } from "./layout.js";
import { escapeHtml, formatDate, getFormData, renderEmpty, showToast, validateRequired } from "./utils.js";

renderLayout({ active: "documents", title: "Documents" });

const tableBody = document.getElementById("documents-body");
const form = document.getElementById("document-form");
const clientSelect = document.getElementById("client_id");
const contractSelect = document.getElementById("contract_id");
const fileInput = document.getElementById("file_input");
const documentsList = document.getElementById("documents-list");
const documentsCount = document.getElementById("documents-count");
const featuredFileName = document.getElementById("featured-file-name");
const featuredFileMeta = document.getElementById("featured-file-meta");
const featuredFileType = document.getElementById("featured-file-type");
const featuredClient = document.getElementById("featured-client");
const featuredContract = document.getElementById("featured-contract");
const featuredDate = document.getElementById("featured-date");
const featuredDownload = document.getElementById("featured-download");
let documents = [];
let contracts = [];

function contractLabel(id) {
  const contract = contracts.find((item) => Number(item.id) === Number(id));
  return contract?.service?.name || `Contract #${id}`;
}

function setFeaturedDocument(item) {
  if (!item) {
    featuredFileName.textContent = "No document selected";
    featuredFileMeta.textContent = "Upload or select a document record";
    featuredFileType.textContent = "Document";
    featuredClient.textContent = "-";
    featuredContract.textContent = "-";
    featuredDate.textContent = "-";
    featuredDownload.setAttribute("href", "#");
    return;
  }

  featuredFileName.textContent = item.file_name || "Untitled document";
  featuredFileMeta.textContent = `Uploaded by ${item.uploader?.name || "Admin"} - ${String(item.file_name || "").split(".").pop()?.toUpperCase() || "FILE"}`;
  featuredFileType.textContent = item.document_type || "Document";
  featuredClient.textContent = item.client?.company_name || `Client #${item.client_id}`;
  featuredContract.textContent = contractLabel(item.contract_id);
  featuredDate.textContent = formatDate(item.uploaded_at || item.created_at);
  featuredDownload.setAttribute("href", item.file_path || "#");
}

function renderDocuments() {
  documentsCount.textContent = `${documents.length} files`;
  documentsList.innerHTML = documents.length
    ? documents.map((item, index) => `
      <button class="flex w-full items-center gap-3 rounded-lg p-2 text-left transition hover:bg-[#fbfcff]" data-doc="${item.id}" type="button">
        <span class="grid h-9 w-9 place-items-center rounded-md bg-[#554cff] text-white">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true"><path d="M6 3h8l4 4v14H6V3Z" stroke="currentColor" stroke-width="2"/><path d="M14 3v5h5M9 16h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </span>
        <span class="min-w-0">
          <span class="block truncate text-xs font-extrabold text-[#0d2a4c]">${escapeHtml(item.file_name)}</span>
          <span class="block truncate text-[11px] font-medium text-[#8aa0b5]">${escapeHtml(item.client?.company_name || `Client #${item.client_id}`)} - ${index + 1} file</span>
        </span>
      </button>
    `).join("")
    : `<div class="rounded-lg border border-dashed border-[#dce3ec] p-4 text-xs font-semibold text-slate-400">No documents yet.</div>`;
  setFeaturedDocument(documents[0]);

  tableBody.innerHTML = documents.length
    ? documents.map((item) => `
      <tr>
        <td>
          <div class="font-semibold text-[#0d2a4c]">${escapeHtml(item.file_name)}</div>
          <div class="text-xs font-medium text-[#8aa0b5]">${escapeHtml(item.file_path)}</div>
        </td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(item.document_type)}</td>
        <td class="font-medium text-[#0d2a4c]">${escapeHtml(item.client?.company_name || `Client #${item.client_id}`)}</td>
        <td class="font-medium text-[#0d2a4c]">${formatDate(item.uploaded_at || item.created_at)}</td>
        <td class="text-right"><button class="filter-chip !border-0 !bg-[#fff1f3] !px-3 !text-[10px] !text-[#d92332]" data-delete="${item.id}" type="button">Delete</button></td>
      </tr>
    `).join("")
    : renderEmpty(5, "No documents uploaded yet.");
}

function syncContractOptions() {
  const clientId = clientSelect.value;
  const filtered = clientId ? contracts.filter((contract) => String(contract.client_id) === clientId) : contracts;
  contractSelect.innerHTML = `<option value="">Choose contract</option>${filtered.map((contract) => `<option value="${contract.id}">#${contract.id} - ${escapeHtml(contract.service?.name || "Service")}</option>`).join("")}`;
}

async function loadDocumentsPage() {
  try {
    const [documentResponse, clientResponse, contractResponse] = await Promise.all([
      api.get("/documents", { loadingMessage: "Loading documents..." }),
      api.get("/clients"),
      api.get("/contracts"),
    ]);

    documents = unwrapData(documentResponse) || [];
    const clients = unwrapData(clientResponse) || [];
    contracts = unwrapData(contractResponse) || [];

    clientSelect.innerHTML = `<option value="">Choose client</option>${clients.map((client) => `<option value="${client.id}">${escapeHtml(client.company_name)}</option>`).join("")}`;
    syncContractOptions();
    renderDocuments();
  } catch (error) {
    documents = [];
    contracts = [];
    clientSelect.innerHTML = `<option value="">Unable to load clients</option>`;
    contractSelect.innerHTML = `<option value="">Unable to load contracts</option>`;
    renderDocuments();
    tableBody.innerHTML = renderEmpty(5, "Unable to load documents right now.");
    showToast(error.message, "error");
  }
}

clientSelect.addEventListener("change", syncContractOptions);

fileInput.addEventListener("change", () => {
  if (fileInput.files[0]) {
    form.file_name.value = fileInput.files[0].name;
    form.file_path.value = `/uploads/${fileInput.files[0].name}`;
  }
});

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = getFormData(form);

  try {
    validateRequired(data, ["client_id", "contract_id", "file_name", "file_path", "document_type"]);
    await api.post("/documents", data, { loadingMessage: "Saving document record..." });
    showToast("Document uploaded.", "success");
    form.reset();
    await loadDocumentsPage();
  } catch (error) {
    showToast(error.message, "error");
  }
});

tableBody.addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-delete]");
  if (!button || !confirm("Delete this document?")) return;

  try {
    await api.delete(`/documents/${button.dataset.delete}`, { loadingMessage: "Deleting document..." });
    showToast("Document deleted.", "success");
    await loadDocumentsPage();
  } catch (error) {
    showToast(error.message, "error");
  }
});

documentsList.addEventListener("click", (event) => {
  const button = event.target.closest("button[data-doc]");
  if (!button) return;
  setFeaturedDocument(documents.find((item) => Number(item.id) === Number(button.dataset.doc)));
});

loadDocumentsPage();
