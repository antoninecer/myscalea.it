<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 2;

include __DIR__ . '/../header.php';
include __DIR__ . '/../menu.php';
?>

<link rel="stylesheet" href="/apartmany/scalea-bella/scalea-bella.css">

<header class="main-header">
  <h1>Scalea Bella Apartamento</h1>
  <p>Holiday apartment in Parco Teresa, Via Luigi Einaudi, Scalea</p>
</header>

<main class="scalea-bella-wrap">

  <section class="scalea-bella-hero">
    <div class="scalea-bella-hero-grid">
      <div class="scalea-bella-copy">
        <div class="scalea-bella-eyebrow">🌊 Scalea · Parco Teresa</div>

        <h1>Scalea Bella Apartamento</h1>

        <p class="scalea-bella-lead">
          Comfortable holiday apartment in Scalea, located in the Parco Teresa condominium
          near Via Luigi Einaudi. A practical base for a seaside stay in Calabria, with
          a kitchen, bathroom, air conditioning and balcony views into the condominium area.
        </p>

        <div class="scalea-bella-badges">
          <span class="scalea-bella-badge">📍 Via Luigi Einaudi</span>
          <span class="scalea-bella-badge">🏢 Parco Teresa</span>
          <span class="scalea-bella-badge">❄️ Air conditioning</span>
          <span class="scalea-bella-badge">🍳 Kitchen</span>
          <span class="scalea-bella-badge">🛁 Bathroom</span>
          <span class="scalea-bella-badge">🌿 Balcony</span>
        </div>

        <div class="scalea-bella-actions">
          <?php if ($propertyId > 0): ?>
            <form method="POST" action="/property_calendar.php" target="_blank" class="inline-form">
              <input type="hidden" name="property_id" value="<?= htmlspecialchars((string)$propertyId) ?>">
              <button type="submit" class="scalea-bella-button">📅 View availability</button>
            </form>
          <?php else: ?>
            <a class="scalea-bella-button" href="#booking-note">📅 Availability coming soon</a>
          <?php endif; ?>

          <a class="scalea-bella-link" href="https://wa.me/420737542981" target="_blank" rel="noopener noreferrer">
            📲 WhatsApp
          </a>

          <a class="scalea-bella-link"
             href="https://www.google.com/maps?q=39.810824664716456,15.797286677844394"
             target="_blank"
             rel="noopener noreferrer">
            🗺️ Open location
          </a>
        </div>
      </div>

      <div class="scalea-bella-visual">
        <img src="/apartmany/scalea-bella/images/09.jpg" alt="Scalea Bella Apartamento living area and dining table">
        <div class="scalea-bella-visual-caption">
          Simple, practical apartment for a relaxed stay in Scalea.
        </div>
      </div>
    </div>
  </section>

  <div class="scalea-bella-grid">
    <div>
      <section class="scalea-bella-card">
        <h2>Apartment overview</h2>
        <p>
          Scalea Bella Apartamento is prepared as a straightforward holiday apartment for guests
          who want to stay in Scalea and enjoy the town, beaches and surrounding Calabria.
          More details will be added after final confirmation from the owner.
        </p>

        <div class="scalea-bella-facts">
          <div class="scalea-bella-fact"><strong>Name:</strong> Scalea Bella Apartamento</div>
          <div class="scalea-bella-fact"><strong>Area:</strong> Parco Teresa, Via Luigi Einaudi, Scalea, 87029</div>
          <div class="scalea-bella-fact"><strong>GPS:</strong> 39.810824664716456, 15.797286677844394</div>
          <div class="scalea-bella-fact"><strong>Pricing:</strong> seasonal prices based on the current Scalea apartment rental market.</div>
        </div>
      </section>

      <section class="scalea-bella-card">
        <h2>Photos</h2>
        <p>
          Click any photo to open the full gallery.
        </p>

        <div class="scalea-bella-photo-grid">
          <figure class="scalea-bella-photo" data-index="0" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/01.jpg" alt="Parco Teresa condominium exterior">
            <figcaption>Parco Teresa condominium</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="1" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/02.jpg" alt="Via 25 Aprile street sign near the apartment">
            <figcaption>Nearby street</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="2" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/09.jpg" alt="Living and dining area">
            <figcaption>Living and dining area</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="3" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/10.jpg" alt="Kitchen and dining area">
            <figcaption>Kitchen and dining area</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="4" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/11.jpg" alt="Bedroom">
            <figcaption>Bedroom</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="5" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/12.jpg" alt="Bedroom detail">
            <figcaption>Bedroom detail</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="6" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/03.jpg" alt="Bathroom">
            <figcaption>Bathroom</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="7" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/04.jpg" alt="Bathroom with washing machine">
            <figcaption>Bathroom</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="8" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/07.jpg" alt="Condominium courtyard view">
            <figcaption>Courtyard view</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="9" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/08.jpg" alt="View from balcony">
            <figcaption>Balcony view</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="10" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/05.jpg" alt="Kitchen">
            <figcaption>Kitchen</figcaption>
          </figure>

          <figure class="scalea-bella-photo" data-index="11" tabindex="0" role="button">
            <img src="/apartmany/scalea-bella/images/06.jpg" alt="Small room with sofa bed">
            <figcaption>Additional sleeping area</figcaption>
          </figure>
        </div>
      </section>
    </div>

    <aside>
      <section class="scalea-bella-card">
        <h2>Contact</h2>
        <div class="scalea-bella-facts">
          <div class="scalea-bella-fact"><strong>Phone:</strong> <a href="tel:+420737542981">+420 737 542 981</a></div>
          <div class="scalea-bella-fact"><strong>Email:</strong> <a href="mailto:cimitachal@gmail.com">cimitachal@gmail.com</a></div>
          <div class="scalea-bella-fact"><strong>Facebook:</strong> Scalea Bella Apartamento</div>
        </div>
      </section>

      <section class="scalea-bella-card" id="booking-note">
        <h2>Availability and prices</h2>
        <p>
          Prices are seasonal and follow the current apartment rental market in Scalea. The final amount is calculated day by day according to the selected dates and number of guests.
        </p>
        <?php if ($propertyId > 0): ?>
          <form method="POST" action="/property_calendar.php" target="_blank" class="inline-form">
            <input type="hidden" name="property_id" value="<?= htmlspecialchars((string)$propertyId) ?>">
            <button type="submit" class="scalea-bella-button scalea-bella-button-full">📅 View availability</button>
          </form>
        <?php else: ?>
          <div class="scalea-bella-note">
            The availability button will be activated after the property is created in the database
            and connected to its Google Calendar.
          </div>
        <?php endif; ?>
      </section>

      <section class="scalea-bella-card">
        <h2>Location</h2>
        <p>
          Parco Teresa, Via Luigi Einaudi, Scalea, Italy, 87029.
        </p>
        <a class="scalea-bella-link"
           href="https://www.google.com/maps?q=39.810824664716456,15.797286677844394"
           target="_blank"
           rel="noopener noreferrer">
          Open in Google Maps
        </a>
      </section>
    </aside>
  </div>

</main>


<div class="scalea-bella-lightbox" id="scaleaBellaLightbox" aria-hidden="true">
  <button class="scalea-bella-lightbox-close" id="scaleaBellaLightboxClose" aria-label="Close gallery">&times;</button>
  <button class="scalea-bella-lightbox-prev" id="scaleaBellaLightboxPrev" aria-label="Previous photo">&#10094;</button>

  <div class="scalea-bella-lightbox-inner">
    <img id="scaleaBellaLightboxImg" src="" alt="">
  </div>

  <button class="scalea-bella-lightbox-next" id="scaleaBellaLightboxNext" aria-label="Next photo">&#10095;</button>
  <div class="scalea-bella-lightbox-caption" id="scaleaBellaLightboxCaption"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const cards = Array.from(document.querySelectorAll('.scalea-bella-photo'));
  const lightbox = document.getElementById('scaleaBellaLightbox');
  const lightboxImg = document.getElementById('scaleaBellaLightboxImg');
  const lightboxCaption = document.getElementById('scaleaBellaLightboxCaption');
  const closeBtn = document.getElementById('scaleaBellaLightboxClose');
  const prevBtn = document.getElementById('scaleaBellaLightboxPrev');
  const nextBtn = document.getElementById('scaleaBellaLightboxNext');

  if (!cards.length || !lightbox || !lightboxImg || !lightboxCaption || !closeBtn || !prevBtn || !nextBtn) {
    return;
  }

  const gallery = cards.map(function (card) {
    const img = card.querySelector('img');
    const caption = card.querySelector('figcaption');

    return {
      src: img ? img.getAttribute('src') : '',
      alt: img ? (img.getAttribute('alt') || '') : '',
      caption: caption ? caption.textContent.trim() : ''
    };
  }).filter(function (item) {
    return item.src;
  });

  let currentIndex = 0;

  function showImage(index) {
    if (!gallery.length) return;

    if (index < 0) {
      currentIndex = gallery.length - 1;
    } else if (index >= gallery.length) {
      currentIndex = 0;
    } else {
      currentIndex = index;
    }

    const item = gallery[currentIndex];
    lightboxImg.src = item.src;
    lightboxImg.alt = item.alt;
    lightboxCaption.textContent = item.caption || item.alt || '';
  }

  function openLightbox(index) {
    showImage(index);
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('scalea-bella-lightbox-open');
  }

  function closeLightbox() {
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('scalea-bella-lightbox-open');
    lightboxImg.src = '';
  }

  cards.forEach(function (card, index) {
    card.addEventListener('click', function () {
      openLightbox(index);
    });

    card.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openLightbox(index);
      }
    });
  });

  closeBtn.addEventListener('click', closeLightbox);
  prevBtn.addEventListener('click', function () { showImage(currentIndex - 1); });
  nextBtn.addEventListener('click', function () { showImage(currentIndex + 1); });

  lightbox.addEventListener('click', function (event) {
    if (event.target === lightbox) {
      closeLightbox();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (!lightbox.classList.contains('is-open')) return;

    if (event.key === 'Escape') {
      closeLightbox();
    } else if (event.key === 'ArrowLeft') {
      showImage(currentIndex - 1);
    } else if (event.key === 'ArrowRight') {
      showImage(currentIndex + 1);
    }
  });
});
</script>

<?php include __DIR__ . '/../footer.php'; ?>
