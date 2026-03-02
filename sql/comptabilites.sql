-- Module Comptabilités (MySQL/MariaDB)
-- Tables: motifs de dépense, dépenses, pièces, comptes bancaires, transactions

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- 1) Motifs de dépense
-- =========================
CREATE TABLE IF NOT EXISTS app_compta_motifs_depense (
  id_motif INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  libelle VARCHAR(150) NOT NULL,
  description TEXT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  PRIMARY KEY (id_motif),
  KEY idx_motif_entreprise (id_entreprise),
  KEY idx_motif_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 2) Dépenses
-- =========================
CREATE TABLE IF NOT EXISTS app_compta_depenses (
  id_depense INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  motif_id INT UNSIGNED NULL,
  date_depense DATE NOT NULL,
  beneficiaire VARCHAR(200) NULL,
  description TEXT NULL,
  montant DECIMAL(15,2) NOT NULL DEFAULT 0,
  mode_paiement VARCHAR(50) NULL, -- cash / banque / mobile_money / autre
  reference VARCHAR(120) NULL, -- ref interne / ticket / bordereau
  create_by INT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  PRIMARY KEY (id_depense),
  KEY idx_dep_entreprise (id_entreprise),
  KEY idx_dep_date (date_depense),
  KEY idx_dep_motif (motif_id),
  CONSTRAINT fk_dep_motif
    FOREIGN KEY (motif_id) REFERENCES app_compta_motifs_depense(id_motif)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 3) Pièces justificatives (factures / reçus)
-- =========================
CREATE TABLE IF NOT EXISTS app_compta_depense_pieces (
  id_piece INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_depense INT UNSIGNED NOT NULL,
  id_entreprise INT NULL,
  url_fichier TEXT NOT NULL,
  nom_original VARCHAR(255) NULL,
  type_fichier VARCHAR(120) NULL, -- mime
  taille_octets BIGINT NULL,
  create_by INT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_piece),
  KEY idx_piece_depense (id_depense),
  KEY idx_piece_entreprise (id_entreprise),
  CONSTRAINT fk_piece_depense
    FOREIGN KEY (id_depense) REFERENCES app_compta_depenses(id_depense)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 4) Comptes bancaires
-- =========================
CREATE TABLE IF NOT EXISTS app_compta_comptes_bancaires (
  id_compte INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  banque VARCHAR(150) NULL,
  nom_compte VARCHAR(150) NOT NULL,
  numero_compte VARCHAR(120) NULL,
  devise VARCHAR(10) NOT NULL DEFAULT 'XOF',
  solde_initial DECIMAL(15,2) NOT NULL DEFAULT 0,
  create_by INT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  PRIMARY KEY (id_compte),
  KEY idx_compte_entreprise (id_entreprise),
  KEY idx_compte_nom (nom_compte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 5) Transactions bancaires
-- =========================
CREATE TABLE IF NOT EXISTS app_compta_transactions_bancaires (
  id_transaction INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT NULL,
  id_compte INT UNSIGNED NOT NULL,
  date_transaction DATE NOT NULL,
  sens ENUM('DEBIT','CREDIT') NOT NULL,
  montant DECIMAL(15,2) NOT NULL DEFAULT 0,
  reference VARCHAR(120) NULL,
  libelle VARCHAR(255) NULL,
  note TEXT NULL,
  create_by INT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NULL,
  PRIMARY KEY (id_transaction),
  KEY idx_tx_entreprise (id_entreprise),
  KEY idx_tx_compte (id_compte),
  KEY idx_tx_date (date_transaction),
  KEY idx_tx_sens (sens),
  CONSTRAINT fk_tx_compte
    FOREIGN KEY (id_compte) REFERENCES app_compta_comptes_bancaires(id_compte)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

