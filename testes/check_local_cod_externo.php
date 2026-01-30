<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$localDb = Database::getInstance()->getConnection();

echo "=== Cursos locais e seus cod_externo ===\n\n";

$stmt = $localDb->query("SELECT id, nome, cod_externo FROM courses WHERE status = 'ativo' ORDER BY nome");
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cursos as $curso) {
    echo "  ID {$curso['id']}: {$curso['nome']}\n";
    echo "      cod_externo: '{$curso['cod_externo']}'\n\n";
}
