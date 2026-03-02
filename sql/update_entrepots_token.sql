-- Ajout des colonnes pour la nouvelle logique token

-- Table des entrepôts
ALTER TABLE app_entrepots 
ADD COLUMN id_entreprise BIGINT NULL,
ADD COLUMN cree_par BIGINT NULL,
ADD COLUMN modifie_par BIGINT NULL;

-- Table des produits d'entrepôt
ALTER TABLE app_entrepots_produits 
ADD COLUMN id_entreprise BIGINT NULL,
ADD COLUMN cree_par BIGINT NULL,
ADD COLUMN modifie_par BIGINT NULL;

-- Table des mouvements d'entrepôt
ALTER TABLE app_entrepots_mouvements 
ADD COLUMN id_entreprise BIGINT NULL,
ADD COLUMN cree_par BIGINT NULL;

-- Table des statistiques d'entrepôt
ALTER TABLE app_entrepots_stats
ADD COLUMN id_entreprise BIGINT NULL;