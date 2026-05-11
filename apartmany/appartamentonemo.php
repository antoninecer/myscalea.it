<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../inc/connect.php';
include __DIR__ . '/../header.php';
include __DIR__ . '/../menu.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appartamento Nemo – Guest Guide</title>

    <link rel="stylesheet" href="/styles.css">
    <link rel="stylesheet" href="/apartmany/apartamento-nemo.css">
</head>
<body>

<div class="page-wrap">

    <section class="hero">
        <div class="hero-grid">
            <div class="hero-copy">
                <h1>Welcome to Appartamento Nemo</h1>
                <p>
                    A bright holiday apartment in Scalea with a fully equipped kitchen, two balconies,
                    air conditioning, Wi-Fi, and easy access to the train station and nearby beaches.
                </p>

                <div class="hero-badges">
                    <span class="hero-badge">🌿 Two balconies</span>
                    <span class="hero-badge">❄️ Air conditioning</span>
                    <span class="hero-badge">📶 Wi-Fi</span>
                    <span class="hero-badge">🍽️ Dishwasher</span>
                    <span class="hero-badge">🏖️ Beaches nearby</span>
                </div>

                <div class="hero-actions">
                    <form method="POST" action="/property_calendar.php" target="_blank" class="inline-form">
                        <input type="hidden" name="property_id" value="1">
                        <button type="submit" class="hero-button">📅 View Availability</button>
                    </form>

                    <a class="hero-link" href="https://wa.me/420608193335" target="_blank" rel="noopener noreferrer">
                        📲 Contact on WhatsApp
                    </a>

                    <a class="hero-link" href="https://myscalea.it/map/" target="_blank" rel="noopener noreferrer">
                        🗺️ Open Map
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <img src="nemo1.png" alt="Appartamento Nemo">
            </div>
        </div>
    </section>

    <div class="content-grid">
        <div class="main-column">

            <section class="section-card">
                <h2>Apartment Overview</h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <strong>Apartment:</strong> Unit 11A in the condominium Stella Marina
                    </div>

                    <div class="detail-item">
                        <strong><span class="notranslate" translate="no">CIN</span>:</strong>
                        <a href="Nemo-CIN.pdf" target="_blank" rel="noopener noreferrer">
                            <span class="notranslate" translate="no">IT078138C2O3JWNOGI</span>
                        </a>
                    </div>

                    <div class="detail-item">
                        <strong>Address:</strong> Via Pietro Manchini 20, Scalea —
                        <a href="https://www.google.com/maps/dir/?api=1&amp;destination=Via+Pietro+Manchini+20+Scalea"
                           target="_blank"
                           rel="noopener noreferrer">
                            Navigate to Stella Marina
                        </a>
                    </div>

                    <div class="detail-item">
                        <strong>Wi-Fi:</strong> <em>TelseyW52-2.4G-69B4</em> / Password: <em>NB8FQNKK</em>
                    </div>

                    <div class="detail-item">
                        <strong>Parking:</strong> One shared parking place inside the condominium or free street parking in front of the building.
                    </div>

                    <div class="detail-item">
                        <strong>Linens &amp; Towels:</strong> Fresh sets are available in the closet.
                    </div>
                </div>
            </section>

            <section class="section-card">
                <h2>Amenities &amp; Appliances</h2>
                <p>The apartment is equipped with:</p>
                <ul class="amenities-list">
                    <li>Air conditioning</li>
                    <li>Refrigerator</li>
                    <li>Dishwasher</li>
                    <li>Washing machine</li>
                    <li>Cooktop</li>
                    <li>Microwave oven</li>
                    <li>Internet TV</li>
                    <li>Hair dryer</li>
                    <li>Clothes iron</li>
                    <li>Oscillating fan</li>
                    <li>Small inflatable boat with oars</li>
                    <li>Two balconies</li>
                    <li>Parking</li>
                </ul>
            </section>

            <section class="section-card">
                <h2>Apartment Photos</h2>

                <p>
                    Take a look inside Appartamento Nemo, including the living area, fully equipped kitchen,
                    renovated bathroom, one double bedroom, a small bedroom with bunk beds, and two balconies.
                    Click any photo to open the full gallery.
                </p>

                <div class="photo-grid">
                    <figure class="photo-card landscape" data-index="0">
                        <img src="nemo/IMG_0673.jpeg" alt="Living area and kitchen">
                        <figcaption class="photo-caption">Living area and kitchen</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="7">
                        <img src="nemo/IMG_0674.jpeg" alt="Kitchen with dishwasher and microwave">
                        <figcaption class="photo-caption">Kitchen with dishwasher and microwave</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="5">
                        <img src="nemo/IMG_0667.jpeg" alt="Main balcony">
                        <figcaption class="photo-caption">Main balcony</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="6">
                        <img src="nemo/IMG_0672.jpeg" alt="Second balcony">
                        <figcaption class="photo-caption">Second balcony</figcaption>
                    </figure>

                    <figure class="photo-card portrait" data-index="1">
                        <img src="nemo/Loznice3.jpeg" alt="Double bedroom">
                        <figcaption class="photo-caption">Double bedroom</figcaption>
                    </figure>

                    <figure class="photo-card portrait" data-index="2">
                        <img src="nemo/IMG_0663.jpeg" alt="Bathroom">
                        <figcaption class="photo-caption">Bathroom</figcaption>
                    </figure>

                    <figure class="photo-card portrait" data-index="3">
                        <img src="nemo/IMG_0670.jpeg" alt="Small bedroom with bunk beds">
                        <figcaption class="photo-caption">Small bedroom with bunk beds</figcaption>
                    </figure>

                    <figure class="photo-card portrait" data-index="4">
                        <img src="nemo/Pokojik1.jpeg" alt="Bunk bed room">
                        <figcaption class="photo-caption">Bunk bed room</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="8">
                        <img src="nemo/Kuchynzpredsine.jpeg" alt="Kitchen with hot top and refrigerator">
                        <figcaption class="photo-caption">Kitchen with gas hot top and refrigerator</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="9">
                        <img src="nemo/IMG_0676.jpeg" alt="Kitchen 1 with hot top and refrigerator">
                        <figcaption class="photo-caption">Kitchen 1 with gas hot top and refrigerator</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="10">
                        <img src="nemo/mapa.jpeg" alt="Map of appartamento in Scalea">
                        <figcaption class="photo-caption">Map of appartamento in Scalea</figcaption>
                    </figure>

                    <figure class="photo-card landscape" data-index="11">
                        <img src="nemo/mapaitaly.jpeg" alt="Where Scale is located in Italy">
                        <figcaption class="photo-caption">Map of Scalea in Italy</figcaption>
                    </figure>
                </div>
            </section>

            <section class="section-card">
                <h2>Discover Scalea on Video</h2>
                <p>Watch a short playlist with videos from Scalea, its beaches, and the surrounding area.</p>

                <div class="video-wrap">
                    <iframe
                        src="https://www.youtube.com/embed/videoseries?list=PL2Nr2YaUS6x3_K7mYp2zDCh8s80hPUsG7"
                        title="Scalea video playlist"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                    </iframe>
                </div>
            </section>

        </div>

        <aside class="side-column">
            <div class="side-stack">

                <section class="mini-card">
                    <h3>Check-In &amp; Check-Out</h3>
                    <ul class="plain-list">
                        <li><strong>Check-In:</strong> from 15:00</li>
                        <li><strong>Check-Out:</strong> by 10:00</li>
                        <li>If you need flexibility, please send us a WhatsApp message.</li>
                    </ul>
                </section>

                <section class="mini-card">
                    <h3>Electrical Capacity</h3>
                    <p>Please note that the apartment has limited electrical capacity.</p>

                    <div class="note">
                        If a temporary power cut occurs, please use fewer high-power appliances at the same time.
                        A simple rule is to switch on one less high-power appliance than were being used when the outage happened.
                        The water heater and air conditioning are controlled by switches in the bathroom, located below the water heater.
                        Please do not use electric kettles, as they can easily cause the instant power load to be exceeded.
                    </div>

                    <div class="card-list card-list-spaced">
                        <a class="panel-link-card" href="Nemo-DICO.pdf" target="_blank" rel="noopener noreferrer">
                            <div class="panel-link-icon">📄</div>
                            <div class="panel-link-body">
                                <span class="panel-link-label">Technical document</span>
                                <div class="panel-link-title">
                                    <span class="notranslate" translate="no">DICHIARAZIONE DI CONFORMITÀ (DICO)</span>
                                </div>
                                <div class="panel-link-subtext">Electrical conformity declaration (PDF)</div>
                            </div>
                        </a>
                    </div>
                </section>

                <section class="mini-card">
                    <h3>House Rules</h3>
                    <ul class="plain-list">
                        <li><strong>No smoking</strong> inside.</li>
                        <li>Smoking is permitted only on the large balcony accessible from the small bedroom.</li>
                        <li><strong>Quiet hours:</strong> 22:00 – 07:00.</li>
                        <li><strong>No pets</strong> allowed.</li>
                        <li>Please wash used dishes and take out the trash before leaving.</li>
                        <li><strong>Waste bins:</strong> Three separate bins are provided for sorting.</li>
                    </ul>
                </section>

                <section class="mini-card">
                    <h3>Safety &amp; Emergencies</h3>
                    <ul class="plain-list">
                        <li><strong>Fire extinguisher:</strong> Mounted at the apartment entrance.</li>
                        <li><strong>First-aid kit:</strong> In the closet by the entrance.</li>
                        <li><strong>112</strong> – All emergency services</li>
                        <li><strong>113</strong> – Police</li>
                        <li><strong>115</strong> – Fire Brigade</li>
                        <li><strong>118</strong> – Medical Ambulance</li>
                    </ul>
                </section>

                <section class="mini-card">
                    <h3>Local Recommendations</h3>
                    <p>
                        Explore our interactive MyScalea map with restaurants, shops, pharmacies, beaches,
                        and other useful points of interest in Scalea and the surrounding area.
                    </p>
                    <p>
                        If you notice an incorrect or no longer existing place, or if you discover a useful new one,
                        please let us know so we can keep the map updated.
                    </p>

                    <div class="card-list">
                        <a class="panel-link-card" href="https://myscalea.it/map/" target="_blank" rel="noopener noreferrer">
                            <div class="panel-link-icon">🗺️</div>
                            <div class="panel-link-body">
                                <span class="panel-link-label">Interactive map</span>
                                <div class="panel-link-title">Open the MyScalea map</div>
                                <div class="panel-link-subtext">Restaurants, beaches, pharmacies, shops and local points of interest</div>
                            </div>
                        </a>
                    </div>
                </section>

                <section class="mini-card">
                    <h3>Basic Navigation</h3>
                    <p>
                        The interactive route map shows the train station and the main walking routes to nearby beaches.
                    </p>

                    <div class="card-list">
                        <a class="panel-link-card" href="https://earth.google.com/web/data=MkEKPwo9CiExWjNWcVpsa3ExeVJiN3NjeFZrTUItdWg2cHlRSGdxeU8SFgoUMEI1NDJDOUEzMzM4MzUzNkVEMDQgAUICCABKCAjjuJulBxAB"
                           target="_blank"
                           rel="noopener noreferrer">
                            <div class="panel-link-icon">📍</div>
                            <div class="panel-link-body">
                                <span class="panel-link-label">Route map</span>
                                <div class="panel-link-title">Open the interactive route map</div>
                                <div class="panel-link-subtext">Train station and walking routes to nearby beaches</div>
                            </div>
                        </a>
                    </div>

                    <ul class="route-list route-list-spaced">
                        <li><strong>Yellow route:</strong> train station — approximately <strong>365 m</strong></li>
                        <li><strong>Red route:</strong> Lido il Corallo via Interspar — just under <strong>1,200 m</strong></li>
                        <li><strong>Purple route:</strong> Lido il Tramonto — just under <strong>1 km</strong></li>
                    </ul>

                    <p class="paragraph-spaced">
                        On the red route to Lido il Corallo, you can stop at Interspar to buy drinks, snacks,
                        and other beach essentials. Depending on the season, there may also be small stalls
                        along the way selling beach items and inflatables.
                    </p>
                </section>

                <section class="mini-card">
                    <h3>Contact</h3>

                    <div class="card-list">
                        <a class="panel-link-card" href="https://wa.me/420608193335" target="_blank" rel="noopener noreferrer">
                            <div class="panel-link-icon">📲</div>
                            <div class="panel-link-body">
                                <span class="panel-link-label">WhatsApp</span>
                                <div class="panel-link-title">Antonín Ečer</div>
                                <div class="panel-link-subtext">Owner of the apartment</div>
                            </div>
                        </a>

                        <a class="panel-link-card" href="mailto:antoninecer@gmail.com">
                            <div class="panel-link-icon">✉️</div>
                            <div class="panel-link-body">
                                <span class="panel-link-label">Email</span>
                                <div class="panel-link-title">antoninecer@gmail.com</div>
                                <div class="panel-link-subtext">For questions before or during your stay</div>
                            </div>
                        </a>
                    </div>
                </section>
            </div>
        </aside>
    </div>
</div>

<div class="lightbox" id="lightbox" aria-hidden="true">
    <button class="lightbox-close" id="lightboxClose" aria-label="Close gallery">&times;</button>
    <button class="lightbox-prev" id="lightboxPrev" aria-label="Previous photo">&#10094;</button>

    <div class="lightbox-inner">
        <img id="lightboxImg" src="" alt="">
    </div>

    <button class="lightbox-next" id="lightboxNext" aria-label="Next photo">&#10095;</button>
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = Array.from(document.querySelectorAll('.photo-card'));
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');

    if (!cards.length || !lightbox || !lightboxImg || !lightboxCaption || !lightboxClose || !lightboxPrev || !lightboxNext) {
        return;
    }

    const gallery = cards.map((card) => {
        const img = card.querySelector('img');

        return {
            src: img ? img.getAttribute('src') : '',
            alt: img ? (img.getAttribute('alt') || '') : ''
        };
    }).filter((item) => item.src);

    let currentIndex = 0;

    function renderImage(index) {
        currentIndex = index;
        lightboxImg.src = gallery[index].src;
        lightboxImg.alt = gallery[index].alt;
        lightboxCaption.textContent = gallery[index].alt;
    }

    function openLightbox(index) {
        renderImage(index);
        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxImg.src = '';
        lightboxImg.alt = '';
        document.body.style.overflow = '';
    }

    function showNext() {
        const nextIndex = (currentIndex + 1) % gallery.length;
        renderImage(nextIndex);
    }

    function showPrev() {
        const prevIndex = (currentIndex - 1 + gallery.length) % gallery.length;
        renderImage(prevIndex);
    }

    cards.forEach((card, index) => {
        card.addEventListener('click', function () {
            openLightbox(index);
        });
    });

    lightboxClose.addEventListener('click', closeLightbox);
    lightboxNext.addEventListener('click', showNext);
    lightboxPrev.addEventListener('click', showPrev);

    lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!lightbox.classList.contains('active')) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowRight') {
            showNext();
        } else if (event.key === 'ArrowLeft') {
            showPrev();
        }
    });
});
</script>
</body>
</html>