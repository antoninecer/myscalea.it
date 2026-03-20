<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>QR Kód generátor</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; }
    label { display: block; margin-top: 1rem; }
    input[type="text"], select, input[type="color"] {
      width: 100%; padding: 0.5rem; margin-top: 0.2rem;
    }
    #preview { margin-top: 2rem; text-align: center; }
    button { padding: 0.5rem 1rem; margin-top: 1rem; }
  </style>
</head>
<body>
  <h1>QR Generátor s logem</h1>

  <form id="qrForm" enctype="multipart/form-data" method="POST" action="generate.php" target="previewFrame">
    <label>Text nebo URL
      <input type="text" name="data" required value="https://myscalea.it/">
    </label>

    <label>Velikost (px)
      <input type="number" name="size" value="300" min="100" max="1000">
    </label>

    <label>Barva QR kódu
      <input type="color" name="fg" value="#000000">
    </label>

    <label>Barva pozadí
      <input type="color" name="bg" value="#ffffff">
    </label>

    <label>Logo (PNG/JPG)
      <input type="file" name="logo" accept="image/png,image/jpeg">
    </label>

    <button type="submit">Vygeneruj QR</button>
  </form>

  <div id="preview">
    <h2>Náhled</h2>
    <iframe name="previewFrame" id="previewFrame" style="width:100%;height:340px;border:none;"></iframe>
    <br>
    <a id="downloadLink" href="#" download="qr.png"><button>📥 Stáhnout jako PNG</button></a>
  </div>

  <script>
    const form = document.getElementById('qrForm');
    const frame = document.getElementById('previewFrame');
    const downloadLink = document.getElementById('downloadLink');

    form.addEventListener('submit', () => {
      setTimeout(() => {
        downloadLink.href = frame.src;
      }, 500); // malá prodleva pro zajištění URL
    });
  </script>
</body>
</html>

