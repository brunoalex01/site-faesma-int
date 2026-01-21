<?php
declare(strict_types=1);

/**
 * Conexão PDO (MySQL/MariaDB) + helper para consultar VIEW.
 * Uso:
 *   require __DIR__ . '/db.php';
 *   $pdo = db();
 *   $rows = fetchAllFromView($pdo, 'vw_nome_da_view');
 */

function db(): PDO
{
    // Preferencial: variáveis de ambiente (mais seguro)
    $host = getenv('DB_HOST') ?: '143.0.121.152';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'site';
    $user = getenv('DB_USER') ?: 'site_faesma';
    $pass = getenv('DB_PASS') ?: 'YwsGps1rBusBmWvPrzj9';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lança exceções
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // retorna array assoc
        PDO::ATTR_EMULATE_PREPARES   => false,                  // prepara real
    ];

    return new PDO($dsn, $user, $pass, $options);
}

/**
 * IMPORTANTE: view/identificador SQL não aceita bind param.
 * Então validamos o nome (whitelist de caracteres) e depois colocamos no SQL.
 */
function fetchAllFromView(PDO $pdo, string $viewName, int $limit = 100): array
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $viewName)) {
        throw new InvalidArgumentException('Nome da view inválido.');
    }

    $limit = max(1, min(5000, $limit)); // evita abuso
    $sql = "SELECT * FROM `{$viewName}` LIMIT {$limit}";
    return $pdo->query($sql)->fetchAll();
}
