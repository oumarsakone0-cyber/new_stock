<?php
// API Comptabilites - Depenses / Motifs / Pieces + Envoi Mail
// Securise par JWT : aucune info sensible dans l'URL ou le body

$allowed_origins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:5175',
    'http://localhost:5176',
    'http://localhost:5177',
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

// Prevent PHP warnings/notices being printed into the HTTP response body which would break JSON parsing
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// ===================== PHPMailer =====================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require_once 'Mailer.php';

// ===================== Database + JWT =====================
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/libs/JWT.php';
require_once __DIR__ . '/libs/Key.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ===================== Cle secrete JWT (meme que api_auth.php) =====================
define('JWT_SECRET', '4e9f1c7b2a6d8f3c5e9a0b7d4c2f8a6e1b3c9d7e5f0a2b4c6d8e1f3a5b7c9d2');

// ===================== Fonctions utilitaires =====================

function jsonResponse($code, $payload) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJsonBody() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(400, ['success' => false, 'error' => 'JSON invalide']);
    }
    return $data ?: [];
}

function getInt($value, $default = null) {
    if ($value === null || $value === '') return $default;
    return (int)$value;
}

function getStr($value, $default = '') {
    if ($value === null) return $default;
    return trim((string)$value);
}

/**
 * Extraire et verifier le token JWT depuis le header Authorization.
 * Retourne les donnees du payload (id, nom, prenom, email, role, id_entreprise, nom_entreprise).
 * Si le token est absent ou invalide -> 401.
 */
function authenticateJWT() {
    $headers = getallheaders();
    // Normaliser les cles (certains serveurs mettent en minuscule)
    $headersLower = [];
    foreach ($headers as $k => $v) {
        $headersLower[strtolower($k)] = $v;
    }
    $authHeader = $headersLower['authorization'] ?? '';

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        jsonResponse(401, [
            'success' => false,
            'error'   => 'Token requis'
        ]);
    }

    try {
        $decoded = JWT::decode($matches[1], new Key(JWT_SECRET, 'HS256'));
        return (array)$decoded->data;
    } catch (Throwable $e) {
        jsonResponse(401, [
            'success' => false,
            'error'   => 'Token invalide ou expire'
        ]);
    }
}

/**
 * Envoi d'une notification email aux administrateurs de l'entreprise.
 */
function sendComptaEmailToAdmins($db, $id_entreprise, $subject, $htmlBody) {
    try {
        $sql = "SELECT email, nom, prenom 
                FROM app_utilisateurs 
                WHERE statut = 'actif' AND id_role = 1";
        $params = [];
        if ($id_entreprise !== null) {
            $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
            $params[] = $id_entreprise;
        }
        $admins = $db->query($sql, $params);
        if (empty($admins)) {
            return;
        }

        foreach ($admins as $admin) {
            $to = trim($admin['email'] ?? '');
            if ($to === '') continue;

            $nomComplet = trim(($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? ''));
            $body  = "<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>";
            $body .= "<h2 style='color:#333;border-bottom:2px solid #007bff;padding-bottom:10px;'>"
                   . htmlspecialchars($subject) . "</h2>";
            $body .= "<p>Bonjour " . htmlspecialchars($nomComplet ?: 'Admin') . ",</p>";
            $body .= $htmlBody;
            $body .= "<p style='color:#666;font-size:12px;margin-top:20px;border-top:1px solid #eee;padding-top:10px;'>"
                   . "Notification automatique - Module comptabilites."
                   . "</p></div>";

            if (class_exists('Mailer') && method_exists('Mailer', 'sendSimpleHtml')) {
                Mailer::sendSimpleHtml($to, $subject, $body);
            } elseif (class_exists('Mailer') && method_exists('Mailer', 'sendCustom')) {
                Mailer::sendCustom($to, $subject, $body);
            }
        }
    } catch (Throwable $e) {
        error_log("Erreur envoi mail compta admins: " . $e->getMessage());
    }
}

// ===================== AUTHENTIFICATION JWT =====================
// Toutes les actions sont protegees : on recupere user_id et id_entreprise depuis le token
$authUser = authenticateJWT();
$auth_user_id      = $authUser['id']             ?? null;
$auth_id_entreprise = $authUser['id_entreprise'] ?? null;
$auth_role         = $authUser['role']           ?? null;
$auth_nom          = ($authUser['prenom'] ?? '') . ' ' . ($authUser['nom'] ?? '');

try {
    if (!class_exists('Database')) {
        throw new Exception("Classe Database introuvable dans config/database.php");
    }
    $db = new Database();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // id_entreprise vient du token, pas des parametres
    $id_entreprise = $auth_id_entreprise;

    // ===================== GET =====================
    if ($method === 'GET') {
        if ($action === 'list_motifs') {
            $sql = "SELECT id_motif as id, libelle
                    FROM app_compta_motifs_depense
                    WHERE 1=1";
            $params = [];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY libelle ASC";
                $rows = $db->query($sql, $params);
                if (!is_array($rows)) $rows = [];
                jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        if ($action === 'list_depenses') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $motif_id = getInt($_GET['motif_id'] ?? null, null);
            $search = getStr($_GET['search'] ?? '');

            $sql = "SELECT 
                        d.id_depense as id,
                        d.motif_id,
                        m.libelle as motif_libelle,
                        d.date_depense,
                        d.beneficiaire,
                        d.description,
                        d.montant,
                        (SELECT COUNT(*) FROM app_compta_depense_pieces p WHERE p.id_depense = d.id_depense) as nb_pieces
                    FROM app_compta_depenses d
                    LEFT JOIN app_compta_motifs_depense m ON m.id_motif = d.motif_id
                    WHERE 1=1";
            $params = [];

            if ($id_entreprise !== null) {
                $sql .= " AND (d.id_entreprise = ? OR d.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            if ($motif_id !== null) {
                $sql .= " AND d.motif_id = ?";
                $params[] = $motif_id;
            }
            if ($date_debut !== '') {
                $sql .= " AND d.date_depense >= ?";
                $params[] = $date_debut;
            }
            if ($date_fin !== '') {
                $sql .= " AND d.date_depense <= ?";
                $params[] = $date_fin;
            }
            if ($search !== '') {
                $sql .= " AND (d.beneficiaire LIKE ? OR d.description LIKE ? OR m.libelle LIKE ?)";
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            $sql .= " ORDER BY d.date_depense DESC, d.id_depense DESC";
            $rows = $db->query($sql, $params);
            if (!is_array($rows)) $rows = [];
            foreach ($rows as &$r) {
                $r['montant'] = (float)($r['montant'] ?? 0);
                $r['nb_pieces'] = (int)($r['nb_pieces'] ?? 0);
            }
            unset($r);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        if ($action === 'stats_depenses') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $motif_id = getInt($_GET['motif_id'] ?? null, null);

            $sql = "SELECT 
                        COUNT(*) as nb_depenses,
                        COALESCE(SUM(montant), 0) as total_periode
                    FROM app_compta_depenses
                    WHERE 1=1";
            $params = [];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            if ($motif_id !== null) {
                $sql .= " AND motif_id = ?";
                $params[] = $motif_id;
            }
            if ($date_debut !== '') {
                $sql .= " AND date_depense >= ?";
                $params[] = $date_debut;
            }
            if ($date_fin !== '') {
                $sql .= " AND date_depense <= ?";
                $params[] = $date_fin;
            }

            $rows = $db->query($sql, $params);
            $row = !empty($rows) ? $rows[0] : ['nb_depenses' => 0, 'total_periode' => 0];
            jsonResponse(200, ['success' => true, 'data' => [
                'nb_depenses' => (int)($row['nb_depenses'] ?? 0),
                'total_periode' => (float)($row['total_periode'] ?? 0)
            ]]);
        }

        if ($action === 'list_pieces') {
            $id_depense = getInt($_GET['id_depense'] ?? null, null);
            if ($id_depense === null) {
                jsonResponse(400, ['success' => false, 'error' => 'id_depense manquant']);
            }
            $sql = "SELECT 
                        p.id_piece as id,
                        p.url_fichier,
                        p.nom_original,
                        p.type_fichier,
                        p.date_creation
                    FROM app_compta_depense_pieces p
                    INNER JOIN app_compta_depenses d ON d.id_depense = p.id_depense
                    WHERE p.id_depense = ?";
            $params = [$id_depense];
            if ($id_entreprise !== null) {
                $sql .= " AND (d.id_entreprise = ? OR d.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY p.id_piece DESC";
            $rows = $db->query($sql, $params);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        if ($action === 'list_pieces_global') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $search = getStr($_GET['search'] ?? '');

            $sql = "SELECT 
                        p.id_piece as id,
                        d.date_depense,
                        d.beneficiaire,
                        m.libelle as motif_libelle,
                        p.url_fichier,
                        p.nom_original,
                        p.type_fichier,
                        p.date_creation
                    FROM app_compta_depense_pieces p
                    INNER JOIN app_compta_depenses d ON d.id_depense = p.id_depense
                    LEFT JOIN app_compta_motifs_depense m ON m.id_motif = d.motif_id
                    WHERE 1=1";
            $params = [];
            if ($id_entreprise !== null) {
                $sql .= " AND (d.id_entreprise = ? OR d.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            if ($date_debut !== '') {
                $sql .= " AND d.date_depense >= ?";
                $params[] = $date_debut;
            }
            if ($date_fin !== '') {
                $sql .= " AND d.date_depense <= ?";
                $params[] = $date_fin;
            }
            if ($search !== '') {
                $sql .= " AND (p.nom_original LIKE ? OR m.libelle LIKE ? OR d.beneficiaire LIKE ?)";
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            $sql .= " ORDER BY p.id_piece DESC";
            $rows = $db->query($sql, $params);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action GET inconnue']);
    }

    // ===================== POST =====================
    if ($method === 'POST') {
        $body = readJsonBody();

        if ($action === 'add_motif') {
            $libelle = getStr($body['libelle'] ?? '');
            $description = getStr($body['description'] ?? '');
            if ($libelle === '') jsonResponse(400, ['success' => false, 'error' => 'Libelle requis']);

            $sql = "INSERT INTO app_compta_motifs_depense (id_entreprise, libelle, description, date_creation)
                    VALUES (?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $libelle, $description]);
            $new_id = $db->lastInsertId();

            // Email notification
            $emailBody = "
                <p>Un nouveau motif de depense a ete ajoute par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($libelle) . "</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Description</td><td style='padding:10px;border:1px solid #dee2e6;'>" . nl2br(htmlspecialchars($description)) . "</td></tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Nouveau motif de depense', $emailBody);

            jsonResponse(200, ['success' => true, 'data' => ['id_motif' => $new_id]]);
        }

        if ($action === 'update_motif') {
            $id_motif = getInt($body['id_motif'] ?? null, null);
            $libelle = getStr($body['libelle'] ?? '');
            $description = getStr($body['description'] ?? '');
            if ($id_motif === null) jsonResponse(400, ['success' => false, 'error' => 'id_motif requis']);
            if ($libelle === '') jsonResponse(400, ['success' => false, 'error' => 'Libelle requis']);

            $sql = "UPDATE app_compta_motifs_depense SET libelle = ?, description = ? WHERE id_motif = ?";
            $params = [$libelle, $description, $id_motif];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            $emailBody = "
                <p>Un motif de depense a ete modifie par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($libelle) . "</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Description</td><td style='padding:10px;border:1px solid #dee2e6;'>" . nl2br(htmlspecialchars($description)) . "</td></tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Motif de depense modifie', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_motif') {
            $id_motif = getInt($body['id_motif'] ?? null, null);
            if ($id_motif === null) jsonResponse(400, ['success' => false, 'error' => 'id_motif requis']);

            $check = $db->query("SELECT COUNT(*) as c FROM app_compta_depenses WHERE motif_id = ?", [$id_motif]);
            $count = !empty($check) ? (int)($check[0]['c'] ?? 0) : 0;
            if ($count > 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Motif utilise dans des depenses']);
            }

            // Recuperer le libelle avant suppression pour l'email
            $motifInfo = $db->query("SELECT libelle FROM app_compta_motifs_depense WHERE id_motif = ?", [$id_motif]);
            $motif_libelle = !empty($motifInfo) ? $motifInfo[0]['libelle'] : 'N/A';

            $sql = "DELETE FROM app_compta_motifs_depense WHERE id_motif = ?";
            $params = [$id_motif];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            $emailBody = "
                <p style='color:#dc3545;font-weight:bold;'>Un motif de depense a ete supprime par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle supprime</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($motif_libelle) . "</td></tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Motif de depense supprime', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'add_depense') {
            $date_depense = getStr($body['date_depense'] ?? '');
            $motif_id = getInt($body['motif_id'] ?? null, null);
            $beneficiaire = getStr($body['beneficiaire'] ?? '');
            $description = getStr($body['description'] ?? '');
            $montant = (float)($body['montant'] ?? 0);

            if ($date_depense === '' || $montant <= 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Date et montant requis']);
            }

            $sql = "INSERT INTO app_compta_depenses 
                        (id_entreprise, motif_id, date_depense, beneficiaire, description, montant, create_by, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $motif_id, $date_depense, $beneficiaire, $description, $montant, $auth_user_id]);
            $id_depense = $db->lastInsertId();

            // Recuperer le libelle du motif pour le mail
            $motif_libelle = 'N/A';
            if ($motif_id !== null) {
                $motifRows = $db->query("SELECT libelle FROM app_compta_motifs_depense WHERE id_motif = ?", [$motif_id]);
                if (!empty($motifRows)) $motif_libelle = $motifRows[0]['libelle'] ?? 'N/A';
            }

            // ===================== NOTIFICATION EMAIL AUX ADMINS =====================
            $montant_fmt = number_format($montant, 0, ',', ' ');
            $emailBody = "
                <p>Une nouvelle depense vient d'etre enregistree par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Date</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($date_depense) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Montant</td>
                        <td style='padding:10px;border:1px solid #dee2e6;color:#dc3545;font-weight:bold;font-size:16px;'>{$montant_fmt} XOF</td>
                    </tr>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Motif</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($motif_libelle) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Beneficiaire</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($beneficiaire) . "</td>
                    </tr>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Description</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . nl2br(htmlspecialchars($description)) . "</td>
                    </tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Nouvelle depense enregistree', $emailBody);

            jsonResponse(200, ['success' => true, 'data' => ['id_depense' => (int)$id_depense]]);
        }

        if ($action === 'update_depense') {
            $id_depense = getInt($body['id_depense'] ?? null, null);
            if ($id_depense === null) jsonResponse(400, ['success' => false, 'error' => 'id_depense requis']);

            $date_depense = getStr($body['date_depense'] ?? '');
            $motif_id = getInt($body['motif_id'] ?? null, null);
            $beneficiaire = getStr($body['beneficiaire'] ?? '');
            $description = getStr($body['description'] ?? '');
            $montant = (float)($body['montant'] ?? 0);

            if ($date_depense === '' || $montant <= 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Date et montant requis']);
            }

            $sql = "UPDATE app_compta_depenses
                    SET motif_id = ?, date_depense = ?, beneficiaire = ?, description = ?, montant = ?, date_modification = NOW()
                    WHERE id_depense = ?";
            $params = [$motif_id, $date_depense, $beneficiaire, $description, $montant, $id_depense];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // ===================== NOTIFICATION EMAIL AUX ADMINS =====================
            $montant_fmt = number_format($montant, 0, ',', ' ');
            $emailBody = "
                <p>Une depense a ete modifiee par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>ID Depense</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars((string)$id_depense) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Nouvelle date</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($date_depense) . "</td>
                    </tr>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Nouveau montant</td>
                        <td style='padding:10px;border:1px solid #dee2e6;color:#dc3545;font-weight:bold;'>{$montant_fmt} XOF</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Beneficiaire</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($beneficiaire) . "</td>
                    </tr>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Description</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . nl2br(htmlspecialchars($description)) . "</td>
                    </tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Depense modifiee', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_depense') {
            $id_depense = getInt($body['id_depense'] ?? null, null);
            if ($id_depense === null) jsonResponse(400, ['success' => false, 'error' => 'id_depense requis']);

            // Recuperer les infos avant suppression pour le mail
            $depenseInfo = null;
            try {
                $infoSql = "SELECT d.date_depense, d.beneficiaire, d.description, d.montant, m.libelle as motif_libelle
                            FROM app_compta_depenses d
                            LEFT JOIN app_compta_motifs_depense m ON m.id_motif = d.motif_id
                            WHERE d.id_depense = ?";
                $infoParams = [$id_depense];
                if ($id_entreprise !== null) {
                    $infoSql .= " AND (d.id_entreprise = ? OR d.id_entreprise IS NULL)";
                    $infoParams[] = $id_entreprise;
                }
                $rowsInfo = $db->query($infoSql, $infoParams);
                if (!empty($rowsInfo)) {
                    $depenseInfo = $rowsInfo[0];
                }
            } catch (Throwable $e) {
                // en cas d'erreur, on continue la suppression
            }

            // Supprimer pieces d'abord (si pas de cascade)
            $db->query("DELETE FROM app_compta_depense_pieces WHERE id_depense = ?", [$id_depense]);

            $sql = "DELETE FROM app_compta_depenses WHERE id_depense = ?";
            $params = [$id_depense];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // ===================== NOTIFICATION EMAIL AUX ADMINS =====================
            $del_date = $depenseInfo['date_depense'] ?? 'N/A';
            $del_montant = isset($depenseInfo['montant']) ? (float)$depenseInfo['montant'] : 0;
            $del_montant_fmt = number_format($del_montant, 0, ',', ' ');
            $del_beneficiaire = $depenseInfo['beneficiaire'] ?? 'N/A';
            $del_description = $depenseInfo['description'] ?? '';
            $del_motif = $depenseInfo['motif_libelle'] ?? 'N/A';

            $emailBody = "
                <p style='color:#dc3545;font-weight:bold;'>Une depense a ete supprimee par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>ID Depense</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars((string)$id_depense) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Date</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($del_date) . "</td>
                    </tr>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Montant</td>
                        <td style='padding:10px;border:1px solid #dee2e6;color:#dc3545;font-weight:bold;'>{$del_montant_fmt} XOF</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Motif</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($del_motif) . "</td>
                    </tr>
                    <tr style='background:#f8f9fa;'>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Beneficiaire</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($del_beneficiaire) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Description</td>
                        <td style='padding:10px;border:1px solid #dee2e6;'>" . nl2br(htmlspecialchars($del_description)) . "</td>
                    </tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Depense supprimee', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'add_pieces') {
            $id_depense = getInt($body['id_depense'] ?? null, null);
            $pieces = $body['pieces'] ?? [];
            if ($id_depense === null) jsonResponse(400, ['success' => false, 'error' => 'id_depense requis']);
            if (!is_array($pieces) || count($pieces) === 0) jsonResponse(200, ['success' => true, 'data' => []]);

            // Verifier que la depense existe
            $checkSql = "SELECT id_depense FROM app_compta_depenses WHERE id_depense = ?";
            $checkParams = [$id_depense];
            if ($id_entreprise !== null) {
                $checkSql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $checkParams[] = $id_entreprise;
            }
            $check = $db->query($checkSql, $checkParams);
            if (empty($check)) jsonResponse(404, ['success' => false, 'error' => 'Depense introuvable']);

            $noms_pieces = [];
            foreach ($pieces as $p) {
                $url = getStr($p['url'] ?? $p['url_fichier'] ?? '');
                if ($url === '') continue;
                $nom_original = getStr($p['nom_original'] ?? '');
                $type_fichier = getStr($p['type_fichier'] ?? '');
                $sql = "INSERT INTO app_compta_depense_pieces
                            (id_depense, id_entreprise, url_fichier, nom_original, type_fichier, create_by, date_creation)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $db->query($sql, [$id_depense, $id_entreprise, $url, $nom_original, $type_fichier, $auth_user_id]);
                if ($nom_original !== '') $noms_pieces[] = $nom_original;
            }

            // Email notification
            $liste_pieces = !empty($noms_pieces) ? implode(', ', array_map('htmlspecialchars', $noms_pieces)) : 'N/A';
            $emailBody = "
                <p>Des pieces justificatives ont ete ajoutees par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>ID Depense</td><td style='padding:10px;border:1px solid #dee2e6;'>{$id_depense}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Nombre de pieces</td><td style='padding:10px;border:1px solid #dee2e6;'>" . count($noms_pieces) . "</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Fichiers</td><td style='padding:10px;border:1px solid #dee2e6;'>{$liste_pieces}</td></tr>
                </table>";
            sendComptaEmailToAdmins($db, $id_entreprise, 'Pieces justificatives ajoutees', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_piece') {
            $id_piece = getInt($body['id_piece'] ?? null, null);
            if ($id_piece === null) jsonResponse(400, ['success' => false, 'error' => 'id_piece requis']);

            // Recuperer infos avant suppression pour l'email
            $pieceInfo = $db->query("SELECT p.nom_original, p.id_depense FROM app_compta_depense_pieces p WHERE p.id_piece = ?", [$id_piece]);

            $sql = "DELETE p FROM app_compta_depense_pieces p
                    INNER JOIN app_compta_depenses d ON d.id_depense = p.id_depense
                    WHERE p.id_piece = ?";
            $params = [$id_piece];
            if ($id_entreprise !== null) {
                $sql .= " AND (d.id_entreprise = ? OR d.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            if (!empty($pieceInfo)) {
                $pi = $pieceInfo[0];
                $emailBody = "
                    <p style='color:#dc3545;font-weight:bold;'>Une piece justificative a ete supprimee par <strong>" . htmlspecialchars($auth_nom) . "</strong>.</p>
                    <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                        <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Fichier supprime</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($pi['nom_original'] ?? 'N/A') . "</td></tr>
                        <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>ID Depense</td><td style='padding:10px;border:1px solid #dee2e6;'>" . ($pi['id_depense'] ?? 'N/A') . "</td></tr>
                    </table>";
                sendComptaEmailToAdmins($db, $id_entreprise, 'Piece justificative supprimee', $emailBody);
            }

            jsonResponse(200, ['success' => true]);
        }

        // ---------- ENVOI MAIL MANUEL (rapport depenses) ----------

        if ($action === 'envoyer_rapport') {
            $email = getStr($body['email'] ?? '');
            $periode = getStr($body['periode'] ?? '');
            $nb_depenses = getInt($body['nb_depenses'] ?? 0, 0);
            $total = (float)($body['total'] ?? 0);
            $details_html = getStr($body['details_html'] ?? '');

            if ($email === '') jsonResponse(400, ['success' => false, 'error' => 'Email requis']);

            try {
                $total_fmt = number_format($total, 0, ',', ' ');
                $sujet = "Rapport des Depenses" . ($periode !== '' ? " - {$periode}" : '');

                $corps = "
                    <div style='font-family:Arial,sans-serif;max-width:700px;margin:0 auto;'>
                        <h2 style='color:#333;border-bottom:2px solid #007bff;padding-bottom:10px;'>
                            Rapport des Depenses
                        </h2>
                        " . ($periode !== '' ? "<p><strong>Periode :</strong> {$periode}</p>" : '') . "

                        <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
                            <tr style='background:#f8f9fa;'>
                                <td style='padding:12px;font-weight:bold;border:1px solid #dee2e6;'>Nombre de depenses</td>
                                <td style='padding:12px;border:1px solid #dee2e6;font-weight:bold;font-size:16px;'>{$nb_depenses}</td>
                            </tr>
                            <tr style='background:#f8d7da;'>
                                <td style='padding:12px;font-weight:bold;border:1px solid #dee2e6;'>Total des depenses</td>
                                <td style='padding:12px;border:1px solid #dee2e6;color:#dc3545;font-weight:bold;font-size:18px;'>{$total_fmt} XOF</td>
                            </tr>
                        </table>

                        " . ($details_html !== '' ? "<h3>Detail des depenses</h3>{$details_html}" : '') . "

                        <p style='color:#666;font-size:12px;margin-top:30px;border-top:1px solid #eee;padding-top:10px;'>
                            Cet email a ete genere automatiquement. Ne pas repondre.
                        </p>
                    </div>
                ";

                Mailer::sendCustom($email, $sujet, $corps);

                jsonResponse(200, ['success' => true, 'message' => 'Rapport envoye par email']);
            } catch (Exception $e) {
                error_log("Erreur envoi rapport: " . $e->getMessage());
                jsonResponse(500, ['success' => false, 'error' => 'Erreur envoi email']);
            }
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
    }

    jsonResponse(405, ['success' => false, 'error' => 'Methode non autorisee']);

} catch (Throwable $e) {
    error_log("Erreur API compta depenses: " . $e->getMessage());
    jsonResponse(500, [
        'success' => false,
        'error' => 'Erreur serveur'
    ]);
}
