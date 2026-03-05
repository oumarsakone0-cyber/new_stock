-- Table d'historique des clôtures de journée pour chaque point de vente
CREATE TABLE IF NOT EXISTS app_points_vente_clotures (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_pdv BIGINT NOT NULL,
  date_cloture DATETIME NOT NULL,
  montant_encaisse DECIMAL(15,2) NOT NULL,
  observations TEXT,
  cree_par BIGINT,
  FOREIGN KEY (id_pdv) REFERENCES app_points_vente(id_pdv)
);