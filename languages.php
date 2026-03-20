<?php
// languages.php
// 1) start session if not yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) definice podporovaných jazyků
$supported = ['en','cs','sk','it','de','fr','es','pl','ru','uk','zh','ja','ko','tr','pt'];

// 3) uložení výběru jazyka z POST do session
if (!empty($_POST['lang']) && in_array($_POST['lang'], $supported, true)) {
    $_SESSION['lang'] = $_POST['lang'];
}

// 4) rozhodnutí, který jazyk použít: session > cookie > Accept-Language > fallback en
if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], $supported, true)) {
    $lang = $_SESSION['lang'];
} elseif (!empty($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported, true)) {
    $lang = $_COOKIE['lang'];
} else {
    // parse Accept-Language
    $lang = 'en';
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($langs as $l) {
            $code = substr(trim($l), 0, 2);
            if (in_array($code, $supported, true)) {
                $lang = $code;
                break;
            }
        }
    }
    $_SESSION['lang'] = $lang;
}

// 5) pokud cookie "lang" neexistuje a ještě jsme nepoptali souhlas, vyžádáme souhlas
if (empty($_COOKIE['lang']) && empty($_SESSION['lang_cookie_asked'])) {
    $_SESSION['lang_cookie_asked'] = true;
    echo '<script>';
    echo 'if(confirm("Chcete uložit Vaši jazykovou volbu do cookie pro příště?")) {';
    echo 'document.cookie = "lang=' . $lang . '; path=/; max-age=" + 60*60*24*365 + ";";';
    echo 'document.cookie = "lang_consent_date=' . date('c') . '; path=/; max-age=" + 60*60*24*365 + ";";';
    echo 'location.reload();';
    echo '}';
    echo '</script>';
}

// 6) nastavíme googtrans cookie před načtením Google Translate elementu
// (pokud používáte widget)
echo "<script>\n";
echo "  document.cookie = 'googtrans=/en/{$lang}; path=/;';\n";
echo "  document.cookie = 'googtrans=/en/{$lang}; path=/; domain={$_SERVER['HTTP_HOST']};';\n";
echo "</script>\n";

// 7) CSS pro skrytí banneru lze přidat v headeru nebo zde jako inline

// Proměnná $lang je nyní k dispozici pro použití v headeru a na stránce
