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
                        $this->updateProduit($id_entreprise, $role, $data);
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

    // ---------- GET ----------
    private function listProduits($id_entreprise, $role) {
        $sql = "SELECT p.*,
                c.libelle AS categorie_nom,
                s.libelle AS sous_categorie_nom
                FROM app_produits p
                LEFT JOIN app_produits_categories c ON c.id_categorie = p.id_categorie
                LEFT JOIN app_produits_sous_categories s ON s.id_sous_categorie = p.id_sous_categorie
                WHERE 1=1";
        $params = [];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (p.id_entreprise = ? OR p.id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY p.libelle ASC";
        try {
            $rows = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            $sql = "SELECT * FROM app_produits WHERE 1=1";
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

    // ---------- POST Produits ----------
    private function addProduit($id_entreprise, $user_id, $data) {
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') {
            $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        }
        $sql = "INSERT INTO app_produits (
                id_entreprise, libelle, sku, reference, qr_code, description,
                prix_achat, prix_vente, image, unite_mesure, peremption, statut, marque,
                quantite_init, seuil_alerte, id_categorie, id_sous_categorie, id_fournisseur,
                create_by, date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
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
            $this->getInt($data['id_categorie'] ?? null),
            $this->getInt($data['id_sous_categorie'] ?? null),
            $this->getInt($data['id_fournisseur'] ?? null),
            $user_id
        ];
        $this->db->query($sql, $params);
        $id = (int) $this->db->lastInsertId();
        $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id, 'id_produit' => $id]]);
    }

    private function updateProduit($id_entreprise, $role, $data) {
        $id_produit = $this->getInt($data['id_produit'] ?? $data['id'] ?? null, null);
        if ($id_produit === null) {
            $this->sendResponse(400, ['success' => false, 'error' => 'id_produit requis']);
        }
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') {
            $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        }
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
            $this->getInt($data['id_categorie'] ?? null),
            $this->getInt($data['id_sous_categorie'] ?? null),
            $this->getInt($data['id_fournisseur'] ?? null),
            $id_produit
        ];
        if ($role !== 'admin' && $id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
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

    // ---------- POST Categories ----------
    private function addCategorie($id_entreprise, $user_id, $data) {
        $libelle = $this->getStr($data['libelle'] ?? '');
        if ($libelle === '') {
            $this->sendResponse(400, ['success' => false, 'error' => 'Le libelle est requis']);
        }
        $sql = "INSERT INTO app_produits_categories (id_entreprise, libelle, create_by, date_creation) VALUES (?, ?, ?, NOW())";
        $this->db->query($sql, [$id_entreprise, $libelle, $user_id]);
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
