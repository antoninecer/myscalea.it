<?php
// map/header.php
// 1) languages & translation logic
require_once __DIR__ . '/../languages.php';

// 2) DB connect & write visits
require_once __DIR__ . '/../inc/connect.php';
require_once __DIR__ . '/../writevisits.php';

// 3) send HTML head
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ($lang==='cs' ? 'Mapa MyScalea' : 'MyScalea Map'); ?></title>

  <!-- Google Translate widget -->
  <script type="text/javascript">
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: '<?php echo implode(',', $supported); ?>',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
      }, 'google_translate_element');
    }
  </script>
  <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

  <!-- Styles & Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="map.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://kit.fontawesome.com/a53c6e5e24.js" crossorigin="anonymous"></script>

  <style>
    body.modal-open { overflow: hidden; }
    #google_translate_element { position: absolute; top: 10px; right: 10px; z-index: 2002; }
    #burger { position: absolute; top: 10px; left: 10px; font-size: 24px; background: white; padding: 6px 10px; border-radius: 5px; cursor: pointer; z-index: 1001; }
    #sideMenu { /* vaše stávající styly menu */ }
    #map { position: absolute; top: 0; bottom: 0; right: 0; left: 0; }
  </style>
  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-GL465JKLQ2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-GL465JKLQ2', {
    'linker': {
      'domains': ['rightdone.eu', 'myscalea.it']
    }
  });
</script>
</head>
<body>
  <!-- Google Translate dropdown -->
  <div id="google_translate_element"></div>

