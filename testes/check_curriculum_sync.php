<?php
/**
 * Script para verificar se a sincronização de currículo está funcionando
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/RemoteSyncService.php';

echo "=== Verificação da Sincronização de Currículo ===\n\n";

try {
    // Conexões
    $localDb = Database::getInstance()->getConnection();
    $remoteDb = db();
    echo "✓ Conexões estabelecidas\n\n";
    
    // Verificar se a view disciplinas_curso_site existe
    echo "=== Verificar view disciplinas_curso_site ===\n";
    try {
        $stmt = $remoteDb->query('SELECT COUNT(*) as total FROM disciplinas_curso_site');
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ View disciplinas_curso_site existe com {$count['total']} registros\n\n";
        
        // Amostra de dados
        echo "=== Amostra de dados da view ===\n";
        $stmt = $remoteDb->query('SELECT * FROM disciplinas_curso_site LIMIT 3');
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($samples as $i => $sample) {
            echo "Registro " . ($i + 1) . ":\n";
            foreach ($sample as $key => $value) {
                $val = is_null($value) ? 'NULL' : (strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value);
                echo "  {$key}: {$val}\n";
            }
            echo "\n";
        }
    } catch (Exception $e) {
        echo "✗ View disciplinas_curso_site NÃO existe ou erro: " . $e->getMessage() . "\n\n";
    }
    
    // Verificar tabela local course_curriculum
    echo "=== Estado atual da tabela course_curriculum local ===\n";
    $stmt = $localDb->query('SELECT COUNT(*) as total FROM course_curriculum');
    $countLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de disciplinas locais: {$countLocal['total']}\n\n";
    
    // Testar sincronização
    echo "=== Executar sincronização de currículo ===\n";
    $syncService = new RemoteSyncService($localDb, $remoteDb);
    $result = $syncService->syncCurriculum('disciplinas_curso_site', 5000);
    
    echo "Status: {$result['status']}\n";
    echo "Mensagem: {$result['mensagem']}\n\n";
    
    if (isset($result['stats'])) {
        echo "Estatísticas:\n";
        foreach ($result['stats'] as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }
    
    // Verificar após sincronização
    echo "\n=== Estado após sincronização ===\n";
    $stmt = $localDb->query('SELECT COUNT(*) as total FROM course_curriculum');
    $countAfter = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de disciplinas locais: {$countAfter['total']}\n";
    
    // Log detalhado
    if (!empty($result['log'])) {
        echo "\n=== Log da sincronização (últimas 20 linhas) ===\n";
        $logLines = array_slice($result['log'], -20);
        foreach ($logLines as $line) {
            echo "  {$line}\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
