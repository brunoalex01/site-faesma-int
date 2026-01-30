<?php
/**
 * Corrigir cod_externo dos cursos para sincronização correta
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== Verificando cod_externo dos cursos ===\n\n";

// Verificar cursos 12 e 13
$stmt = $pdo->query("SELECT id, nome, cod_externo, slug FROM courses WHERE id IN (12, 13)");
$cursos = $stmt->fetchAll();

echo "Cursos encontrados:\n";
foreach ($cursos as $c) {
    echo "  ID {$c['id']}: {$c['nome']}\n";
    echo "    cod_externo: {$c['cod_externo']}\n";
    echo "    slug: {$c['slug']}\n\n";
}

// A view remota tem id=12 para "Gestão de Recursos Humanos"
// Precisamos que o curso local "Gestão de Recursos Humanos" tenha cod_externo=12

echo "\n=== Correção ===\n";
echo "A view remota usa id=12 para 'Gestão de Recursos Humanos (Tecnólogo)'\n";
echo "Vamos corrigir os cod_externo:\n";

// O curso ID 12 (Gestão de RH) deve ter cod_externo=12 (para sincronizar)
// O curso ID 13 (teste) pode ter outro cod_externo ou NULL

$pdo->beginTransaction();
try {
    // Primeiro, mudar o curso 13 para não ter conflito
    $stmt = $pdo->prepare("UPDATE courses SET cod_externo = 'teste' WHERE id = 13");
    $stmt->execute();
    echo "  - Curso ID 13: cod_externo alterado para 'teste'\n";
    
    // Depois, corrigir o curso 12 para cod_externo=12
    $stmt = $pdo->prepare("UPDATE courses SET cod_externo = '12' WHERE id = 12");
    $stmt->execute();
    echo "  - Curso ID 12: cod_externo alterado para '12'\n";
    
    $pdo->commit();
    echo "\n✓ Correção aplicada!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erro: " . $e->getMessage() . "\n";
}

// Verificar resultado
echo "\n=== Verificação final ===\n";
$stmt = $pdo->query("SELECT id, nome, cod_externo FROM courses WHERE id IN (12, 13)");
$cursos = $stmt->fetchAll();
foreach ($cursos as $c) {
    echo "  ID {$c['id']}: {$c['nome']} (cod_externo: {$c['cod_externo']})\n";
}

echo "\nAgora execute test_curriculum_sync.php para sincronizar.\n";
