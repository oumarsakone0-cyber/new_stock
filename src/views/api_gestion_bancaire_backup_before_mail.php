<?php
// API Gestion Bancaire - Comptes / Transactions + Envoi Mail

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

// ===================== Database =====================
require_once __DIR__ . '/config/database.php';

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

try {
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

        // ---------- COMPTES ----------

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
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_compte') {
            $id_compte = getInt($body['id_compte'] ?? null, null);
            if ($id_compte === null) jsonResponse(400, ['success' => false, 'error' => 'id_compte requis']);

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
            $user_id = getInt($body['user_id'] ?? null, null);
            $email_destinataire = getStr($body['email_notification'] ?? '');

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
            $db->query($sql, [$id_entreprise, $id_compte, $date_transaction, $sens, $montant, $reference, $libelle, $note, $user_id]);
            $id_transaction = (int)$db->lastInsertId();

            // ===================== ENVOI MAIL NOTIFICATION =====================
            $mail_sent = false;
            if ($email_destinataire !== '') {
                try {
                    $type_label = ($sens === 'CREDIT') ? 'Credit' : 'Debit';
                    $montant_fmt = number_format($montant, 0, ',', ' ');
                    $sujet = "Nouvelle transaction {$type_label} - {$montant_fmt} XOF";

                    $corps = "
                        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                            <h2 style='color:#333;border-bottom:2px solid #007bff;padding-bottom:10px;'>
                                Notification de Transaction Bancaire
                            </h2>
                            <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
                                <tr style='background:#f8f9fa;'>
                                    <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Type</td>
                                    <td style='padding:10px;border:1px solid #dee2e6;color:" . ($sens === 'CREDIT' ? '#28a745' : '#dc3545') . ";font-weight:bold;'>{$type_label}</td>
                                </tr>
                                <tr>
                                    <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Montant</td>
                                    <td style='padding:10px;border:1px solid #dee2e6;font-size:18px;font-weight:bold;'>{$montant_fmt} XOF</td>
                                </tr>
                                <tr style='background:#f8f9fa;'>
                                    <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Compte</td>
                                    <td style='padding:10px;border:1px solid #dee2e6;'>{$nom_compte}</td>
                                </tr>
                                <tr>
                                    <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Date</td>
                                    <td style='padding:10px;border:1px solid #dee2e6;'>{$date_transaction}</td>
                                </tr>
                                <tr style='background:#f8f9fa;'>
                                    <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Reference</td>
                                    <td style='padding:10px;border:1px solid #dee2e6;'>{$reference}</td>
                                </tr>
                                <tr>
                                    <td style='padding:10px;font-weight:bold;border:1px solid #dee2e6;'>Libelle</td>
                                    <td style='padding:10px;border:1px solid #dee2e6;'>{$libelle}</td>
                                </tr>
                            </table>
                            <p style='color:#666;font-size:12px;margin-top:20px;'>
                                Cet email a ete envoye automatiquement. Ne pas repondre.
                            </p>
                        </div>
                    ";

                    Mailer::sendCustom($email_destinataire, $sujet, $corps);
                    $mail_sent = true;
                } catch (Exception $e) {
                    error_log("Erreur envoi mail transaction: " . $e->getMessage());
                }
            }

            jsonResponse(200, [
                'success' => true,
                'data' => [
                    'id_transaction' => $id_transaction,
                    'mail_sent' => $mail_sent
                ]
            ]);
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
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_transaction') {
            $id_transaction = getInt($body['id_transaction'] ?? null, null);
            if ($id_transaction === null) jsonResponse(400, ['success' => false, 'error' => 'id_transaction requis']);

            $sql = "DELETE FROM app_compta_transactions_bancaires WHERE id_transaction = ?";
            $params = [$id_transaction];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);
            jsonResponse(200, ['success' => true]);
        }

        // ---------- ENVOI MAIL MANUEL (rapport / releve) ----------

        if ($action === 'envoyer_releve') {
            $email = getStr($body['email'] ?? '');
            $nom_compte = getStr($body['nom_compte'] ?? '');
            $periode = getStr($body['periode'] ?? '');
            $total_credit = (float)($body['total_credit'] ?? 0);
            $total_debit = (float)($body['total_debit'] ?? 0);
            $solde = (float)($body['solde'] ?? 0);
            $transactions_html = getStr($body['transactions_html'] ?? '');

            if ($email === '') jsonResponse(400, ['success' => false, 'error' => 'Email requis']);

            try {
                $credit_fmt = number_format($total_credit, 0, ',', ' ');
                $debit_fmt = number_format($total_debit, 0, ',', ' ');
                $solde_fmt = number_format($solde, 0, ',', ' ');
                $solde_color = $solde >= 0 ? '#28a745' : '#dc3545';

                $sujet = "Releve Bancaire - {$nom_compte}" . ($periode !== '' ? " ({$periode})" : '');

                $corps = "
                    <div style='font-family:Arial,sans-serif;max-width:700px;margin:0 auto;'>
                        <h2 style='color:#333;border-bottom:2px solid #007bff;padding-bottom:10px;'>
                            Releve de Compte Bancaire
                        </h2>
                        <p><strong>Compte :</strong> {$nom_compte}</p>
                        " . ($periode !== '' ? "<p><strong>Periode :</strong> {$periode}</p>" : '') . "

                        <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
                            <tr style='background:#d4edda;'>
                                <td style='padding:12px;font-weight:bold;border:1px solid #dee2e6;'>Total Credits</td>
                                <td style='padding:12px;border:1px solid #dee2e6;color:#28a745;font-weight:bold;font-size:16px;'>+{$credit_fmt} XOF</td>
                            </tr>
                            <tr style='background:#f8d7da;'>
                                <td style='padding:12px;font-weight:bold;border:1px solid #dee2e6;'>Total Debits</td>
                                <td style='padding:12px;border:1px solid #dee2e6;color:#dc3545;font-weight:bold;font-size:16px;'>-{$debit_fmt} XOF</td>
                            </tr>
                            <tr style='background:#e2e3e5;'>
                                <td style='padding:12px;font-weight:bold;border:1px solid #dee2e6;'>Solde</td>
                                <td style='padding:12px;border:1px solid #dee2e6;color:{$solde_color};font-weight:bold;font-size:18px;'>{$solde_fmt} XOF</td>
                            </tr>
                        </table>

                        " . ($transactions_html !== '' ? "<h3>Detail des transactions</h3>{$transactions_html}" : '') . "

                        <p style='color:#666;font-size:12px;margin-top:30px;border-top:1px solid #eee;padding-top:10px;'>
                            Cet email a ete genere automatiquement. Ne pas repondre.
                        </p>
                    </div>
                ";

                Mailer::sendCustom($email, $sujet, $corps);

                jsonResponse(200, ['success' => true, 'message' => 'Releve envoye par email']);
            } catch (Exception $e) {
                jsonResponse(500, ['success' => false, 'error' => 'Erreur envoi email', 'details' => $e->getMessage()]);
            }
        }

        jsonResponse(404, ['success' => false, 'error' => 'Action POST inconnue']);
    }

    jsonResponse(405, ['success' => false, 'error' => 'Methode non autorisee']);

} catch (Throwable $e) {
    jsonResponse(500, [
        'success' => false,
        'error' => 'Erreur serveur',
        'details' => $e->getMessage()
    ]);
}
