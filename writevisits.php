<?php
// writevisits.php – finální logování návštěv s PHP timestampem, UA parsingem, geolokací a GPS koordináty

// 0) PHP časová zóna
date_default_timezone_set('Europe/Prague');

// 1) Session + DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inc/connect.php';

// 2) MySQL timezone synchronizace
try {
    $pdo->exec("SET time_zone = '+02:00'");
} catch (Exception $e) {
    error_log('Failed to set MySQL time_zone: ' . $e->getMessage());
}

// 3) Autoload (DeviceDetector, GeoIP2)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

use DeviceDetector\DeviceDetector;
use GeoIp2\Database\Reader;

// 4) Helpers
// IP
function getClientIp(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}
// UA parse
function parseUserAgent(string $ua): array {
    $browser = 'unknown';
    $os = 'unknown';
    $device = 'unknown';
    if (class_exists(DeviceDetector::class)) {
        $dd = new DeviceDetector($ua);
        $dd->parse();
        $ci = $dd->getClient() ?: [];
        $browser = trim(($ci['name'] ?? '') . ' ' . ($ci['version'] ?? '')) ?: 'unknown';
        $oi = $dd->getOs() ?: [];
        $os = trim(($oi['name'] ?? '') . ' ' . ($oi['version'] ?? '')) ?: 'unknown';
        if (method_exists($dd, 'getDeviceName')) {
            $device = $dd->getDeviceName() ?: 'unknown';
        } else {
            // fallback
            $device = stripos($ua, 'Mobile') !== false ? 'mobile' : 'desktop';
        }
    } else {
        if (preg_match('/Chrome\/([0-9\.]+)/', $ua, $m)) {
            $browser = 'Chrome ' . $m[1];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/', $ua, $m)) {
            $browser = 'Firefox ' . $m[1];
        }
        $device = stripos($ua, 'Mobile') !== false ? 'mobile' : 'desktop';
        if (preg_match('/Windows NT ([0-9\.]+)/', $ua, $m)) {
            $os = 'Windows ' . $m[1];
        } elseif (preg_match('/Mac OS X ([0-9_]+)/', $ua, $m)) {
            $os = 'Mac ' . str_replace('_', '.', $m[1]);
        }
    }
    return ['browser'=>$browser,'os'=>$os,'device_type'=>$device];
}
// Language
function getPrimaryLanguage(): string {
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return 'unknown';
    $parts = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    return strtolower(substr(trim($parts[0]),0,5));
}

// 5) GeoIP2 City
$cityDb = __DIR__ . '/db/GeoLite2-City.mmdb';
$geoReader = (class_exists(Reader::class) && file_exists($cityDb)) ? new Reader($cityDb) : null;

// 6) Sběr dat
$timestamp = date('Y-m-d H:i:s');
$pageUrl   = $_SERVER['REQUEST_URI'];
$ip        = getClientIp();
$uaRaw     = $_SERVER['HTTP_USER_AGENT'] ?? '';
$uaData    = parseUserAgent($uaRaw);
$language  = getPrimaryLanguage();
$country   = 'unknown';
$latitude  = null; $longitude = null;
if ($geoReader) {
    try {
        $rec = $geoReader->city($ip);
        $country   = $rec->country->isoCode   ?? 'unknown';
        $latitude  = $rec->location->latitude ?? null;
        $longitude = $rec->location->longitude ?? null;
    } catch (Exception $e) {
        // ignore
    }
}

// 7) Debug mód
if (!empty($_GET['debug_visits'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Timestamp: $timestamp\n";
    echo "URL:       $pageUrl\n";
    echo "IP:        $ip\n";
    echo "UA:        $uaRaw\n";
    echo "Parsed:    " . print_r($uaData,true) . "\n";
    echo "Lang:      $language\n";
    echo "Country:   $country\n";
    echo "Latitude:  $latitude\n";
    echo "Longitude: $longitude\n";
    exit;
}

// 8) Throttling & INSERT
$now = time();
$lastUrl = $_SESSION['last_visit_page'] ?? '';
$lastTs  = $_SESSION['last_visit_time'] ?? 0;
if ($pageUrl !== $lastUrl || ($now - $lastTs) >= 2) {
    try {
        $uid = $_SESSION['user']['id'] ?? null;
        $sid = session_id();
        $ref = $_SERVER['HTTP_REFERER'] ?? null;
        $sql = "INSERT INTO visits
            (user_id, session_id, page_url, page_title, visit_start,
             ip_address, user_agent, device_type, os, browser,
             country, latitude, longitude, language, referrer_url)
         VALUES
            (:uid,:sid,:page,:title,:ts,
             INET6_ATON(:ip),:ua,:dev,:os,:br,
             :cty,:lat,:lon,:lng,:ref)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid'=> $uid, ':sid'=> $sid, ':page'=> $pageUrl,
            ':title'=> $pageTitle ?? null, ':ts'=> $timestamp,
            ':ip'=> $ip, ':ua'=> $uaRaw, ':dev'=> $uaData['device_type'],
            ':os'=> $uaData['os'], ':br'=> $uaData['browser'],
            ':cty'=> $country, ':lat'=> $latitude, ':lon'=> $longitude,
            ':lng'=> $language, ':ref'=> $ref
        ]);
        $_SESSION['last_visit_page'] = $pageUrl;
        $_SESSION['last_visit_time'] = $now;
        $_SESSION['visit_id']        = $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log('Visit logging failed: ' . $e->getMessage());
    }
}
