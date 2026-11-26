<?php
$base = '../';
$pageCss = 'lokasi.css';
$pageTitle = 'Lokasi Toko | PinkFlow';
include '../includes/header.php';
?>

<main>
    <section class="pagehero">
        <span class="eyebrow">COME SAY HELLO</span>
        <h1>Find <em>PinkFlow.</em></h1>
        <p>Kunjungi lokasi toko melalui petunjuk Google Maps.</p>
    </section>

    <section class="section location">
        <div class="location-copy">
            <h2>We are here<br>for your lovely<br>moments.</h2>
            <p>Temukan toko PinkFlow dan kunjungi kami untuk melihat rangkaian bunga secara langsung.</p>
            <a
                class="btn dark"
                target="_blank"
                rel="noopener"
                href="https://maps.app.goo.gl/9Z9akDoJ4rMbf9Gq5?g_st=ic">
                Buka Google Maps ↗
            </a>
        </div>

        <div class="mapbox">
            <iframe
                title="Peta lokasi PinkFlow"
                src="https://maps.google.com/maps?q=-2.5782300,121.0527488&z=15&output=embed"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen>
            </iframe>
            <div class="map-caption">
                <span class="map-flower">✿</span>
                <strong>PinkFlow Store</strong>
                <a
                    target="_blank"
                    rel="noopener"
                    href="https://maps.app.goo.gl/9Z9akDoJ4rMbf9Gq5?g_st=ic">
                    Lihat petunjuk arah ↗
                </a>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
