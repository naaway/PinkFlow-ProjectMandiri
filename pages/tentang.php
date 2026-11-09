
<?php
$basePath = "../";
$isHome = false;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tentang Kami | PinkFlow</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* ABOUT PAGE */
        .about-page {
            background: #fff9fa;
            min-height: 70vh;
        }

        .about-intro {
            padding: 80px 20px 45px;
            text-align: center;
        }

        .about-intro .eyebrow {
            margin-bottom: 14px;
        }

        .about-intro h1 {
            margin: 0 0 18px;
            font-family: Georgia, serif;
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            font-weight: 500;
            line-height: 1.2;
            color: #49343e;
        }

        .about-intro h1 span {
            color: #c45d80;
        }

        .about-intro p {
            max-width: 560px;
            margin: 0 auto;
            color: #806d76;
            line-height: 1.8;
        }

        .about-story {
            padding: 35px 20px 90px;
            background: #fff9fa;
        }

        .about-story-inner {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            align-items: center;
            gap: 65px;
        }

        .about-photo {
            height: 390px;
            overflow: hidden;
            border-radius: 180px 180px 18px 18px;
            background: #fce7ed;
            box-shadow: 0 8px 24px rgba(95, 47, 65, 0.08);
        }

        .about-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-story-text .eyebrow {
            margin-bottom: 12px;
        }

        .about-story-text h2 {
            margin: 0 0 20px;
            font-family: Georgia, serif;
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 500;
            line-height: 1.3;
            color: #49343e;
        }

        .about-story-text p {
            margin: 0 0 16px;
            color: #806d76;
            line-height: 1.9;
        }

        .about-link {
            display: inline-flex;
            margin-top: 12px;
            padding: 12px 22px;
            color: #ffffff;
            background: #c45d80;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .about-link:hover {
            background: #ad496c;
            transform: translateY(-2px);
        }

        @media (max-width: 700px) {
            .about-intro {
                padding: 55px 20px 30px;
            }

            .about-story {
                padding: 25px 20px 60px;
            }

            .about-story-inner {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .about-photo {
                height: 320px;
                max-width: 360px;
                width: 100%;
                margin: 0 auto;
            }

            .about-story-text {
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <?php require_once "../includes/navbar.php"; ?>

    <main class="about-page">

        <section class="about-intro">

            <p class="eyebrow">ABOUT PINKFLOW</p>

            <h1>
                Cerita di Balik
                <span>PinkFlow.</span>
            </h1>

            <p>
                Setiap buket memiliki cerita, dan setiap hadiah
                menyimpan makna. PinkFlow hadir untuk membantu
                kamu menyampaikan perasaan melalui hadiah yang
                sederhana namun berkesan.
            </p>

        </section>

        <section class="about-story">

            <div class="about-story-inner">

                <div class="about-photo">

                    <img
                        src="../assets/images/hero-bouquet.jpg"
                        alt="Buket bunga PinkFlow"
                    >
                </div>

                <div class="about-story-text">

                    <p class="eyebrow">OUR STORY</p>

                    <h2>
                        Berawal dari sebuah hobi.
                    </h2>

                    <p>
                        PinkFlow berawal dari kecintaan terhadap
                        bunga dan keinginan untuk menghadirkan
                        hadiah yang indah bagi setiap momen.
                    </p>

                    <p>
                        Sejak 27 Maret 2024, PinkFlow terus
                        berkembang dengan menghadirkan buket,
                        hadiah, dan berbagai pilihan lainnya
                        dengan harga terjangkau serta pelayanan
                        yang ramah.
                    </p>

                    <a
                        href="katalog.php"
                        class="about-link"
                    >
                        Lihat Katalog
                    </a>

                </div>

            </div>

        </section>

    </main>

    <?php require_once "../includes/footer.php"; ?>

</body>

</html>
