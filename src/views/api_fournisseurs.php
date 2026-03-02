<?php
// API Fournisseurs - Utilise la table app_fournisseurs

$allowed_origins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:5175',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:5174',
    'http://127.0.0.1:5175'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 86400");
header("Content-Type: application/json; charset=UTF-8");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

try {
    require_once __DIR__ . '/config/database.php';
    
    if (!class_exists('Database')) {
        throw new Exception("Classe Database introuvable dans config/database.php");
    }
    
    $db = new Database();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';

    if ($method === 'GET') {
        if ($action === 'get_fournisseur') {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID manquant']);
                exit;
            }
            $sql = "SELECT *, id_fournisseur as id FROM app_fournisseurs WHERE id_fournisseur = ?";
            $result = $db->query($sql, [$id]);
            if (!empty($result)) {
                $result[0]['id'] = $result[0]['id_fournisseur'];
            }
            echo json_encode(['success' => true, 'data' => !empty($result) ? $result[0] : null]);
            exit;
        }
        
        if ($action === 'stats_fournisseur') {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID manquant']);
                exit;
            }
            $stats = [
                'total_commandes' => 0,
                'montant_total' => 0,
                'montant_impaye' => 0,
                'nombre_produits' => 0,
                'evaluation_moyenne' => 0
            ];
            $sql = "SELECT evaluation FROM app_fournisseurs WHERE id_fournisseur = ?";
            $row = $db->query($sql, [$id]);
            if (!empty($row)) {
                $stats['evaluation_moyenne'] = floatval($row[0]['evaluation'] ?? 0);
            }
            echo json_encode(['success' => true, 'data' => $stats]);
            exit;
        }
        
        if ($action === 'list_produits') {
            // Liste des produits d'un fournisseur
            $fournisseur_id = $_GET['fournisseur_id'] ?? null;
            if (!$fournisseur_id) {
                echo json_encode(['success' => false, 'message' => 'ID fournisseur manquant']);
                exit;
            }
            // TODO: Adapter selon votre table de produits
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }
        
        if ($action === 'list_commandes') {
            // Liste des commandes d'un fournisseur
            $fournisseur_id = $_GET['fournisseur_id'] ?? null;
            if (!$fournisseur_id) {
                echo json_encode(['success' => false, 'message' => 'ID fournisseur manquant']);
                exit;
            }
            // TODO: Adapter selon votre table de commandes
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }
        
        if ($action === 'list_fournisseurs') {
            $user_id = $_GET['user_id'] ?? null;
            $id_entreprise = $_GET['id_entreprise'] ?? null;
            
            // REQUÊTE CORRECTE: app_fournisseurs avec create_by (PAS stok_fournisseurs avec user_id)
            $sql = "SELECT *, id_fournisseur as id FROM app_fournisseurs WHERE 1=1";
            $params = [];
            if ($user_id !== null && $user_id !== '') {
                $sql .= " AND (create_by = ? OR create_by IS NULL)";
                $params[] = $user_id;
            }
            if ($id_entreprise !== null && $id_entreprise !== '') {
                $sql .= " AND id_entreprise = ?";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY date_creation DESC, nom ASC";
            
            $result = $db->query($sql, $params);
            foreach ($result as &$fournisseur) {
                if (!isset($fournisseur['id'])) {
                    $fournisseur['id'] = $fournisseur['id_fournisseur'];
                }
                $fournisseur['nombre_produits'] = (int)($fournisseur['nombre_produits'] ?? 0);
                $fournisseur['nombre_commandes'] = (int)($fournisseur['nombre_commandes'] ?? 0);
                $fournisseur['total_dette'] = floatval($fournisseur['total_dette'] ?? 0);
            }
            unset($fournisseur);
            echo json_encode(['success' => true, 'data' => $result]);
            exit;
        }
        
        if ($action === 'rapport_global') {
            $user_id = $_GET['user_id'] ?? null;
            $stats = [
                'total_fournisseurs' => 0,
                'fournisseurs_actifs' => 0,
                'total_produits' => 0,
                'commandes_en_cours' => 0,
                'total_achats' => 0,
                'total_dettes' => 0
            ];
            
            try {
                if ($user_id !== null && $user_id !== '') {
                    $sql = "SELECT 
                        COUNT(*) as total_fournisseurs,
                        COUNT(*) as fournisseurs_actifs,
                        0 as total_produits,
                        0 as commandes_en_cours,
                        0 as total_achats,
                        0 as total_dettes
                        FROM app_fournisseurs f WHERE (f.create_by = ? OR f.create_by IS NULL)";
                    $result = $db->query($sql, [$user_id]);
                } else {
                    $sql = "SELECT 
                        COUNT(*) as total_fournisseurs,
                        COUNT(*) as fournisseurs_actifs,
                        0 as total_produits,
                        0 as commandes_en_cours,
                        0 as total_achats,
                        0 as total_dettes
                        FROM app_fournisseurs f";
                    $result = $db->query($sql, []);
                }
                
                if (!empty($result)) {
                    $stats['total_fournisseurs'] = intval($result[0]['total_fournisseurs'] ?? 0);
                    $stats['fournisseurs_actifs'] = intval($result[0]['fournisseurs_actifs'] ?? 0);
                    $stats['total_produits'] = intval($result[0]['total_produits'] ?? 0);
                    $stats['commandes_en_cours'] = intval($result[0]['commandes_en_cours'] ?? 0);
                    $stats['total_achats'] = floatval($result[0]['total_achats'] ?? 0);
                    $stats['total_dettes'] = floatval($result[0]['total_dettes'] ?? 0);
                }
            } catch (Throwable $e) {
                // En cas d'erreur, retourner des stats à 0
            }
            
            echo json_encode(['success' => true, 'data' => ['statistiques' => $stats]]);
            exit;
        }
        
        // Par défaut : liste des fournisseurs (app_fournisseurs)
        $user_id = $_GET['user_id'] ?? null;
        $id_entreprise = $_GET['id_entreprise'] ?? null;
        $sql = "SELECT *, id_fournisseur as id FROM app_fournisseurs WHERE 1=1";
        $params = [];
        if ($user_id !== null && $user_id !== '') {
            $sql .= " AND (create_by = ? OR create_by IS NULL)";
            $params[] = $user_id;
        }
        if ($id_entreprise !== null && $id_entreprise !== '') {
            $sql .= " AND id_entreprise = ?";
            $params[] = $id_entreprise;
        }
        $sql .= " ORDER BY date_creation DESC, nom ASC";
        $result = $db->query($sql, $params);
        foreach ($result as &$fournisseur) {
            if (!isset($fournisseur['id'])) {
                $fournisseur['id'] = $fournisseur['id_fournisseur'];
            }
        }
        unset($fournisseur);
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'add_produit') {
            // TODO: Implémenter l'ajout de produit
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité non implémentée']);
            exit;
        }
        
        if ($action === 'add_commande') {
            // TODO: Implémenter l'ajout de commande
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité non implémentée']);
            exit;
        }
        
        if ($action === 'add_paiement') {
            // TODO: Implémenter l'ajout de paiement
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité non implémentée']);
            exit;
        }
        
        if ($action === 'add_fournisseur') {
            if (empty($data['nom'])) {
                echo json_encode(['success' => false, 'message' => 'Le nom est requis']);
                exit;
            }
            $create_by = isset($data['user_id']) ? (int)$data['user_id'] : (isset($data['create_by']) ? (int)$data['create_by'] : null);
            $sql = "INSERT INTO app_fournisseurs (id_entreprise, categorie_fournisseur, nom, adresse, contact, email, telephone_commercial, delai_livraison, notes, evaluation, date_creation, create_by, for_shop) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
            $params = [
                isset($data['id_entreprise']) && $data['id_entreprise'] !== '' ? (int)$data['id_entreprise'] : null,
                $data['categorie_fournisseur'] ?? $data['type_fournisseur'] ?? null,
                $data['nom'],
                $data['adresse'] ?? null,
                $data['contact'] ?? $data['telephone'] ?? null,
                $data['email'] ?? null,
                $data['telephone_commercial'] ?? null,
                $data['delai_livraison'] ?? $data['delai_livraison_moyen'] ?? null,
                $data['notes'] ?? null,
                isset($data['evaluation']) ? floatval($data['evaluation']) : (isset($data['note_evaluation']) ? floatval($data['note_evaluation']) : null),
                $create_by,
                isset($data['for_shop']) ? (int)$data['for_shop'] : 0
            ];
            $db->query($sql, $params);
            $fournisseurId = $db->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Fournisseur créé avec succès',
                'data' => ['id' => $fournisseurId, 'id_fournisseur' => $fournisseurId]
            ]);
            exit;
        }
        
        if ($action === 'update_fournisseur') {
            $id = $_GET['id'] ?? $data['id'] ?? $data['id_fournisseur'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID manquant pour la mise à jour']);
                exit;
            }
            $checkSql = "SELECT id_fournisseur FROM app_fournisseurs WHERE id_fournisseur = ?";
            $existing = $db->query($checkSql, [$id]);
            if (empty($existing)) {
                echo json_encode(['success' => false, 'message' => 'Fournisseur non trouvé']);
                exit;
            }
            $allowedFields = ['id_entreprise', 'categorie_fournisseur', 'nom', 'adresse', 'contact', 'email', 'telephone_commercial', 'delai_livraison', 'notes', 'evaluation', 'for_shop'];
            $fieldMapping = ['type_fournisseur' => 'categorie_fournisseur', 'telephone' => 'contact', 'delai_livraison_moyen' => 'delai_livraison', 'note_evaluation' => 'evaluation'];
            $fields = [];
            $params = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = ?";
                    if ($field === 'evaluation') {
                        $params[] = floatval($data[$field]);
                    } elseif (in_array($field, ['id_entreprise', 'for_shop'])) {
                        $params[] = (int)$data[$field];
                    } else {
                        $params[] = $data[$field];
                    }
                }
            }
            foreach ($fieldMapping as $old => $new) {
                if (isset($data[$old]) && !isset($data[$new])) {
                    $fields[] = "{$new} = ?";
                    $params[] = $new === 'evaluation' ? floatval($data[$old]) : $data[$old];
                }
            }
            if (empty($fields)) {
                echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour']);
                exit;
            }
            $fields[] = "date_modification = NOW()";
            $params[] = $id;
            $sql = "UPDATE app_fournisseurs SET " . implode(', ', $fields) . " WHERE id_fournisseur = ?";
            $db->query($sql, $params);
            echo json_encode(['success' => true, 'message' => 'Fournisseur mis à jour avec succès']);
            exit;
        }
        
        if ($action === 'update_produit') {
            // TODO: Implémenter la mise à jour de produit
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité non implémentée']);
            exit;
        }
        
        // Par défaut : ajouter un fournisseur (app_fournisseurs)
        if (empty($data['nom'])) {
            echo json_encode(['success' => false, 'message' => 'Le nom est requis']);
            exit;
        }
        $create_by = isset($data['user_id']) ? (int)$data['user_id'] : (isset($data['create_by']) ? (int)$data['create_by'] : null);
        $sql = "INSERT INTO app_fournisseurs (id_entreprise, categorie_fournisseur, nom, adresse, contact, email, delai_livraison, notes, evaluation, date_creation, create_by, for_shop) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $params = [
            isset($data['id_entreprise']) && $data['id_entreprise'] !== '' ? (int)$data['id_entreprise'] : null,
            $data['categorie_fournisseur'] ?? $data['type_fournisseur'] ?? null,
            $data['nom'],
            $data['adresse'] ?? null,
            $data['contact'] ?? $data['telephone'] ?? null,
            $data['email'] ?? null,
            $data['delai_livraison'] ?? null,
            $data['notes'] ?? null,
            isset($data['evaluation']) ? floatval($data['evaluation']) : null,
            $create_by,
            isset($data['for_shop']) ? (int)$data['for_shop'] : 0
        ];
        $db->query($sql, $params);
        $fournisseurId = $db->lastInsertId();
        echo json_encode(['success' => true, 'data' => ['id' => $fournisseurId, 'id_fournisseur' => $fournisseurId]]);
        exit;
    }

    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        
        // Mettre à jour un fournisseur (app_fournisseurs)
        $allowedFields = ['id_entreprise', 'categorie_fournisseur', 'nom', 'adresse', 'contact', 'email', 'delai_livraison', 'notes', 'evaluation', 'for_shop'];
        $fields = [];
        $params = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $field === 'evaluation' ? floatval($data[$field]) : (in_array($field, ['id_entreprise', 'for_shop']) ? (int)$data[$field] : $data[$field]);
            }
        }
        if (isset($data['telephone']) && !isset($data['contact'])) {
            $fields[] = "contact = ?";
            $params[] = $data['telephone'];
        }
        if (isset($data['type_fournisseur']) && !isset($data['categorie_fournisseur'])) {
            $fields[] = "categorie_fournisseur = ?";
            $params[] = $data['type_fournisseur'];
        }
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour']);
            exit;
        }
        $fields[] = "date_modification = NOW()";
        $params[] = $id;
        $sql = "UPDATE app_fournisseurs SET " . implode(', ', $fields) . " WHERE id_fournisseur = ?";
        $db->query($sql, $params);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'DELETE') {
        $action = $_GET['action'] ?? 'delete';
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        if ($action === 'delete_produit') {
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité non implémentée']);
            exit;
        }
        $sql = "DELETE FROM app_fournisseurs WHERE id_fournisseur = ?";
        $db->query($sql, [$id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    
    // Détection d'erreur avec ancien code (utile pour le diagnostic)
    $errorMessage = $e->getMessage();
    if (stripos($errorMessage, 'stok_fournisseurs') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: Le serveur exécute encore l\'ancien fichier avec stok_fournisseurs. Veuillez déployer la nouvelle version.',
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Mode debug (optionnel via ?debug=1)
    if (isset($_GET['debug'])) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<pre style='background:#f5f5f5;padding:20px;border:2px solid #d00;font-family:monospace;'>";
        echo "<h2 style='color:#d00;'>Erreur API</h2>\n";
        echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n";
        echo "<strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . "\n";
        echo "<strong>Ligne:</strong> " . $e->getLine() . "\n\n";
        echo "<strong>Trace:</strong>\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        exit;
    }
    
    // Mode production : JSON propre
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
