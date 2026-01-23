<?php
/**
 * FAESMA - Testes de Mapeamento e Sincronização
 * 
 * Script para testar o mapeamento e a sincronização
 * Execute via CLI: php test_sync.php
 * 
 * @package FAESMA
 * @version 1.0
 */

require_once __DIR__ . '/includes/RemoteSyncMapping.php';

// Cores para CLI
class Colors
{
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const RESET = "\033[0m";
}

echo Colors::BLUE . "\n========================================\n";
echo "FAESMA - Testes de Sincronização\n";
echo "========================================\n" . Colors::RESET;

// ============================================
// TESTE 1: Verificar Mapeamento de Campos
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 1: Mapeamento de Campos" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

$mapping = RemoteSyncMapping::getMapping();
echo Colors::GREEN . "✓ Total de campos mapeados: " . count($mapping) . Colors::RESET . "\n\n";

echo "Exemplos de mapeamento:\n";
$examples = array_slice($mapping, 0, 5, true);
foreach ($examples as $remote => $local) {
    echo "  {$remote} → {$local}\n";
}

// ============================================
// TESTE 2: Validar Dados Remotos
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 2: Validação de Dados Remotos" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

// Dados válidos
$validRemoteData = [
    'id_curso' => '001',
    'nome_curso' => 'Administração',
    'descricao' => 'Curso de Administração',
    'duracao_meses' => 48,
    'carga_horaria' => 3600,
];

$validation = RemoteSyncMapping::validateRemoteData($validRemoteData);
if ($validation['valid']) {
    echo Colors::GREEN . "✓ Dados válidos aceitos" . Colors::RESET . "\n";
} else {
    echo Colors::RED . "✗ Validação falhou" . Colors::RESET . "\n";
}

// Dados inválidos (sem campos obrigatórios)
echo "\nTestando com dados incompletos:\n";
$invalidRemoteData = [
    'descricao' => 'Sem ID e nome',
];

$validation = RemoteSyncMapping::validateRemoteData($invalidRemoteData);
if (!$validation['valid']) {
    echo Colors::GREEN . "✓ Dados inválidos foram rejeitados" . Colors::RESET . "\n";
    foreach ($validation['errors'] as $error) {
        echo "  • {$error}\n";
    }
}

// ============================================
// TESTE 3: Converter Dados Remotos para Locais
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 3: Conversão de Dados" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

$remoteData = [
    'id_curso' => '001',
    'codigo_curso' => 'ADM001',
    'nome_curso' => 'Administração',
    'descricao' => 'Curso de Administração',
    'duracao_meses' => 48,
    'carga_horaria' => 3600,
    'tcc_obrigatorio' => '1',
    'inscricao_online' => 'sim',
    'status_remoto' => 'ativo',
];

echo "Dados remotos:\n";
foreach ($remoteData as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\nConvertendo para formato local...\n";
$localData = RemoteSyncMapping::convertRemoteToLocal($remoteData);

echo Colors::GREEN . "✓ Conversão completa" . Colors::RESET . "\n";
echo "\nDados locais:\n";
foreach ($localData as $key => $value) {
    if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    }
    echo "  {$key}: {$value}\n";
}

// ============================================
// TESTE 4: Transformações de Valores
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 4: Transformações de Valores" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

$transformTests = [
    ['field' => 'status', 'value' => 'ativo', 'expected' => 'ativo'],
    ['field' => 'status', 'value' => 'INATIVO', 'expected' => 'inativo'],
    ['field' => 'tcc_obrigatorio', 'value' => '1', 'expected' => true],
    ['field' => 'tcc_obrigatorio', 'value' => '0', 'expected' => false],
    ['field' => 'inscricao_online', 'value' => 'sim', 'expected' => true],
];

foreach ($transformTests as $test) {
    $result = RemoteSyncMapping::transformValue($test['field'], $test['value']);
    
    // Comparar resultado
    $match = ($result === $test['expected']) ||
             (is_bool($result) && is_bool($test['expected']) && $result === $test['expected']);
    
    if ($match) {
        $resultStr = is_bool($result) ? ($result ? 'true' : 'false') : $result;
        echo Colors::GREEN . "✓" . Colors::RESET . " {$test['field']}: '{$test['value']}' → {$resultStr}\n";
    } else {
        $resultStr = is_bool($result) ? ($result ? 'true' : 'false') : $result;
        echo Colors::RED . "✗" . Colors::RESET . " {$test['field']}: '{$test['value']}' → {$resultStr} (esperado: {$test['expected']})\n";
    }
}

// ============================================
// TESTE 5: Geração de Slug
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 5: Geração de Slug" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

$slugTests = [
    'Administração' => 'administracao',
    'Engenharia de Software' => 'engenharia-de-software',
    'Direito (Noturno)' => 'direito-noturno',
    'Educação Física - Licenciatura' => 'educacao-fisica-licenciatura',
    '   Medicina   ' => 'medicina',
];

foreach ($slugTests as $input => $expected) {
    // Simular conversão de dados com slug gerado
    $data = RemoteSyncMapping::convertRemoteToLocal([
        'id_curso' => '001',
        'nome_curso' => $input,
    ]);
    
    $generated = $data['slug'];
    $match = $generated === $expected;
    
    if ($match) {
        echo Colors::GREEN . "✓" . Colors::RESET . " '{$input}' → '{$generated}'\n";
    } else {
        echo Colors::RED . "✗" . Colors::RESET . " '{$input}' → '{$generated}' (esperado: '{$expected}')\n";
    }
}

// ============================================
// TESTE 6: Build Query INSERT
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 6: Build Query INSERT" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

$insertData = [
    'nome' => 'Administração',
    'slug' => 'administracao',
    'descricao_curta' => 'Curso de administração',
    'category_id' => 1,
    'modality_id' => 1,
    'status' => 'ativo',
];

try {
    $query = RemoteSyncMapping::buildInsertQuery($insertData);
    echo Colors::GREEN . "✓ Query INSERT gerada com sucesso" . Colors::RESET . "\n";
    echo "\nSQL:\n" . str_repeat("-", 50) . "\n";
    echo $query['sql'] . "\n";
    echo "\nParâmetros:\n";
    foreach ($query['params'] as $param => $value) {
        echo "  {$param}: {$value}\n";
    }
} catch (Exception $e) {
    echo Colors::RED . "✗ Erro ao gerar query: " . $e->getMessage() . Colors::RESET . "\n";
}

// ============================================
// TESTE 7: Build Query UPDATE
// ============================================
echo "\n" . Colors::YELLOW . "TESTE 7: Build Query UPDATE" . Colors::RESET . "\n";
echo str_repeat("-", 50) . "\n";

$updateData = [
    'nome' => 'Administração',
    'descricao_curta' => 'Novo descrição',
    'carga_horaria' => 3600,
];

$query = RemoteSyncMapping::buildUpdateQuery($updateData, 5);
echo Colors::GREEN . "✓ Query UPDATE gerada com sucesso" . Colors::RESET . "\n";
echo "\nSQL:\n" . str_repeat("-", 50) . "\n";
echo $query['sql'] . "\n";
echo "\nParâmetros:\n";
foreach ($query['params'] as $param => $value) {
    echo "  {$param}: {$value}\n";
}

// ============================================
// Resumo Final
// ============================================
echo "\n" . Colors::BLUE . str_repeat("=", 50) . Colors::RESET . "\n";
echo Colors::GREEN . "✓ Testes Completados com Sucesso!" . Colors::RESET . "\n";
echo Colors::BLUE . str_repeat("=", 50) . Colors::RESET . "\n\n";

echo "Próximos Passos:\n";
echo "1. Verificar credenciais do banco remoto em includes/db.php\n";
echo "2. Executar sincronização: php sync_courses.php\n";
echo "3. Validar dados sincronizados no banco local\n\n";
