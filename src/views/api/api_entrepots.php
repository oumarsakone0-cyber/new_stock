<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php'; // Pour getUserFromToken()

class EntrepotsAPI {
        // Met à jour les stats de l'entrepôt selon la structure réelle de la table
        private function updateEntrepotStats($entrepot_id, $id_entreprise) {
            // total_produits
            $sql1 = "SELECT COUNT(*) as total_produits, COALESCE(SUM(quantite_actuelle),0) as total_quantite FROM app_entrepots_produits WHERE entrepot_id = ? AND id_entreprise = ?";
            $res1 = $this->db->query($sql1, [$entrepot_id, $id_entreprise]);
            $total_produits = $res1[0]['total_produits'] ?? 0;
            $total_quantite = $res1[0]['total_quantite'] ?? 0;

            // total_entrees
            $sql2 = "SELECT COALESCE(SUM(quantite),0) as total_entrees FROM app_entrepots_mouvements WHERE entrepot_id = ? AND id_entreprise = ? AND type_mouvement = 'Entree'";
            $res2 = $this->db->query($sql2, [$entrepot_id, $id_entreprise]);
            $total_entrees = $res2[0]['total_entrees'] ?? 0;

            // total_sorties
            $sql3 = "SELECT COALESCE(SUM(quantite),0) as total_sorties FROM app_entrepots_mouvements WHERE entrepot_id = ? AND id_entreprise = ? AND type_mouvement = 'Sortie'";
            $res3 = $this->db->query($sql3, [$entrepot_id, $id_entreprise]);
            $total_sorties = $res3[0]['total_sorties'] ?? 0;

            // date_maj
            $date_maj = date('Y-m-d H:i:s');

            // Vérifier si une ligne existe déjà
            $sqlCheck = "SELECT id FROM app_entrepots_stats WHERE entrepot_id = ? AND id_entreprise = ?";
            $check = $this->db->query($sqlCheck, [$entrepot_id, $id_entreprise]);
            if ($check && isset($check[0]['id'])) {
                // Update
                $sqlUpdate = "UPDATE app_entrepots_stats SET total_produits=?, total_quantite=?, total_entrees=?, total_sorties=?, date_maj=? WHERE entrepot_id=? AND id_entreprise=?";
                $this->db->query($sqlUpdate, [$total_produits, $total_quantite, $total_entrees, $total_sorties, $date_maj, $entrepot_id, $id_entreprise]);
            } else {
                // Insert
                $sqlInsert = "INSERT INTO app_entrepots_stats (entrepot_id, id_entreprise, total_produits, total_quantite, total_entrees, total_sorties, date_maj) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $this->db->query($sqlInsert, [$entrepot_id, $id_entreprise, $total_produits, $total_quantite, $total_entrees, $total_sorties, $date_maj]);
            }
        }
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        $user = getUserFromToken();
        $role = in_array('ALL', $user['access']) ? 'admin' : 'user';
        try {
            switch ($action) {
                // ENTREPÔTS
                case 'list_entrepots':
                    $this->listEntrepots($user['id_entreprise'], $role);
                    break;
                case 'get_entrepot':
                    $this->getEntrepot($user['id_entreprise'], $role);
                    break;
                case 'add_entrepot':
                    $this->addEntrepot();
                    break;
                case 'update_entrepot':
                    $this->updateEntrepot();
                    break;
                case 'delete_entrepot':
                    $this->deleteEntrepot($user['id_entreprise'], $user['id']);
                    break;
                case 'stats_entrepot':
                    $this->getStatsEntrepot($user['id_entreprise'], $role);
                    break;
                // PRODUITS
                case 'list_produits':
                    $this->listProduits($user['id_entreprise'], $role);
                    break;
                case 'get_produit':
                    $this->getProduit($user['id_entreprise'], $role);
                    break;
                case 'add_produit':
                    $this->addProduit($user['id_entreprise'], $user['id']);
                    break;
                case 'update_produit':
                    $this->updateProduit($user['id_entreprise'], $user['id']);
                    break;
                case 'delete_produit':
                    $this->deleteProduit($user['id_entreprise'], $user['id']);
                    break;
                case 'alertes':
                    $this->getAlertes($user['id_entreprise'], $role);
                    break;
                // MOUVEMENTS
                case 'list_mouvements':
                    $this->listMouvements($user['id_entreprise'], $role);
                    break;
                case 'add_mouvement':
                    $this->addMouvement($user['id_entreprise'], $user['id']);
                    break;
                case 'rapport_periode':
                    $this->getRapportPeriode($user['id_entreprise'], $role);
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

    // ==================== ENTREPÔTS ====================

    private function listEntrepots($id_entreprise, $role) {
        try {
            $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM app_entrepots_produits WHERE entrepot_id = e.id) as nombre_produits,
                (SELECT COALESCE(SUM(quantite_actuelle), 0) FROM app_entrepots_produits WHERE entrepot_id = e.id) as quantite_totale,
                e.date_modification
                FROM app_entrepots e ";
            $params = [];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " WHERE e.id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY e.nom";
            $entrepots = $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $entrepots
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getEntrepot($id_entreprise, $role) {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        try {
            $sql = "SELECT * FROM app_entrepots WHERE id = ?";
            $params = [$id];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $entrepot = $this->db->query($sql, $params);
            if (empty($entrepot)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Entrepôt non trouvé']);
            }
            $this->sendResponse(200, [
                'success' => true,
                'data' => $entrepot[0]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function addEntrepot() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = getUserFromToken();
        $id_entreprise = $user['id_entreprise'];
        $user_id = $user['id'];
        if (empty($data['nom']) || empty($data['code'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Nom et code requis']);
        }
        try {
            $sql = "INSERT INTO app_entrepots 
                    (nom, code, adresse, ville, pays, telephone, email, responsable_nom, responsable_telephone, 
                     capacite_maximale, superficie, type_stockage, statut, notes, id_entreprise, cree_par, modifie_par) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['nom'],
                $data['code'],
                $data['adresse'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? 'Côte d\'Ivoire',
                $data['telephone'] ?? null,
                $data['email'] ?? null,
                $data['responsable_nom'] ?? null,
                $data['responsable_telephone'] ?? null,
                $data['capacite_maximale'] ?? null,
                $data['superficie'] ?? null,
                $data['type_stockage'] ?? 'General',
                $data['statut'] ?? 'Actif',
                $data['notes'] ?? null,
                $id_entreprise,
                $user_id,
                $user_id
            ];
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Entrepôt créé avec succès',
                'data' => ['id' => $id]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function updateEntrepot() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = getUserFromToken();
        $id_entreprise = $user['id_entreprise'];
        $user_id = $user['id'];
        if (empty($data['id'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        try {
            $date_modification = date('Y-m-d H:i:s');
            $sql = "UPDATE app_entrepots SET 
                    nom = ?, adresse = ?, ville = ?, pays = ?, telephone = ?, email = ?,
                    responsable_nom = ?, responsable_telephone = ?, capacite_maximale = ?,
                    superficie = ?, type_stockage = ?, statut = ?, notes = ?, modifie_par = ?, date_modification = ?
                    WHERE id = ? AND id_entreprise = ?";
            $params = [
                $data['nom'],
                $data['adresse'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? 'Côte d\'Ivoire',
                $data['telephone'] ?? null,
                $data['email'] ?? null,
                $data['responsable_nom'] ?? null,
                $data['responsable_telephone'] ?? null,
                $data['capacite_maximale'] ?? null,
                $data['superficie'] ?? null,
                $data['type_stockage'] ?? 'General',
                $data['statut'] ?? 'Actif',
                $data['notes'] ?? null,
                $user_id,
                $date_modification,
                $data['id'],
                $id_entreprise
            ];
            $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Entrepôt mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function deleteEntrepot($id_entreprise, $user_id) {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
            $sql = "DELETE FROM app_entrepots WHERE id = ? AND id_entreprise = ?";
            $this->db->query($sql, [$id, $id_entreprise]);
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Entrepôt supprimé avec succès'
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getStatsEntrepot($id_entreprise, $role) {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
            $sql = "SELECT * FROM app_entrepots_stats WHERE entrepot_id = ?";
            $params = [$id];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $stats = $this->db->query($sql, $params);
            $defaultStats = [
                'nombre_produits' => 0,
                'quantite_totale' => 0,
                'entrees_totales' => 0,
                'sorties_totales' => 0,
                'date_maj' => null
            ];
            if ($stats && isset($stats[0]) && is_array($stats[0])) {
                $row = $stats[0];
                $result = [
                    'nombre_produits' => (int)($row['total_produits'] ?? 0),
                    'quantite_totale' => (float)($row['total_quantite'] ?? 0),
                    'entrees_totales' => (float)($row['total_entrees'] ?? 0),
                    'sorties_totales' => (float)($row['total_sorties'] ?? 0),
                    'date_maj' => $row['date_maj'] ?? null
                ];
            } else {
                $result = $defaultStats;
            }
            $this->sendResponse(200, [
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ==================== PRODUITS ====================

    private function listProduits($id_entreprise, $role) {
        $entrepotId = $_GET['entrepot_id'] ?? null;
        if (!$entrepotId) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID entrepôt requis']);
        }
        try {
            $sql = "SELECT p.*,
                    CASE 
                        WHEN p.quantite_actuelle = 0 THEN 'Rupture'
                        WHEN p.quantite_actuelle <= p.seuil_alerte THEN 'Stock bas'
                        ELSE 'OK'
                    END as statut
                    FROM app_entrepots_produits p
                    WHERE p.entrepot_id = ?";
            $params = [$entrepotId];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND p.id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY p.nom";
            $produits = $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $produits
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getProduit($id_entreprise, $role) {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        try {
            $sql = "SELECT * FROM app_entrepots_produits WHERE id = ?";
            $params = [$id];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $produit = $this->db->query($sql, $params);
            if (empty($produit)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Produit non trouvé']);
            }
            $this->sendResponse(200, [
                'success' => true,
                'data' => $produit[0]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function addProduit($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['entrepot_id']) || empty($data['reference']) || empty($data['nom'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Données incomplètes']);
        }

        try {
            $sql = "INSERT INTO app_entrepots_produits 
                    (entrepot_id, reference, nom, description, categorie, unite_mesure, 
                     quantite_actuelle, seuil_alerte, emplacement, date_peremption, notes, id_entreprise, cree_par, modifie_par) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['entrepot_id'],
                $data['reference'],
                $data['nom'],
                $data['description'] ?? null,
                $data['categorie'] ?? null,
                $data['unite_mesure'] ?? 'Unite',
                $data['quantite_actuelle'] ?? 0,
                $data['seuil_alerte'] ?? 10,
                $data['emplacement'] ?? null,
                $data['date_peremption'] ?? null,
                $data['notes'] ?? null,
                $id_entreprise,
                $user_id,
                $user_id
            ];
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
            // MAJ stats
            $this->updateEntrepotStats($data['entrepot_id'], $id_entreprise);
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Produit créé avec succès',
                'data' => ['id' => $id]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function updateProduit($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
            $sql = "UPDATE app_entrepots_produits SET 
                    reference = ?, nom = ?, description = ?, categorie = ?, unite_mesure = ?,
                    seuil_alerte = ?, emplacement = ?, date_peremption = ?, notes = ?, modifie_par = ?
                    WHERE id = ? AND id_entreprise = ?";
            $params = [
                $data['reference'],
                $data['nom'],
                $data['description'] ?? null,
                $data['categorie'] ?? null,
                $data['unite_mesure'] ?? 'Unite',
                $data['seuil_alerte'] ?? 10,
                $data['emplacement'] ?? null,
                $data['date_peremption'] ?? null,
                $data['notes'] ?? null,
                $user_id,
                $data['id'],
                $id_entreprise
            ];
            $this->db->query($sql, $params);
            // MAJ stats
            if (!empty($data['entrepot_id'])) {
                $this->updateEntrepotStats($data['entrepot_id'], $id_entreprise);
            }
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Produit mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function deleteProduit($id_entreprise, $user_id) {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
            $sql = "DELETE FROM app_entrepots_produits WHERE id = ? AND id_entreprise = ?";
            $this->db->query($sql, [$id, $id_entreprise]);
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getAlertes($id_entreprise, $role) {
        $entrepotId = $_GET['entrepot_id'] ?? null;
        if (!$entrepotId) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID entrepôt requis']);
        }
        try {
            $sql = "SELECT * FROM stok_entrepots_alertes WHERE entrepot_id = ?";
            $params = [$entrepotId];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $alertes = $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $alertes
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ==================== MOUVEMENTS ====================

    private function listMouvements($id_entreprise, $role) {
        $entrepotId = $_GET['entrepot_id'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;
        if (!$entrepotId) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID entrepôt requis']);
        }
        try {
            $sql = "SELECT m.*, p.reference, p.nom as produit_nom, p.unite_mesure
                    FROM app_entrepots_mouvements m
                    JOIN app_entrepots_produits p ON m.produit_id = p.id
                    WHERE m.entrepot_id = ?";
            $params = [$entrepotId];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND m.id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            if ($dateDebut && $dateFin) {
                $sql .= " AND DATE(m.date_mouvement) BETWEEN ? AND ?";
                $params[] = $dateDebut;
                $params[] = $dateFin;
            }
            $sql .= " ORDER BY m.date_mouvement DESC LIMIT 100";
            $mouvements = $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $mouvements
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function addMouvement($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['entrepot_id']) || empty($data['produit_id']) || 
            empty($data['type_mouvement']) || empty($data['quantite']) || empty($data['motif'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Données incomplètes']);
        }

        try {
            $this->db->beginTransaction();
            // Récupérer la quantité actuelle du produit
            $sqlStock = "SELECT quantite_actuelle, nom FROM app_entrepots_produits WHERE id = ? AND id_entreprise = ?";
            $produit = $this->db->query($sqlStock, [$data['produit_id'], $id_entreprise]);
            if (empty($produit)) {
                throw new Exception("Produit non trouvé");
            }
            $quantite_actuelle = (float)$produit[0]['quantite_actuelle'];
            $nouvelle_quantite = $quantite_actuelle;
            if ($data['type_mouvement'] === 'Entree') {
                $nouvelle_quantite += (float)$data['quantite'];
            } else if ($data['type_mouvement'] === 'Sortie') {
                if ($quantite_actuelle < (float)$data['quantite']) {
                    throw new Exception("Stock insuffisant pour " . $produit[0]['nom']);
                }
                $nouvelle_quantite -= (float)$data['quantite'];
            }
            // Mettre à jour le stock du produit
            $sqlUpdateStock = "UPDATE app_entrepots_produits SET quantite_actuelle = ? WHERE id = ? AND id_entreprise = ?";
            $this->db->query($sqlUpdateStock, [$nouvelle_quantite, $data['produit_id'], $id_entreprise]);

            $sql = "INSERT INTO app_entrepots_mouvements 
                    (entrepot_id, produit_id, type_mouvement, quantite, motif, 
                     destination_ou_provenance, responsable_nom, beneficiaire_nom,
                     numero_document, reference_externe, observation, utilisateur, id_entreprise, cree_par) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['entrepot_id'],
                $data['produit_id'],
                $data['type_mouvement'],
                $data['quantite'],
                $data['motif'],
                $data['destination_ou_provenance'] ?? null,
                $data['responsable_nom'] ?? null,
                $data['beneficiaire_nom'] ?? null,
                $data['numero_document'] ?? null,
                $data['reference_externe'] ?? null,
                $data['observation'] ?? null,
                $user_id,
                $id_entreprise,
                $user_id
            ];
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
            // MAJ stats
            $this->updateEntrepotStats($data['entrepot_id'], $id_entreprise);
            $this->db->commit();
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Mouvement enregistré avec succès',
                'data' => ['id' => $id]
            ]);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendResponse(500, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getRapportPeriode($id_entreprise, $role) {
        $entrepotId = $_GET['entrepot_id'] ?? null;
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;
        if (!$entrepotId || !$dateDebut || !$dateFin) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Paramètres incomplets']);
        }
        try {
            // Info entrepôt
            $sqlEntrepot = "SELECT * FROM stok_entrepots WHERE id = ?";
            $paramsEntrepot = [$entrepotId];
            if ($role !== 'admin' && $id_entreprise) {
                $sqlEntrepot .= " AND id_entreprise = ?";
                $paramsEntrepot[] = $id_entreprise;
            }
            $entrepot = $this->db->query($sqlEntrepot, $paramsEntrepot);
            if (empty($entrepot)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Entrepôt non trouvé']);
            }
            // Mouvements de la période
            $sqlMouvements = "SELECT m.*, p.reference, p.nom as produit_nom, p.unite_mesure
                             FROM stok_entrepots_mouvements m
                             JOIN stok_entrepots_produits p ON m.produit_id = p.id
                             WHERE m.entrepot_id = ?";
            $paramsMouvements = [$entrepotId];
            if ($role !== 'admin' && $id_entreprise) {
                $sqlMouvements .= " AND m.id_entreprise = ?";
                $paramsMouvements[] = $id_entreprise;
            }
            $sqlMouvements .= " AND DATE(m.date_mouvement) BETWEEN ? AND ?
                             ORDER BY m.date_mouvement DESC";
            $paramsMouvements[] = $dateDebut;
            $paramsMouvements[] = $dateFin;
            $mouvements = $this->db->query($sqlMouvements, $paramsMouvements);
            // Statistiques
            $totalEntrees = 0;
            $totalSorties = 0;
            $nombreEntrees = 0;
            $nombreSorties = 0;
            foreach ($mouvements as $mvt) {
                if ($mvt['type_mouvement'] === 'Entree') {
                    $totalEntrees += $mvt['quantite'];
                    $nombreEntrees++;
                } else {
                    $totalSorties += $mvt['quantite'];
                    $nombreSorties++;
                }
            }
            $this->sendResponse(200, [
                'success' => true,
                'data' => [
                    'entrepot' => $entrepot[0],
                    'periode' => [
                        'date_debut' => $dateDebut,
                        'date_fin' => $dateFin,
                        'libelle' => $this->getPeriodeLibelle($dateDebut, $dateFin)
                    ],
                    'mouvements' => $mouvements,
                    'statistiques' => [
                        'total_entrees' => $totalEntrees,
                        'total_sorties' => $totalSorties,
                        'nombre_entrees' => $nombreEntrees,
                        'nombre_sorties' => $nombreSorties,
                        'nombre_mouvements' => count($mouvements),
                        'solde_net' => $totalEntrees - $totalSorties
                    ]
                ]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getPeriodeLibelle($dateDebut, $dateFin) {
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        return 'Du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y');
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$api = new EntrepotsAPI();
$api->handleRequest();