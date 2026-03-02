<?php
class Database {
    private $host = '213.255.195.35';
    private $dbname = 'aliad2663340';
    private $username = 'aliad2663340';
    private $password = 'Stock2025@';
    private $pdo;
    
    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
    
            $type = strtoupper(strtok(trim($sql), ' ')); // ex: SELECT, UPDATE...
    
            if ($type === 'SELECT') {
                return $stmt->fetchAll();
            } elseif (in_array($type, ['INSERT', 'UPDATE', 'DELETE'])) {
                return $stmt->rowCount(); // nombre de lignes affectées
            }
    
            return true; // pour d'autres types éventuels
    
        } catch (PDOException $e) {
            throw new Exception("Erreur SQL : " . $e->getMessage() . " | SQL : " . $sql . " | Params : " . json_encode($params));
        }
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollback();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute(array_values($data))) {
            return $this->pdo->lastInsertId();
        } else {
            return false;
        }
    }
}
?>