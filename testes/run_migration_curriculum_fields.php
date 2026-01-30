<?php
/**
 * Executa a migração para adicionar campos faltantes na tabela course_curriculum
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== Migração: Adicionar campos da view remota ===\n\n";

// Verificar quais campos já existem
$stmt = $pdo->query('DESCRIBE course_curriculum');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$existingFields = array_column($columns, 'Field');

$fieldsToAdd = [
    'duracao' => "ADD COLUMN duracao VARCHAR(100) NULL COMMENT 'Duração da disciplina/módulo (da view remota)' AFTER ementa",
    'modalidade' => "ADD COLUMN modalidade VARCHAR(255) NULL COMMENT 'Modalidade (EAD, Presencial, etc)' AFTER duracao",
    'cod_externo_remoto' => "ADD COLUMN cod_externo_remoto VARCHAR(15) NULL COMMENT 'Código externo do curso (sigla da view remota)' AFTER modalidade",
    'id_curso_remoto' => "ADD COLUMN id_curso_remoto VARCHAR(100) NULL COMMENT 'ID do curso na view remota' AFTER cod_externo_remoto",
    'curso_nome_remoto' => "ADD COLUMN curso_nome_remoto VARCHAR(255) NULL COMMENT 'Nome do curso (da view remota)' AFTER id_curso_remoto",
];

$alterStatements = [];
foreach ($fieldsToAdd as $field => $sql) {
    if (!in_array($field, $existingFields)) {
        $alterStatements[] = $sql;
        echo "Campo '{$field}' será adicionado.\n";
    } else {
        echo "Campo '{$field}' já existe - pulando.\n";
    }
}

if (!empty($alterStatements)) {
    try {
        $sql = "ALTER TABLE course_curriculum\n" . implode(",\n", $alterStatements);
        echo "\nExecutando SQL:\n{$sql}\n\n";
        $pdo->exec($sql);
        echo "✓ Campos adicionados com sucesso!\n";
    } catch (Exception $e) {
        echo "✗ Erro ao adicionar campos: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "\nNenhum campo precisa ser adicionado.\n";
}

// Verificar se índice existe
echo "\nVerificando índice idx_id_curso_remoto...\n";
$stmt = $pdo->query("SHOW INDEX FROM course_curriculum WHERE Key_name = 'idx_id_curso_remoto'");
$index = $stmt->fetch();

if (!$index) {
    try {
        $pdo->exec("ALTER TABLE course_curriculum ADD INDEX idx_id_curso_remoto (id_curso_remoto)");
        echo "✓ Índice idx_id_curso_remoto criado!\n";
    } catch (Exception $e) {
        echo "Aviso: " . $e->getMessage() . "\n";
    }
} else {
    echo "Índice idx_id_curso_remoto já existe.\n";
}

// Mostrar estrutura final
echo "\n=== Estrutura final da tabela ===\n";
$stmt = $pdo->query('DESCRIBE course_curriculum');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}

echo "\nMigração concluída!\n";
