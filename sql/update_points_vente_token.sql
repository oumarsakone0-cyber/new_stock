-- Ajout de la colonne id_entreprise pour la gestion multi-entreprise sur les points de vente
ALTER TABLE app_points_vente ADD COLUMN id_entreprise BIGINT NULL;