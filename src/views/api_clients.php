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

class ClientsAPI {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        // Ajout d'un paramètre aléatoire à chaque requête
        if (!isset($_GET['_rand'])) {
            $_GET['_rand'] = time() . '_' . bin2hex(random_bytes(4));
        }


        // Nouvelle gestion : récupération sécurisée via le token JWT
        $user = getUserFromToken();
        $user_id = $user['id'];
        $id_entreprise = $user['id_entreprise'];
        $role = in_array('ALL', $user['access']) ? 'admin' : 'user';

        try {
            switch ($action) {
                // CLIENTS
                case 'list_clients':
                    $this->listClients($id_entreprise, $role);
                    break;
                case 'list_users':
                    $this->getUsers();
                    break;
                case 'get_client':
                    $this->getClient();
                    break;
                    // ==================== UTILISATEURS ====================

                    
                case 'add_client':
                    $this->addClient($id_entreprise, $user_id);
                    break;
                case 'update_client':
                    $this->updateClient($id_entreprise, $user_id);
                    break;
                case 'delete_client':
                    $this->deleteClient();
                    break;
                case 'stats_client':
                    $this->getStatsClient();
                    break;
                case 'search_clients':
                    $this->searchClients($id_entreprise, $role);
                    break;

                // CREDITS
                case 'list_credits':
                    $this->listCredits();
                    break;
                case 'get_credit':
                    $this->getCredit();
                    break;
                case 'add_credit':
                    $this->addCredit();
                    break;
                case 'update_credit':
                    $this->updateCredit();
                    break;
                case 'credits_en_cours':
                    $this->getCreditsEnCours($id_entreprise, $role);
                    break;
                case 'credits_retard':
                    $this->getCreditsEnRetard();
                    break;
                case 'top_dettes':
                    $this->getTopDettes();
                    break;

                // PAIEMENTS
                case 'add_paiement':
                    $this->addPaiement();
                    break;
                case 'historique_paiements':
                    $this->getHistoriquePaiements();
                    break;

                // RAPPORTS
                case 'rapport_client':
                    $this->getRapportClient();
                    break;
                case 'rapport_global':
                    $this->getRapportGlobal($id_entreprise, $role);
                    break;

                default:
                    $this->sendResponse(404, [
                        'success' => false,
                        'error' => 'Action non trouvee'
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

    private function getUsers() {
                        $currentUser = getUserFromToken();
                        $idEntreprise = $currentUser['id_entreprise'];

                        try {
                            $sql = "SELECT 
                                        u.id, u.nom, u.prenom, u.email, u.access, u.id_role, u.id_entreprise, e.nom_entreprise
                                    FROM app_utilisateurs u
                                    LEFT JOIN app_entreprises e ON e.id_entreprise = u.id_entreprise
                                    WHERE u.id_entreprise = ?
                                    ORDER BY u.date_create DESC";
                            $users = $this->db->query($sql, [$idEntreprise]);
                            $this->sendResponse(200, [
                                'success' => true,
                                'data' => $users
                            ]);
                        } catch (Exception $e) {
                            $this->sendResponse(500, [
                                'success' => false,
                                'error' => 'Erreur récupération des utilisateurs',
                                'details' => $e->getMessage()
                            ]);
                        }
                    }

    // ==================== CLIENTS ====================

    private function listClients($id_entreprise, $role) {
        // On reçoit $id_entreprise et $role depuis handleRequest
        try {
            $sql = "SELECT * FROM app_clients";
            $params = [];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " WHERE id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY nom, prenom";
            $clients = $this->db->query($sql, $params);
            // Supprimer les champs sensibles
            $clients = array_map(function($c) {
                unset($c['id_entreprise'], $c['cree_par'], $c['modifie_par'], $c['id']);
                // On conserve id_client pour l'affichage
                return $c;
            }, $clients);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $clients
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getClient() {
        $id_client = $_GET['id_client'] ?? null;
        $currentUser = getUserFromToken();
        $id_entreprise = $currentUser['id_entreprise'];
        $role = in_array('ALL', $currentUser['access']) ? 'admin' : 'user';
        if (!$id_client) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        try {
            $sql = "SELECT * FROM app_clients WHERE id_client = ?";
            $params = [$id_client];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $client = $this->db->query($sql, $params);
            if (empty($client)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Client non trouve']);
            }
            // Supprimer les champs sensibles
            $c = $client[0];
            unset($c['id_entreprise'], $c['cree_par'], $c['modifie_par'], $c['id']);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $c
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function addClient($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nom']) || empty($data['telephone'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Nom et telephone requis']);
        }
        try {
            $sql = "INSERT INTO app_clients 
                (id_entreprise, categorie_client, type_client, nom, prenom, nom_entreprise, adresse, ville, pays, telephone, email, statut, total_achats, nombre_commandes, date_creation, create_by, for_shop) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
            $params = [
                $id_entreprise,
                $data['categorie_client'] ?? 'particulier',
                $data['type_client'] ?? 'detaillant',
                $data['nom'],
                $data['prenom'] ?? null,
                $data['nom_entreprise'] ?? null,
                $data['adresse'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? null,
                $data['telephone'],
                $data['email'] ?? null,
                $data['statut'] ?? 'actif',
                $data['total_achats'] ?? 0.00,
                $data['nombre_commandes'] ?? 0,
                $user_id,
                $data['for_shop'] ?? 0
            ];
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Client cree avec succes',
                'data' => []
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function updateClient($id_entreprise, $user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $currentUser = getUserFromToken();
        $id_entreprise = $currentUser['id_entreprise'];
        $role = in_array('ALL', $currentUser['access']) ? 'admin' : 'user';
        if (empty($data['id_client'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        try {
            $sql = "UPDATE app_clients SET 
                id_entreprise = ?, categorie_client = ?, type_client = ?, nom = ?, prenom = ?, nom_entreprise = ?, adresse = ?, ville = ?, pays = ?, telephone = ?, email = ?, statut = ?, total_achats = ?, nombre_commandes = ?, create_by = ?, for_shop = ?
                WHERE id_client = ?";
            $params = [
                $id_entreprise,
                $data['categorie_client'] ?? 'particulier',
                $data['type_client'] ?? 'detaillant',
                $data['nom'],
                $data['prenom'] ?? null,
                $data['nom_entreprise'] ?? null,
                $data['adresse'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? null,
                $data['telephone'],
                $data['email'] ?? null,
                $data['statut'] ?? 'actif',
                $data['total_achats'] ?? 0.00,
                $data['nombre_commandes'] ?? 0,
                $user_id,
                $data['for_shop'] ?? 0,
                $data['id_client']
            ];
            // Sécurisation : on ne met à jour que si le client appartient à l'entreprise
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Client mis à jour avec succès',
                'data' => []
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function deleteClient() {
        $id_client = $_GET['id_client'] ?? null;
        $currentUser = getUserFromToken();
        $id_entreprise = $currentUser['id_entreprise'];
        $role = in_array('ALL', $currentUser['access']) ? 'admin' : 'user';
        if (!$id_client) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }
        try {
            $sql = "DELETE FROM app_clients WHERE id_client = ?";
            $params = [$id_client];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Client supprimé avec succès',
                'data' => []
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getStatsClient() {
        $id = $_GET['id'] ?? null;
        $currentUser = getUserFromToken();
        $id_entreprise = $currentUser['id_entreprise'];
        $role = in_array('ALL', $currentUser['access']) ? 'admin' : 'user';
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
            $sql = "SELECT * FROM app_clients_stats WHERE id_client = ?";
            $params = [$id];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $stats = $this->db->query($sql, $params);
            $this->sendResponse(200, [
                'success' => true,
                'data' => []
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function searchClients($id_entreprise, $role) {
        $query = $_GET['q'] ?? '';
        if (strlen($query) < 2) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Requete trop courte']);
        }
        try {
            $searchTerm = "%{$query}%";
            $sql = "SELECT c.*, 
                    COALESCE(cs.total_dette, 0) as total_dette
                    FROM app_clients c
                    LEFT JOIN app_clients_stats cs ON c.id_client = cs.id_client
                    WHERE (c.nom LIKE ? OR c.prenom LIKE ? OR c.telephone LIKE ?)";
            $params = [$searchTerm, $searchTerm, $searchTerm];
            // Filtrer par entreprise si non admin
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND c.id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY c.nom LIMIT 20";
            $clients = $this->db->query($sql, $params);
            $clients = array_map(function($c) {
                unset($c['id_entreprise'], $c['cree_par'], $c['modifie_par'], $c['id']);
                return $c;
            }, $clients);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $clients
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ==================== CREDITS ====================

    private function listCredits() {
        $clientId = $_GET['client_id'] ?? null;
        
        if (!$clientId) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID client requis']);
        }

        try {
            // Correction ici : id_client
            $sql = "SELECT * FROM app_clients_credits 
                    WHERE id_client = ? 
                    ORDER BY date_achat DESC";
            
            $credits = $this->db->query($sql, [$clientId]);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $credits
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getCredit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
                $sql = "SELECT cc.*, c.nom, c.prenom, c.telephone 
                    FROM app_clients_credits cc
                    JOIN app_clients c ON cc.id_client = c.id_client
                    WHERE cc.id = ?";
            $credit = $this->db->query($sql, [$id]);
            
            if (empty($credit)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Credit non trouve']);
            }

            $this->sendResponse(200, [
                'success' => true,
                'data' => $credit[0]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function addCredit() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['client_id']) || empty($data['description']) || 
            empty($data['montant_total']) || empty($data['date_achat'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Donnees incompletes']);
        }

        try {
            $sql = "INSERT INTO app_clients_credits 
                    (client_id, description, date_achat, montant_total, montant_paye, 
                     date_echeance, numero_reference, observation, utilisateur) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['client_id'],
                $data['description'],
                $data['date_achat'],
                $data['montant_total'],
                $data['montant_paye'] ?? 0,
                $data['date_echeance'] ?? null,
                $data['numero_reference'] ?? null,
                $data['observation'] ?? null,
                $data['utilisateur'] ?? 'Systeme'
            ];
            
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
            
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Credit enregistre avec succes',
                'data' => ['id' => $id]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function updateCredit() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID requis']);
        }

        try {
            $sql = "UPDATE app_clients_credits SET 
                    description = ?, date_achat = ?, montant_total = ?,
                    date_echeance = ?, numero_reference = ?, observation = ?
                    WHERE id = ?";
            
            $params = [
                $data['description'],
                $data['date_achat'],
                $data['montant_total'],
                $data['date_echeance'] ?? null,
                $data['numero_reference'] ?? null,
                $data['observation'] ?? null,
                $data['id']
            ];
            
            $this->db->query($sql, $params);
            
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Credit mis a jour avec succes'
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getCreditsEnCours($id_entreprise, $role) {
        try {
            $sql = "SELECT cc.*, c.nom, c.prenom, c.telephone
                FROM app_clients_credits cc
                JOIN app_clients c ON cc.id_client = c.id_client
                WHERE cc.statut = 'En cours'";
            $params = [];
            if ($role !== 'admin' && $id_entreprise) {
                $sql .= " AND c.id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY cc.montant_restant DESC";
            $credits = $this->db->query($sql, $params);
            // Nettoyage des champs sensibles
            $credits = array_map(function($c) {
                unset($c['id_entreprise'], $c['cree_par'], $c['modifie_par'], $c['id'], $c['user_id'], $c['role'], $c['_rand']);
                return $c;
            }, $credits);
            $this->sendResponse(200, [
                'success' => true,
                'data' => $credits
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getCreditsEnRetard() {
        try {
            $sql = "SELECT * FROM app_clients_credits_retard";
            $credits = $this->db->query($sql);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $credits
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getTopDettes() {
        // Cette fonction est désactivée car la table n'existe pas
        $this->sendResponse(200, [
            'success' => true,
            'data' => []
        ]);
    }

    // ==================== PAIEMENTS ====================

    private function addPaiement() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['credit_id']) || empty($data['client_id']) || 
            !isset($data['montant_paye']) || $data['montant_paye'] <= 0) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Donnees incompletes']);
        }

        try {
            $this->db->beginTransaction();

            // Verifier le credit
            $sqlCredit = "SELECT montant_total, montant_paye, montant_restant 
                         FROM app_clients_credits WHERE id = ?";
            $credit = $this->db->query($sqlCredit, [$data['credit_id']]);
            
            if (empty($credit)) {
                throw new Exception("Credit non trouve");
            }
            
            if ($data['montant_paye'] > $credit[0]['montant_restant']) {
                throw new Exception("Le montant depasse le reste a payer");
            }

            // Enregistrer le paiement
            $sqlPaiement = "INSERT INTO app_clients_paiements 
                           (credit_id, client_id, montant_paye, mode_paiement, 
                            reference_paiement, observation, utilisateur) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $paramsPaiement = [
                $data['credit_id'],
                $data['client_id'],
                $data['montant_paye'],
                $data['mode_paiement'] ?? 'Especes',
                $data['reference_paiement'] ?? null,
                $data['observation'] ?? null,
                $data['utilisateur'] ?? 'Systeme'
            ];

            $this->db->query($sqlPaiement, $paramsPaiement);

            $this->db->commit();

            $nouveauMontantPaye = $credit[0]['montant_paye'] + $data['montant_paye'];
            $creditSolde = ($nouveauMontantPaye >= $credit[0]['montant_total']);

            $this->sendResponse(201, [
                'success' => true,
                'message' => $creditSolde 
                    ? 'Credit entierement solde ! Merci pour le paiement complet.' 
                    : 'Paiement enregistre avec succes',
                'data' => [
                    'montant_paye' => $data['montant_paye'],
                    'nouveau_montant_total_paye' => $nouveauMontantPaye,
                    'reste_a_payer' => $credit[0]['montant_total'] - $nouveauMontantPaye,
                    'credit_solde' => $creditSolde
                ]
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->sendResponse(500, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getHistoriquePaiements() {
        $creditId = $_GET['credit_id'] ?? null;
        
        if (!$creditId) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID credit requis']);
        }

        try {
            $sql = "SELECT * FROM app_clients_paiements 
                    WHERE credit_id = ? 
                    ORDER BY date_paiement DESC";
            
            $paiements = $this->db->query($sql, [$creditId]);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $paiements
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ==================== RAPPORTS ====================

    private function getRapportClient() {
        $clientId = $_GET['client_id'] ?? null;
        
        if (!$clientId) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID client requis']);
        }

        try {
            $sqlClient = "SELECT * FROM app_clients WHERE id_client = ?";
            $client = $this->db->query($sqlClient, [$clientId]);
            
            if (empty($client)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Client non trouve']);
            }

            // Correction ici : id_client
            $sqlStats = "SELECT * FROM app_clients_stats WHERE id_client = ?";
            $stats = $this->db->query($sqlStats, [$clientId]);

            $sqlCredits = "SELECT * FROM app_clients_credits 
                          WHERE id_client = ? 
                          ORDER BY date_achat DESC";
            $credits = $this->db->query($sqlCredits, [$clientId]);

            // Nettoyage des données pour ne pas exposer les id
            $clientData = $client[0];
            unset($clientData['id_entreprise'], $clientData['cree_par'], $clientData['modifie_par'], $clientData['id']);
            $credits = array_map(function($c) {
                unset($c['id_entreprise'], $c['cree_par'], $c['modifie_par'], $c['id']);
                return $c;
            }, $credits);
            $this->sendResponse(200, [
                'success' => true,
                'data' => [
                    'client' => $clientData,
                    'stats' => [],
                    'credits' => $credits
                ]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getRapportGlobal($id_entreprise, $role) {
        try {
            $sqlGlobal = "SELECT 
 COUNT(DISTINCT c.id_client) as total_clients,
 COUNT(DISTINCT CASE WHEN c.statut = 'actif' THEN c.id_client END) as clients_actifs,
 COUNT(DISTINCT cc.id) as total_credits,
 COUNT(DISTINCT CASE WHEN cc.statut = 'En cours' THEN cc.id END) as credits_en_cours,
 COUNT(DISTINCT CASE WHEN cc.statut = 'Solde' THEN cc.id END) as credits_soldes,
 COUNT(DISTINCT CASE WHEN cc.statut = 'En retard' THEN cc.id END) as credits_retard,
 COALESCE(SUM(CASE WHEN cc.statut = 'En cours' THEN cc.montant_restant ELSE 0 END), 0) as total_dette_globale,
 COALESCE(SUM(cc.montant_total), 0) as total_ventes_credit,
 COALESCE(SUM(cc.montant_paye), 0) as total_paiements
 FROM app_clients c
 LEFT JOIN app_clients_credits cc ON c.id_client = cc.id_client";
            $params = [];
            if ($role !== 'admin' && $id_entreprise) {
                $sqlGlobal .= " WHERE c.id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $stats = $this->db->query($sqlGlobal, $params);
            // Table top_dettes supprimée, on retourne un tableau vide
            $topClients = [];
            $creditsRetard = [];
            // Nettoyage explicite du payload
            $statistiques = $stats[0];
            unset($statistiques['user_id'], $statistiques['role'], $statistiques['_rand']);
            $this->sendResponse(200, [
                'success' => true,
                'data' => [
                    'statistiques' => $statistiques,
                    'top_clients' => $topClients,
                    'credits_retard' => $creditsRetard
                ]
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$api = new ClientsAPI();
$api->handleRequest();
