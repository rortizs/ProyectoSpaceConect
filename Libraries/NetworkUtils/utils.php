<?php

function encrypt_aes($text, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt_aes($text, $key)
{
    $text = base64_decode($text);
    $iv = substr($text, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = substr($text, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

function humanReadableBytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $index = 0;
    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }
    return round($bytes, 2) . ' ' . $units[$index];
}
function getAvailableIPs($ipAddress, $excludeList)
{
    $ipParts = explode('/', $ipAddress);
    $ipAddress = $ipParts[0];
    $subnetMask = isset($ipParts[1]) ? (int)$ipParts[1] : 24;

    $hostPart = explode('.', $ipAddress);
    $hostPart = array_slice($hostPart, 0, 3);

    $availableIPs = [];

    if ($subnetMask == 25) {
        $startRange = 2;
        $endRange = 126;
    } else {
        $startRange = 2;
        $endRange = 254;
    }

    for ($i = $startRange; $i <= $endRange; $i++) {
        $ip = implode('.', array_merge($hostPart, [$i]));
        if (!in_array($ip, $excludeList)) {
            $availableIPs[] = $ip;
        }
    }

    return $availableIPs;
}
function convertToBytes($value, $denomination)
{
    $denominations = [
        'BPS' => 1,
        'KBPS' => 1024,
        'MBPS' => pow(1024, 2),
        'GBPS' => pow(1024, 3),
        'TBPS' => pow(1024, 4),
        'PBPS' => pow(1024, 5),
        'EBPS' => pow(1024, 6),
        'ZBPS' => pow(1024, 7),
        'YBPS' => pow(1024, 8),
    ];

    $denomination = strtoupper($denomination);
    if (!isset($denominations[$denomination])) {
        throw new InvalidArgumentException("Invalid denomination");
    }

    return $value * $denominations[$denomination];
}
function simplePing($host, $count = 4)
{
    $output = array();
    exec("ping -n $count $host", $output, $status);

    if ($status === 0) {
        foreach ($output as $line) {
            echo $line, "\n";
        }
        return true;
    } else {
        echo "Ping failed.";
        return false;
    }
}
function convert_uptime($time)
{
    $units = array(
        's' => 'segundos',
        'm' => 'minutos',
        'h' => 'horas',
        'd' => 'días'
    );

    $parts = array();
    $current_value = '';

    for ($i = 0; $i < strlen($time); $i++) {
        $char = $time[$i];

        if (ctype_digit($char)) {
            $current_value .= $char;
        } else {
            $parts[] = array('value' => (int) $current_value, 'unit' => $char);
            $current_value = '';
        }
    }

    $output = '';

    foreach ($parts as $part) {
        $value = $part['value'];
        $unit = $part['unit'];

        if ($value == 1) {
            $unit = str_replace('s', '', $units[$unit]);
        }

        if ($output != '') {
            $output .= ' ';
        }

        $output .= $value . ' ' . $units[$unit];
    }

    return 'Hace ' . $output;
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
}

// =============================================
// CIDR / IP Validation Helpers (Municipal Module)
// =============================================

function ipInCidr(string $ip, string $cidr): bool
{
    if (strpos($cidr, '/') === false) {
        return $ip === $cidr;
    }

    list($subnet, $bits) = explode('/', $cidr);
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);

    if ($ip_long === false || $subnet_long === false) {
        return false;
    }

    $mask = -1 << (32 - (int)$bits);
    $subnet_long &= $mask;

    return ($ip_long & $mask) === $subnet_long;
}

function cidrOverlap(string $cidr1, string $cidr2): bool
{
    list($net1, $bits1) = explode('/', $cidr1);
    list($net2, $bits2) = explode('/', $cidr2);

    $net1_long = ip2long($net1);
    $net2_long = ip2long($net2);

    if ($net1_long === false || $net2_long === false) {
        return false;
    }

    $min_bits = min((int)$bits1, (int)$bits2);
    $mask = -1 << (32 - $min_bits);

    return ($net1_long & $mask) === ($net2_long & $mask);
}

function getUsableIpsFromCidr(string $cidr): array
{
    if (strpos($cidr, '/') === false) {
        return [$cidr];
    }

    list($subnet, $bits) = explode('/', $cidr);
    $bits = (int)$bits;
    $subnet_long = ip2long($subnet);

    if ($subnet_long === false || $bits < 1 || $bits > 32) {
        return [];
    }

    $mask = -1 << (32 - $bits);
    $network = $subnet_long & $mask;
    $broadcast = $network | ~$mask;

    $ips = [];
    // Skip network address and broadcast (first and last)
    for ($i = $network + 1; $i < $broadcast; $i++) {
        $ips[] = long2ip($i);
    }

    return $ips;
}

// =============================================
// Simple Range Helpers (Municipal Module - Flat Network)
// =============================================

function ipInRange(string $ip, string $rangeStart, string $rangeEnd): bool
{
    $ip_long = ip2long($ip);
    $start_long = ip2long($rangeStart);
    $end_long = ip2long($rangeEnd);

    if ($ip_long === false || $start_long === false || $end_long === false) {
        return false;
    }

    return $ip_long >= $start_long && $ip_long <= $end_long;
}

function parseSimpleRange(string $range): ?array
{
    $parts = explode('-', $range, 2);
    if (count($parts) !== 2) {
        return null;
    }

    $start = trim($parts[0]);
    $end = trim($parts[1]);

    if (ip2long($start) === false || ip2long($end) === false) {
        return null;
    }

    if (ip2long($start) > ip2long($end)) {
        return null;
    }

    return ['start' => $start, 'end' => $end];
}

function getUsableIpsFromRange(string $rangeStart, string $rangeEnd): array
{
    $start_long = ip2long($rangeStart);
    $end_long = ip2long($rangeEnd);

    if ($start_long === false || $end_long === false || $start_long > $end_long) {
        return [];
    }

    $ips = [];
    for ($i = $start_long; $i <= $end_long; $i++) {
        $ips[] = long2ip($i);
    }

    return $ips;
}

function simpleRangesOverlap(string $range1, string $range2): bool
{
    $r1 = parseSimpleRange($range1);
    $r2 = parseSimpleRange($range2);

    if ($r1 === null || $r2 === null) {
        return false;
    }

    $r1Start = ip2long($r1['start']);
    $r1End = ip2long($r1['end']);
    $r2Start = ip2long($r2['start']);
    $r2End = ip2long($r2['end']);

    return $r1Start <= $r2End && $r2Start <= $r1End;
}

function sanitizeQueueName(string $name): string
{
    $name = mb_strtolower($name, 'UTF-8');
    // Replace accented chars
    $name = str_replace(
        ['á','é','í','ó','ú','ñ','ü'],
        ['a','e','i','o','u','n','u'],
        $name
    );
    // Replace spaces and underscores with hyphens
    $name = preg_replace('/[\s_]+/', '-', $name);
    // Remove anything that's not alphanumeric or hyphen
    $name = preg_replace('/[^a-z0-9\-]/', '', $name);
    // Collapse multiple hyphens
    $name = preg_replace('/-+/', '-', $name);
    return trim($name, '-');
}

