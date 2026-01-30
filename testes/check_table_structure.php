<?php
/**
 * Verifica estrutura da tabela course_curriculum
 * e compara com campos da view remota
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== Estrutura atual da tabela course_curriculum ===\n\n";

$stmt = $pdo->query('DESCRIBE course_curriculum');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Campos existentes:\n";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}

echo "\n=== Campos da view remota disciplinas_curso_site ===\n";
echo "- disciplina_nome (varchar 150)\n";
echo "- id (varchar 100) - ID do curso remoto\n";
echo "- curso_nome (varchar 255)\n";
echo "- cod_externo (varchar 15) - Sigla do curso\n";
echo "- carga_horaria (float)\n";
echo "- duracao (varchar 100) - Duração do curso\n";
echo "- modalidade (varchar 255)\n";
echo "- modulo (varchar 255)\n";

echo "\n=== Análise de campos faltantes ===\n";

// Campos locais atuais
$localFields = array_column($columns, 'Field');

// Campos que deveriam existir baseado na view (mapeamento direto)
$viewFields = [
    'disciplina_nome' => ['local' => 'disciplina', 'exists' => in_array('disciplina', $localFields)],
    'carga_horaria' => ['local' => 'carga_horaria', 'exists' => in_array('carga_horaria', $localFields)],
    'modulo' => ['local' => 'semestre', 'exists' => in_array('semestre', $localFields)],
    'duracao' => ['local' => 'duracao', 'exists' => in_array('duracao', $localFields)],
    'modalidade' => ['local' => 'modalidade', 'exists' => in_array('modalidade', $localFields)],
    'cod_externo' => ['local' => 'cod_externo_remoto', 'exists' => in_array('cod_externo_remoto', $localFields)],
    'id' => ['local' => 'id_curso_remoto', 'exists' => in_array('id_curso_remoto', $localFields)],
    'curso_nome' => ['local' => 'curso_nome_remoto', 'exists' => in_array('curso_nome_remoto', $localFields)],
];

echo "\nCampos mapeados:\n";
foreach ($viewFields as $remote => $info) {
    $status = $info['exists'] ? '✓ Existe' : '✗ FALTANDO';
    echo "- {$remote} -> {$info['local']}: {$status}\n";
}

echo "\n=== Verificar dados atuais da view remota ===\n";

// Conectar ao banco remoto para ver uma amostra
try {
    $remoteConfig = [
        'host' => '10.6.21.166',
        'dbname' => 'site',
        'user' => 'site',
        'pass' => '@Faesma25#'
    ];
    
    $remotePdo = new PDO(
        "mysql:host={$remoteConfig['host']};dbname={$remoteConfig['dbname']};charset=utf8mb4",
        $remoteConfig['user'],
        $remoteConfig['pass']
    );
    
    $stmt = $remotePdo->query('SELECT * FROM disciplinas_curso_site LIMIT 3');
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Amostra de dados da view remota:\n";
    foreach ($samples as $i => $row) {
        echo "\nRegistro " . ($i + 1) . ":\n";
        foreach ($row as $field => $value) {
            echo "  {$field}: {$value}\n";
        }
    }
} catch (Exception $e) {
    echo "Erro ao conectar ao banco remoto: " . $e->getMessage() . "\n";
}

echo "\n=== SQL para adicionar campos faltantes ===\n";
$alterStatements = [];

if (!in_array('duracao', $localFields)) {
    $alterStatements[] = "ADD COLUMN duracao VARCHAR(100) NULL COMMENT 'Duração da disciplina/módulo (da view remota)'";
}
if (!in_array('modalidade', $localFields)) {
    $alterStatements[] = "ADD COLUMN modalidade VARCHAR(255) NULL COMMENT 'Modalidade (EAD, Presencial, etc)'";
}
if (!in_array('cod_externo_remoto', $localFields)) {
    $alterStatements[] = "ADD COLUMN cod_externo_remoto VARCHAR(15) NULL COMMENT 'Código externo do curso (sigla da view remota)'";
}
if (!in_array('id_curso_remoto', $localFields)) {
    $alterStatements[] = "ADD COLUMN id_curso_remoto VARCHAR(100) NULL COMMENT 'ID do curso na view remota'";
}
if (!in_array('curso_nome_remoto', $localFields)) {
    $alterStatements[] = "ADD COLUMN curso_nome_remoto VARCHAR(255) NULL COMMENT 'Nome do curso (da view remota)'";
}

if (!empty($alterStatements)) {
    echo "\nALTER TABLE course_curriculum\n";
    echo implode(",\n", $alterStatements) . ";\n";
} else {
    echo "Todos os campos já existem!\n";
}

echo "\nScript finalizado.\n";
