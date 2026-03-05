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

class MagasinProduitsAPI {
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
        try {
            if ($method === 'GET') {
                switch ($action) {
                    case 'list':
                        $this->listMagasinProduits();
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
                    case 'add':
                        $this->addMagasinProduit($data);
                        break;
                    case 'update':
                        $this->updateMagasinProduit($data);
                        break;
                    case 'delete':
                        $this->deleteMagasinProduit($data);
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
    private function listMagasinProduits() {
        $id_magasin = $this->getInt($_GET['magasin_id'] ?? null);
        if (!$id_magasin) $this->sendResponse(400, ['success' => false, 'error' => 'magasin_id requis']);
        $sql = "SELECT mp.*, p.libelle AS produit_nom, p.sku, p.description, p.marque, p.image, p.id_categorie, p.id_sous_categorie, p.prix_achat AS prix_achat_ref, p.prix_vente AS prix_vente_ref FROM app_magasin_produits mp LEFT JOIN app_produits p ON p.id_produit = mp.id_produit WHERE mp.id_magasin = ? AND mp.statut != 'archive' ORDER BY mp.date_ajout DESC";
        $rows = $this->db->query($sql, [$id_magasin]);
        if (!is_array($rows)) $rows = [];
        foreach ($rows as &$r) {
            $r['id'] = $r['id_magasin_produit'] ?? $r['id'] ?? null;
        }
        unset($r);
        $this->sendResponse(200, ['success' => true, 'data' => $rows]);
    }
    private function addMagasinProduit($data) {
        $id_magasin = $this->getInt($data['id_magasin'] ?? null);
        $id_produit = $this->getInt($data['id_produit'] ?? null);
        $stock = $this->getInt($data['stock'] ?? 0);
        $prix_vente = (float)($data['prix_vente'] ?? 0);
        $seuil_alerte = $this->getInt($data['seuil_alerte'] ?? 0);
        if (!$id_magasin || !$id_produit) $this->sendResponse(400, ['success' => false, 'error' => 'id_magasin et id_produit requis']);
        $sql = "INSERT INTO app_magasin_produits (id_magasin, id_produit, stock, prix_vente, seuil_alerte, statut, date_ajout) VALUES (?, ?, ?, ?, ?, 'actif', NOW())";
        $this->db->query($sql, [$id_magasin, $id_produit, $stock, $prix_vente, $seuil_alerte]);
        $id = (int) $this->db->lastInsertId();
        $this->sendResponse(200, ['success' => true, 'data' => ['id' => $id]]);
    }
    private function updateMagasinProduit($data) {
        $id = $this->getInt($data['id'] ?? null);
        $stock = $this->getInt($data['stock'] ?? 0);
        $prix_vente = (float)($data['prix_vente'] ?? 0);
        $seuil_alerte = $this->getInt($data['seuil_alerte'] ?? 0);
        if (!$id) $this->sendResponse(400, ['success' => false, 'error' => 'id requis']);
        $sql = "UPDATE app_magasin_produits SET stock = ?, prix_vente = ?, seuil_alerte = ?, date_modification = NOW() WHERE id_magasin_produit = ?";
        $this->db->query($sql, [$stock, $prix_vente, $seuil_alerte, $id]);
        $this->sendResponse(200, ['success' => true]);
    }
    private function deleteMagasinProduit($data) {
        $id = $this->getInt($data['id'] ?? null);
        if (!$id) $this->sendResponse(400, ['success' => false, 'error' => 'id requis']);
        $sql = "UPDATE app_magasin_produits SET statut = 'archive', date_modification = NOW() WHERE id_magasin_produit = ?";
        $this->db->query($sql, [$id]);
        $this->sendResponse(200, ['success' => true]);
    }
    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$api = new MagasinProduitsAPI();
$api->handleRequest();
