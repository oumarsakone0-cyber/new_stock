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

class MagasinsAPI {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        $user = getUserFromToken();
        $user_id = $user['id'];
        $id_entreprise = $user['id_entreprise'];
        $role = in_array('ALL', $user['access']) ? 'admin' : 'user';
        try {
            switch ($action) {
                case 'list':
                    $this->listMagasins($id_entreprise, $role);
                    break;
                case 'get':
                    $this->getMagasin($id_entreprise, $role);
                    break;
                case 'add':
                    $this->addMagasin($id_entreprise, $user_id);
                    break;
                case 'update':
                    $this->updateMagasin($id_entreprise, $user_id, $role);
                    break;
                case 'delete':
                    $this->deleteMagasin($id_entreprise, $role);
                    break;
                // Gestion du stock magasin
                case 'etat_stock':
                    $this->etatStock($id_entreprise, $role);
                    break;
                case 'mouvements_stock':
                    $this->mouvementsStock($id_entreprise, $role);
                    break;
                case 'stats_stock':
                    $this->statsStock($id_entreprise, $role);
                    break;
                case 'entree_stock':
                    $this->entreeStock($id_entreprise, $user_id);
                    break;
                case 'sortie_stock':
                    $this->sortieStock($id_entreprise, $user_id);
                    break;
                default:
                    $this->sendResponse(404, [
                        'success' => false,
                            // --- GESTION STOCK ---
                            private function etatStock($id_entreprise, $role) {
                                $magasin_id = $_GET['magasin_id'] ?? null;
                                if (!$magasin_id) {
                                    $this->sendResponse(400, ['success' => false, 'error' => 'ID magasin requis']);
                                }
                                $sql = "SELECT s.id, s.produit_id, p.nom, p.sku, s.stock_actuel, s.seuil_alerte, s.statut, s.derniere_maj FROM app_stock_magasins s JOIN app_produits p ON s.produit_id = p.id WHERE s.magasin_id = ?";
                                $params = [$magasin_id];
                                $result = $this->db->query($sql, $params);
                                $this->sendResponse(200, ['success' => true, 'data' => $result]);
                            }

                            private function mouvementsStock($id_entreprise, $role) {
                                $magasin_id = $_GET['magasin_id'] ?? null;
                                if (!$magasin_id) {
                                    $this->sendResponse(400, ['success' => false, 'error' => 'ID magasin requis']);
                                }
                                $sql = "SELECT m.*, p.nom AS produit_nom FROM app_stock_mouvements m JOIN app_produits p ON m.produit_id = p.id WHERE m.magasin_id = ? ORDER BY m.date_mouvement DESC LIMIT 100";
                                $params = [$magasin_id];
                                $result = $this->db->query($sql, $params);
                                $this->sendResponse(200, ['success' => true, 'data' => $result]);
                            }

                            private function statsStock($id_entreprise, $role) {
                                $magasin_id = $_GET['magasin_id'] ?? null;
                                if (!$magasin_id) {
                                    $this->sendResponse(400, ['success' => false, 'error' => 'ID magasin requis']);
                                }
                                $sql = "SELECT COUNT(*) AS total_produits, SUM(stock_actuel) AS stock_total, SUM(CASE WHEN statut = 'Stock bas' THEN 1 ELSE 0 END) AS stock_bas FROM app_stock_magasins WHERE magasin_id = ?";
                                $params = [$magasin_id];
                                $stats = $this->db->query($sql, $params)[0] ?? [];
                                // Entrées/sorties du mois
                                $mois = date('Y-m-01');
                                $sqlEntrees = "SELECT SUM(quantite) AS entrees_mois FROM app_stock_mouvements WHERE magasin_id = ? AND type_mouvement = 'entree' AND date_mouvement >= ?";
                                $sqlSorties = "SELECT SUM(quantite) AS sorties_mois FROM app_stock_mouvements WHERE magasin_id = ? AND type_mouvement = 'sortie' AND date_mouvement >= ?";
                                $stats['entrees_mois'] = $this->db->query($sqlEntrees, [$magasin_id, $mois])[0]['entrees_mois'] ?? 0;
                                $stats['sorties_mois'] = $this->db->query($sqlSorties, [$magasin_id, $mois])[0]['sorties_mois'] ?? 0;
                                $this->sendResponse(200, ['success' => true, 'data' => $stats]);
                            }

                            private function entreeStock($id_entreprise, $user_id) {
                                $data = json_decode(file_get_contents('php://input'), true);
                                if (empty($data['produit_id']) || empty($data['magasin_id']) || empty($data['quantite'])) {
                                    $this->sendResponse(400, ['success' => false, 'error' => 'Champs requis manquants']);
                                }
                                // Ajout mouvement
                                $sql = "INSERT INTO app_stock_mouvements (magasin_id, produit_id, type_mouvement, quantite, motif, notes, utilisateur, date_mouvement) VALUES (?, ?, 'entree', ?, ?, ?, ?, NOW())";
                                $params = [
                                    $data['magasin_id'],
                                    $data['produit_id'],
                                    $data['quantite'],
                                    $data['motif'] ?? null,
                                    $data['notes'] ?? null,
                                    $data['utilisateur'] ?? 'Système'
                                ];
                                $this->db->query($sql, $params);
                                // MAJ stock
                                $sql2 = "UPDATE app_stock_magasins SET stock_actuel = stock_actuel + ?, derniere_maj = NOW() WHERE magasin_id = ? AND produit_id = ?";
                                $this->db->query($sql2, [$data['quantite'], $data['magasin_id'], $data['produit_id']]);
                                $this->sendResponse(200, ['success' => true, 'message' => 'Entrée de stock enregistrée']);
                            }

                            private function sortieStock($id_entreprise, $user_id) {
                                $data = json_decode(file_get_contents('php://input'), true);
                                if (empty($data['produit_id']) || empty($data['magasin_id']) || empty($data['quantite'])) {
                                    $this->sendResponse(400, ['success' => false, 'error' => 'Champs requis manquants']);
                                }
                                // Ajout mouvement
                                $sql = "INSERT INTO app_stock_mouvements (magasin_id, produit_id, type_mouvement, quantite, motif, notes, utilisateur, date_mouvement) VALUES (?, ?, 'sortie', ?, ?, ?, ?, NOW())";
                                $params = [
                                    $data['magasin_id'],
                                    $data['produit_id'],
                                    $data['quantite'],
                                    $data['motif'] ?? null,
                                    $data['notes'] ?? null,
                                    $data['utilisateur'] ?? 'Système'
                                ];
                                $this->db->query($sql, $params);
                                // MAJ stock
                                $sql2 = "UPDATE app_stock_magasins SET stock_actuel = stock_actuel - ?, derniere_maj = NOW() WHERE magasin_id = ? AND produit_id = ?";
                                $this->db->query($sql2, [$data['quantite'], $data['magasin_id'], $data['produit_id']]);
                                $this->sendResponse(200, ['success' => true, 'message' => 'Sortie de stock enregistrée']);
                            }
                        'error' => 'Action non trouvée'
                    ]);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur serveur',
                'details' => $e->getMessage()
            ]);
        }
    }

    private function listMagasins($id_entreprise, $role) {
        $sql = "SELECT * FROM app_magasins";
        $params = [];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " WHERE id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY nom";
        $magasins = $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'data' => $magasins]);
    }

    private function getMagasin($id_entreprise, $role) {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        $sql = "SELECT * FROM app_magasins WHERE id = ?";
        $params = [$id];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $magasin = $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'data' => $magasin[0] ?? null]);
    }

    private function addMagasin($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nom'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Nom du magasin requis']);
        }
        $sql = "INSERT INTO app_magasins (id_entreprise, nom, adresse, telephone, couleur, actif, date_creation, cree_par) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $params = [
            $id_entreprise,
            $data['nom'],
            $data['adresse'] ?? null,
            $data['telephone'] ?? null,
            $data['couleur'] ?? '#10b981',
            isset($data['actif']) ? $data['actif'] : 1,
            $user_id
        ];
        $this->db->query($sql, $params);
        $id_nouveau_magasin = $this->db->lastInsertId();
        $this->sendResponse(201, [
            'success' => true,
            'id' => $id_nouveau_magasin,
            'message' => 'Magasin créé avec succès'
        ]);
    }

    private function updateMagasin($id_entreprise, $user_id, $role) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        $sql = "UPDATE app_magasins SET nom=?, adresse=?, telephone=?, couleur=?, actif=?, date_modification=NOW(), modifie_par=? WHERE id=?";
        $params = [
            $data['nom'],
            $data['adresse'] ?? null,
            $data['telephone'] ?? null,
            $data['couleur'] ?? '#10b981',
            isset($data['actif']) ? $data['actif'] : 1,
            $user_id,
            $data['id']
        ];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'message' => 'Magasin mis à jour avec succès']);
    }

    private function deleteMagasin($id_entreprise, $role) {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        $sql = "DELETE FROM app_magasins WHERE id = ?";
        $params = [$id];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'message' => 'Magasin supprimé avec succès']);
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$api = new MagasinsAPI();
$api->handleRequest();
