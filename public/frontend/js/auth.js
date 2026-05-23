import { api, unwrapData } from "./api.js";
import { getFormData, getUser, setUser, showToast, validateEmail, validateRequired } from "./utils.js";

const loginPanel = document.getElementById("login-panel");
const registerPanel = document.getElementById("register-panel");
const showRegisterButton = document.getElementById("show-register");
const showLoginButton = document.getElementById("show-login");
const loginForm = document.getElementById("login-form");
const registerForm = document.getElementById("register-form");
const loginError = document.getElementById("login-error");
const registerError = document.getElementById("register-error");

function setLoading(button, loading) {
  button.disabled = loading;
  button.textContent = loading ? "Please wait..." : button.dataset.label;
}

function showPanel(panel) {
  const isRegister = panel === "register";
  loginPanel.classList.toggle("hidden", isRegister);
  registerPanel.classList.toggle("hidden", !isRegister);
  loginError?.classList.add("hidden");
  registerError?.classList.add("hidden");
}

function showInlineError(element, message) {
  if (!element) return;
  element.textContent = message;
  element.classList.remove("hidden");
}

showRegisterButton.addEventListener("click", () => showPanel("register"));
showLoginButton.addEventListener("click", () => showPanel("login"));

if (getUser()) {
  window.location.href = "dashboard.html";
}

loginForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const button = loginForm.querySelector("button[type='submit']");
  const data = getFormData(loginForm);

  try {
    loginError?.classList.add("hidden");

    validateRequired(data, ["email", "password"]);
    validateEmail(data.email);

    setLoading(button, true);

    const response = await api.post("/login", data);

    // IMPORTANT
    const authData = response.data || response;

    if (!authData.token) {
      throw new Error("Token not found from login response");
    }

    localStorage.setItem("crm_user", JSON.stringify(authData));

    showToast("Welcome back. Redirecting...", "success");

    // kasih delay dikit
    setTimeout(() => {
      window.location.href = "dashboard.html";
    }, 1000);

  } catch (error) {
    console.error(error);

    showInlineError(loginError, error.message);
    showToast(error.message, "error");

  } finally {
    setLoading(button, false);
  }
});

registerForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const button = registerForm.querySelector("button[type='submit']");
  const data = getFormData(registerForm);

  try {
    registerError?.classList.add("hidden");
    validateRequired(data, ["name", "email", "password"]);
    validateEmail(data.email);
    if (data.password.length < 6) throw new Error("Password must be at least 6 characters.");

    setLoading(button, true);
    await api.post("/register", data);
    showToast("Account created. Please log in.", "success");
    registerForm.reset();
    showPanel("login");
  } catch (error) {
    showInlineError(registerError, error.message);
    showToast(error.message, "error");
  } finally {
    setLoading(button, false);
  }
});
