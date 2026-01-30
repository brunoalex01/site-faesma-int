<?php
/**
 * Script para testar o novo mapeamento de nr_grau e inscricao_online
 */

require_once __DIR__ . '/../includes/RemoteSyncMapping.php';

echo "=== Teste do Novo Mapeamento ===\n\n";

// Teste 1: Verificar mapeamento de campos
echo "1. Verificar mapeamento de campos:\n";
$mapping = RemoteSyncMapping::getMapping();
echo "   - nr_grau → " . ($mapping['nr_grau'] ?? 'NÃO MAPEADO') . "\n";
echo "   - inscricao_online → " . ($mapping['inscricao_online'] ?? 'NÃO MAPEADO') . "\n\n";

// Teste 2: Testar transformação de nr_grau para category_id
echo "2. Testar transformação de nr_grau para category_id:\n";
$testGraus = [3, 4, 5, null, '3', '4'];
foreach ($testGraus as $grau) {
    $result = RemoteSyncMapping::transformValue('category_id', $grau);
    $grauStr = is_null($grau) ? 'NULL' : $grau;
    echo "   - NR_GRAU={$grauStr} → category_id={$result}\n";
}
echo "\n";

// Teste 3: Testar transformação de inscricao_online para status
echo "3. Testar transformação de inscricao_online para status:\n";
$testInscricao = ['S', 'N', 's', 'n', '', null, 'X'];
foreach ($testInscricao as $inscricao) {
    $result = RemoteSyncMapping::transformValue('status', $inscricao);
    $inscricaoStr = is_null($inscricao) ? 'NULL' : (empty($inscricao) ? 'VAZIO' : $inscricao);
    echo "   - inscricao_online='{$inscricaoStr}' → status='{$result}'\n";
}
echo "\n";

// Teste 4: Testar conversão completa de dados remotos
echo "4. Testar conversão completa de dados remotos:\n";

// Curso Graduação Ativo (nr_grau=3, inscricao_online=S)
$remoteData1 = [
    'id' => 10,
    'nome' => 'Administração',
    'cod_externo' => 'ADM.PRE',
    'nr_grau' => 3,
    'inscricao_online' => 'S',
    'carga_horaria' => 3200,
];

echo "\n   Dados remotos (Graduação Ativo):\n";
foreach ($remoteData1 as $k => $v) {
    echo "     {$k}: {$v}\n";
}

$localData1 = RemoteSyncMapping::convertRemoteToLocal($remoteData1);
echo "\n   Dados convertidos:\n";
echo "     category_id: {$localData1['category_id']} (esperado: 3 = Graduação)\n";
echo "     status: {$localData1['status']} (esperado: ativo)\n";

// Curso Pós-Graduação Inativo (nr_grau=4, inscricao_online=N)
$remoteData2 = [
    'id' => 20,
    'nome' => 'MBA em Gestão Empresarial',
    'cod_externo' => 'MBA.GE',
    'nr_grau' => 4,
    'inscricao_online' => 'N',
    'carga_horaria' => 360,
];

echo "\n   Dados remotos (Pós-Graduação Inativo):\n";
foreach ($remoteData2 as $k => $v) {
    echo "     {$k}: {$v}\n";
}

$localData2 = RemoteSyncMapping::convertRemoteToLocal($remoteData2);
echo "\n   Dados convertidos:\n";
echo "     category_id: {$localData2['category_id']} (esperado: 4 = Pós-Graduação)\n";
echo "     status: {$localData2['status']} (esperado: inativo)\n";

echo "\n=== Teste Concluído ===\n";

// Verificação final
$passed = true;
if ($localData1['category_id'] !== 3) {
    echo "❌ FALHA: category_id deveria ser 3 para Graduação\n";
    $passed = false;
}
if ($localData1['status'] !== 'ativo') {
    echo "❌ FALHA: status deveria ser 'ativo' para inscricao_online='S'\n";
    $passed = false;
}
if ($localData2['category_id'] !== 4) {
    echo "❌ FALHA: category_id deveria ser 4 para Pós-Graduação\n";
    $passed = false;
}
if ($localData2['status'] !== 'inativo') {
    echo "❌ FALHA: status deveria ser 'inativo' para inscricao_online='N'\n";
    $passed = false;
}

if ($passed) {
    echo "\n✅ TODOS OS TESTES PASSARAM!\n";
} else {
    echo "\n❌ ALGUNS TESTES FALHARAM!\n";
}
