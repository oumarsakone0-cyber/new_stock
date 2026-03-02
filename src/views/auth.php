<?php
require_once __DIR__ . '/libs/JWT.php';
require_once __DIR__ . '/libs/Key.php';
require_once __DIR__ . '/config/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$JWT_SECRET = "4e9f1c7b2a6d8f3c5e9a0b7d4c2f8a6e1b3c9d7e5f0a2b4c6d8e1f3a5b7c9d2";

/**
 * Récupère l'utilisateur connecté via le token JWT
 */
function getUserFromToken() {
    global $JWT_SECRET;

    // Récupérer le header Authorization de différentes manières (compatibilité)
    $authHeader = '';
    
    // Méthode 1: getallheaders() (fonctionne sur Apache)
    $headers = getallheaders();
    if ($headers && isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
    
    // Méthode 2: $_SERVER (fonctionne sur Nginx et autres serveurs)
    if (empty($authHeader)) {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
    }
    
    // Méthode 3: apache_request_headers() si disponible
    if (empty($authHeader) && function_exists('apache_request_headers')) {
        $apacheHeaders = apache_request_headers();
        if (isset($apacheHeaders['Authorization'])) {
            $authHeader = $apacheHeaders['Authorization'];
        }
    }

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        // En mode production, ne pas exposer les détails de debug
        $isDebug = isset($_GET['debug']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
        
        $errorData = [
            'success' => false,
            'error' => 'Token manquant ou invalide'
        ];
        
        if ($isDebug) {
            $errorData['debug'] = [
                'has_headers' => !empty($headers),
                'server_auth' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'not set',
                'redirect_auth' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'not set',
                'all_headers' => $headers ?? [],
                'all_server_keys' => array_keys($_SERVER)
            ];
        }
        
        sendResponse(401, $errorData);
    }

    $jwt = $matches[1];

    try {
        $decoded = JWT::decode($jwt, new Key($JWT_SECRET, 'HS256'));
        $userId = $decoded->data->id;

        $db = new Database();
        // IMPORTANT: Ne pas inclure 'role' dans le SELECT car cette colonne n'existe pas dans app_utilisateurs
        // Le rôle est déterminé dynamiquement via: in_array('ALL', $user['access']) ? 'admin' : 'user'
        $sql = "SELECT id, nom, prenom, email, id_entreprise, access 
                FROM app_utilisateurs 
                WHERE id = ? AND statut = 'actif'";
        $users = $db->query($sql, [$userId]);

        if (empty($users)) {
            sendResponse(404, [
                'success' => false,
                'error' => 'Utilisateur non trouvé'
            ]);
        }

        $user = $users[0];
        $user['access'] = json_decode($user['access'], true) ?? [];
        return $user;

    } catch (Exception $e) {
        sendResponse(401, [
            'success' => false,
            'error' => 'Token invalide ou expiré',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * Envoie une réponse JSON
 */
function sendResponse($code, $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
