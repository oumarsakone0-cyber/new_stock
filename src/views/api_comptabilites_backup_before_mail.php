<?php
// API Comptabilités - Dépenses / Motifs / Pièces

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
    require_once __DIR__ . '/config/database.php';
    if (!class_exists('Database')) {
        throw new Exception("Classe Database introuvable dans config/database.php");
    }
    $db = new Database();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // Multi-tenant (optionnel mais recommandé)
    $id_entreprise = getInt($_GET['id_entreprise'] ?? null, null);

    // ===================== GET =====================
    if ($method === 'GET') {
        if ($action === 'list_motifs') {
            $sql = "SELECT id_motif as id, libelle, description, date_creation
                    FROM app_compta_motifs_depense
                    WHERE 1=1";
            $params = [];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $sql .= " ORDER BY libelle ASC";
            $rows = $db->query($sql, $params);
            jsonResponse(200, ['success' => true, 'data' => $rows]);
        }

        if ($action === 'list_depenses') {
            $date_debut = getStr($_GET['date_debut'] ?? '');
            $date_fin = getStr($_GET['date_fin'] ?? '');
            $motif_id = getInt($_GET['motif_id'] ?? null, null);
            $search = getStr($_GET['search'] ?? '');

            $sql = "SELECT 
                        d.id_depense as id,
                        d.id_entreprise,
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
                        p.id_depense,
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
                        p.id_depense,
                        d.date_depense,
                        d.beneficiaire,
                        d.motif_id,
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
        $id_entreprise_body = getInt($body['id_entreprise'] ?? null, null);
        if ($id_entreprise === null && $id_entreprise_body !== null) {
            $id_entreprise = $id_entreprise_body;
        }

        if ($action === 'add_motif') {
            $libelle = getStr($body['libelle'] ?? '');
            $description = getStr($body['description'] ?? '');
            if ($libelle === '') jsonResponse(400, ['success' => false, 'error' => 'Libellé requis']);

            $sql = "INSERT INTO app_compta_motifs_depense (id_entreprise, libelle, description, date_creation)
                    VALUES (?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $libelle, $description]);
            jsonResponse(200, ['success' => true, 'data' => ['id_motif' => $db->lastInsertId()]]);
        }

        if ($action === 'update_motif') {
            $id_motif = getInt($body['id_motif'] ?? null, null);
            $libelle = getStr($body['libelle'] ?? '');
            $description = getStr($body['description'] ?? '');
            if ($id_motif === null) jsonResponse(400, ['success' => false, 'error' => 'id_motif requis']);
            if ($libelle === '') jsonResponse(400, ['success' => false, 'error' => 'Libellé requis']);

            $sql = "UPDATE app_compta_motifs_depense SET libelle = ?, description = ? WHERE id_motif = ?";
            $params = [$libelle, $description, $id_motif];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_motif') {
            $id_motif = getInt($body['id_motif'] ?? null, null);
            if ($id_motif === null) jsonResponse(400, ['success' => false, 'error' => 'id_motif requis']);

            // Sécurité: empêcher suppression si utilisé
            $check = $db->query("SELECT COUNT(*) as c FROM app_compta_depenses WHERE motif_id = ?", [$id_motif]);
            $count = !empty($check) ? (int)($check[0]['c'] ?? 0) : 0;
            if ($count > 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Motif utilisé dans des dépenses']);
            }

            $sql = "DELETE FROM app_compta_motifs_depense WHERE id_motif = ?";
            $params = [$id_motif];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'add_depense') {
            $date_depense = getStr($body['date_depense'] ?? '');
            $motif_id = getInt($body['motif_id'] ?? null, null);
            $beneficiaire = getStr($body['beneficiaire'] ?? '');
            $description = getStr($body['description'] ?? '');
            $montant = (float)($body['montant'] ?? 0);
            $user_id = getInt($body['user_id'] ?? null, null);

            if ($date_depense === '' || $montant <= 0) {
                jsonResponse(400, ['success' => false, 'error' => 'Date et montant requis']);
            }

            $sql = "INSERT INTO app_compta_depenses 
                        (id_entreprise, motif_id, date_depense, beneficiaire, description, montant, create_by, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$id_entreprise, $motif_id, $date_depense, $beneficiaire, $description, $montant, $user_id]);
            $id_depense = $db->lastInsertId();
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
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_depense') {
            $id_depense = getInt($body['id_depense'] ?? null, null);
            if ($id_depense === null) jsonResponse(400, ['success' => false, 'error' => 'id_depense requis']);

            // Supprimer pièces d’abord (si pas de cascade)
            $db->query("DELETE FROM app_compta_depense_pieces WHERE id_depense = ?", [$id_depense]);

            $sql = "DELETE FROM app_compta_depenses WHERE id_depense = ?";
            $params = [$id_depense];
            if ($id_entreprise !== null) {
                $sql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'add_pieces') {
            $id_depense = getInt($body['id_depense'] ?? null, null);
            $pieces = $body['pieces'] ?? [];
            $user_id = getInt($body['user_id'] ?? null, null);
            if ($id_depense === null) jsonResponse(400, ['success' => false, 'error' => 'id_depense requis']);
            if (!is_array($pieces) || count($pieces) === 0) jsonResponse(200, ['success' => true, 'data' => []]);

            // Vérifier que la dépense existe
            $checkSql = "SELECT id_depense FROM app_compta_depenses WHERE id_depense = ?";
            $checkParams = [$id_depense];
            if ($id_entreprise !== null) {
                $checkSql .= " AND (id_entreprise = ? OR id_entreprise IS NULL)";
                $checkParams[] = $id_entreprise;
            }
            $check = $db->query($checkSql, $checkParams);
            if (empty($check)) jsonResponse(404, ['success' => false, 'error' => 'Dépense introuvable']);

            foreach ($pieces as $p) {
                $url = getStr($p['url'] ?? $p['url_fichier'] ?? '');
                if ($url === '') continue;
                $nom_original = getStr($p['nom_original'] ?? '');
                $type_fichier = getStr($p['type_fichier'] ?? '');
                $sql = "INSERT INTO app_compta_depense_pieces
                            (id_depense, id_entreprise, url_fichier, nom_original, type_fichier, create_by, date_creation)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $db->query($sql, [$id_depense, $id_entreprise, $url, $nom_original, $type_fichier, $user_id]);
            }
            jsonResponse(200, ['success' => true]);
        }

        if ($action === 'delete_piece') {
            $id_piece = getInt($body['id_piece'] ?? null, null);
            if ($id_piece === null) jsonResponse(400, ['success' => false, 'error' => 'id_piece requis']);

            $sql = "DELETE p FROM app_compta_depense_pieces p
                    INNER JOIN app_compta_depenses d ON d.id_depense = p.id_depense
                    WHERE p.id_piece = ?";
            $params = [$id_piece];
            if ($id_entreprise !== null) {
                $sql .= " AND (d.id_entreprise = ? OR d.id_entreprise IS NULL)";
                $params[] = $id_entreprise;
            }
            $db->query($sql, $params);
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

