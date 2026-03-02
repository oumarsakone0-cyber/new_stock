<?php
// API Gestion Bancaire - Comptes / Transactions

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

// Modèle identique à api_auth.php / api_users.php pour PHPMailer / Mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require_once 'Mailer.php';

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
 * Notification email automatique aux administrateurs lors des opérations bancaires.
 * Remarque: l'envoi ne doit jamais bloquer l'API.
 */
function sendBanqueEmailToAdmins($db, $id_entreprise, $subject, $htmlBody) {
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
            $body  = "<p>Bonjour " . htmlspecialchars($nomComplet ?: 'Admin') . ",</p>";
            $body .= $htmlBody;
            $body .= "<p style=\"font-size:12px;color:#6b7280;\">"
                . "Notification automatique - Gestion bancaire Next Stock."
                . "</p>";

            if (class_exists('Mailer')) {
                Mailer::sendSimpleHtml($to, $subject, $body);
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}

try {
    require_once __DIR__ . '/config/database.php';
    if (!class_exists('Database')) {
        throw new Exception("Classe Database introuvable dans config/database.php");
    }
    $db = new Database();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    $id_entreprise = getInt($_GET['id_entreprise'] ?? null, null);

    // ===================== GET =====================
    if ($method === 'GET') {
        if ($action === 'list_comptes') {
            // solde_actuel calculé: solde_initial + credits - debits
            $sql = "SELECT 
                        c.id_compte as id,
                        c.id_entreprise,
                        c.banque,
                        c.nom_compte,
                        c.numero_compte,
                        c.devise,
                        c.solde_initial,
                        COALESCE(c.solde_initial, 0)
                          + COALESCE(SUM(CASE WHEN t.sens = 'CREDIT' THEN t.montant ELSE 0 END), 0)
                          - COALESCE(SUM(CASE WHEN t.sens = 'DEBIT' THEN t.montant ELSE 0 END), 0)
                          AS solde_actuel
                    FROM app_compta_comptes_bancaires c
                    LEFT JOIN app_compta_transactions_bancaires t ON t.id_compte = c.id_compte
                    WHERE 1=1";
            $params = [];
            if ($id_entreprise !== null) {
                $sql .= " AND (c.id_entreprise = ? OR c.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " GROUP BY c.id_compte
                      ORDER BY c.nom_compte ASC";
            $rows = $db->query($sql, $params);
            foreach ($rows as &$r) {
                $r['solde_initial'] = (float)($r['solde_initial'] ?? 0);
                $r['solde_actuel'] = (float)($r['solde_actuel'] ?? 0);
            }
            unset($r);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        if ($action === 'list_transactions') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $id_compte = getInt($_GET['id_compte'] ?? null, null);
            $sens = getStr($_GET['sens'] ?? '');
            $search = getStr($_GET['search'] ?? '');

            $sql = "SELECT
                        t.id_transaction as id,
                        t.id_compte,
                        c.nom_compte as compte_nom,
                        t.date_transaction,
                        t.sens,
                        t.montant,
                        t.reference,
                        t.libelle,
                        t.note,
                        t.date_creation
                    FROM app_compta_transactions_bancaires t
                    INNER JOIN app_compta_comptes_bancaires c ON c.id_compte = t.id_compte
                    WHERE 1=1";
            $params = [];
            if ($id_entreprise !== null) {
                $sql .= " AND (t.id_entreprise = ? OR t.id_entreprise IS NULL)";
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
            $rows = $db->query($sql, $params);
            foreach ($rows as &$r) {
                $r['montant'] = (float)($r['montant'] ?? 0);
            }
            unset($r);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action GET inconnue']);
    }

    // ===================== POST =====================
    if ($method === 'POST') {
        $body = readJsonBody();
        $id_entreprise_body = getInt($body['id_entreprise'] ?? null, null);
        if ($id_entreprise === null && $id_entreprise_body !== null) {
            $id_entreprise = $id_entreprise_body;
        }

        if ($action === 'add_compte') {
            $banque = getStr($body['banque'] ?? '');
            $nom_compte = getStr($body['nom_compte'] ?? '');
            $numero_compte = getStr($body['numero_compte'] ?? '');
            $devise = getStr($body['devise'] ?? 'XOF');
            $solde_initial = (float)($body['solde_initial'] ?? 0);
            $user_id = getInt($body['user_id'] ?? null, null);
            if ($nom_compte === '') jsonResponse(400, ['success' => false, 'error' => 'Nom du compte requis']);

            $sql = "INSERT INTO app_compta_comptes_bancaires
                        (id_entreprise, banque, nom_compte, numero_compte, devise, solde_initial, create_by, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $banque, $nom_compte, $numero_compte, $devise, $solde_initial, $user_id]);

            $emailBody = "<p>Un nouveau compte bancaire a &eacute;t&eacute; cr&eacute;&eacute;.</p>"
                . "<ul>"
                . "<li><strong>Banque :</strong> " . htmlspecialchars($banque) . "</li>"
                . "<li><strong>Nom du compte :</strong> " . htmlspecialchars($nom_compte) . "</li>"
                . "<li><strong>Num&eacute;ro :</strong> " . htmlspecialchars($numero_compte) . "</li>"
                . "<li><strong>Devise :</strong> " . htmlspecialchars($devise) . "</li>"
                . "<li><strong>Solde initial :</strong> " . htmlspecialchars((string)$solde_initial) . "</li>"
                . "</ul>";
            sendBanqueEmailToAdmins($db, $id_entreprise, 'Nouveau compte bancaire', $emailBody);

            jsonResponse(200, ['success' => true, 'data' => ['id_compte' => (int)$db->lastInsertId()]]);
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

            $emailBody = "<p>Un compte bancaire a &eacute;t&eacute; modifi&eacute;.</p>"
                . "<ul>"
                . "<li><strong>ID compte :</strong> " . htmlspecialchars((string)$id_compte) . "</li>"
                . "<li><strong>Banque :</strong> " . htmlspecialchars($banque) . "</li>"
                . "<li><strong>Nom du compte :</strong> " . htmlspecialchars($nom_compte) . "</li>"
                . "<li><strong>Num&eacute;ro :</strong> " . htmlspecialchars($numero_compte) . "</li>"
                . "<li><strong>Devise :</strong> " . htmlspecialchars($devise) . "</li>"
                . "<li><strong>Solde initial :</strong> " . htmlspecialchars((string)$solde_initial) . "</li>"
                . "</ul>";
            sendBanqueEmailToAdmins($db, $id_entreprise, 'Compte bancaire modifi&eacute;', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_compte') {
            $id_compte = getInt($body['id_compte'] ?? null, null);
            if ($id_compte === null) jsonResponse(400, ['success' => false, 'error' => 'id_compte requis']);

            // Récupérer infos avant suppression pour le mail
            $compteInfo = null;
            try {
                $infoSql = "SELECT banque, nom_compte, numero_compte, devise, solde_initial
                            FROM app_compta_comptes_bancaires
                            WHERE id_compte = ?";
                $infoParams = [$id_compte];
                if ($id_entreprise !== null) {
                    $infoSql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                    $infoParams[] = $id_entreprise;
                }
                $rowsInfo = $db->query($infoSql, $infoParams);
                if (!empty($rowsInfo)) $compteInfo = $rowsInfo[0];
            } catch (Throwable $e) {
                // ignore
            }

            // Empêcher suppression si transactions existent
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

            $banque = $compteInfo['banque'] ?? '';
            $nom_compte = $compteInfo['nom_compte'] ?? '';
            $numero_compte = $compteInfo['numero_compte'] ?? '';
            $devise = $compteInfo['devise'] ?? '';
            $solde_initial = isset($compteInfo['solde_initial']) ? (float)$compteInfo['solde_initial'] : null;

            $emailBody = "<p>Un compte bancaire a &eacute;t&eacute; supprim&eacute;.</p>"
                . "<ul>"
                . "<li><strong>ID compte :</strong> " . htmlspecialchars((string)$id_compte) . "</li>"
                . "<li><strong>Banque :</strong> " . htmlspecialchars($banque) . "</li>"
                . "<li><strong>Nom du compte :</strong> " . htmlspecialchars($nom_compte) . "</li>"
                . "<li><strong>Num&eacute;ro :</strong> " . htmlspecialchars($numero_compte) . "</li>"
                . "<li><strong>Devise :</strong> " . htmlspecialchars($devise) . "</li>"
                . "<li><strong>Solde initial :</strong> " . htmlspecialchars((string)$solde_initial) . "</li>"
                . "</ul>";
            sendBanqueEmailToAdmins($db, $id_entreprise, 'Compte bancaire supprim&eacute;', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'add_transaction') {
            $date_transaction = getStr($body['date_transaction'] ?? '');
            $id_compte = getInt($body['id_compte'] ?? null, null);
            $sens = getStr($body['sens'] ?? '');
            $montant = (float)($body['montant'] ?? 0);
            $reference = getStr($body['reference'] ?? '');
            $libelle = getStr($body['libelle'] ?? '');
            $note = getStr($body['note'] ?? '');
            $user_id = getInt($body['user_id'] ?? null, null);

            if ($date_transaction === '' || $id_compte === null || ($sens !== 'DEBIT' && $sens !== 'CREDIT') || $montant <= 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Champs requis: date_transaction, id_compte, sens, montant']);
            }

            // vérifier compte existant
            $checkSql = "SELECT id_compte FROM app_compta_comptes_bancaires WHERE id_compte = ?";
            $checkParams = [$id_compte];
            if ($id_entreprise !== null) {
                $checkSql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $checkParams[] = $id_entreprise;
            }
            $check = $db->query($checkSql, $checkParams);
            if (empty($check)) jsonResponse(404, ['success' => false, 'error' => 'Compte introuvable']);

            $sql = "INSERT INTO app_compta_transactions_bancaires
                        (id_entreprise, id_compte, date_transaction, sens, montant, reference, libelle, note, create_by, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $id_compte, $date_transaction, $sens, $montant, $reference, $libelle, $note, $user_id]);

            $compteNom = '';
            try {
                $c = $db->query("SELECT nom_compte FROM app_compta_comptes_bancaires WHERE id_compte = ?", [$id_compte]);
                if (!empty($c)) $compteNom = $c[0]['nom_compte'] ?? '';
            } catch (Throwable $e) {}

            $emailBody = "<p>Une nouvelle transaction bancaire a &eacute;t&eacute; enregistr&eacute;e.</p>"
                . "<ul>"
                . "<li><strong>Date :</strong> " . htmlspecialchars($date_transaction) . "</li>"
                . "<li><strong>Compte :</strong> " . htmlspecialchars($compteNom ?: (string)$id_compte) . "</li>"
                . "<li><strong>Sens :</strong> " . htmlspecialchars($sens) . "</li>"
                . "<li><strong>Montant :</strong> " . htmlspecialchars((string)$montant) . "</li>"
                . "<li><strong>R&eacute;f&eacute;rence :</strong> " . htmlspecialchars($reference) . "</li>"
                . "<li><strong>Libell&eacute; :</strong> " . htmlspecialchars($libelle) . "</li>"
                . "</ul>";
            sendBanqueEmailToAdmins($db, $id_entreprise, 'Nouvelle transaction bancaire', $emailBody);

            jsonResponse(200, ['success' => true, 'data' => ['id_transaction' => (int)$db->lastInsertId()]]);
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

            $compteNom = '';
            try {
                $c = $db->query("SELECT nom_compte FROM app_compta_comptes_bancaires WHERE id_compte = ?", [$id_compte]);
                if (!empty($c)) $compteNom = $c[0]['nom_compte'] ?? '';
            } catch (Throwable $e) {}

            $emailBody = "<p>Une transaction bancaire a &eacute;t&eacute; modifi&eacute;e.</p>"
                . "<ul>"
                . "<li><strong>ID transaction :</strong> " . htmlspecialchars((string)$id_transaction) . "</li>"
                . "<li><strong>Date :</strong> " . htmlspecialchars($date_transaction) . "</li>"
                . "<li><strong>Compte :</strong> " . htmlspecialchars($compteNom ?: (string)$id_compte) . "</li>"
                . "<li><strong>Sens :</strong> " . htmlspecialchars($sens) . "</li>"
                . "<li><strong>Montant :</strong> " . htmlspecialchars((string)$montant) . "</li>"
                . "<li><strong>R&eacute;f&eacute;rence :</strong> " . htmlspecialchars($reference) . "</li>"
                . "<li><strong>Libell&eacute; :</strong> " . htmlspecialchars($libelle) . "</li>"
                . "</ul>";
            sendBanqueEmailToAdmins($db, $id_entreprise, 'Transaction bancaire modifi&eacute;e', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_transaction') {
            $id_transaction = getInt($body['id_transaction'] ?? null, null);
            if ($id_transaction === null) jsonResponse(400, ['success' => false, 'error' => 'id_transaction requis']);

            // récupérer infos avant suppression
            $txInfo = null;
            try {
                $infoSql = "SELECT t.date_transaction, t.sens, t.montant, t.reference, t.libelle, t.id_compte, c.nom_compte as compte_nom
                            FROM app_compta_transactions_bancaires t
                            INNER JOIN app_compta_comptes_bancaires c ON c.id_compte = t.id_compte
                            WHERE t.id_transaction = ?";
                $infoParams = [$id_transaction];
                if ($id_entreprise !== null) {
                    $infoSql .= " AND (t.id_entreprise = ? OR t.id_entreprise IS NULL)";
                    $infoParams[] = $id_entreprise;
                }
                $rowsInfo = $db->query($infoSql, $infoParams);
                if (!empty($rowsInfo)) $txInfo = $rowsInfo[0];
            } catch (Throwable $e) {}

            $sql = "DELETE FROM app_compta_transactions_bancaires WHERE id_transaction = ?";
            $params = [$id_transaction];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);

            $date_transaction = $txInfo['date_transaction'] ?? '';
            $sens = $txInfo['sens'] ?? '';
            $montant = isset($txInfo['montant']) ? (float)$txInfo['montant'] : null;
            $reference = $txInfo['reference'] ?? '';
            $libelle = $txInfo['libelle'] ?? '';
            $compteNom = $txInfo['compte_nom'] ?? ($txInfo['id_compte'] ?? '');

            $emailBody = "<p>Une transaction bancaire a &eacute;t&eacute; supprim&eacute;e.</p>"
                . "<ul>"
                . "<li><strong>ID transaction :</strong> " . htmlspecialchars((string)$id_transaction) . "</li>"
                . "<li><strong>Date :</strong> " . htmlspecialchars((string)$date_transaction) . "</li>"
                . "<li><strong>Compte :</strong> " . htmlspecialchars((string)$compteNom) . "</li>"
                . "<li><strong>Sens :</strong> " . htmlspecialchars((string)$sens) . "</li>"
                . "<li><strong>Montant :</strong> " . htmlspecialchars((string)$montant) . "</li>"
                . "<li><strong>R&eacute;f&eacute;rence :</strong> " . htmlspecialchars((string)$reference) . "</li>"
                . "<li><strong>Libell&eacute; :</strong> " . htmlspecialchars((string)$libelle) . "</li>"
                . "</ul>";
            sendBanqueEmailToAdmins($db, $id_entreprise, 'Transaction bancaire supprim&eacute;e', $emailBody);

            jsonResponse(200, ['success' => true]);
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
    }

    jsonResponse(405, ['success' => false, 'error' => 'Méthode non autorisée']);

} catch (Throwable $e) {
    jsonResponse(500, [
        'success' => false,
        'error' => 'Erreur serveur',
        'details' => $e->getMessage()
    ]);
}

