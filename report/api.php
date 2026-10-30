
<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/includes/snmp.php';

try {
    $printers = [];

    foreach ($config['printers'] as $printer) {
        $printers[] = getPrinterData($printer, $config);
    }

    $total = count($printers);

    $online = count(array_filter(
        $printers,
        fn($printer) => $printer['online'] === true
    ));

    echo json_encode([
        'success' => true,
        'total' => $total,
        'online' => $online,
        'offline' => $total - $online,
        'last_updated' => date('Y-m-d H:i:s'),
        'refresh_interval' => $config['refresh_interval'],
        'printers' => $printers,
    ], JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Throwable $e) {
    http_response_code(500);

    error_log($e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve printer information.',
    ]);
}
