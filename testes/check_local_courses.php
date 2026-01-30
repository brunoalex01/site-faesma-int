<?php
define('FAESMA_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance()->getConnection();

echo "=== Cursos no banco local ===\n\n";
$r = $db->query('SELECT id, nome, cod_externo, cd_oferta FROM courses ORDER BY nome LIMIT 30')->fetchAll();

printf("%-5s | %-40s | %-15s | %-15s\n", "ID", "Nome", "cod_externo", "cd_oferta");
echo str_repeat("-", 85) . "\n";

foreach ($r as $row) {
    printf("%-5s | %-40s | %-15s | %-15s\n", 
        $row['id'], 
        substr($row['nome'], 0, 40),
        $row['cod_externo'] ?? 'NULL',
        $row['cd_oferta'] ?? 'NULL'
    );
}

echo "\nTotal: " . count($r) . " cursos\n";
