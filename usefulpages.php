<?php
session_start();
require_once __DIR__ . '/inc/connect.php';
include 'header.php';
include 'menu.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Useful Web Pages</title>
    <link rel="stylesheet" href="/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        section {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            color: #2c3e50;
        }
        h2 {
            margin-top: 40px;
            font-size: 1.8em;
            color: #34495e;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .link-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        .link-card a {
            text-decoration: none;
            color: #34495e;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .link-card .icon {
            font-size: 1.5em;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<section>
    <h1>Useful Web Pages</h1>

    <h2>Entertainment</h2>
    <div class="links-grid">
        <div class="link-card">
            <a href="https://radio.garden" target="_blank">
                <span class="icon">📻</span>
                Radio Stations
            </a>
        </div>
        <div class="link-card">
            <a href="https://tv.garden" target="_blank">
                <span class="icon">📺</span>
                TV Stations
            </a>
        </div>
    </div>

    <h2>Information</h2>
    <div class="links-grid">
        <div class="link-card">
            <a href="https://www.paginegialle.it/calabria/scalea.htm" target="_blank">
                <span class="icon">ℹ️</span>
                Yellow Pages Scalea
            </a>
        </div>
    </div>

    <h2>Internet connection</h2>
    <div class="links-grid">
        <div class="link-card">
            <a href="https://tariffe.segugio.it/tariffe-adsl-internet/ricerca-offerte-adsl-internet.aspx" target="_blank">
                <span class="icon">📶</span>
                Internet Tariffe in your location
            </a>
        </div>
    </div>

    <h2>Travel</h2>
    <div class="links-grid">
        <div class="link-card">
            <a href="https://www.kiwi.com/" target="_blank">
                <span class="icon">✈️</span>
                Kiwi Air Travel
            </a>
        </div>
        <div class="link-card">
            <a href="https://www.rome2rio.com/" target="_blank">
                <span class="icon">🌐</span>
                Travel How-To
            </a>
        </div>
    </div>

    <h2>Furniture Shopping</h2>
    <div class="links-grid">
        <div class="link-card">
            <a href="https://www.mondoconv.it/" target="_blank">
                <span class="icon">🛋️</span>
                Mondo Convenienza
            </a>
        </div>
        <div class="link-card">
            <a href="https://www.conforama.it/" target="_blank">
                <span class="icon">🛒</span>
                Conforama
            </a>
        </div>
    </div>

    <h2>Online order tips</h2>
    <div class="links-grid">
        <div class="link-card">
            <a href="onlineorder.html" target="_blank">
                <span class="icon">🛋️</span>
                text oabout online orders
            </a>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>
</body>
</html>
