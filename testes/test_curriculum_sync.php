<?php
/**
 * Teste de sincronização do currículo
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/RemoteSyncService.php';

echo "=== Teste de Sincronização de Currículo ===\n\n";

try {
    // Conexão local
    $localDb = Database::getInstance()->getConnection();
    echo "[✓] Conexão local OK\n";
    
    // Conexão remota
    $remoteDb = db();
    echo "[✓] Conexão remota OK\n";
    
    // Criar serviço de sincronização
    $syncService = new RemoteSyncService($localDb, $remoteDb);
    
    echo "\n--- Iniciando sincronização de currículo ---\n\n";
    
    // Sincronizar currículo
    $result = $syncService->syncCurriculum('disciplinas_curso_site', 5000);
    
    echo "\n=== RESULTADO ===\n";
    echo "Status: {$result['status']}\n";
    echo "Mensagem: {$result['mensagem']}\n";
    
    if (isset($result['stats'])) {
        echo "\nEstatísticas:\n";
        echo "  - Criados: {$result['stats']['criado']}\n";
        echo "  - Atualizados: {$result['stats']['atualizado']}\n";
        echo "  - Falhas: {$result['stats']['falha']}\n";
        echo "  - Removidos: {$result['stats']['removido']}\n";
    }
    
    echo "\n=== LOG ===\n";
    foreach ($result['log'] as $logEntry) {
        echo "  {$logEntry}\n";
    }
    
    // Verificar dados sincronizados
    echo "\n\n=== Verificação: Disciplinas no banco local ===\n";
    $stmt = $localDb->query("
        SELECT cc.*, c.nome as curso_nome_local 
        FROM course_curriculum cc 
        JOIN courses c ON cc.course_id = c.id 
        ORDER BY c.nome, cc.semestre, cc.ordem 
        LIMIT 20
    ");
    $disciplinas = $stmt->fetchAll();
    
    if (empty($disciplinas)) {
        echo "Nenhuma disciplina encontrada no banco local.\n";
    } else {
        echo "Total encontrado (primeiros 20):\n";
        foreach ($disciplinas as $d) {
            echo "\n  [{$d['curso_nome_local']}]\n";
            echo "    Módulo {$d['semestre']}: {$d['disciplina']} ({$d['carga_horaria']}h)\n";
            echo "    Modalidade: " . ($d['modalidade'] ?? 'N/A') . "\n";
            echo "    Duração: " . ($d['duracao'] ?? 'N/A') . "\n";
            echo "    Cod.Externo: " . ($d['cod_externo_remoto'] ?? 'N/A') . "\n";
            echo "    ID Remoto: " . ($d['id_curso_remoto'] ?? 'N/A') . "\n";
            echo "    Curso Nome Remoto: " . ($d['curso_nome_remoto'] ?? 'N/A') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n[ERRO] " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
