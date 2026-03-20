v<?php
require_once 'inc/connect.php';

$success = sendEmail(
  'seton@centrum.cz',            // nahraď vlastním
  'Testovací uživatel',
  'Test from MyScalea.it',
  '<h1>Hello from MyScalea</h1><p>This is a test email.</p>'
);

echo $success ? '✅ E-mail odeslán.' : '❌ Chyba při odesílání.';

