<?php
require_once __DIR__ . '/../includes/db.php';

$pdo = db();

// Verificar valores distintos de cod_externo na view
$stmt = $pdo->query('SELECT DISTINCT cod_externo, curso_nome FROM disciplinas_curso_site');
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Cursos na view disciplinas_curso_site ===\n";
foreach ($cursos as $curso) {
    echo "  cod_externo: {$curso['cod_externo']} | curso_nome: {$curso['curso_nome']}\n";
}

echo "\n=== Verificar cursos locais correspondentes ===\n";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$localDb = Database::getInstance()->getConnection();

foreach ($cursos as $curso) {
    $stmt = $localDb->prepare("SELECT id, nome, cod_externo FROM courses WHERE cod_externo = :cod_externo OR cod_externo LIKE :cod_like");
    $stmt->execute([
        ':cod_externo' => $curso['cod_externo'],
        ':cod_like' => $curso['cod_externo'] . '.%'
    ]);
    $local = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($local) {
        echo "  ✓ {$curso['cod_externo']} -> Local ID {$local['id']} ({$local['nome']})\n";
    } else {
        echo "  ✗ {$curso['cod_externo']} -> NÃO ENCONTRADO localmente\n";
    }
}
