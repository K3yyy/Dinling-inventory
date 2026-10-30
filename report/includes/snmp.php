
<?php

function snmpValue(
    string $ip,
    string $community,
    string $oid
): ?string {
    $result = @snmpget(
        $ip,
        $community,
        $oid,
        2000000,
        1
    );

    return $result === false ? null : trim($result);
}

function snmpNumber(
    string $ip,
    string $community,
    string $oid
): ?int {
    $value = snmpValue($ip, $community, $oid);

    if ($value === null) {
        return null;
    }

    if (preg_match('/(-?\d+)\s*$/', $value, $matches)) {
        return (int) $matches[1];
    }

    return null;
}

function snmpText(
    string $ip,
    string $community,
    string $oid
): ?string {
    $value = snmpValue($ip, $community, $oid);

    if ($value === null) {
        return null;
    }

    $value = preg_replace(
        '/^(STRING|OCTET STRING):\s*/i',
        '',
        $value
    );

    return trim($value, " \t\n\r\0\x0B\"");
}

function formatPrinterUptime(?int $ticks): string
{
    if ($ticks === null) {
        return 'N/A';
    }

    $seconds = (int) floor($ticks / 100);

    $days = intdiv($seconds, 86400);
    $hours = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);

    return "{$days}d {$hours}h {$minutes}m";
}

function readPrinterCounters(
    string $ip,
    string $community,
    string $brand
): array {

    $counters = [
        'total_printed' => null,
        'color_printed' => null,
        'black_printed' => null,
        'total_copied' => null,
        'total_device' => null,
    ];

    // Fuji Xerox enterprise counters
    if ($brand === 'xerox') {

        $base = '1.3.6.1.4.1.253.8.53.13.2.1.6.1.20.';

        $deviceTotal = snmpNumber(
            $ip,
            $community,
            $base . '1'
        );

        $black = snmpNumber(
            $ip,
            $community,
            $base . '7'
        );

        $color = snmpNumber(
            $ip,
            $community,
            $base . '29'
        );

        $counters['total_device'] = $deviceTotal;
        $counters['black_printed'] = $black;
        $counters['color_printed'] = $color;

        if (
            $deviceTotal !== null &&
            $black !== null &&
            $color !== null &&
            ($black + $color) <= $deviceTotal
        ) {
            $printed = $black + $color;

            $counters['total_printed'] = $printed;
            $counters['total_copied'] = $deviceTotal - $printed;
        }
    }

    // Canon MF750C
    elseif ($brand === 'canon_mf750') {

        $base = '1.3.6.1.4.1.1602.1.11.1.3.1.4.';

        $total = snmpNumber(
            $ip,
            $community,
            $base . '101'
        );

        $black = snmpNumber(
            $ip,
            $community,
            $base . '108'
        );

        $color = snmpNumber(
            $ip,
            $community,
            $base . '402'
        );

        $counters['total_device'] = $total;
        $counters['black_printed'] = $black;
        $counters['color_printed'] = $color;

        // The observed Canon counters add up to the total.
        // Keep the print total unavailable if they do not.
        if (
            $total !== null &&
            $black !== null &&
            $color !== null &&
            ($black + $color) === $total
        ) {
            $counters['total_printed'] = $total;
        }
    }

    // Canon iR-ADV 4525
    elseif ($brand === 'canon_ir') {

        // Standard Printer-MIB total device impressions.
        // This does not separate print jobs from copy jobs.
        $counters['total_device'] = snmpNumber(
            $ip,
            $community,
            '1.3.6.1.2.1.43.10.2.1.4.1.1'
        );
    }

    return $counters;
}

function getPrinterData(
    array $printer,
    array $config
): array {

    $ip = $printer['ip'];
    $community = $config['community'];

    $description = snmpText(
        $ip,
        $community,
        '1.3.6.1.2.1.1.1.0'
    );

    if ($description === null) {
        return [
            'name' => $printer['name'],
            'ip' => $ip,
            'online' => false,
            'model' => 'Unavailable',
            'uptime' => 'N/A',
            'status' => 'Offline',
            'counters' => [
                'total_printed' => null,
                'color_printed' => null,
                'black_printed' => null,
                'total_copied' => null,
                'total_device' => null,
            ],
        ];
    }

    $uptime = snmpNumber(
        $ip,
        $community,
        '1.3.6.1.2.1.1.3.0'
    );

    $printerStatus = snmpNumber(
        $ip,
        $community,
        '1.3.6.1.2.1.25.3.5.1.1.1'
    );

    return [
        'name' => $printer['name'],
        'ip' => $ip,
        'online' => true,
        'model' => $description,
        'uptime' => formatPrinterUptime($uptime),
        'status' => $printerStatus === 3 ? 'Ready' : 'Online',
        'counters' => readPrinterCounters(
            $ip,
            $community,
            $printer['brand']
        ),
    ];
}
