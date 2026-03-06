-- Catalogue Produits (MySQL/MariaDB)
-- Tables: categories, sous-categories, produits
-- Utilise par api_produit.php et Liste_produits.vue

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- 1) Categories produits
-- =========================
CREATE TABLE IF NOT EXISTS app_produits_categories (
  id_categorie INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  libelle VARCHAR(150) NOT NULL,
  create_by INT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  PRIMARY KEY (id_categorie),
  KEY idx_cat_entreprise (id_entreprise),
  KEY idx_cat_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 2) Sous-categories produits
-- =========================
CREATE TABLE IF NOT EXISTS app_produits_sous_categories (
  id_sous_categorie INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  id_categorie INT UNSIGNED NULL,
  libelle VARCHAR(150) NOT NULL,
  rayon VARCHAR(80) NULL,
  palier VARCHAR(80) NULL,
  zone VARCHAR(80) NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  PRIMARY KEY (id_sous_categorie),
  KEY idx_sc_entreprise (id_entreprise),
  KEY idx_sc_categorie (id_categorie),
  KEY idx_sc_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 3) Produits (structure alignée avec la table réelle)
-- =========================
CREATE TABLE IF NOT EXISTS app_produits (
  id_produit INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  id_fournisseur INT NULL,
  id_categorie INT UNSIGNED NULL,
  id_sous_categorie INT UNSIGNED NULL,
  libelle VARCHAR(255) NOT NULL,
  description TEXT NULL,
  prix_achat DECIMAL(15,2) NOT NULL DEFAULT 0,
  prix_vente DECIMAL(15,2) NOT NULL DEFAULT 0,
  image VARCHAR(500) NULL,
  code_barres VARCHAR(120) NULL,
  code_barre VARCHAR(120) NULL,
  sku VARCHAR(80) NULL,
  reference VARCHAR(120) NULL,
  qr_code VARCHAR(120) NULL,
  unite_mesure VARCHAR(30) NOT NULL DEFAULT 'unite',
  peremption DATE NULL,
  statut VARCHAR(20) NOT NULL DEFAULT 'actif',
  marque VARCHAR(120) NULL,
  entrepot VARCHAR(120) NULL,
  quantite_init INT NOT NULL DEFAULT 0,
  seuil_alerte INT NOT NULL DEFAULT 0,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  create_by INT NULL,
  modif_user INT NULL,
  PRIMARY KEY (id_produit),
  KEY idx_prod_entreprise (id_entreprise),
  KEY idx_prod_libelle (libelle),
  KEY idx_prod_statut (statut),
  KEY idx_prod_categorie (id_categorie),
  KEY idx_prod_sous_categorie (id_sous_categorie),
  KEY idx_prod_fournisseur (id_fournisseur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4) Corriger la FK si app_produits pointe vers app_categories au lieu de app_produits_categories
-- Exécuter ce bloc si vous avez l'erreur "foreign key constraint fails" sur id_categorie
-- =========================
-- ALTER TABLE app_produits DROP FOREIGN KEY app_produits_ibfk_2;
-- ALTER TABLE app_produits ADD CONSTRAINT app_produits_ibfk_2 FOREIGN KEY (id_categorie) REFERENCES app_produits_categories (id_categorie) ON DELETE SET NULL ON UPDATE CASCADE;
-- (Décommentez les 2 lignes ci-dessus et exécutez dans phpMyAdmin si nécessaire)

SET FOREIGN_KEY_CHECKS = 1;
