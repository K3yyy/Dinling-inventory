
<?php

$config = require __DIR__ . '/config.php';

$canonOids = [
    'Total Counter'       => '1.3.6.1.4.1.1602.1.11.1.3.1.4.101',
    'Monochrome Counter'  => '1.3.6.1.4.1.1602.1.11.1.3.1.4.108',
    'Color Counter A'     => '1.3.6.1.4.1.1602.1.11.1.3.1.4.122',
    'Color Counter B'     => '1.3.6.1.4.1.1602.1.11.1.3.1.4.123',
    'Copy Black Candidate'=> '1.3.6.1.4.1.1602.1.11.1.3.1.4.222',
    'Copy Color Candidate'=> '1.3.6.1.4.1.1602.1.11.1.3.1.4.231',
    'Print Black Candidate'=> '1.3.6.1.4.1.1602.1.11.1.3.1.4.314',
    'Print Color Candidate'=> '1.3.6.1.4.1.1602.1.11.1.3.1.4.323',
];

$xeroxOids = [
    'Total Impressions Candidate' =>
        '1.3.6.1.4.1.253.8.53.13.2.1.6.1.20.1',

    'Color Impressions Candidate' =>
        '1.3.6.1.4.1.253.8.53.13.2.1.6.1.20.33',

    'Black Impressions Candidate' =>
        '1.3.6.1.4.1.253.8.53.13.2.1.6.1.20.34',

    'Standard Total Counter' =>
        '1.3.6.1.2.1.43.10.2.1.4.1.1',
];

foreach ($config['printers'] as $printer) {

    $ip = $printer['ip'];

    echo "\n========================================\n";
    echo $printer['name'] . " ($ip)\n";
    echo "========================================\n";

    $description = @snmpget(
        $ip,
        $config['community'],
        '1.3.6.1.2.1.1.1.0',
        2000000,
        1
    );

    if ($description === false) {
        echo "OFFLINE / SNMP NOT RESPONDING\n";
        continue;
    }

    echo "MODEL: $description\n\n";

    $isCanon = stripos($description, 'Canon') !== false;

    $oids = $isCanon ? $canonOids : $xeroxOids;

    foreach ($oids as $label => $oid) {

        $result = @snmpget(
            $ip,
            $config['community'],
            $oid,
            2000000,
            1
        );

        echo $label . "\n";
        echo "OID: $oid\n";
        echo "VALUE: " . (
            $result !== false ? $result : 'NOT SUPPORTED'
        );
        echo "\n\n";
    }
}
