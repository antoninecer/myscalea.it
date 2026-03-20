<?php
session_start();
// connect.php lives in /inc at web-root
require_once __DIR__ . '/../inc/connect.php';
// header/menu/footer also one level up
include __DIR__ . '/../header.php';
include __DIR__ . '/../menu.php';
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Appartamento Nemo – Guest Guide</title>
    <link rel='stylesheet' href='/styles.css'>
    <style>
        /* Full-page fixed background via a separate fixed div for mobile support */
        .bg-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('nemo21.png') no-repeat center center;
            background-size: contain;
            z-index: -1;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #333;
            /* Remove body background to avoid mobile issues */
        }
        /* Content container with semi-transparent backdrop */
        section {
            max-width: 800px;
            margin: 80px auto 40px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 0.5em;
        }
        h2 {
            margin-top: 1.5em;
            font-size: 1.8em;
            color: #34495e;
            border-bottom: 2px solid #ddd;
            padding-bottom: 0.3em;
        }
        p, ul {
            line-height: 1.6;
        }
        ul { margin-left: 1.2em; }
        .contact-list { list-style: none; padding: 0; }
        .contact-list li { margin: 0.5em 0; font-size: 1.1em; }
        .contact-list .icon { font-size: 1.3em; margin-right: 8px; vertical-align: middle; }
    </style>
</head>
<body>

<!-- Fixed background div -->
<div class='bg-fixed'></div>

<section>
  <h1>Welcome to Appartamento Nemo</h1>
  <p>We’re delighted to have you here!</p>

  <h2>1. Apartment Details</h2>
  <ul>
    <li><strong>Apartment:</strong> Unit 11A in the condominium Stella Marina (Italian for 'Sea Star')</li>
    <li><strong>Address:</strong> Via Pietro Manchini 20, Scalea — <a href='https://www.google.com/maps/dir/?api=1&destination=Via+Pietro+Manchini+20+Scalea' target='_blank' rel='noopener'>Navigate to Stella Marina</a></li>
    <li><strong>Wi-Fi:</strong> <em>GuestNemo2025</em> / Password: <em>ocean2025</em></li>
    <li><strong>Parking:</strong> Free street parking in front, no permit needed.</li>
    <li><strong>Linens & Towels:</strong> Fresh sets available in the closet.</li>
  </ul>

  <h2>2. Check-In & Check-Out</h2>
  <ul>
    <li><strong>Check-In:</strong> from 15:00</li>
    <li><strong>Check-Out:</strong> by 10:00</li>
    <li>If you need flexibility, just send us a WhatsApp message.</li>
  </ul>

  <h2>3. House Rules</h2>
  <ul>
    <li><strong>No smoking</strong> inside. Smoking only permitted on the large balcony accessible from the small bedroom.</li>
    <li><strong>Quiet hours:</strong> 22:00 – 07:00. Please respect neighbours.</li>
    <li><strong>No pets</strong> allowed (thank you for understanding).</li>
    <li>Please wash used dishes and take out the trash before leaving.</li>
    <li><strong>Waste bins:</strong> Three separate bins provided for sorting.</li>
  </ul>

  <h2>4. Safety & Emergencies</h2>
  <ul>
    <li><strong>Fire extinguisher:</strong> Mounted at the apartment entrance.</li>
    <li><strong>First-aid kit:</strong> In the closet by the entrance.</li>
    <li><strong>Emergency numbers:</strong>
      <ul>
        <li>112 – All emergency services</li>
        <li>113 – Police (Polizia di Stato)</li>
        <li>115 – Fire Brigade (Vigili del Fuoco)</li>
        <li>118 – Medical Ambulance (Emergenza Sanitaria)</li>
      </ul>
    </li>
  </ul>

  <h2>5. Interactive Map & Points of Interest</h2>
  <p>I have prepared a web map highlighting restaurants, shopping centers, pharmacies, and more. Click the 'Map' link in the main menu to explore and navigate directly to any location.</p>

  <h2>6. Local Recommendations</h2>
  <p>Please choose from the points of interest directly on the map.</p>

  <h2>7. Contact</h2>
  <ul class='contact-list'>
    <li><a href='https://wa.me/420608193335' target='_blank' rel='noopener'><span class='icon'>📲</span> Owner: Antonín Ečer</a><br><small>(Owner of this modest abode)</small></li>
    <li><span class='icon'>✉️</span><a href='mailto:antoninecer@gmail.com'>antoninecer@gmail.com</a></li>
  </ul>

  <h2>8. How to get to us</h2>
  <ul class='contact-list'>
    <li><a href='https://earth.google.com/web/data=MkEKPwo9CiExWjNWcVpsa3ExeVJiN3NjeFZrTUItdWg2cHlRSGdxeU8SFgoUMEI1NDJDOUEzMzM4MzUzNkVEMDQgAUICCABKCAjjuJulBxAB' target='_blank' rel='noopener'><span class='icon'>📲</span> Walking route between the apartment and the train station</a><br><small>(Walking route between the apartment and the train station)</small></li>
  </ul>
</section>
<form method="POST" action="/property_calendar.php" target="_blank" style="margin:0;">
      <input type="hidden" name="property_id" value="1">
      <button type="submit" class="cta-button">📅 View Availability</button>
    </form>

<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
