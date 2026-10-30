
const API_URL = "api.php";
const REFRESH_INTERVAL = 30000;

const elements = {
    total: document.getElementById("totalPrinters"),
    online: document.getElementById("onlinePrinters"),
    offline: document.getElementById("offlinePrinters"),
    updated: document.getElementById("lastUpdated"),
    grid: document.getElementById("printerGrid"),
    error: document.getElementById("errorMessage"),
    refresh: document.getElementById("refreshButton"),
};

function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, character => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
    })[character]);
}

function formatNumber(value) {
    if (value === null || value === undefined) {
        return "N/A";
    }

    return Number(value).toLocaleString();
}

function renderCounter(label, value) {
    return `
        <div class="usage-row">
            <span>${label}</span>
            <strong>${formatNumber(value)}</strong>
        </div>
    `;
}

function renderPrinter(printer) {
    const counters = printer.counters || {};

    const statusClass = printer.online ? "online" : "offline";

    return `
        <article class="printer-card ${statusClass}">

            <div class="printer-card-header">
                <div>
                    <h3>${escapeHtml(printer.name)}</h3>
                    <p class="printer-ip">${escapeHtml(printer.ip)}</p>
                </div>

                <span class="printer-status ${statusClass}">
                    ${escapeHtml(printer.status)}
                </span>
            </div>

            <div class="printer-details">
                <div>
                    <span>Printer Model</span>
                    <strong>${escapeHtml(printer.model)}</strong>
                </div>

                <div>
                    <span>Uptime</span>
                    <strong>${escapeHtml(printer.uptime)}</strong>
                </div>
            </div>

            <div class="usage-section">
                <h4>Usage Counters</h4>

                ${renderCounter(
                    "Total Printed Impressions",
                    counters.total_printed
                )}

                ${renderCounter(
                    "Color Printed Impressions",
                    counters.color_printed
                )}

                ${renderCounter(
                    "Black Printed Impressions",
                    counters.black_printed
                )}

                ${renderCounter(
                    "Total Copied Impressions",
                    counters.total_copied
                )}
            </div>

        </article>
    `;
}

function renderDashboard(data) {
    elements.total.textContent = data.total;
    elements.online.textContent = data.online;
    elements.offline.textContent = data.offline;
    elements.updated.textContent = data.last_updated;

    elements.grid.innerHTML = data.printers
        .map(renderPrinter)
        .join("");

    elements.error.textContent = "";
}

async function loadDashboard() {
    elements.refresh.disabled = true;
    elements.refresh.textContent = "Loading...";

    try {
        const response = await fetch(
            `${API_URL}?t=${Date.now()}`,
            { cache: "no-store" }
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "API error");
        }

        renderDashboard(data);

    } catch (error) {
        console.error("PRINTMON error:", error);

        elements.error.textContent =
            "Could not load printer information. Please check api.php.";

    } finally {
        elements.refresh.disabled = false;
        elements.refresh.textContent = "↻ Refresh";
    }
}

elements.refresh.addEventListener("click", loadDashboard);

loadDashboard();

setInterval(loadDashboard, REFRESH_INTERVAL);
