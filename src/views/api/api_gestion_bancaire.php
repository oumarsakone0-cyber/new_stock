<?php
// API Gestion Bancaire - Comptes / Transactions
// Securise par JWT

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

// ===================== PHPMailer =====================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require_once 'Mailer.php';

// ===================== Database + Auth (JWT + user from DB pour id_entreprise fiable) =====================
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth.php';

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

// ===================== Notification Email aux Admins =====================
function sendBancaireEmailToAdmins($db, $id_entreprise, $subject, $htmlContent, $auteur = '') {
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
        if (empty($admins)) return;

        foreach ($admins as $admin) {
            $to = trim($admin['email'] ?? '');
            if ($to === '') continue;

            $nomComplet = trim(($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? ''));
            $body  = "<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>";
            $body .= "<h2 style='color:#333;border-bottom:2px solid #007bff;padding-bottom:10px;'>"
                   . htmlspecialchars($subject) . "</h2>";
            $body .= "<p>Bonjour " . htmlspecialchars($nomComplet ?: 'Admin') . ",</p>";
            if ($auteur !== '') {
                $body .= "<p>Action effectuee par <strong>" . htmlspecialchars($auteur) . "</strong>.</p>";
            }
            $body .= $htmlContent;
            $body .= "<p style='color:#666;font-size:12px;margin-top:20px;border-top:1px solid #eee;padding-top:10px;'>"
                   . "Notification automatique - Gestion Bancaire."
                   . "</p></div>";

            if (class_exists('Mailer') && method_exists('Mailer', 'sendSimpleHtml')) {
                Mailer::sendSimpleHtml($to, $subject, $body);
            } elseif (class_exists('Mailer') && method_exists('Mailer', 'sendCustom')) {
                Mailer::sendCustom($to, $subject, $body);
            }
        }
    } catch (Throwable $e) {
        error_log("sendBancaireEmailToAdmins error: " . $e->getMessage());
    }
}

// ===================== AUTHENTIFICATION (user depuis la BDD pour id_entreprise correct) =====================
$authUser = getUserFromToken();
$auth_user_id       = $authUser['id'] ?? null;
$auth_id_entreprise = $authUser['id_entreprise'] ?? null;
$auth_nom           = trim(($authUser['prenom'] ?? '') . ' ' . ($authUser['nom'] ?? ''));
$access             = $authUser['access'] ?? [];
$is_admin           = is_array($access) && in_array('ALL', $access);

try {
    if (!class_exists('Database')) {
        throw new Exception("Classe Database introuvable dans config/database.php");
    }
    $db = new Database();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // id_entreprise: toujours depuis l'utilisateur connecte (comme api_clients) pour coherence add/list
    $id_entreprise = $auth_id_entreprise;

    // ===================== GET =====================
    if ($method === 'GET') {
        if ($action === 'list_comptes') {
            $sql = "SELECT 
                        c.id_compte as id,
                        c.banque,
                        c.nom_compte,
                        c.devise,
                        COALESCE(c.solde_initial, 0)
                          + COALESCE(SUM(CASE WHEN t.sens = 'CREDIT' THEN t.montant ELSE 0 END), 0)
                          - COALESCE(SUM(CASE WHEN t.sens = 'DEBIT' THEN t.montant ELSE 0 END), 0)
                          AS solde_actuel
                    FROM app_compta_comptes_bancaires c
                    LEFT JOIN app_compta_transactions_bancaires t ON t.id_compte = c.id_compte
                    WHERE 1=1";
            $params = [];
            if (!$is_admin && $id_entreprise !== null) {
                $sql .= " AND (c.id_entreprise = ? OR c.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " GROUP BY c.id_compte
                      ORDER BY c.nom_compte ASC";
            $rows = $db->query($sql, $params);
            if (!is_array($rows)) $rows = [];
            foreach ($rows as &$r) {
                $r['solde_actuel'] = (float)($r['solde_actuel'] ?? 0);
            }
            unset($r);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        // Action combinee : comptes + transactions en une requete (pour la page Transactions)
        if ($action === 'list_comptes_et_transactions') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $id_compte = getInt($_GET['id_compte'] ?? null, null);
            $sens = getStr($_GET['sens'] ?? '');
            $search = getStr($_GET['search'] ?? '');

            $sql_comptes = "SELECT 
                        c.id_compte as id,
                        c.banque,
                        c.nom_compte,
                        c.devise,
                        COALESCE(c.solde_initial, 0)
                          + COALESCE(SUM(CASE WHEN t.sens = 'CREDIT' THEN t.montant ELSE 0 END), 0)
                          - COALESCE(SUM(CASE WHEN t.sens = 'DEBIT' THEN t.montant ELSE 0 END), 0)
                          AS solde_actuel
                    FROM app_compta_comptes_bancaires c
                    LEFT JOIN app_compta_transactions_bancaires t ON t.id_compte = c.id_compte
                    WHERE 1=1";
            $params_c = [];
            if (!$is_admin && $id_entreprise !== null) {
                $sql_comptes .= " AND (c.id_entreprise = ? OR c.id_entreprise IS NULL)";
                $params_c[] = $id_entreprise;
            }
            $sql_comptes .= " GROUP BY c.id_compte ORDER BY c.nom_compte ASC";
            $comptes = $db->query($sql_comptes, $params_c);
            if (!is_array($comptes)) $comptes = [];
            foreach ($comptes as &$r) {
                $r['solde_actuel'] = (float)($r['solde_actuel'] ?? 0);
            }
            unset($r);

            // Transactions : partir des comptes deja retournes (ids) pour eviter tout decalage filtre
            $compte_ids = array_column($comptes, 'id');
            $transactions = [];
            if (!empty($compte_ids)) {
                $placeholders = implode(',', array_fill(0, count($compte_ids), '?'));
                $sql_t = "SELECT
                        t.id_transaction as id,
                        t.id_compte,
                        t.date_transaction,
                        t.sens,
                        t.montant,
                        t.reference,
                        t.libelle,
                        t.note,
                        t.date_creation,
                        c.nom_compte as compte_nom
                    FROM app_compta_transactions_bancaires t
                    INNER JOIN app_compta_comptes_bancaires c ON c.id_compte = t.id_compte
                    WHERE t.id_compte IN ($placeholders)";
                $params_t = array_values($compte_ids);
                if ($id_compte !== null && in_array($id_compte, $compte_ids, true)) {
                    $sql_t .= " AND t.id_compte = ?";
                    $params_t[] = $id_compte;
                }
                if ($sens === 'DEBIT' || $sens === 'CREDIT') {
                    $sql_t .= " AND t.sens = ?";
                    $params_t[] = $sens;
                }
                if ($date_debut !== '') {
                    $sql_t .= " AND t.date_transaction >= ?";
                    $params_t[] = $date_debut;
                }
                if ($date_fin !== '') {
                    $sql_t .= " AND t.date_transaction <= ?";
                    $params_t[] = $date_fin;
                }
                if ($search !== '') {
                    $sql_t .= " AND (t.reference LIKE ? OR t.libelle LIKE ? OR t.note LIKE ?)";
                    $like = '%' . $search . '%';
                    $params_t[] = $like;
                    $params_t[] = $like;
                    $params_t[] = $like;
                }
                $sql_t .= " ORDER BY t.date_transaction DESC, t.id_transaction DESC";
                try {
                    $transactions = $db->query($sql_t, $params_t);
                } catch (Throwable $e) {
                    error_log("list_comptes_et_transactions transactions: " . $e->getMessage());
                    $transactions = [];
                }
            }
            if (!is_array($transactions)) $transactions = [];
            foreach ($transactions as &$r) {
                $r['montant'] = (float)($r['montant'] ?? 0);
                if (empty($r['compte_nom'])) $r['compte_nom'] = '--';
            }
            unset($r);

            jsonResponse(200, [
                'success' => true,
                'comptes' => $comptes,
                'transactions' => $transactions,
            ]);
        }

        if ($action === 'list_transactions') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $id_compte = getInt($_GET['id_compte'] ?? null, null);
            $sens = getStr($_GET['sens'] ?? '');
            $search = getStr($_GET['search'] ?? '');

            // Meme logique : JOIN comptes pour filtrer par entreprise
            $sql = "SELECT
                        t.id_transaction as id,
                        t.id_compte,
                        t.date_transaction,
                        t.sens,
                        t.montant,
                        t.reference,
                        t.libelle,
                        t.note,
                        t.date_creation,
                        c.nom_compte as compte_nom
                    FROM app_compta_transactions_bancaires t
                    INNER JOIN app_compta_comptes_bancaires c ON c.id_compte = t.id_compte
                    WHERE 1=1";
            $params = [];
            if (!$is_admin && $id_entreprise !== null) {
                $sql .= " AND (c.id_entreprise = ? OR c.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            if ($id_compte !== null) {
                $sql .= " AND t.id_compte = ?";
                $params[] = $id_compte;
            }
            if ($sens === 'DEBIT' || $sens === 'CREDIT') {
                $sql .= " AND t.sens = ?";
                $params[] = $sens;
            }
            if ($date_debut !== '') {
                $sql .= " AND t.date_transaction >= ?";
                $params[] = $date_debut;
            }
            if ($date_fin !== '') {
                $sql .= " AND t.date_transaction <= ?";
                $params[] = $date_fin;
            }
            if ($search !== '') {
                $sql .= " AND (t.reference LIKE ? OR t.libelle LIKE ? OR t.note LIKE ?)";
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            $sql .= " ORDER BY t.date_transaction DESC, t.id_transaction DESC";
            try {
                $rows = $db->query($sql, $params);
            } catch (Throwable $e) {
                error_log("list_transactions: " . $e->getMessage());
                $rows = [];
            }
            if (!is_array($rows)) $rows = [];
            foreach ($rows as &$r) {
                $r['montant'] = (float)($r['montant'] ?? 0);
                if (empty($r['compte_nom'])) $r['compte_nom'] = '--';
            }
            unset($r);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action GET inconnue']);
    }

    // ===================== POST =====================
    if ($method === 'POST') {
        $body = readJsonBody();

        // ---------- COMPTES ----------

        if ($action === 'add_compte') {
            $banque = getStr($body['banque'] ?? '');
            $nom_compte = getStr($body['nom_compte'] ?? '');
            $numero_compte = getStr($body['numero_compte'] ?? '');
            $devise = getStr($body['devise'] ?? 'XOF');
            $solde_initial = (float)($body['solde_initial'] ?? 0);
            if ($nom_compte === '') jsonResponse(400, ['success' => false, 'error' => 'Nom du compte requis']);

            $sql = "INSERT INTO app_compta_comptes_bancaires
                        (id_entreprise, banque, nom_compte, numero_compte, devise, solde_initial, create_by, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $banque, $nom_compte, $numero_compte, $devise, $solde_initial, $auth_user_id]);
            $new_id = (int)$db->lastInsertId();

            // Email notification
            $montant_fmt = number_format($solde_initial, 0, ',', ' ');
            $emailHtml = "
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Banque</td><td style='padding:10px;border:1px solid #dee2e6;'>{$banque}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Nom du compte</td><td style='padding:10px;border:1px solid #dee2e6;'>{$nom_compte}</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Devise</td><td style='padding:10px;border:1px solid #dee2e6;'>{$devise}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Solde initial</td><td style='padding:10px;border:1px solid #dee2e6;font-weight:bold;'>{$montant_fmt} {$devise}</td></tr>
                </table>
            ";
            sendBancaireEmailToAdmins($db, $id_entreprise, "Nouveau compte bancaire : {$nom_compte}", $emailHtml, $auth_nom);

            jsonResponse(200, ['success' => true, 'data' => ['id_compte' => $new_id]]);
        }

        if ($action === 'update_compte') {
            $id_compte = getInt($body['id_compte'] ?? null, null);
            if ($id_compte === null) jsonResponse(400, ['success' => false, 'error' => 'id_compte requis']);
            $banque = getStr($body['banque'] ?? '');
            $nom_compte = getStr($body['nom_compte'] ?? '');
            $numero_compte = getStr($body['numero_compte'] ?? '');
            $devise = getStr($body['devise'] ?? 'XOF');
            $solde_initial = (float)($body['solde_initial'] ?? 0);
            if ($nom_compte === '') jsonResponse(400, ['success' => false, 'error' => 'Nom du compte requis']);

            $sql = "UPDATE app_compta_comptes_bancaires
                    SET banque = ?, nom_compte = ?, numero_compte = ?, devise = ?, solde_initial = ?, date_modification = NOW()
                    WHERE id_compte = ?";
            $params = [$banque, $nom_compte, $numero_compte, $devise, $solde_initial, $id_compte];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            $montant_fmt = number_format($solde_initial, 0, ',', ' ');
            $emailHtml = "
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Banque</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($banque) . "</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Nom du compte</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($nom_compte) . "</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Devise</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($devise) . "</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Solde initial</td><td style='padding:10px;border:1px solid #dee2e6;font-weight:bold;'>{$montant_fmt} {$devise}</td></tr>
                </table>
            ";
            sendBancaireEmailToAdmins($db, $id_entreprise, "Compte bancaire modifie : {$nom_compte}", $emailHtml, $auth_nom);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_compte') {
            $id_compte = getInt($body['id_compte'] ?? null, null);
            if ($id_compte === null) jsonResponse(400, ['success' => false, 'error' => 'id_compte requis']);

            // Recuperer infos avant suppression pour l'email
            $compteInfo = $db->query("SELECT banque, nom_compte, devise, solde_initial FROM app_compta_comptes_bancaires WHERE id_compte = ?", [$id_compte]);

            $check = $db->query("SELECT COUNT(*) as c FROM app_compta_transactions_bancaires WHERE id_compte = ?", [$id_compte]);
            $count = !empty($check) ? (int)($check[0]['c'] ?? 0) : 0;
            if ($count > 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Compte contient des transactions']);
            }

            $sql = "DELETE FROM app_compta_comptes_bancaires WHERE id_compte = ?";
            $params = [$id_compte];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            if (!empty($compteInfo)) {
                $ci = $compteInfo[0];
                $emailHtml = "
                    <p style='color:#dc3545;font-weight:bold;'>Compte bancaire supprime</p>
                    <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                        <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Banque</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($ci['banque']) . "</td></tr>
                        <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Nom du compte</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($ci['nom_compte']) . "</td></tr>
                        <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Devise</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($ci['devise']) . "</td></tr>
                    </table>
                ";
                sendBancaireEmailToAdmins($db, $id_entreprise, "Compte bancaire supprime : " . $ci['nom_compte'], $emailHtml, $auth_nom);
            }

            jsonResponse(200, ['success' => true]);
        }

        // ---------- TRANSACTIONS ----------

        if ($action === 'add_transaction') {
            $date_transaction = getStr($body['date_transaction'] ?? '');
            $id_compte = getInt($body['id_compte'] ?? null, null);
            $sens = getStr($body['sens'] ?? '');
            $montant = (float)($body['montant'] ?? 0);
            $reference = getStr($body['reference'] ?? '');
            $libelle = getStr($body['libelle'] ?? '');
            $note = getStr($body['note'] ?? '');

            if ($date_transaction === '' || $id_compte === null || ($sens !== 'DEBIT' && $sens !== 'CREDIT') || $montant <= 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Champs requis: date_transaction, id_compte, sens, montant']);
            }

            // Verifier compte existant
            $checkSql = "SELECT id_compte, nom_compte FROM app_compta_comptes_bancaires WHERE id_compte = ?";
            $checkParams = [$id_compte];
            if ($id_entreprise !== null) {
                $checkSql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $checkParams[] = $id_entreprise;
            }
            $check = $db->query($checkSql, $checkParams);
            if (empty($check)) jsonResponse(404, ['success' => false, 'error' => 'Compte introuvable']);

            $nom_compte = $check[0]['nom_compte'] ?? 'N/A';

            $sql = "INSERT INTO app_compta_transactions_bancaires
                        (id_entreprise, id_compte, date_transaction, sens, montant, reference, libelle, note, create_by, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $id_compte, $date_transaction, $sens, $montant, $reference, $libelle, $note, $auth_user_id]);
            $id_transaction = (int)$db->lastInsertId();

            // Email notification automatique aux admins
            $type_label = ($sens === 'CREDIT') ? 'Credit' : 'Debit';
            $color = ($sens === 'CREDIT') ? '#28a745' : '#dc3545';
            $montant_fmt = number_format($montant, 0, ',', ' ');
            $emailHtml = "
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Type</td><td style='padding:10px;border:1px solid #dee2e6;color:{$color};font-weight:bold;'>{$type_label}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Montant</td><td style='padding:10px;border:1px solid #dee2e6;font-size:18px;font-weight:bold;'>{$montant_fmt} XOF</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Compte</td><td style='padding:10px;border:1px solid #dee2e6;'>{$nom_compte}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Date</td><td style='padding:10px;border:1px solid #dee2e6;'>{$date_transaction}</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Reference</td><td style='padding:10px;border:1px solid #dee2e6;'>{$reference}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle</td><td style='padding:10px;border:1px solid #dee2e6;'>{$libelle}</td></tr>
                </table>
            ";
            sendBancaireEmailToAdmins($db, $id_entreprise, "Nouvelle transaction {$type_label} - {$montant_fmt} XOF", $emailHtml, $auth_nom);

            jsonResponse(200, ['success' => true, 'data' => ['id_transaction' => $id_transaction]]);
        }

        if ($action === 'update_transaction') {
            $id_transaction = getInt($body['id_transaction'] ?? null, null);
            if ($id_transaction === null) jsonResponse(400, ['success' => false, 'error' => 'id_transaction requis']);

            $date_transaction = getStr($body['date_transaction'] ?? '');
            $id_compte = getInt($body['id_compte'] ?? null, null);
            $sens = getStr($body['sens'] ?? '');
            $montant = (float)($body['montant'] ?? 0);
            $reference = getStr($body['reference'] ?? '');
            $libelle = getStr($body['libelle'] ?? '');
            $note = getStr($body['note'] ?? '');

            if ($date_transaction === '' || $id_compte === null || ($sens !== 'DEBIT' && $sens !== 'CREDIT') || $montant <= 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Champs requis: date_transaction, id_compte, sens, montant']);
            }

            $sql = "UPDATE app_compta_transactions_bancaires
                    SET id_compte = ?, date_transaction = ?, sens = ?, montant = ?, reference = ?, libelle = ?, note = ?, date_modification = NOW()
                    WHERE id_transaction = ?";
            $params = [$id_compte, $date_transaction, $sens, $montant, $reference, $libelle, $note, $id_transaction];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            $type_label = ($sens === 'CREDIT') ? 'Credit' : 'Debit';
            $color = ($sens === 'CREDIT') ? '#28a745' : '#dc3545';
            $montant_fmt = number_format($montant, 0, ',', ' ');
            // Recuperer nom_compte
            $compteRows = $db->query("SELECT nom_compte FROM app_compta_comptes_bancaires WHERE id_compte = ?", [$id_compte]);
            $nom_compte_email = !empty($compteRows) ? $compteRows[0]['nom_compte'] : 'N/A';
            $emailHtml = "
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Type</td><td style='padding:10px;border:1px solid #dee2e6;color:{$color};font-weight:bold;'>{$type_label}</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Montant</td><td style='padding:10px;border:1px solid #dee2e6;font-size:18px;font-weight:bold;'>{$montant_fmt} XOF</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Compte</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($nom_compte_email) . "</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Date</td><td style='padding:10px;border:1px solid #dee2e6;'>{$date_transaction}</td></tr>
                    <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Reference</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($reference) . "</td></tr>
                    <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($libelle) . "</td></tr>
                </table>
            ";
            sendBancaireEmailToAdmins($db, $id_entreprise, "Transaction modifiee {$type_label} - {$montant_fmt} XOF", $emailHtml, $auth_nom);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_transaction') {
            $id_transaction = getInt($body['id_transaction'] ?? null, null);
            if ($id_transaction === null) jsonResponse(400, ['success' => false, 'error' => 'id_transaction requis']);

            // Recuperer les infos avant suppression pour l'email
            $info = $db->query(
                "SELECT t.sens, t.montant, t.date_transaction, t.reference, t.libelle, c.nom_compte
                 FROM app_compta_transactions_bancaires t
                 INNER JOIN app_compta_comptes_bancaires c ON c.id_compte = t.id_compte
                 WHERE t.id_transaction = ?", [$id_transaction]
            );

            $sql = "DELETE FROM app_compta_transactions_bancaires WHERE id_transaction = ?";
            $params = [$id_transaction];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            // Email notification
            if (!empty($info)) {
                $tr = $info[0];
                $type_label = ($tr['sens'] === 'CREDIT') ? 'Credit' : 'Debit';
                $montant_fmt = number_format((float)$tr['montant'], 0, ',', ' ');
                $emailHtml = "
                    <p style='color:#dc3545;font-weight:bold;'>Transaction supprimee</p>
                    <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                        <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Type</td><td style='padding:10px;border:1px solid #dee2e6;'>{$type_label}</td></tr>
                        <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Montant</td><td style='padding:10px;border:1px solid #dee2e6;'>{$montant_fmt} XOF</td></tr>
                        <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Compte</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($tr['nom_compte']) . "</td></tr>
                        <tr><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Date</td><td style='padding:10px;border:1px solid #dee2e6;'>{$tr['date_transaction']}</td></tr>
                        <tr style='background:#f8f9fa;'><td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle</td><td style='padding:10px;border:1px solid #dee2e6;'>" . htmlspecialchars($tr['libelle']) . "</td></tr>
                    </table>
                ";
                sendBancaireEmailToAdmins($db, $id_entreprise, "Suppression transaction {$type_label} - {$montant_fmt} XOF", $emailHtml, $auth_nom);
            }

            jsonResponse(200, ['success' => true]);
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
    }

    jsonResponse(405, ['success' => false, 'error' => 'Methode non autorisee']);

} catch (Throwable $e) {
    error_log("Erreur API bancaire: " . $e->getMessage());
    jsonResponse(500, [
        'success' => false,
        'error' => 'Erreur serveur'
    ]);
}
