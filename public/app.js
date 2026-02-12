document.addEventListener("DOMContentLoaded", () => {
  const urlList = document.getElementById("urlList");
  const addUrlBtn = document.getElementById("addUrlBtn");
  const urlModal = document.getElementById("urlModal");
  const urlForm = document.getElementById("urlForm");
  const closeModal = document.getElementById("closeModal");
  const modalTitle = document.getElementById("modalTitle");

  // State
  let urls = [];

  // Fetch all URLs
  async function fetchUrls() {
    try {
      const response = await fetch("/api/urls");
      const result = await response.json();
      urls = result.data;
      renderUrls();
    } catch (error) {
      console.error("Error fetching URLs:", error);
      urlList.innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: var(--danger);">Failed to load URLs. Check console.</td></tr>';
    }
  }

  // Render URLs in the table
  function renderUrls() {
    if (urls.length === 0) {
      urlList.innerHTML =
        '<tr><td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">No URLs monitored yet. Click "Add URL" to start.</td></tr>';
      return;
    }

    urlList.innerHTML = urls
      .map(
        (url) => `
            <tr class="url-row">
                <td><strong>${url.name || "Unnamed"}</strong></td>
                <td><a href="${url.url}" target="_blank" style="color: var(--primary); text-decoration: none;">${url.url}</a></td>
                <td><span class="status-badge status-${url.status}">${url.status}</span></td>
                <td style="color: var(--text-muted); font-size: 0.875rem;">${url.last_checked_at || "Never"}</td>
                <td>
                    <div class="actions">
                        <button class="icon-btn" onclick="checkHealth(${url.id})" title="Check Now">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        </button>
                        <button class="icon-btn" onclick="editUrl(${url.id})" title="Edit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="icon-btn delete" onclick="deleteUrl(${url.id})" title="Delete">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `,
      )
      .join("");
  }

  // Modal Controls
  addUrlBtn.onclick = () => {
    urlForm.reset();
    document.getElementById("urlId").value = "";
    modalTitle.innerText = "Add New URL";
    urlModal.style.display = "flex";
  };

  closeModal.onclick = () => {
    urlModal.style.display = "none";
  };

  window.onclick = (event) => {
    if (event.target === urlModal) {
      urlModal.style.display = "none";
    }
  };

  // Save (Create/Update)
  urlForm.onsubmit = async (e) => {
    e.preventDefault();
    const id = document.getElementById("urlId").value;
    const data = {
      name: document.getElementById("name").value,
      url: document.getElementById("url").value,
    };

    const method = id ? "PUT" : "POST";
    const endpoint = id ? `/api/urls/${id}` : "/api/urls";

    try {
      const response = await fetch(endpoint, {
        method: method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      if (response.ok) {
        urlModal.style.display = "none";
        fetchUrls();
      } else {
        const error = await response.json();
        alert(error.error || "Failed to save URL");
      }
    } catch (error) {
      console.error("Error saving URL:", error);
    }
  };

  // Global Action Functions
  window.editUrl = (id) => {
    const url = urls.find((u) => u.id === id);
    if (url) {
      document.getElementById("urlId").value = url.id;
      document.getElementById("name").value = url.name;
      document.getElementById("url").value = url.url;
      modalTitle.innerText = "Edit URL";
      urlModal.style.display = "flex";
    }
  };

  window.deleteUrl = async (id) => {
    if (confirm("Are you sure you want to delete this URL?")) {
      try {
        const response = await fetch(`/api/urls/${id}`, { method: "DELETE" });
        if (response.ok) {
          fetchUrls();
        }
      } catch (error) {
        console.error("Error deleting URL:", error);
      }
    }
  };

  window.checkHealth = async (id) => {
    const btn = event.currentTarget;
    btn.style.opacity = "0.5";
    btn.style.pointerEvents = "none";

    try {
      await fetch(`/api/urls/${id}/check`, { method: "POST" });
      fetchUrls();
    } catch (error) {
      console.error("Error checking health:", error);
    } finally {
      btn.style.opacity = "1";
      btn.style.pointerEvents = "auto";
    }
  };

  // Initial load
  fetchUrls();
});
