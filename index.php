<?php require_once 'config/database.php';require_once 'includes/functions.php';$base='';$active='home';$pageTitle='PinkFlow — Make Every Moment Bloom';$res=$conn->query("SELECT id,name,category,price,image FROM products WHERE is_active=1 ORDER BY created_at DESC LIMIT 4");$products=$res->fetch_all(MYSQLI_ASSOC);$pageCss='beranda.css';include 'includes/header.php';?>
<main><section class="hero"><div class="hero-text"><span class="eyebrow">THE ART OF GIVING FLOWERS</span><h1>Make every<br><em>moment bloom.</em></h1><p>Rangkaian bunga dan hadiah penuh cerita, dirancang untuk membuat momen sederhana terasa istimewa.</p><a class="btn dark" href="pages/katalog.php">Jelajahi koleksi ↗</a><a class="simple-link" href="pages/custom.php">Rancang buketmu sendiri</a><div class="hero-foot">✳ &nbsp; Made with love, arranged for you.</div></div><div class="hero-art"><img src="https://images.unsplash.com/photo-1520763185298-1b434c919102?auto=format&fit=crop&w=1100&q=85" alt="Buket bunga PinkFlow"><div class="sticker">FLOWERS<br>FOR YOU<br>♡</div></div></section>
<div class="marquee">FLOWERS THAT SPEAK　 ✳　 GIFTS THAT MATTER　 ✳　 MADE FOR YOUR MOMENTS　 ✳</div>
<section class="section"><div class="section-title"><div><span class="eyebrow">CURATED WITH LOVE</span><h2>Our lovely <em>selection</em></h2></div><a class="simple-link" href="pages/katalog.php">Lihat semua ↗</a></div><div class="products"><?php foreach($products as $i=>$p):?><article class="product reveal"><a class="product-img" href="pages/detail.php?id=<?=$p['id']?>"><img src="<?=e(product_image_url($p['image'], $p['name'], ''))?>" alt="<?=e($p['name'])?>" onerror="this.src='https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=700&q=80'"></a><small><?=e($p['category'])?> <span><?=rp($p['price'])?></span></small><h3><a href="pages/detail.php?id=<?=$p['id']?>"><?=e($p['name'])?></a></h3></article><?php endforeach;?></div><?php if(!$products):?><p class="empty">Produk pilihan akan segera hadir.</p><?php endif;?></section>
<section class="section home-location">
    <div class="home-location-copy">
        <span class="eyebrow">COME AND VISIT US</span>
        <h2>Find your way<br><em>to PinkFlow.</em></h2>
        <p>Kunjungi toko kami dan temukan rangkaian favoritmu secara langsung.</p>
        <a class="btn dark" href="pages/lokasi.php">Lihat lokasi toko ↗</a>
    </div>
    <div class="home-location-map">
        <iframe
            title="Peta lokasi PinkFlow"
            src="https://maps.google.com/maps?q=-2.5782300,121.0527488&z=15&output=embed"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen>
        </iframe>
    </div>
</section>
</main><?php include 'includes/footer.php';?>
