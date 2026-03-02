<?php
$allowed_origins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:5175',
    'http://localhost:5176',
    'http://localhost:5177',
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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

require_once 'Mailer.php';

require_once 'config/database.php';
require_once __DIR__ . '/libs/JWT.php';
require_once __DIR__ . '/libs/Key.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthAPI {
    private $db;

    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur de connexion à la base de données',
                'details' => $e->getMessage()
            ]);
        }
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'login';

        switch ($method) {
            case 'POST':
                $this->handlePost($action);
                break;
            case 'GET':
                $this->handleGet($action);
                break;
            default:
                $this->sendResponse(405, ['success' => false, 'error' => 'Méthode non autorisée']);
        }
    }

    private function handlePost($action) {
        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'activate':
                $this->activateAccount();
                break;
            case 'set_password':           // ← NOUVEAU
                $this->setPassword();
                break;
            case 'register2':
                $this->register2();
                break;
            default:
                $this->sendResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
        }
    }

    private function handleGet($action) {
        switch ($action) {
            case 'verify':
                $this->verifyToken();
                break;
            case 'user':
                $this->getUserInfo();
                break;
            default:
                $this->sendResponse(404, ['success' => false, 'error' => 'Action GET inconnue']);
        }
    }

    /**
     * POST - Connexion utilisateur
     */
    private function login() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['success' => false, 'error' => 'JSON invalide']);
        }

        $email    = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->sendResponse(400, [
                'success' => false,
                'error'   => 'Email et mot de passe requis'
            ]);
        }

        try {
            // JOIN pour récupérer nom_entreprise en même temps
            $sql = "SELECT u.*, e.nom_entreprise
                    FROM app_utilisateurs u
                    LEFT JOIN app_entreprises e ON e.id_entreprise = u.id_entreprise
                    WHERE u.email = ?";

            $users = $this->db->query($sql, [$email]);

            if (empty($users)) {
                $this->sendResponse(401, [
                    'success' => false,
                    'error'   => 'Email ou mot de passe incorrect'
                ]);
            }

            $user = $users[0];

            // Vérifier le mot de passe AVANT de tester le statut
            if (!password_verify($password, $user['mot_de_passe'])) {
                $this->sendResponse(401, [
                    'success' => false,
                    'error'   => 'Email ou mot de passe incorrect'
                ]);
            }

            // Gestion des statuts
            switch ($user['statut']) {

                case 'actif':
                    break;

                case 'validation':
                    $this->sendResponse(403, [
                        'success'         => false,
                        'error'           => 'Votre compte n\'est pas encore activé.',
                        'error_code'      => 'ACCOUNT_PENDING_VALIDATION',
                        'message'         => 'Veuillez entrer le code d\'activation à 4 caractères reçu par email, ou cliquer sur le lien d\'activation dans le mail.',
                        'requires_action' => 'activation_code',
                        'email'           => $user['email']
                    ]);
                    break;

                case 'supprime':
                    $this->sendResponse(403, [
                        'success'    => false,
                        'error'      => 'Ce compte a été supprimé.',
                        'error_code' => 'ACCOUNT_DELETED',
                        'message'    => 'Votre compte a été supprimé. Si vous pensez qu\'il s\'agit d\'une erreur, contactez le support.'
                    ]);
                    break;

                case 'inactif':
                    $this->sendResponse(403, [
                        'success'    => false,
                        'error'      => 'Votre compte est restreint.',
                        'error_code' => 'ACCOUNT_INACTIVE',
                        'message'    => 'L\'accès à votre compte a été restreint. Contactez votre administrateur pour plus d\'informations.'
                    ]);
                    break;

                default:
                    $this->sendResponse(403, [
                        'success'    => false,
                        'error'      => 'Statut de compte invalide.',
                        'error_code' => 'ACCOUNT_STATUS_UNKNOWN'
                    ]);
                    break;
            }

            // Mettre à jour le last_login
            $updateSql = "UPDATE app_utilisateurs SET last_login = NOW() WHERE id = ?";
            $this->db->query($updateSql, [$user['id']]);

            // Générer le JWT avec toutes les infos
            $token     = $this->generateJWT($user);
            $accesData = $this->parseAcces($user);

            // Nettoyer les données sensibles avant de renvoyer
            unset($user['mot_de_passe']);
            unset($user['token']);

            $user['acces'] = $accesData;
            $user['token'] = $token;

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Connexion réussie',
                'data'    => $user
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error'   => 'Erreur lors de la connexion',
                'details' => $e->getMessage()
            ]);
        }
    }

/**
 * Génération JWT
 */
private function generateJWT($user) {
    $secret_key      = "4e9f1c7b2a6d8f3c5e9a0b7d4c2f8a6e1b3c9d7e5f0a2b4c6d8e1f3a5b7c9d2";
    $issuer_claim    = "rg_api_auth";
    $audience_claim  = "rg_app_users";
    $issuedat_claim  = time();
    $notbefore_claim = $issuedat_claim;
    $expire_claim    = $issuedat_claim + (60 * 60 * 24); // 24h

    $payload = [
        "iss" => $issuer_claim,
        "aud" => $audience_claim,
        "iat" => $issuedat_claim,
        "nbf" => $notbefore_claim,
        "exp" => $expire_claim,
        "data" => [
            "id"             => $user['id'],
            "nom"            => $user['nom'],
            "prenom"         => $user['prenom'],
            "email"          => $user['email'],
            "role"           => $user['id_role']           ?? null,
            "id_entreprise"  => $user['id_entreprise']  ?? null,
            "nom_entreprise" => $user['nom_entreprise'] ?? null,
        ]
    ];

    return JWT::encode($payload, $secret_key, 'HS256');
}

    /**
     * POST - Valider le code OTP d'activation (4 caractères)
     * ⚠️  Ne passe PAS encore en 'actif' — attend la définition du mot de passe
     */
    private function activateAccount() {
        $input = json_decode(file_get_contents('php://input'), true);

        $email = trim($input['email'] ?? '');
        $code  = strtoupper(trim($input['code'] ?? ''));

        if (empty($email) || empty($code)) {
            $this->sendResponse(400, [
                'success' => false,
                'error'   => 'Email et code d\'activation requis'
            ]);
        }

        if (strlen($code) !== 4) {
            $this->sendResponse(400, [
                'success' => false,
                'error'   => 'Le code d\'activation doit contenir 4 caractères'
            ]);
        }

        try {
            $sql   = "SELECT * FROM app_utilisateurs WHERE email = ? AND statut = 'validation'";
            $users = $this->db->query($sql, [$email]);

            if (empty($users)) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error'   => 'Aucun compte en attente de validation pour cet email'
                ]);
            }

            $user = $users[0];

            // Vérifier le code stocké dans la colonne `token`
            if (strtoupper($user['token']) !== $code) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error'   => 'Code d\'activation incorrect'
                ]);
            }

            // Marquer le code comme consommé SANS encore activer le compte.
            // Le statut passera en 'actif' uniquement après set_password.
            $updateSql = "UPDATE app_utilisateurs SET token_valide = 'oui', token = NULL WHERE id = ?";
            $this->db->query($updateSql, [$user['id']]);

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Code validé. Veuillez maintenant définir votre mot de passe.',
                'step'    => 'set_password'
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error'   => 'Erreur lors de l\'activation',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST - Définir le mot de passe après validation du code OTP
     *        Condition : statut = 'validation' ET token_valide = 'oui'
     *        Résultat  : mot de passe hashé + statut → 'actif'
     */
    private function setPassword() {
        $input    = json_decode(file_get_contents('php://input'), true);
        $email    = trim($input['email']    ?? '');
        $password =      $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->sendResponse(400, [
                'success' => false,
                'error'   => 'Email et mot de passe requis'
            ]);
        }

        if (strlen($password) < 8) {
            $this->sendResponse(400, [
                'success' => false,
                'error'   => 'Le mot de passe doit contenir au moins 8 caractères'
            ]);
        }

        try {
            // Vérifier que le code OTP a bien été validé (token_valide = 'oui')
            $sql   = "SELECT * FROM app_utilisateurs WHERE email = ? AND statut = 'validation' AND token_valide = 'oui'";
            $users = $this->db->query($sql, [$email]);

            if (empty($users)) {
                $this->sendResponse(403, [
                    'success'    => false,
                    'error'      => 'Action non autorisée. Veuillez d\'abord valider votre code d\'activation.',
                    'error_code' => 'OTP_NOT_VALIDATED'
                ]);
            }

            $user = $users[0];

            // Hasher le nouveau mot de passe et activer le compte
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $updateSql = "UPDATE app_utilisateurs SET mot_de_passe = ?, statut = 'actif', token_valide = NULL WHERE id = ?";
            $this->db->query($updateSql, [$hashedPassword, $user['id']]);

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Mot de passe défini avec succès. Votre compte est maintenant actif !'
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error'   => 'Erreur lors de la définition du mot de passe',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * Génération JWT
     */

    /**
     * POST - Inscription d'un nouvel utilisateur
     */
    private function register() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, ['success' => false, 'error' => 'JSON invalide']);
        }

        $required = ['nom', 'prenom', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => "Le champ {$field} est requis"
                ]);
            }
        }

        $email = trim($input['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendResponse(400, [
                'success' => false,
                'error' => 'Email invalide'
            ]);
        }

        try {
            $checkSql = "SELECT id FROM app_utilisateurs WHERE email = ?";
            $existing = $this->db->query($checkSql, [$email]);

            if (!empty($existing)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Cet email est déjà utilisé'
                ]);
            }

            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

            $accesMagasins  = $input['acces_magasins']  ?? '';
            $accesEntrepots = $input['acces_entrepots'] ?? '';

            if (is_array($accesMagasins))  { $accesMagasins  = implode(',', $accesMagasins); }
            if (is_array($accesEntrepots)) { $accesEntrepots = implode(',', $accesEntrepots); }

            $sql = "INSERT INTO app_utilisateurs 
                    (nom, prenom, email, mot_de_passe, contact, photo, id_role, id_entreprise, access, statut, date_create, create_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

            $params = [
                $input['nom'],
                $input['prenom'],
                $email,
                $hashedPassword,
                $input['telephone'] ?? '',
                $input['photo'],
                $input['role']      ?? '1',
                $input['entreprise'] ?? 'nexcode',
                $input['access'],
                $input['statut'],
                $input['user_id']   ?? '1',
            ];

            $this->db->query($sql, $params);
            $userId = $this->db->lastInsertId();

            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'data'    => ['id' => $userId]
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error'   => 'Erreur lors de la création de l\'utilisateur',
                'details' => $e->getMessage()
            ]);
        }
    }

    private function register2() {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->sendResponse(400, ['success' => false, 'error' => 'JSON invalide']);
    }

    if (empty($input['nom']) || empty($input['prenom']) || empty($input['email']) || empty($input['password'])) {
        $this->sendResponse(400, ['success' => false, 'error' => 'Nom, prénom, email et mot de passe requis']);
    }

    $email = trim($input['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->sendResponse(400, ['success' => false, 'error' => 'Email invalide']);
    }

    try {

        // ===============================
        // 1️⃣ Vérifier si email existe
        // ===============================
        $checkSql = "SELECT id FROM app_utilisateurs WHERE email = ?";
        $existing = $this->db->query($checkSql, [$email]);

        if (!empty($existing)) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Cet email est déjà utilisé']);
        }

        // ===============================
        // 2️⃣ CREATION ENTREPRISE
        // ===============================

        // Si entreprise envoyée
        if (!empty($input['entreprise'])) {
            $nomEntreprise = trim($input['entreprise']);
        } else {
            // Génération automatique pour particulier
            $nomEntreprise = 'ENT-' . rand(100000, 999999);
        }

        // Générer slug
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $nomEntreprise));

        $sqlEntreprise = "INSERT INTO app_entreprises 
            (nom_entreprise, slug, telephone, email, devise, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $this->db->query($sqlEntreprise, [
            $nomEntreprise,
            $slug,
            $input['telephone'] ?? '',
            $email,
            'XOF',
            'actif'
        ]);

        $idEntreprise = $this->db->lastInsertId();

        // ===============================
        // 3️⃣ CREATION UTILISATEUR
        // ===============================

        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        $token = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        $sqlUser = "INSERT INTO app_utilisateurs 
            (nom, prenom, email, mot_de_passe, contact, id_role, access, statut, invite, token, id_entreprise, date_create) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW())";

        $params = [
            $input['nom'],
            $input['prenom'],
            $email,
            $hashedPassword,
            $input['telephone'] ?? '',
            $input['role'] ?? 1,
            $input['access'] ?? '',
            $input['statut'] ?? 'actif',
            $token,
            $idEntreprise
        ];

        $this->db->query($sqlUser, $params);
        $lastId = $this->db->lastInsertId();

        // ===============================
        // 4️⃣ ENVOI MAIL
        // ===============================
        Mailer::sendRegister($email, $input['prenom'], $nomEntreprise, $token);

        $this->sendResponse(201, [
            'success' => true,
            'message' => 'Entreprise et utilisateur créés avec succès',
            'data' => [
                'id_user' => $lastId,
                'id_entreprise' => $idEntreprise
            ]
        ]);

    } catch (Exception $e) {
        $this->sendResponse(500, [
            'success' => false,
            'error' => 'Erreur création',
            'details' => $e->getMessage()
        ]);
    }
}

    /**
     * GET - Récupérer les informations de l'utilisateur connecté
     */
    private function getUserInfo() {
        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->sendResponse(401, [
                'success' => false,
                'error'   => 'Token requis'
            ]);
        }

        $jwt        = $matches[1];
        $secret_key = "4e9f1c7b2a6d8f3c5e9a0b7d4c2f8a6e1b3c9d7e5f0a2b4c6d8e1f3a5b7c9d2";

        try {
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            $userId  = $decoded->data->id;

            $sql = "SELECT id, nom, prenom, email, telephone, avatar, role,
                        acces_magasins, acces_entrepots, acces_clients, acces_fournisseurs,
                        statut, last_login, created_at
                    FROM app_utilisateurs
                    WHERE id = ? AND actif = 1";

            $users = $this->db->query($sql, [$userId]);

            if (empty($users)) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error'   => 'Utilisateur non trouvé'
                ]);
            }

            $user          = $users[0];
            $user['acces'] = $this->parseAcces($user);

            $this->sendResponse(200, [
                'success' => true,
                'data'    => $user
            ]);

        } catch (Exception $e) {
            $this->sendResponse(401, [
                'success' => false,
                'error'   => 'Token invalide ou expiré'
            ]);
        }
    }

    /**
     * Parser les accès de l'utilisateur
     */
    private function parseAcces($user) {
        $accessJson = $user['access'] ?? '[]';

        if (is_array($accessJson)) {
            return $accessJson;
        }

        $accessArray = json_decode($accessJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $accessArray;
    }

    /**
     * Vérifier un token
     */
    private function verifyToken() {
        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->sendResponse(401, [
                'success' => false,
                'error'   => 'Token manquant'
            ]);
        }

        $jwt        = $matches[1];
        $secret_key = "4e9f1c7b2a6d8f3c5e9a0b7d4c2f8a6e1b3c9d7e5f0a2b4c6d8e1f3a5b7c9d2";

        try {
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Token valide',
                'user'    => $decoded->data
            ]);

        } catch (Exception $e) {
            $this->sendResponse(401, [
                'success' => false,
                'error'   => 'Token invalide ou expiré',
                'details' => $e->getMessage()
            ]);
        }
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// Démarrage de l'API
$api = new AuthAPI();
$api->handleRequest();