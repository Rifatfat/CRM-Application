import { getUser, logout, requireAuth } from "./utils.js";

const icons = {
  logo: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M4 12a8 8 0 1 1 3.08 6.31L4 19l.69-3.08A7.96 7.96 0 0 1 4 12Z" stroke="currentColor" stroke-width="2"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/></svg>`,
  dashboard: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="currentColor" aria-hidden="true"><path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z"/></svg>`,
  clients: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="currentColor" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6.7 1.25a3.4 3.4 0 1 0 0-6.8 3.4 3.4 0 0 0 0 6.8ZM2.5 20.4c0-4.2 2.9-7.2 6.5-7.2s6.5 3 6.5 7.2v.6h-13v-.6Zm12.6.6h6.4v-.5c0-3.35-2.2-5.8-5.08-6.15a8.5 8.5 0 0 1 1.68 5.05v1.6Z"/></svg>`,
  contracts: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M7 3h8l4 4v14H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M15 3v5h5M10 12h6M10 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
  communication: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M5 16a7 7 0 1 1 2.4 2.1L4 19l1-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M11 10h5M8 13h8M15 17l2 3h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
  documents: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M6 3h8l4 4v14H6V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3v5h5M9 13h6M9 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
  payments: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M4 7h16v12H4V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M4 10h16M16 15h1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
  schedule: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M5 5h14v14H5V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M8 3v4M16 3v4M5 10h14M8 16l2-2 2 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
  bell: `<svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M10 20a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
  plus: `<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>`,
  logout: `<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 4v16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
};

const navItems = [
  ["dashboard", "Dashboard", "dashboard.html", icons.dashboard],
  ["clients", "Clients", "clients.html", icons.clients],
  ["contracts", "Deals", "contracts.html", icons.contracts],
  ["communication", "Communication", "communication.html", icons.communication],
  ["documents", "Documents", "documents.html", icons.documents],
  ["payments", "Payments", "payments.html", icons.payments],
  
];

const headerActions = {
  clients: ["Add New Client", "add-client.html"],
  contracts: ["Add New Deal", "add-contract.html"],
  payments: ["Add Payment", "add-payment.html"],
  documents: ["Add Documents", "documents.html#document-form"],
};

function navLink([key, label, href, icon], active, compact = false) {
  const isActive = key === active;
  const classes = isActive
    ? "border-[#d7dcff] bg-[#f5f4ff] text-[#554cff] shadow-sm"
    : "border-transparent text-[#6f8092] hover:border-[#e7ecf2] hover:bg-[#fbfcff] hover:text-[#0d2a4c]";
  const content = compact
    ? `${icon}<span class="sr-only">${label}</span>`
    : `${icon}<span class="truncate">${label}</span>`;

  return `<a class="group relative flex h-11 ${compact ? "w-11 justify-center" : "w-full gap-3 px-3"} items-center rounded-lg border text-sm font-bold transition ${classes}" href="${href}" aria-label="${label}">
    ${isActive ? `<span class="absolute left-0 h-6 w-1 rounded-r-full bg-[#554cff]"></span>` : ""}
    ${content}
    ${compact ? `<span class="pointer-events-none absolute left-[3.35rem] z-50 hidden rounded-md bg-slate-950 px-2 py-1 text-xs font-semibold text-white shadow-lg group-hover:block">${label}</span>` : ""}
  </a>`;
}

function shouldShowAction(active, title) {
  return headerActions[active] && !/^Add\b/i.test(title);
}

export function renderLayout({ active = "dashboard", title = "Dashboard" } = {}) {
  const user = requireAuth();
  if (!user) return null;

  const pageContent = document.getElementById("page-content");
  const contentFragment = document.createDocumentFragment();

  if (pageContent) {
    contentFragment.append(...Array.from(pageContent.childNodes));
  }

  const action = shouldShowAction(active, title) ? headerActions[active] : null;

  document.body.innerHTML = `
    <aside id="sidebar" class="fixed left-0 top-0 z-40 hidden h-screen w-64 flex-col border-r border-[#e7ecf2] bg-white lg:flex">
      <div class="flex h-24 items-center gap-3 border-b border-[#e7ecf2] px-5">
        <a href="dashboard.html" class="grid h-10 w-10 place-items-center rounded-lg bg-[#554cff] text-white shadow-sm" aria-label="CLIENTLY home">${icons.logo}</a>
        <div class="min-w-0">
          <p class="truncate text-sm font-extrabold text-black">CLIENTLY</p>
          <p class="truncate text-xs font-semibold text-[#8aa0b5]">Client agency suite</p>
        </div>
      </div>
      <nav class="flex flex-1 flex-col gap-2 px-4 py-5">
        <p class="px-3 pb-2 text-[11px] font-extrabold uppercase tracking-wide text-[#a2afbc]">Workspace</p>
        ${navItems.map((item) => navLink(item, active)).join("")}
      </nav>
      <div class="border-t border-[#e7ecf2] p-4">
        <button id="logout-button" class="flex h-10 w-full items-center justify-center gap-2 rounded-lg text-xs font-bold text-[#6f8092] transition hover:bg-[#fbfcff] hover:text-[#0d2a4c]" type="button">${icons.logout}<span>Logout</span></button>
      </div>
    </aside>

    <div id="mobile-menu" class="fixed inset-0 z-50 hidden bg-slate-950/35 p-4 lg:hidden">
      <div class="flex h-full max-w-xs flex-col rounded-xl border border-[#e7ecf2] bg-white p-4 shadow-xl">
        <div class="mb-5 flex items-center justify-between">
          <span class="text-lg font-extrabold text-black">CLIENTLY</span>
          <button id="close-mobile-menu" class="btn-ghost !min-h-8 !px-3 !py-2" type="button">Close</button>
        </div>
        <nav class="flex flex-col gap-2">${navItems.map((item) => navLink(item, active, false)).join("")}</nav>
      </div>
    </div>

    <div class="min-h-screen bg-[#fbfcfe] lg:pl-64">
      <header class="sticky top-0 z-30 border-b border-[#e7ecf2] bg-white/95 backdrop-blur">
        <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
          <div class="flex min-w-0 items-center gap-4">
            <button id="mobile-menu-button" class="btn-ghost !min-h-9 !px-3 lg:hidden" type="button">Menu</button>
            <div class="min-w-0">
              <h1 class="truncate text-2xl font-extrabold tracking-normal text-black">${title}</h1>
              <p class="hidden text-xs font-semibold text-[#8aa0b5] sm:block">Monitor clients, deals, payments, and activity.</p>
            </div>
          </div>
          <div class="flex items-center gap-5">
            ${action ? `<a class="btn-primary hidden sm:inline-flex" href="${action[1]}">${action[0]} ${icons.plus}</a>` : ""}
            
            <div id="user-initials" class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-base font-extrabold text-[#0d2a4c] ring-1 ring-[#e7ecf2]">U</div>
          </div>
        </div>
      </header>
      <main id="main-content" class="page-enter px-4 py-5 sm:px-6 lg:px-8"></main>
    </div>

    <div id="global-loader" class="fixed inset-0 z-[70] hidden place-items-center bg-white/70 backdrop-blur-sm">
      <div class="rounded-lg border border-[#e7ecf2] bg-white px-6 py-5 text-center shadow-xl">
        <div class="mx-auto mb-3 h-9 w-9 animate-spin rounded-full border-4 border-[#eeecff] border-t-[#554cff]"></div>
        <p id="global-loader-message" class="text-sm font-bold text-[#0d2a4c]">Loading...</p>
      </div>
    </div>
    <div id="toast-container" class="fixed right-4 top-4 z-[80] flex w-[min(92vw,420px)] flex-col gap-3"></div>
  `;

  document.getElementById("main-content").append(contentFragment);

  const currentUser = getUser();
  const initials = String(currentUser?.name || currentUser?.email || "U").slice(0, 2).toUpperCase();
  document.getElementById("user-initials").textContent = initials;

  document.getElementById("logout-button").addEventListener("click", logout);
  document.getElementById("mobile-menu-button").addEventListener("click", () => document.getElementById("mobile-menu").classList.remove("hidden"));
  document.getElementById("close-mobile-menu").addEventListener("click", () => document.getElementById("mobile-menu").classList.add("hidden"));

  window.addEventListener("crm:loading", (event) => {
    const loader = document.getElementById("global-loader");
    const message = document.getElementById("global-loader-message");

    message.textContent = event.detail?.message || "Loading...";
    loader.classList.toggle("hidden", !event.detail?.active);
    loader.classList.toggle("grid", Boolean(event.detail?.active));
  });

  return user;
}
