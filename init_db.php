<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Infra\SqliteConnection;

$ds = DIRECTORY_SEPARATOR;

try {
    $dbDir = __DIR__ . $ds . 'src' . $ds . 'Infra' . $ds . 'database';

    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
        echo "Pasta 'database' criada em: {$dbDir}" . PHP_EOL;
    }

    $dbFile = $dbDir . $ds . 'estacionamento.sqlite';

    if (!file_exists($dbFile)) {
        touch($dbFile);
        echo "Arquivo 'estacionamento.sqlite' criado em: {$dbFile}" . PHP_EOL;
    }

    $connection = SqliteConnection::getInstance();
    $connection->initSchema();

    echo "Banco de dados e tabela 'registros' criados com sucesso em 'src/Infra/database/'!" . PHP_EOL;

} catch (\Throwable $e) {
    echo "Erro: " . $e->getMessage() . PHP_EOL;
}
