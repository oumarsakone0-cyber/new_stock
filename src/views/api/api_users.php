<?php
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


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

require_once 'auth.php';

require_once 'config/database.php';
require_once 'Mailer.php';

class UsersAPI {
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
        $action = $_GET['action'] ?? 'list';

        switch ($method) {
            case 'GET':
                $this->handleGet($action);
                break;
            case 'POST':
                $this->handlePost($action);
                break;
            case 'PUT':
                $this->handlePut($action);
                break;
            case 'DELETE':
                $this->handleDelete($action);
                break;
            default:
                $this->sendResponse(405, ['success' => false, 'error' => 'Méthode non autorisée']);
        }
    }

    private function handleGet($action) {
        switch ($action) {
            case 'list':
                $this->getUsers();
                break;
            case 'details':
                $this->getUserDetails();
                break;
            case 'magasins':
                $this->getMagasins();
                break;
            case 'entrepots':
                $this->getEntrepots();
                break;
            case 'stats':
                $this->getStats();
                break;
            default:
                $this->sendResponse(404, ['success' => false, 'error' => 'Action GET inconnue']);
        }
    }

    private function handlePost($action) {
        switch ($action) {
            case 'add':
                $this->addUser();
                break;
            default:
                $this->sendResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
        }
    }

    private function handlePut($action) {
        switch ($action) {
            case 'update':
                $this->updateUser();
                break;
            case 'toggle-status':
                $this->toggleUserStatus();
                break;
            default:
                $this->sendResponse(404, ['success' => false, 'error' => 'Action PUT inconnue']);
        }
    }

    private function handleDelete($action) {
        switch ($action) {
            case 'delete':
                $this->deleteUser();
                break;
            default:
                $this->sendResponse(404, ['success' => false, 'error' => 'Action DELETE inconnue']);
        }
    }

    /**
     * GET - Liste tous les utilisateurs
     */
    private function getUsers() {
        $currentUser = getUserFromToken();
        $idEntreprise = $currentUser['id_entreprise'];

        try {
            $sql = "SELECT 
                        id, nom, prenom, email, contact, photo, id_role,
                        access, statut, last_login, date_create
                    FROM app_utilisateurs 
                    WHERE id_entreprise = ?
                    ORDER BY date_create DESC";
            
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

    /**
     * GET - Détails d'un utilisateur spécifique
     */
    private function getUserDetails() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID utilisateur requis']);
        }

        try {
            $sql = "SELECT 
                        id, nom, prenom, email, telephone, avatar, role,
                        acces_magasins, acces_entrepots, acces_clients, acces_fournisseurs,
                        actif, derniere_connexion, created_at
                    FROM app_utilisateurs 
                    WHERE id = ?";
            
            $users = $this->db->query($sql, [$id]);
            
            if (empty($users)) {
                $this->sendResponse(404, ['success' => false, 'error' => 'Utilisateur non trouvé']);
            }

            $user = $users[0];
            $user['acces'] = $this->parseAcces($user);

            $this->sendResponse(200, [
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur récupération détails utilisateur',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET - Liste des magasins disponibles
     */
    private function getMagasins() {
        try {
            $sql = "SELECT id, nom, couleur FROM stok_magasins WHERE actif = 1 ORDER BY nom ASC";
            $magasins = $this->db->query($sql);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $magasins
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur récupération des magasins',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET - Liste des entrepôts disponibles
     */
    private function getEntrepots() {
        try {
            // Récupérer tous les entrepôts (pas de colonne 'actif' dans stok_entrepots)
            $sql = "SELECT id, nom, code, ville, statut FROM stok_entrepots ORDER BY nom ASC";
            $entrepots = $this->db->query($sql);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $entrepots
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur récupération des entrepôts',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET - Statistiques globales
     */
    private function getStats() {
        try {
            $sqlTotal = "SELECT COUNT(*) as total FROM app_utilisateurs";
            $sqlActifs = "SELECT COUNT(*) as actifs FROM app_utilisateurs WHERE actif = 1";
            $sqlInactifs = "SELECT COUNT(*) as inactifs FROM app_utilisateurs WHERE actif = 0";
            $sqlRoles = "SELECT role, COUNT(*) as count FROM app_utilisateurs GROUP BY role";
            
            $total = $this->db->query($sqlTotal)[0]['total'];
            $actifs = $this->db->query($sqlActifs)[0]['actifs'];
            $inactifs = $this->db->query($sqlInactifs)[0]['inactifs'];
            $roles = $this->db->query($sqlRoles);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => [
                    'total' => $total,
                    'actifs' => $actifs,
                    'inactifs' => $inactifs,
                    'roles' => $roles
                ]
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur récupération statistiques',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST - Ajouter un nouvel utilisateur
     */
    private function addUser() {
        $currentUser  = getUserFromToken();
        $idEntreprise = $currentUser['id_entreprise'];
        $createBy     = $currentUser['id'];

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
            $checkSql = "SELECT id FROM app_utilisateurs WHERE email = ?";
            $existing = $this->db->query($checkSql, [$email]);

            if (!empty($existing)) {
                $this->sendResponse(400, ['success' => false, 'error' => 'Cet email est déjà utilisé']);
            }

            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            $token = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

            $sql = "INSERT INTO app_utilisateurs 
                    (nom, prenom, email, mot_de_passe, contact, id_role, access, statut, invite, token, id_entreprise, create_by, date_create) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, NOW())";

            $params = [
                $input['nom'],
                $input['prenom'],
                $email,
                $hashedPassword,
                $input['telephone'] ?? '',
                $input['id_role'] ?? '1',
                $input['access'] ?? '',
                $input['statut'] ?? 'validation',
                $token,
                $idEntreprise,  // ← depuis le token JWT
                $createBy       // ← depuis le token JWT
            ];

            $this->db->query($sql, $params);
            $lastId = $this->db->lastInsertId();

            $nomEntreprise = $currentUser['nom_entreprise'] ?? 'Votre entreprise';
            Mailer::sendInvitation($email, $input['prenom'], $nomEntreprise, $token);

            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'data' => ['id' => $lastId]
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur création utilisateur',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * PUT - Mettre à jour un utilisateur
     */
   private function updateUser() {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validation JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->sendResponse(400, ['success' => false, 'error' => 'JSON invalide']);
    }

    // Champs obligatoires (même logique que addUser)
    if (empty($input['id'])) {
        $this->sendResponse(400, ['success' => false, 'error' => 'ID utilisateur requis']);
    }
    if (empty($input['nom']) || empty($input['prenom']) || empty($input['email'])) {
        $this->sendResponse(400, ['success' => false, 'error' => 'Nom, prénom et email requis']);
    }

    $email = trim($input['email']);

    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->sendResponse(400, ['success' => false, 'error' => 'Email invalide']);
    }

    try {
        // Vérifier que l'utilisateur existe
        $user = $this->db->query("SELECT id, prenom, email FROM app_utilisateurs WHERE id = ?", [$input['id']]);
        if (empty($user)) {
            $this->sendResponse(404, ['success' => false, 'error' => 'Utilisateur non trouvé']);
        }

        // Vérifier que le nouvel email n'est pas déjà pris par quelqu'un d'autre
        $emailCheck = $this->db->query(
            "SELECT id FROM app_utilisateurs WHERE email = ? AND id != ?",
            [$email, $input['id']]
        );
        if (!empty($emailCheck)) {
            $this->sendResponse(400, ['success' => false, 'error' => 'Cet email est déjà utilisé par un autre utilisateur']);
        }

        // Préparer les accès (même logique que addUser)
        $access            = $input['access'] ?? '';
        $statut            = isset($input['statut']) ? ($input['statut'] ? 1 : 0) : 1;

        // Mot de passe : optionnel à la mise à jour
        if (!empty($input['password'])) {
            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

            $sql = "UPDATE app_utilisateurs 
                    SET nom        = ?,
                        prenom     = ?,
                        email      = ?,
                        mot_de_passe = ?,
                        contact    = ?,
                        id_role    = ?,
                        access     = ?,
                        statut     = ?
                    WHERE id = ?";

            $params = [
                $input['nom'],
                $input['prenom'],
                $email,
                $hashedPassword,
                $input['contact'] ?? '',
                $input['id_role'] ?? 1,
                $access,
                $statut,
                $input['id']
            ];
        } else {
            $sql = "UPDATE app_utilisateurs 
                    SET nom     = ?,
                        prenom  = ?,
                        email   = ?,
                        contact = ?,
                        id_role = ?,
                        access  = ?,
                        statut  = ?
                    WHERE id = ?";

            $params = [
                $input['nom'],
                $input['prenom'],
                $email,
                $input['contact'] ?? '',
                $input['id_role'] ?? 1,
                $access,
                $statut,
                $input['id']
            ];
        }

        $this->db->query($sql, $params);

        // ✉️ Email de confirmation de mise à jour
        Mailer::sendAccountUpdated(
            $email,
            $input['prenom'],
            !empty($input['password']) // true = mot de passe modifié
        );

        $this->sendResponse(200, [
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data'    => ['id' => $input['id']]
        ]);

    } catch (Exception $e) {
        $this->sendResponse(500, [
            'success' => false,
            'error'   => 'Erreur mise à jour utilisateur',
            'details' => $e->getMessage()
        ]);
    }
}

   private function sendInvitationEmail($email, $prenom, $nomEntreprise, $token)
    {
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = 'mail16.lwspanel.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@aliadjame.com';
        $mail->Password   = '0Objectif-';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('no-reply@aliadjame.com', 'Next Stock');
        $mail->addAddress($email, $prenom);

        $mail->isHTML(true);
        $mail->Subject = "✉️ Votre invitation pour rejoindre $nomEntreprise sur Next Stock";

        $prenomSafe      = htmlspecialchars($prenom);
        $entrepriseSafe  = htmlspecialchars($nomEntreprise);
        $tokenSafe       = htmlspecialchars($token);
        $tokenUrl        = urlencode($token);

        $mail->Body = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
        <title>Invitation Next Stock</title>
        </head>
        <body style="margin:0;padding:0;background-color:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4ff;padding:48px 16px;">
            <tr>
            <td align="center">

                <table width="560" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(26,115,232,0.10);">

                <!-- Header gradient -->
                <tr>
                    <td style="background:rgb(16, 185, 129)">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td style="background:rgba(255,255,255,0.2);border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:22px;font-weight:800;line-height:44px;">N</span>
                        </td>
                        <td style="padding-left:12px;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.4px;">Next Stock</span>
                        </td>
                        </tr>
                    </table>
                    <p style="margin:24px 0 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase;">Invitation</p>
                    <h1 style="margin:6px 0 0 0;color:#ffffff;font-size:28px;font-weight:800;line-height:1.3;">
                        Bonjour {$prenomSafe}&nbsp;👋
                    </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:36px 40px 0 40px;">
                    <p style="margin:0 0 24px 0;color:#4b5675;font-size:15px;line-height:1.75;">
                        <strong style="color:#1a202c;">{$entrepriseSafe}</strong> vous invite à rejoindre leur espace de gestion de stock sur <strong style="color:#248162">Next Stock</strong>.
                        <br/>Utilisez le code ci-dessous ou cliquez sur le bouton pour accéder à votre compte.
                    </p>
                    </td>
                </tr>

                <!-- Token block -->
                <tr>
                    <td style="padding:0 40px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td style="background:#10b9812b;border:1.5px dashed #10b981;border-radius:14px;padding:26px;text-align:center;">
                            <p style="margin:0 0 10px 0;color:#6b7da8;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;">Code d'invitation</p>
                            <p style="margin:0;color:#1e7f5f;font-size:34px;font-weight:800;letter-spacing:8px;font-family:'Courier New',monospace;">{$tokenSafe}</p>
                        </td>
                        </tr>
                    </table>
                    </td>
                </tr>

                <!-- CTA -->
                <tr>
                    <td style="padding:28px 40px 0 40px;text-align:center;">
                    <a href="https://nextstock.app/invitation?token={$tokenUrl}"
                        style="display:inline-block;background:linear-gradient(135deg, #10b981 0%, #10b981c9 100%);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:15px 40px;border-radius:12px;letter-spacing:0.3px;box-shadow:0 4px 20px rgba(26,115,232,0.35);">
                        Rejoindre l'espace →
                    </a>
                    </td>
                </tr>

                <!-- Security note -->
                <tr>
                    <td style="padding:24px 40px 0 40px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td style="background:#d7f3ea61;border-left:3px solid #10b981;border-radius:0 10px 10px 0;padding:14px 16px;">
                            <p style="margin:0;color:#5b7ba8;font-size:12px;line-height:1.7;">
                            🔒 <strong style="color:#1e3a5f;">Lien sécurisé</strong> — Si vous n'êtes pas à l'origine de cette invitation, ignorez simplement cet email.
                            </p>
                        </td>
                        </tr>
                    </table>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:32px 40px 0 40px;">
                    <hr style="border:none;border-top:1px solid #e8eef8;margin:0;"/>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:24px 40px 36px 40px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td>
                            <p style="margin:0 0 3px 0;color:#1a202c;font-size:13px;font-weight:700;">L'équipe Next Stock</p>
                            <p style="margin:0;color:#9ba3b5;font-size:12px;">Gestion de stock intelligente</p>
                        </td>
                        <td align="right" style="vertical-align:middle;">
                            <a href="https://nextstock.app" style="color:#248162font-size:12px;font-weight:600;text-decoration:none;">nextstock.app</a>
                        </td>
                        </tr>
                    </table>
                    </td>
                </tr>

                </table>

                <p style="color:#b0bcd4;font-size:11px;margin:20px 0 0 0;text-align:center;">
                Cet email a été envoyé automatiquement par Next Stock · Ne pas répondre
                </p>

            </td>
            </tr>
        </table>

        </body>
        </html>
        HTML;

                $mail->AltBody = "Bonjour $prenom,\n\n$nomEntreprise vous invite à rejoindre leur espace sur Next Stock.\n\nCode d'invitation : $token\n\nLien : https://nextstock.app/invitation?token=$tokenUrl\n\nL'équipe Next Stock";

                $mail->send();
                return true;

            } catch (Exception $e) {
                error_log("Erreur email: {$mail->ErrorInfo}");
                return false;
            }
        }

    /**
     * PUT - Activer/désactiver un utilisateur
     */
    private function toggleUserStatus() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id'])) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID utilisateur requis']);
        }

        try {
            $sql = "UPDATE app_utilisateurs SET actif = NOT actif WHERE id = ?";
            $this->db->query($sql, [$input['id']]);

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Statut utilisateur mis à jour'
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur changement statut',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * DELETE - Supprimer un utilisateur
     */
    private function deleteUser() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendResponse(400, ['success' => false, 'error' => 'ID utilisateur requis']);
        }

        try {
            $sql = "DELETE FROM app_utilisateurs WHERE id = ?";
            $this->db->query($sql, [$id]);

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Erreur suppression utilisateur',
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parser les accès de l'utilisateur
     */
    private function parseAcces($user) {
        $acces = [
            'magasins' => [],
            'entrepots' => [],
            'clients' => (bool)$user['acces_clients'],
            'fournisseurs' => (bool)$user['acces_fournisseurs']
        ];

        // Parser les magasins
        if ($user['acces_magasins'] === 'all') {
            $acces['magasins'] = 'all';
        } elseif (!empty($user['acces_magasins'])) {
            $acces['magasins'] = array_map('intval', explode(',', $user['acces_magasins']));
        }

        // Parser les entrepôts
        if ($user['acces_entrepots'] === 'all') {
            $acces['entrepots'] = 'all';
        } elseif (!empty($user['acces_entrepots'])) {
            $acces['entrepots'] = array_map('intval', explode(',', $user['acces_entrepots']));
        }

        return $acces;
    }

    /**
     * Formater les accès pour la base de données
     */
    private function formatAcces($acces) {
        if ($acces === 'all' || $acces === ['all']) {
            return 'all';
        }
        
        if (is_array($acces) && !empty($acces)) {
            // Filtrer les valeurs vides et convertir en entiers
            $filtered = array_filter($acces, function($val) {
                return !empty($val) && $val !== 'all';
            });
            
            if (empty($filtered)) {
                return '';
            }
            
            return implode(',', array_map('intval', $filtered));
        }
        
        return '';
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// Démarrage de l'API
$api = new UsersAPI();
$api->handleRequest();