-- Corriger la clé étrangère sur app_produits : faire pointer id_categorie vers app_produits_categories
-- À exécuter dans phpMyAdmin si vous avez l'erreur "Integrity constraint violation" / "app_produits_ibfk_2"

-- 1) Supprimer l'ancienne contrainte (qui pointe vers app_categories)
ALTER TABLE app_produits DROP FOREIGN KEY app_produits_ibfk_2;

-- 2) Ajouter la nouvelle contrainte vers app_produits_categories
ALTER TABLE app_produits
  ADD CONSTRAINT app_produits_ibfk_2
  FOREIGN KEY (id_categorie)
  REFERENCES app_produits_categories (id_categorie)
  ON DELETE SET NULL
  ON UPDATE CASCADE;
