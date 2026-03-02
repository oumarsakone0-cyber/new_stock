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

class PointsVenteAPI {
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
                    $this->listPointsVente($id_entreprise, $role);
                    break;
                case 'get':
                    $this->getPointVente($id_entreprise, $role);
                    break;
                case 'add':
                    $this->addPointVente($id_entreprise, $user_id);
                    break;
                case 'update':
                    $this->updatePointVente($id_entreprise, $user_id, $role);
                    break;
                case 'delete':
                    $this->deletePointVente($id_entreprise, $role);
                    break;
                default:
                    $this->sendResponse(404, [
                        'success' => false,
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

    private function listPointsVente($id_entreprise, $role) {
        $sql = "SELECT * FROM app_points_vente";
        $params = [];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " WHERE id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY nom";
        $points = $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'data' => $points]);
    }

    private function getPointVente($id_entreprise, $role) {
        $id = $_GET['id_pdv'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        $sql = "SELECT * FROM app_points_vente WHERE id_pdv = ?";
        $params = [$id];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $points = $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'data' => $points[0] ?? null]);
    }

    private function addPointVente($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nom']) || empty($data['ville']) || empty($data['responsable']) || empty($data['contact'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Champs obligatoires manquants']);
        }
        $sql = "INSERT INTO app_points_vente (id_entreprise, nom, ville, quartier, adresse, responsable, contact, email, statut, objectif_jour, type_pdv, notes, cree_par, modifie_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $id_entreprise,
            $data['nom'],
            $data['ville'],
            $data['quartier'] ?? '',
            $data['adresse'] ?? '',
            $data['responsable'],
            $data['contact'],
            $data['email'] ?? '',
            $data['statut'] ?? 'actif',
            $data['objectif_jour'] ?? 0,
            $data['type_pdv'] ?? '',
            $data['notes'] ?? '',
            $user_id,
            null // modifie_par à la création
        ];
        $this->db->query($sql, $params);
        $this->sendResponse(201, ['success' => true, 'message' => 'Point de vente créé avec succès']);
    }

    private function updatePointVente($id_entreprise, $user_id, $role) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_pdv'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        $sql = "UPDATE app_points_vente SET nom=?, ville=?, quartier=?, adresse=?, responsable=?, contact=?, email=?, statut=?, objectif_jour=?, type_pdv=?, notes=?, modifie_par=? WHERE id_pdv=?";
        $params = [
            $data['nom'],
            $data['ville'],
            $data['quartier'] ?? '',
            $data['adresse'] ?? '',
            $data['responsable'],
            $data['contact'],
            $data['email'] ?? '',
            $data['statut'] ?? 'actif',
            $data['objectif_jour'] ?? 0,
            $data['type_pdv'] ?? '',
            $data['notes'] ?? '',
            $user_id,
            $data['id_pdv']
        ];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'message' => 'Point de vente mis à jour avec succès']);
    }

    private function deletePointVente($id_entreprise, $role) {
        $id = $_GET['id_pdv'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        $sql = "DELETE FROM app_points_vente WHERE id_pdv = ?";
        $params = [$id];
        if ($role !== 'admin' && $id_entreprise) {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $this->db->query($sql, $params);
        $this->sendResponse(200, ['success' => true, 'message' => 'Point de vente supprimé avec succès']);
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$api = new PointsVenteAPI();
$api->handleRequest();
