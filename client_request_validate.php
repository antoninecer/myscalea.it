<?php
function validateClientRequest($name, $email, $phone, $password, $requirements) {
    $errors = [];

    if (!$name) {
        $errors[] = "❌ Jméno je povinné.";
    }

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "❌ Zadejte platný e-mail.";
    }
    // --- begin phone validation ---
    if (!$phone) {
       $errors[] = "❌ Telefonní číslo je povinné.";
    } elseif (!preg_match('/^\+?[0-9\s\-\(\)]{7,}$/', $phone)) {
       $errors[] = "❌ Zadejte platné telefonní číslo (pouze čísla, mezery, +, -, ()).";
    }
    // --- end phone validation ---
    if (!$password || strlen($password) < 6) {
        $errors[] = "❌ Heslo musí mít alespoň 6 znaků.";
    }

    if (!$requirements) {
        $errors[] = "❌ Neplatný nebo chybějící požadavek.";
    } else {
        $decoded = json_decode($requirements, true);
        if (!is_array($decoded) || !isset($decoded['area'], $decoded['budget'])) {
            $errors[] = "❌ Požadavek je neúplný nebo neplatný.";
        }
    }

    return $errors;
}
