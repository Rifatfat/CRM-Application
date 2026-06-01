export const baseURL = "https://cliently-app-f8hrfrgfdqa6bvbw.southeastasia-01.azurewebsites.net/public/index.php/api";

const defaultHeaders = {
  Accept: "application/json",
  "Content-Type": "application/json",
};

function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem("crm_user"));
  } catch {
    return null;
  }
}

function getStoredToken() {
  const user = getStoredUser();

  return (
    user?.token ||
    user?.access_token ||
    user?.data?.token ||
    null
  );
}

function authHeaders() {
  const token = getStoredToken();

  if (!token) {
    return {};
  }

  return {
    Authorization: `Bearer ${token}`,
  };
}

function emitLoading(active, message = "Loading...") {
  window.dispatchEvent(
    new CustomEvent("crm:loading", {
      detail: { active, message },
    })
  );
}

function extractMessage(payload, fallback) {
  if (!payload) return fallback;

  if (typeof payload.message === "string") {
    return payload.message;
  }

  if (typeof payload.error === "string") {
    return payload.error;
  }

  const errors = payload.errors || payload;

  if (typeof errors === "object") {
    const first = Object.values(errors)
      .flat()
      .find(Boolean);

    if (first) return String(first);
  }

  return fallback;
}

export function unwrapData(response) {
  if (
    response &&
    typeof response === "object" &&
    "data" in response
  ) {
    if (
      response.data &&
      typeof response.data === "object" &&
      Array.isArray(response.data.data)
    ) {
      return response.data.data;
    }

    return response.data;
  }

  return response;
}

async function request(endpoint, options = {}) {
  const {
    method = "GET",
    body,
    headers = {},
    loadingMessage,
  } = options;

  if (loadingMessage) {
    emitLoading(true, loadingMessage);
  }

  try {
    const finalHeaders = {
      ...defaultHeaders,
      ...authHeaders(),
      ...headers,
    };

    const response = await fetch(`${baseURL}${endpoint}`, {
      method,
      headers: finalHeaders,
      body:
        body === undefined
          ? undefined
          : JSON.stringify(body),
    });

    const contentType =
      response.headers.get("content-type") || "";

    const hasBody = response.status !== 204;

    const payload = hasBody
      ? contentType.includes("application/json")
        ? await response.json()
        : await response.text()
      : null;

    if (!response.ok) {

      if (response.status === 401 && !["/login", "/register"].includes(endpoint)) {
        localStorage.removeItem("crm_user");
        localStorage.removeItem("crm_token");
        window.location.href = "login.html";
      }

      const message = extractMessage(
        payload,
        `Request failed with status ${response.status}`
      );

      const error = new Error(message);

      error.status = response.status;
      error.payload = payload;

      throw error;
    }

    return payload;

  } finally {
    if (loadingMessage) {
      emitLoading(false);
    }
  }
}

export const api = {
  get(endpoint, options = {}) {
    return request(endpoint, {
      ...options,
      method: "GET",
    });
  },

  post(endpoint, body, options = {}) {
    return request(endpoint, {
      ...options,
      method: "POST",
      body,
    });
  },

  put(endpoint, body, options = {}) {
    return request(endpoint, {
      ...options,
      method: "PUT",
      body,
    });
  },

  delete(endpoint, options = {}) {
    return request(endpoint, {
      ...options,
      method: "DELETE",
    });
  },
};
