<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="kedai.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

</head>

<body>

    <!-- HEADER -->
    <header>
        <div class="overlay">

            <nav>
                <h1>Kedai Kopi Sudut Senja</h1>

                <ul>
                    <li><a href="#hero">Home</a></li>
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#tentang">Tentang</a></li>
                    <li><a href="#galeri">Galeri</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
                <div class="nav-auth">
                    <a href="auth/login.php" class="btn-login">Login</a>
                    <a href="auth/register.php" class="btn-register">Daftar</a>
                </div>
            </nav>

            <div class="hero" id="hero">
                <h2>Selamat Datang di Kedai Kopi Sudut Senja</h2>

                <p>
                    Tempat terbaik untuk menikmati secangkir kopi hangat dengan suasana nyaman,
                    santai, dan penuh kenikmatan. Kami menghadirkan berbagai pilihan kopi khas
                    Indonesia dengan cita rasa premium yang dibuat langsung oleh barista profesional.
                </p>

                <div class="hero-btns">
                    <a href="isi.php" class="btn">Lihat Menu</a>
                    <a href="auth/login.php" class="btn btn-outline-hero">Masuk Sekarang</a>
                </div>
            </div>

        </div>
    </header>

    <!-- MENU -->
    <section id="menu">

        <div class="title">
            <h2>Menu Favorit</h2>
            <p>Berbagai pilihan kopi dan makanan terbaik</p>
        </div>

        <div class="menu-container">

            <div class="menu-card">
                <img src="menu/card_1.png" alt="Espresso">

                <div class="menu-content">
                    <h3>Espresso</h3>

                    <p>
                        Kopi hitam pekat dengan aroma kuat dan rasa khas
                    </p>

                </div>
            </div>

            <div class="menu-card">
                <img src="menu/card_2.png" alt="Cappuccino">

                <div class="menu-content">
                    <h3>Cappuccino</h3>

                    <p>
                        Perpaduan espresso dengan susu creamy dan foam lembut
                    </p>

                </div>
            </div>

            <div class="menu-card">
                <img src="menu/card_3.png" alt="Latte">

                <div class="menu-content">
                    <h3>Latte</h3>

                    <p>
                        Perpaduan sempurna antara kekuatan espresso dan kelembutan susu.

                    </p>

                </div>
            </div>

            <div class="menu-card">
                <img src="menu/card_4.png" 
                alt="Milk Coffee">

                <div class="menu-content">
                    <h3>Milk coffe</h3>

                    <p>
                        Minuman kopi dengan campuran susu hangat yang
                    </p>

                </div>
            </div>

        </div>
    </section>

    <!-- TENTANG -->
    <section id="tentang">
        <div class="title">
            <h2>Tentang Kedai Kami</h2>
            <p>Mengenal lebih dekat Kedai Kopi Sudut Senja</p>
        </div>

        <div class="about">
            <img src="gallery/cafe_1.png"
                alt="Suasana Kedai Kopi">

            <div class="about-text">
                <h3>Kenikmatan Kopi Asli Indonesia</h3>
                <p>
                    Kedai Kopi Sudut Senja adalah tempat berkumpulnya para pecinta kopi
                    yang ingin menikmati suasana hangat dan nyaman. Kami menghadirkan
                    berbagai jenis kopi pilihan dari berbagai daerah di Indonesia seperti
                    Aceh Gayo, Toraja, Lampung, dan Bali Kintamani.
                </p>

                <p>
                    Dengan konsep modern dan tradisional yang dipadukan menjadi satu,
                    kami ingin memberikan pengalaman berbeda bagi setiap pelanggan yang datang.
                    Tidak hanya menikmati kopi, pelanggan juga dapat bersantai, bekerja,
                    maupun berkumpul bersama keluarga dan teman.
                </p>

                <p>
                    Semua minuman dibuat dari biji kopi berkualitas tinggi yang diproses
                    dengan standar terbaik. Kami juga menyediakan berbagai makanan ringan
                    dan dessert yang cocok dinikmati bersama kopi favorit Anda.
                </p>
            </div>
        </div>
    </section>

    <!-- GALERI -->
    <section id="galeri">

        <div class="title">
            <h2>Galeri Kedai</h2>
            <p>Suasana nyaman dan modern di Kedai Kopi Sudut Senja</p>
        </div>

        <div class="gallery">

            <img src="gallery/cafe_1.png" alt="Galeri 1">

            <img src="gallery/cafe_2.png" alt="Galeri 2">

            <img src="gallery/cafe_3.png" alt="Galeri 3">

            <img src="gallery/cafe_4.png" alt="Galeri 4">

        </div>

    </section>

    <!-- TESTIMONI -->
    <section id="testimoni">

        <div class="title">
            <h2>Testimoni Pelanggan</h2>
            <p>Apa kata pelanggan tentang kedai kami</p>
        </div>

        <div class="testimoni-container">

            <div class="testi">
                <p>
                    "Tempatnya sangat nyaman dan kopinya enak sekali.
                    Cocok untuk nongkrong maupun mengerjakan tugas."
                </p>

                <h4>- Andi Saputra</h4>
            </div>

            <div class="testi">
                <p>
                    "Pelayanan ramah, suasana estetik, dan harga menu
                    sangat terjangkau untuk kualitas premium."
                </p>

                <h4>- Rina Amelia</h4>
            </div>

            <div class="testi">
                <p>
                    "Saya suka kopi Lampung di sini. Rasanya khas dan
                    aroma kopinya sangat kuat."
                </p>

                <h4>- Budi Hartono</h4>
            </div>

        </div>

    </section>

    <!-- KONTAK -->
    <section id="kontak">

        <div class="contact">

            <h2>Hubungi Kami</h2>
            <div class="contact-content">
                <p>📍 Jl. Wisnu, Pringsewu, Lampung</p>
                <p>💬 whatsapp: <a href="https://wa.me/6281234567890" target="_blank">+62 812-3456-7890</a></p>
                <p>✉️ email: <a href="mailto:kopi@nusantara.com">kopi@nusantara.com</a></p>
                <p>⏰ Setiap Hari : 08.00 - 23.00 WIB</p>
            </div>

            <div class="footer-bottom">
                <p>Copyright © 2026 Kedai Kopi Sudut Senja. All rights reserved.</p>
            </div>
        </div>
    </section>

</body>

</html>