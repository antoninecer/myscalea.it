<?php
/**
 * mareflag.php — HTML widget pro Tyrhénské moře (Scalea)
 * Použití: <?php include __DIR__ . '/mareflag.php'; ?>
 */

// POZN.: Content-Type hlavičku nastav v nadřazeném skriptu, ne zde.

// --- Nastavení ---
$SOURCE_URL = 'https://www.3bmeteo.com/previsioni/mare/calabria';
$CACHE_FILE = sys_get_temp_dir() . '/mareflag_scalea_cache.html';
$CACHE_TTL  = 5 * 60; // 5 min

function http_get($url, $timeout = 8) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (MareFlag/1.1; +myscalea.it)'
        ]);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data !== false) return $data;
    }
    $ctx = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'header'  => "User-Agent: Mozilla/5.0 (MareFlag/1.1)\r\n"
        ]
    ]);
    return @file_get_contents($url, false, $ctx);
}

// --- Cache ---
if (is_file($CACHE_FILE) && (time() - filemtime($CACHE_FILE) < $CACHE_TTL)) {
    readfile($CACHE_FILE);
    return;
}

// --- Výchozí hodnoty ---
$html = http_get($SOURCE_URL);
$dotColor = '#aaaaaa';
$flag = 'unknown';
$sea_text = 'neznámo';
$wave_m = null;

// --- Parsování ---
if ($html && preg_match('/Alto\s*Tirreno\s*-\s*Cosentino:(.*?)(?:<\/p>|<br|$)/siu', $html, $blk)) {
    $block = $blk[1];

    if (preg_match('/mare\s+([a-zàèéìòù\s]+?)(?:,|\.|\s{2,}|$)/iu', $block, $m1)) {
        $sea_text = trim($m1[1]);
    }
    if (preg_match('/altezza\s+dell.?onda\s+([0-9\.,]+)\s*m/iu', $block, $m2)) {
        $wave_m = floatval(str_replace(',', '.', $m2[1]));
    }

    $s = mb_strtolower($sea_text, 'UTF-8');
    if (strpos($s, 'calmo') !== false || strpos($s, 'poco mosso') !== false || ($wave_m !== null && $wave_m <= 0.6)) {
        $flag = 'green';  $dotColor = '#24c26a';
    } elseif (strpos($s, 'agitato') !== false || strpos($s, 'molto mosso') !== false || ($wave_m !== null && $wave_m > 1.2)) {
        $flag = 'red';    $dotColor = '#e53e3e';
    } elseif (strpos($s, 'mosso') !== false || ($wave_m !== null && $wave_m > 0.6)) {
        $flag = 'yellow'; $dotColor = '#f6ad55';
    }
}

// --- Bezpečné texty do HTML ---
$label     = 'Scalea · Tyrhénské moře';
$waveLabel = ($wave_m !== null) ? number_format($wave_m, 1, ',', ' ') . ' m' : '—';
$seaLabel  = ($sea_text !== 'neznámo') ? $sea_text : 'stav nezjištěn';

$h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

$widget = <<<HTML
<div class="mare-flag" style="display:inline-flex;align-items:center;gap:.6rem;padding:.5rem .75rem;border:1px solid #e5e7eb;border-radius:.75rem;font:600 14px/1.2 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial;">
  <span aria-hidden="true" style="width:12px;height:12px;border-radius:999px;background:{$dotColor};display:inline-block;"></span>
  <span style="font-weight:600;">{$h($label)}</span>
  <span style="opacity:.7;font-weight:500;">• vlny {$h($waveLabel)}</span>
  <span style="opacity:.7;font-weight:500;">• {$h($seaLabel)}</span>
</div>
HTML;

@file_put_contents($CACHE_FILE, $widget);
echo $widget;
