export function getUser() {
  try {
    return JSON.parse(localStorage.getItem("crm_user"));
  } catch {
    return null;
  }
}

export function getToken() {
  const user = getUser();
  return user?.token || user?.access_token || null;
}

export function setUser(user) {
  localStorage.setItem("crm_user", JSON.stringify(user));
}

export function requireAuth() {
  const user = getUser();

  if (!user || !getToken()) {
    window.location.href = "login.html";
    return null;
  }

  return user;
}

export function logout() {
  localStorage.removeItem("crm_user");
  localStorage.removeItem("crm_token");
  window.location.href = "login.html";
}

export function showToast(message, type = "info") {
  let container = document.getElementById("toast-container");

  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className =
      "fixed right-4 top-4 z-[80] flex w-[min(92vw,420px)] flex-col gap-3";

    document.body.appendChild(container);
  }

  const toast = document.createElement("div");

  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  container.appendChild(toast);

  window.setTimeout(() => {
    toast.remove();
  }, 3600);
}

export function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

export function formatCurrency(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(Number(value || 0));
}

export function formatDate(value) {
  if (!value) return "Not set";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return "Not set";
  }

  return new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(date);
}

export function getFormData(form) {
  return Object.fromEntries(new FormData(form).entries());
}

export function validateRequired(data, fields) {
  const missing = fields.filter(
    (field) => !String(data[field] ?? "").trim()
  );

  if (missing.length) {
    throw new Error(`Please complete: ${missing.join(", ")}`);
  }
}

export function validateEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!regex.test(email)) {
    throw new Error("Please enter a valid email address.");
  }
}

export function validateNumber(value, label) {
  if (Number.isNaN(Number(value)) || Number(value) < 0) {
    throw new Error(`${label} must be a positive number.`);
  }
}

export function statusBadge(status) {
  const normalized = String(status || "pending").toLowerCase();

  const label = normalized
    .split(" ")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");

  const map = {
    active: "bg-[#ece9ff] text-[#554cff]",
    "in progress": "bg-[#ece9ff] text-[#554cff]",
    paid: "bg-[#c6edcf] text-[#2dad58]",
    done: "bg-[#c6edcf] text-[#2dad58]",
    completed: "bg-[#c6edcf] text-[#2dad58]",
    pending: "bg-[#fff0bd] text-[#d89a21]",
    inactive: "bg-[#ffebb2] text-[#c98c13]",
    expired: "bg-[#ffe4e8] text-[#e12635]",
    failed: "bg-[#ffe4e8] text-[#e12635]",
    new: "bg-[#ffb9c2] text-[#e12635]",
  };

  return `
    <span class="status-badge ${
      map[normalized] || "bg-[#ece9ff] text-[#554cff]"
    }">
      ${escapeHtml(label)}
    </span>
  `;
}

export function renderEmpty(colspan, message) {
  return `
    <tr>
      <td colspan="${colspan}" class="text-center text-sm font-semibold text-slate-400">
        ${escapeHtml(message)}
      </td>
    </tr>
  `;
}