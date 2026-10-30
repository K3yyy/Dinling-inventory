
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PRINTMON | Printer Monitoring</title>

    <link rel="stylesheet" href="assets/style.css?v=20">
</head>

<body>

<div class="app-layout">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">P</div>
            <div>
                <h2>PRINTMON</h2>
                <span>Printer Monitoring</span>
            </div>
        </div>

        <nav class="navigation">
            <a href="#" class="nav-link active">
                <span>▦</span>
                Dashboard
            </a>

            <a href="#printerGrid" class="nav-link">
                <span>▤</span>
                Printers
            </a>

            <a href="#printerGrid" class="nav-link">
                <span>▥</span>
                Usage Counters
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="connection-dot"></span>
            SNMP Monitoring
            <small>Local Network</small>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Printer Dashboard</h1>
                <p>Monitor printer connectivity and usage counters.</p>
            </div>

            <button id="refreshButton" class="refresh-button">
                ↻ Refresh
            </button>
        </header>

        <section class="summary-grid">

            <div class="summary-card">
                <div class="summary-icon blue">▤</div>
                <div>
                    <span>Total Printers</span>
                    <strong id="totalPrinters">0</strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon green">●</div>
                <div>
                    <span>Online Printers</span>
                    <strong id="onlinePrinters">0</strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon red">●</div>
                <div>
                    <span>Offline Printers</span>
                    <strong id="offlinePrinters">0</strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon purple">◷</div>
                <div>
                    <span>Last Updated</span>
                    <strong id="lastUpdated" class="updated-value">--</strong>
                </div>
            </div>

        </section>

        <div id="errorMessage" class="error-message"></div>

        <section class="section-heading">
            <div>
                <h2>Connected Printers</h2>
                <p>Live status and lifetime usage information</p>
            </div>

            <span class="auto-refresh-label">
                Auto refresh: 30 seconds
            </span>
        </section>

        <section id="printerGrid" class="printer-grid">

            <div class="loading-message">
                Loading printer information...
            </div>

        </section>

        <footer class="page-footer">
            PRINTMON · Printer Monitoring System
        </footer>

    </main>

</div>

<script src="assets/app.js?v=20"></script>

</body>
</html>
