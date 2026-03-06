<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';

class ProduitsAPI {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];

        $user = getUserFromToken();
        $user_id = $user['id'];
        $id_entreprise = $user['id_entreprise'] ?? null;
        $role = in_array('ALL', $user['access'] ?? []) ? 'admin' : 'user';

        try {
            if ($method === 'GET') {
                switch ($action) {
                    case 'list':
                    case '':
                        $this->listProduits($id_entreprise, $role);
                        break;
                    case 'list_categories':
                        $this->listCategories($id_entreprise, $role);
                        break;
                    case 'list_sous_categories':
                        $this->listSousCategories($id_entreprise, $role);
                        break;
                    case 'list_fournisseurs':
                        $this->listFournisseurs($id_entreprise, $role);
                        break;
                    case 'list_entrepots':
                        $this->listEntrepots($id_entreprise, $role);
                        break;
                    default:
                        $this->sendResponse(404, ['success' => false, 'error' => 'Action GET inconnue']);
                }
                return;
            }

            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!is_array($data)) $data = [];

                switch ($action) {
                    case 'add_produit':
                        $this->addProduit($id_entreprise, $user_id, $data);
                        break;
                    case 'update_produit':
                        $this->updateProduit($id_entreprise, $role, $data, $user_id);
                        break;
                    case 'delete_produit':
                        $this->deleteProduit($id_entreprise, $role, $data);
                        break;
                    case 'add_categorie':
                        $this->addCategorie($id_entreprise, $user_id, $data);
                        break;
                    case 'update_categorie':
                        $this->updateCategorie($id_entreprise, $role, $data);
                        break;
                    case 'delete_categorie':
                        $this->deleteCategorie($id_entreprise, $role, $data);
                        break;
                    case 'add_sous_categorie':
                        $this->addSousCategorie($id_entreprise, $data);
                        break;
                    case 'delete_sous_categorie':
                        $this->deleteSousCategorie($id_entreprise, $role, $data);
                        break;
                    case 'seed_default_categories':
                        $this->seedDefaultCategories($id_entreprise, $user_id);
                        break;
                    default:
                        $this->sendResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
                }
                return;
            }

            $this->sendResponse(405, ['success' => false, 'error' => 'Methode non autorisee']);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur serveur',
                'details' => $e->getMessage()
            ]);
        }
    }

    private function getInt($v, $default = null) {
        if ($v === null || $v === '') return $default;
        return (int) $v;
    }

    private function getStr($v, $default = '') {
        if ($v === null) return $default;
        return trim((string) $v); 
    }

    /** Trouve ou crée une catégorie par libellé ; retourne id_categorie ou null si libelle vide. */
    private function resolveOrCreateCategorie($id_entreprise, $user_id, $libelle) {
        $libelle = $this->getStr($libelle, '');
        if ($libelle === '') return null;
        if ($id_entreprise !== null) {
            $row = $this->db->query(
                "SELECT id_categorie FROM app_produits_categories WHERE libelle = ? AND (id_entreprise = ? OR id_entreprise IS NULL) LIMIT 1",
                [$libelle, $id_entreprise]
            );
        } else {
            $row = $this->db->query(
                "SELECT id_categorie FROM app_produits_categories WHERE libelle = ? AND id_entreprise IS NULL LIMIT 1",
                [$libelle]
            );
        }
        if (is_array($row) && count($row) > 0) return (int) $row[0]['id_categorie'];
        $this->db->query(
            "INSERT INTO app_produits_categories (id_entreprise, libelle, date_creation) VALUES (?, ?, NOW())",
            [$id_entreprise, $libelle]
        );
        $lid = $this->db->lastInsertId();
        return $lid !== null && $lid !== '' ? (int) $lid : null;
    }

    /** Trouve ou crée une sous-catégorie par libellé pour une catégorie ; retourne id_sous_categorie ou null. */
    private function resolveOrCreateSousCategorie($id_entreprise, $id_categorie, $libelle) {
        $libelle = $this->getStr($libelle, '');
        if ($libelle === '' || $id_categorie === null) return null;
        if ($id_entreprise !== null) {
            $row = $this->db->query(
                "SELECT id_sous_categorie FROM app_produits_sous_categories WHERE id_categorie = ? AND libelle = ? AND (id_entreprise = ? OR id_entreprise IS NULL) LIMIT 1",
                [$id_categorie, $libelle, $id_entreprise]
            );
        } else {
            $row = $this->db->query(
                "SELECT id_sous_categorie FROM app_produits_sous_categories WHERE id_categorie = ? AND libelle = ? AND id_entreprise IS NULL LIMIT 1",
                [$id_categorie, $libelle]
            );
        }
        if (is_array($row) && count($row) > 0) return (int) $row[0]['id_sous_categorie'];
        $this->db->query(
            "INSERT INTO app_produits_sous_categories (id_entreprise, id_categorie, libelle, date_creation) VALUES (?, ?, ?, NOW())",
            [$id_entreprise, $id_categorie, $libelle]
        );
        $lid = $this->db->lastInsertId();
        return $lid !== null && $lid !== '' ? (int) $lid : null;
    }

    // ---------- GET ----------
    private function listProduits($id_entreprise, $role) {
        $sql = "SELECT p.*,
                c.libelle AS categorie_nom,
                s.libelle AS sous_categorie_nom
                FROM app_produits p
                LEFT JOIN app_produits_categories c ON c.id_categorie = p.id_categorie
                LEFT JOIN app_produits_sous_categories s ON s.id_sous_categorie = p.id_sous_categorie
                WHERE 1=1 AND (p.statut IS NULL OR p.statut != 'archive')";
        $params = [];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (p.id_entreprise = ? OR p.id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY p.libelle ASC";
        try {
            $rows = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $sql = "SELECT * FROM app_produits WHERE 1=1 AND (statut IS NULL OR statut != 'archive')";
            $params = [];
            if ($role !== 'admin' && $id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY libelle ASC";
            $rows = $this->db->query($sql, $params);
        }
        if (!is_array($rows)) $rows = [];
        foreach ($rows as &$r) {
            $r['id'] = $r['id_produit'] ?? $r['id'] ?? null;
            $r['prix_achat'] = (float)($r['prix_achat'] ?? 0);
            $r['prix_vente'] = (float)($r['prix_vente'] ?? 0);
            $r['quantite_init'] = (int)($r['quantite_init'] ?? 0);
            $r['seuil_alerte'] = (int)($r['seuil_alerte'] ?? 0);
        }
        unset($r);
        $this->sendResponse(200, ['success' => true, 'data' => $rows]);
    }

    private function listCategories($id_entreprise, $role) {
        $sql = "SELECT * FROM app_produits_categories WHERE 1=1";
        $params = [];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY libelle ASC";
        try {
            $rows = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $rows = [];
        }
        if (!is_array($rows)) $rows = [];
        $rows = array_values($rows);
        foreach ($rows as &$r) {
            $r['id'] = $r['id_categorie'] ?? $r['id'] ?? null;
        }
        unset($r);
        $this->sendResponse(200, ['success' => true, 'data' => $rows]);
    }

    private function listSousCategories($id_entreprise, $role) {
        $sql = "SELECT * FROM app_produits_sous_categories WHERE 1=1";
        $params = [];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY libelle ASC";
        try {
            $rows = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $rows = [];
        }
        if (!is_array($rows)) $rows = [];
        foreach ($rows as &$r) {
            $r['id'] = $r['id_sous_categorie'] ?? $r['id'] ?? null;
        }
        unset($r);
        $this->sendResponse(200, ['success' => true, 'data' => $rows]);
    }

    private function listFournisseurs($id_entreprise, $role) {
        $sql = "SELECT id_fournisseur AS id, nom, adresse, contact, email, telephone_commercial
                FROM app_fournisseurs WHERE 1=1";
        $params = [];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY nom ASC";
        try {
            $rows = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $rows = [];
        }
        if (!is_array($rows)) $rows = [];
        $this->sendResponse(200, ['success' => true, 'data' => $rows]);
    }

    private function listEntrepots($id_entreprise, $role) {
        try {
            $sql = "SELECT id, nom, code FROM app_entrepots WHERE 1=1";
            $params = [];
            if ($role !== 'admin' && $id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY nom ASC";
            $rows = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $rows = [];
        }
        if (!is_array($rows)) $rows = [];
        $this->sendResponse(200, ['success' => true, 'data' => $rows]);
    }

    // ---------- POST Produits ----------
    private function addProduit($id_entreprise, $user_id, $data) {
        try {
            $libelle = trim((string)($data['libelle'] ?? ''));
            if ($libelle === '') {
                $this->sendResponse(400, ['success' => false, 'error' => 'Le libellé est requis']);
            }
            $prix_achat = isset($data['prix_achat']) ? (float) $data['prix_achat'] : null;
            $prix_vente = isset($data['prix_vente']) ? (float) $data['prix_vente'] : null;
            if ($prix_achat === null || $prix_achat < 0) {
                $this->sendResponse(400, ['success' => false, 'error' => 'Le prix d\'achat est requis et doit être >= 0']);
            }
            if ($prix_vente === null || $prix_vente < 0) {
                $this->sendResponse(400, ['success' => false, 'error' => 'Le prix de vente est requis et doit être >= 0']);
            }
            // Catégorie : id (liste page Catégories) ou création à partir de saisie libre (categorie_libelle)
            $categorie_libelle = $this->getStr($data['categorie_libelle'] ?? '');
            $id_categorie = null;
            $id_sous_categorie = null;
            if ($categorie_libelle !== '') {
                try {
                    $id_categorie = $this->resolveOrCreateCategorie($id_entreprise, $user_id, $categorie_libelle);
                    if ($id_categorie !== null) {
                        $sous_cat_lib = $this->getStr($data['sous_categorie_libelle'] ?? '');
                        if ($sous_cat_lib !== '') {
                            $id_sous_categorie = $this->resolveOrCreateSousCategorie($id_entreprise, $id_categorie, $sous_cat_lib);
                        }
                    }
                } catch (Throwable $ex) {
                    error_log("addProduit categorie saisie libre: " . $ex->getMessage());
                }
            } else {
                $id_cat_raw = $data['id_categorie'] ?? null;
                if ($id_cat_raw !== null && $id_cat_raw !== '') {
                    $id_categorie = $this->getInt($id_cat_raw);
                    if ($id_categorie !== null) {
                        $id_sous_categorie = $this->getInt($data['id_sous_categorie'] ?? null);
                    }
                }
            }
            $entrepot_nom = $this->getStr($data['entrepot'] ?? '');
            $sqlMain = "INSERT INTO app_produits (
                    id_entreprise, libelle, sku, reference, qr_code, description,
                    prix_achat, prix_vente, image, unite_mesure, peremption, statut, marque,
                    quantite_init, seuil_alerte, id_categorie, id_sous_categorie, id_fournisseur, entrepot,
                    create_by, date_creation
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $sql = $sqlMain;
            $params = [
                $id_entreprise,
                $libelle,
                $this->getStr($data['sku'] ?? ''),
                $this->getStr($data['reference'] ?? ''),
                $this->getStr($data['qr_code'] ?? ''),
                $this->getStr($data['description'] ?? ''),
                (float)($data['prix_achat'] ?? 0),
                (float)($data['prix_vente'] ?? 0),
                $this->getStr($data['image'] ?? ''),
                $this->getStr($data['unite_mesure'] ?? 'unite'),
                $this->getStr($data['peremption'] ?? ''),
                $this->getStr($data['statut'] ?? 'actif'),
                $this->getStr($data['marque'] ?? ''),
                $this->getInt($data['quantite_init'] ?? 0, 0),
                $this->getInt($data['seuil_alerte'] ?? 0, 0),
                $id_categorie,
                $id_sous_categorie,
                $this->getInt($data['id_fournisseur'] ?? null),
                $entrepot_nom,
                $user_id
            ];
            $run = function ($sql, $params) {
                $this->db->query($sql, $params);
            };
            try {
                $run($sql, $params);
            } catch (Throwable $e) {
                $msg = $e->getMessage();
                $retryWithoutEntrepot = (stripos($msg, 'entrepot') !== false)
                    || (stripos($msg, 'Column count') !== false)
                    || (stripos($msg, "doesn't match value") !== false);
                if ($retryWithoutEntrepot) {
                    // 1) Essai sans colonne entrepot (table à 20 colonnes)
                    $sqlNoEntrepot = "INSERT INTO app_produits (
                            id_entreprise, libelle, sku, reference, qr_code, description,
                            prix_achat, prix_vente, image, unite_mesure, peremption, statut, marque,
                            quantite_init, seuil_alerte, id_categorie, id_sous_categorie, id_fournisseur,
                            create_by, date_creation
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $paramsNoEntrepot = [
                        $id_entreprise,
                        $libelle,
                        $this->getStr($data['sku'] ?? ''),
                        $this->getStr($data['reference'] ?? ''),
                        $this->getStr($data['qr_code'] ?? ''),
                        $this->getStr($data['description'] ?? ''),
                        (float)($data['prix_achat'] ?? 0),
                        (float)($data['prix_vente'] ?? 0),
                        $this->getStr($data['image'] ?? ''),
                        $this->getStr($data['unite_mesure'] ?? 'unite'),
                        $this->getStr($data['peremption'] ?? ''),
                        $this->getStr($data['statut'] ?? 'actif'),
                        $this->getStr($data['marque'] ?? ''),
                        $this->getInt($data['quantite_init'] ?? 0, 0),
                        $this->getInt($data['seuil_alerte'] ?? 0, 0),
                        $id_categorie,
                        $id_sous_categorie,
                        $this->getInt($data['id_fournisseur'] ?? null),
                        $user_id
                    ];
                    try {
                        $run($sqlNoEntrepot, $paramsNoEntrepot);
                    } catch (Throwable $e2) {
                        $msg2 = $e2->getMessage();
                        // 2) Si "Column count" : la table a la colonne entrepot, réessayer avec entrepot (20 params)
                        if (stripos($msg2, 'Column count') !== false || stripos($msg2, "doesn't match value") !== false) {
                            $run($sqlMain, $params);
                        } elseif (stripos($msg2, 'Unknown column') !== false) {
                            $sql = "INSERT INTO app_produits (
                                    id_entreprise, libelle, description,
                                    prix_achat, prix_vente, image, unite_mesure, peremption, statut, marque,
                                    quantite_init, seuil_alerte, id_categorie, id_sous_categorie, id_fournisseur,
                                    create_by, date_creation
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                            $params = [
                                $id_entreprise,
                                $libelle,
                                $this->getStr($data['description'] ?? ''),
                                (float)($data['prix_achat'] ?? 0),
                                (float)($data['prix_vente'] ?? 0),
                                $this->getStr($data['image'] ?? ''),
                                $this->getStr($data['unite_mesure'] ?? 'unite'),
                                $this->getStr($data['peremption'] ?? ''),
                                $this->getStr($data['statut'] ?? 'actif'),
                                $this->getStr($data['marque'] ?? ''),
                                $this->getInt($data['quantite_init'] ?? 0, 0),
                                $this->getInt($data['seuil_alerte'] ?? 0, 0),
                                $id_categorie,
                                $id_sous_categorie,
                                $this->getInt($data['id_fournisseur'] ?? null),
                                $user_id
                            ];
                            $run($sql, $params);
                        } else {
                            throw $e2;
                        }
                    }
                } else {
                    throw $e;
                }
            }
            $lid = $this->db->lastInsertId();
            $id = ($lid !== null && $lid !== '') ? (int) $lid : 0;
            $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id, 'id_produit' => $id]]);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            $hadCategory = ($id_categorie !== null || $id_sous_categorie !== null);
            if ($hadCategory) {
                try {
                    $paramsNoCat = [
                        $id_entreprise,
                        $libelle,
                        $this->getStr($data['sku'] ?? ''),
                        $this->getStr($data['reference'] ?? ''),
                        $this->getStr($data['qr_code'] ?? ''),
                        $this->getStr($data['description'] ?? ''),
                        (float)($data['prix_achat'] ?? 0),
                        (float)($data['prix_vente'] ?? 0),
                        $this->getStr($data['image'] ?? ''),
                        $this->getStr($data['unite_mesure'] ?? 'unite'),
                        $this->getStr($data['peremption'] ?? ''),
                        $this->getStr($data['statut'] ?? 'actif'),
                        $this->getStr($data['marque'] ?? ''),
                        $this->getInt($data['quantite_init'] ?? 0, 0),
                        $this->getInt($data['seuil_alerte'] ?? 0, 0),
                        null,
                        null,
                        $this->getInt($data['id_fournisseur'] ?? null),
                        $entrepot_nom,
                        $user_id
                    ];
                    $this->db->query($sqlMain, $paramsNoCat);
                    $lid = $this->db->lastInsertId();
                    $id = ($lid !== null && $lid !== '') ? (int) $lid : 0;
                    $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id, 'id_produit' => $id], 'category_skipped' => true]);
                } catch (Throwable $e2) {
                    $msg2 = $e2->getMessage();
                    if (stripos($msg2, 'entrepot') !== false || stripos($msg2, 'Column count') !== false || stripos($msg2, "doesn't match") !== false) {
                        $sqlNoEntrepot = "INSERT INTO app_produits (
                                id_entreprise, libelle, sku, reference, qr_code, description,
                                prix_achat, prix_vente, image, unite_mesure, peremption, statut, marque,
                                quantite_init, seuil_alerte, id_categorie, id_sous_categorie, id_fournisseur,
                                create_by, date_creation
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                        $paramsNoCatNoEntrepot = [
                            $id_entreprise,
                            $libelle,
                            $this->getStr($data['sku'] ?? ''),
                            $this->getStr($data['reference'] ?? ''),
                            $this->getStr($data['qr_code'] ?? ''),
                            $this->getStr($data['description'] ?? ''),
                            (float)($data['prix_achat'] ?? 0),
                            (float)($data['prix_vente'] ?? 0),
                            $this->getStr($data['image'] ?? ''),
                            $this->getStr($data['unite_mesure'] ?? 'unite'),
                            $this->getStr($data['peremption'] ?? ''),
                            $this->getStr($data['statut'] ?? 'actif'),
                            $this->getStr($data['marque'] ?? ''),
                            $this->getInt($data['quantite_init'] ?? 0, 0),
                            $this->getInt($data['seuil_alerte'] ?? 0, 0),
                            null,
                            null,
                            $this->getInt($data['id_fournisseur'] ?? null),
                            $user_id
                        ];
                        $this->db->query($sqlNoEntrepot, $paramsNoCatNoEntrepot);
                        $lid = $this->db->lastInsertId();
                        $id = ($lid !== null && $lid !== '') ? (int) $lid : 0;
                        $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id, 'id_produit' => $id], 'category_skipped' => true]);
                    } else {
                        error_log("addProduit error (after category retry): " . $e2->getMessage());
                        $this->sendResponse(500, ['success' => false, 'error' => 'Erreur lors de la création du produit', 'details' => $e2->getMessage()]);
                    }
                }
                return;
            }
            error_log("addProduit error: " . $e->getMessage());
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur lors de la création du produit',
                'details' => $e->getMessage()
            ]);
        }
    }

    private function updateProduit($id_entreprise, $role, $data, $user_id = null) {
        $id_produit = $this->getInt($data['id_produit'] ?? $data['id'] ?? null, null);
        if ($id_produit === null) {
            $this->sendResponse(400, ['success' => false, 'error' => 'id_produit requis']);
        }
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') {
            $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        }
        $uid = $user_id ?? $this->getInt($data['user_id'] ?? null, null);
        $categorie_libelle = $this->getStr($data['categorie_libelle'] ?? '');
        $sous_categorie_libelle = $this->getStr($data['sous_categorie_libelle'] ?? '');
        $id_categorie = $this->getInt($data['id_categorie'] ?? null);
        $id_sous_categorie = $this->getInt($data['id_sous_categorie'] ?? null);
        if ($categorie_libelle !== '') {
            $id_categorie = $this->resolveOrCreateCategorie($id_entreprise, $uid ?: 0, $categorie_libelle);
            $id_sous_categorie = null;
        }
        if ($id_categorie !== null && $sous_categorie_libelle !== '') {
            $id_sous_categorie = $this->resolveOrCreateSousCategorie($id_entreprise, $id_categorie, $sous_categorie_libelle);
        }
        $entrepot_nom = $this->getStr($data['entrepot'] ?? '');
        $sql = "UPDATE app_produits SET
                libelle = ?, sku = ?, reference = ?, qr_code = ?, description = ?,
                prix_achat = ?, prix_vente = ?, image = ?, unite_mesure = ?, peremption = ?, statut = ?, marque = ?,
                quantite_init = ?, seuil_alerte = ?, id_categorie = ?, id_sous_categorie = ?, id_fournisseur = ?, entrepot = ?,
                date_modification = NOW(), modif_user = ?
                WHERE id_produit = ?";
        $params = [
            $libelle,
            $this->getStr($data['sku'] ?? ''),
            $this->getStr($data['reference'] ?? ''),
            $this->getStr($data['qr_code'] ?? ''),
            $this->getStr($data['description'] ?? ''),
            (float)($data['prix_achat'] ?? 0),
            (float)($data['prix_vente'] ?? 0),
            $this->getStr($data['image'] ?? ''),
            $this->getStr($data['unite_mesure'] ?? 'unite'),
            $this->getStr($data['peremption'] ?? ''),
            $this->getStr($data['statut'] ?? 'actif'),
            $this->getStr($data['marque'] ?? ''),
            $this->getInt($data['quantite_init'] ?? 0, 0),
            $this->getInt($data['seuil_alerte'] ?? 0, 0),
            $id_categorie,
            $id_sous_categorie,
            $this->getInt($data['id_fournisseur'] ?? null),
            $entrepot_nom,
            $uid,
            $id_produit
        ];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        try {
            $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            $isFk = (stripos($msg, 'foreign key') !== false || stripos($msg, 'Integrity constraint') !== false || stripos($msg, '23000') !== false);
            if ($isFk && ($id_categorie !== null || $id_sous_categorie !== null)) {
                $paramsFk = [
                    $libelle,
                    $this->getStr($data['sku'] ?? ''),
                    $this->getStr($data['reference'] ?? ''),
                    $this->getStr($data['qr_code'] ?? ''),
                    $this->getStr($data['description'] ?? ''),
                    (float)($data['prix_achat'] ?? 0),
                    (float)($data['prix_vente'] ?? 0),
                    $this->getStr($data['image'] ?? ''),
                    $this->getStr($data['unite_mesure'] ?? 'unite'),
                    $this->getStr($data['peremption'] ?? ''),
                    $this->getStr($data['statut'] ?? 'actif'),
                    $this->getStr($data['marque'] ?? ''),
                    $this->getInt($data['quantite_init'] ?? 0, 0),
                    $this->getInt($data['seuil_alerte'] ?? 0, 0),
                    null,
                    null,
                    $this->getInt($data['id_fournisseur'] ?? null),
                    $entrepot_nom,
                    $uid,
                    $id_produit
                ];
                $sqlFk = "UPDATE app_produits SET libelle = ?, sku = ?, reference = ?, qr_code = ?, description = ?, prix_achat = ?, prix_vente = ?, image = ?, unite_mesure = ?, peremption = ?, statut = ?, marque = ?, quantite_init = ?, seuil_alerte = ?, id_categorie = ?, id_sous_categorie = ?, id_fournisseur = ?, entrepot = ?, date_modification = NOW(), modif_user = ? WHERE id_produit = ?";
                if ($role !== 'admin' && $id_entreprise !== null) {
                    $sqlFk .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                    $paramsFk[] = $id_entreprise;
                }
                $this->db->query($sqlFk, $paramsFk);
            } elseif (stripos($msg, 'entrepot') !== false || stripos($msg, 'modif_user') !== false) {
                $sql = "UPDATE app_produits SET
                        libelle = ?, sku = ?, reference = ?, qr_code = ?, description = ?,
                        prix_achat = ?, prix_vente = ?, image = ?, unite_mesure = ?, peremption = ?, statut = ?, marque = ?,
                        quantite_init = ?, seuil_alerte = ?, id_categorie = ?, id_sous_categorie = ?, id_fournisseur = ?,
                        date_modification = NOW()
                        WHERE id_produit = ?";
                $params = [
                    $libelle,
                    $this->getStr($data['sku'] ?? ''),
                    $this->getStr($data['reference'] ?? ''),
                    $this->getStr($data['qr_code'] ?? ''),
                    $this->getStr($data['description'] ?? ''),
                    (float)($data['prix_achat'] ?? 0),
                    (float)($data['prix_vente'] ?? 0),
                    $this->getStr($data['image'] ?? ''),
                    $this->getStr($data['unite_mesure'] ?? 'unite'),
                    $this->getStr($data['peremption'] ?? ''),
                    $this->getStr($data['statut'] ?? 'actif'),
                    $this->getStr($data['marque'] ?? ''),
                    $this->getInt($data['quantite_init'] ?? 0, 0),
                    $this->getInt($data['seuil_alerte'] ?? 0, 0),
                    $id_categorie,
                    $id_sous_categorie,
                    $this->getInt($data['id_fournisseur'] ?? null),
                    $id_produit
                ];
                if ($role !== 'admin' && $id_entreprise !== null) {
                    $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                    $params[] = $id_entreprise;
                }
                $this->db->query($sql, $params);
            } else {
                throw $e;
            }
        }
        $this->sendResponse(200, ['success' => true]);
    }

    private function deleteProduit($id_entreprise, $role, $data) {
        $id_produit = $this->getInt($data['id_produit'] ?? $data['id'] ?? null, null);
        if ($id_produit === null) {
            $this->sendResponse(400, ['success' => false, 'error' => 'id_produit requis']);
        }
        $sql = "UPDATE app_produits SET statut = 'archive', date_modification = NOW() WHERE id_produit = ?";
        $params = [$id_produit];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true]);
    }

    // ---------- Catégories par défaut (longue liste, tous types de produits) ----------
    private function seedDefaultCategories($id_entreprise, $user_id) {
        $defaults = [
            'Alimentation',
            'Boissons',
            'Hygiène & Beauté',
            'Électronique',
            'Textile & Habillement',
            'Maison & Cuisine',
            'Bureau & Fournitures',
            'Fruits & Légumes',
            'Viandes & Poissons',
            'Produits laitiers',
            'Épicerie',
            'Surgelés',
            'Boulangerie & Pâtisserie',
            'Céréales & Petit-déjeuner',
            'Confiserie & Snacks',
            'Conserves',
            'Huiles & Condiments',
            'Café & Thé',
            'Jus & Sodas',
            'Eau & Boissons',
            'Cosmétiques',
            'Parfumerie',
            'Produits d\'entretien',
            'Médicaments & Santé',
            'Téléphonie & Accessoires',
            'Informatique',
            'Électroménager',
            'Audio & Vidéo',
            'Vêtements',
            'Chaussures',
            'Maroquinerie',
            'Sport & Loisirs',
            'Jouets',
            'Librairie & Papeterie',
            'Bricolage',
            'Jardinage',
            'Auto & Moto',
            'Animaux',
            'Bébé & Puériculture',
            'Autre',
        ];
        $added = 0;
        foreach ($defaults as $libelle) {
            $libelle = trim($libelle);
            if ($libelle === '') continue;
            $exists = $this->db->query(
                "SELECT id_categorie FROM app_produits_categories WHERE libelle = ? AND (id_entreprise = ? OR id_entreprise IS NULL)",
                [$libelle, $id_entreprise]
            );
            if (is_array($exists) && count($exists) > 0) continue;
            $this->db->query(
                "INSERT INTO app_produits_categories (id_entreprise, libelle, date_creation) VALUES (?, ?, NOW())",
                [$id_entreprise, $libelle]
            );
            $added++;
        }
        $this->sendResponse(200, ['success' => true, 'data' => ['added' => $added, 'total_defaults' => count($defaults)]]);
    }

    // ---------- POST Categories ----------
    private function addCategorie($id_entreprise, $user_id, $data) {
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') {
            $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        }
        $sql = "INSERT INTO app_produits_categories (id_entreprise, libelle, date_creation) VALUES (?, ?, NOW())";
        $this->db->query($sql, [$id_entreprise, $libelle]);
        $id = (int) $this->db->lastInsertId();
        $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id, 'id_categorie' => $id]]);
    }

    private function updateCategorie($id_entreprise, $role, $data) {
        $id_cat = $this->getInt($data['id_categorie'] ?? $data['id'] ?? null, null);
        if ($id_cat === null) $this->sendResponse(400, ['success' => false, 'error' => 'id_categorie requis']);
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        $sql = "UPDATE app_produits_categories SET libelle = ?, date_modification = NOW() WHERE id_categorie = ?";
        $params = [$libelle, $id_cat];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true]);
    }

    private function deleteCategorie($id_entreprise, $role, $data) {
        $id_cat = $this->getInt($data['id_categorie'] ?? $data['id'] ?? null, null);
        if ($id_cat === null) $this->sendResponse(400, ['success' => false, 'error' => 'id_categorie requis']);
        $sql = "DELETE FROM app_produits_categories WHERE id_categorie = ?";
        $params = [$id_cat];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true]);
    }

    // ---------- POST Sous-categories ----------
    private function addSousCategorie($id_entreprise, $data) {
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        $id_categorie = $this->getInt($data['id_categorie'] ?? null, null);
        $sql = "INSERT INTO app_produits_sous_categories (id_entreprise, id_categorie, libelle, rayon, palier, zone, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $this->db->query($sql, [
            $id_entreprise,
            $id_categorie,
            $libelle,
            $this->getStr($data['rayon'] ?? ''),
            $this->getStr($data['palier'] ?? ''),
            $this->getStr($data['zone'] ?? '')
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id, 'id_sous_categorie' => $id]]);
    }

    private function deleteSousCategorie($id_entreprise, $role, $data) {
        $id_sc = $this->getInt($data['id_sous_categorie'] ?? $data['id'] ?? null, null);
        if ($id_sc === null) $this->sendResponse(400, ['success' => false, 'error' => 'id_sous_categorie requis']);
        $sql = "DELETE FROM app_produits_sous_categories WHERE id_sous_categorie = ?";
        $params = [$id_sc];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true]);
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$api = new ProduitsAPI();
$api->handleRequest();
