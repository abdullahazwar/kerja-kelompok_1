-- DATABASE: Kedai Kopi Sudut Senja
CREATE DATABASE IF NOT EXISTS kedaikopi_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE kedaikopi_db;

-- TABEL: users (Admin & Anggota)
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    telepon     VARCHAR(20)   DEFAULT NULL,
    role        ENUM('admin','anggota') NOT NULL DEFAULT 'anggota',
    foto_profil VARCHAR(255)  DEFAULT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABEL: kategori_menu
CREATE TABLE IF NOT EXISTS kategori_menu (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    deskripsi   TEXT         DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABEL: menu
CREATE TABLE IF NOT EXISTS menu (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id  INT           NOT NULL,
    nama         VARCHAR(150)  NOT NULL,
    deskripsi    TEXT          DEFAULT NULL,
    harga        DECIMAL(10,2) NOT NULL DEFAULT 0,
    gambar       VARCHAR(255)  DEFAULT NULL,
    stok         INT           NOT NULL DEFAULT 0,
    tersedia     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_menu_kategori FOREIGN KEY (kategori_id)
        REFERENCES kategori_menu(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- TABEL: pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT           NOT NULL,
    total_harga  DECIMAL(10,2) NOT NULL DEFAULT 0,
    status       ENUM('pending','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
    catatan      TEXT          DEFAULT NULL,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- TABEL: detail_pesanan
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id  INT           NOT NULL,
    menu_id     INT           NOT NULL,
    jumlah      INT           NOT NULL DEFAULT 1,
    harga_saat  DECIMAL(10,2) NOT NULL,
    subtotal    DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id)
        REFERENCES pesanan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_menu FOREIGN KEY (menu_id)
        REFERENCES menu(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- TABEL: galeri
CREATE TABLE IF NOT EXISTS galeri (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    judul       VARCHAR(150)  DEFAULT NULL,
    gambar      VARCHAR(255)  NOT NULL,
    urutan      INT           DEFAULT 0,
    aktif       TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- DATA AWAL: Admin kata sandi
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'kedaikopi@email.com', '$2y$10$SMCYH6o1w/spcj0zQCn2O.DKThU/egbE027Euy4DfUmdCe8VuG4Mi', 'admin');
-- Password default admin: admin123

-- DATA AWAL: Kategori Menu
INSERT INTO kategori_menu (nama, deskripsi) VALUES
('Panas',  'Minuman kopi disajikan panas'),
('Dingin', 'Minuman kopi disajikan dingin'),
('Non-Kopi',    'Minuman selain kopi'),
('Makanan',     'Cemilan dan makanan pendamping');

-- DATA AWAL: Menu
INSERT INTO menu (kategori_id, nama, deskripsi, harga, gambar, stok) VALUES
(1, 'Espresso',       'Kopi hitam pekat dengan aroma kuat.', 20000, 'menu/card_1', 50),
(1, 'Cappuccino',     'Perpaduan espresso dengan susu creamy dan foam lembut.',28000, 'menu/card_2', 50),
(1, 'Latte',          'Espresso dengan susu steamed dalam porsi besar.',25000, 'menu/card_3', 50),
(2, 'Milk Coffee',    'Minuman kopi dengan campuran susu hangat.',18000, 'menu/card_4', 50),
(2, 'Iced Americano', 'Espresso dengan air dingin dan es batu.',22000, NULL, 50),
(3, 'Matcha Latte',   'Matcha premium dengan susu segar.',30000, NULL, 30),
(4, 'Croissant',      'Croissant butter lembut dan renyah.',20000, NULL, 20);

-- DATA AWAL: Galeri
INSERT INTO galeri (judul, gambar, urutan) VALUES
('Suasana Kedai 1', 'gallery/png_1', 1),
('Suasana Kedai 2', 'gallery/png_2', 2),
('Suasana Kedai 3', 'gallery/png_3', 3),
('Suasana Kedai 4', 'gallery/png_4', 4);
