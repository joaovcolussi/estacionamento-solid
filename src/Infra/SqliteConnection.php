<?php
namespace App\Infra;

use PDO;
use PDOException;

class SqliteConnection
{
    private static $instance = null;
    private $pdo;
    
    private $dbPath = __DIR__ . '/database/estacionamento.sqlite';

    private function __construct()
    {
        try {
            $this->pdo = new PDO("sqlite:" . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro ao conectar ao SQLite: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function initSchema(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS registros (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            placa TEXT NOT NULL,
            tipo_veiculo TEXT NOT NULL,
            data_entrada DATETIME NOT NULL,
            data_saida DATETIME NULL,
            valor_total REAL NULL
        );
        ";
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Erro ao criar o esquema: " . $e->getMessage());
        }
    }
}