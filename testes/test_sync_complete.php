<?php
/**
 * Script para testar a sincronização completa com os novos mapeamentos
 * - category_id baseado em nr_grau
 * - status baseado em inscricao_online
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/RemoteSyncMapping.php';
require_once __DIR__ . '/../includes/RemoteSyncService.php';

echo "=== Teste de Sincronização Completa ===\n\n";

try {
    // Conexão local
    $localDb = Database::getInstance()->getConnection();
    echo "✓ Conexão local estabelecida\n";
    
    // Conexão remota
    $remoteDb = db();
    echo "✓ Conexão remota estabelecida\n\n";
    
    // Verificar categorias locais
    echo "=== Categorias no banco local ===\n";
    $stmt = $localDb->query('SELECT id, nome FROM course_categories ORDER BY id');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as $cat) {
        echo "  ID {$cat['id']}: {$cat['nome']}\n";
    }
    echo "\n";
    
    // Verificar dados da view remota antes da sincronização
    echo "=== Amostra de dados da view remota ===\n";
    $stmt = $remoteDb->query('SELECT id, nome, nr_grau, inscricao_online FROM cursos_site LIMIT 5');
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($samples as $sample) {
        $grauNome = ($sample['nr_grau'] == 4) ? 'Pós-Grad' : 'Grad';
        $statusNome = ($sample['inscricao_online'] == 'S') ? 'ATIVO' : 'INATIVO';
        echo "  [{$sample['id']}] {$sample['nome']}\n";
        echo "      nr_grau={$sample['nr_grau']} ({$grauNome}), inscricao_online={$sample['inscricao_online']} ({$statusNome})\n";
    }
    echo "\n";
    
    // Contagem antes da sincronização
    echo "=== Estado atual do banco local ===\n";
    $stmt = $localDb->query('SELECT COUNT(*) as total FROM courses');
    $totalAntes = $stmt->fetch()['total'];
    
    $stmt = $localDb->query("SELECT COUNT(*) as total FROM courses WHERE status = 'ativo'");
    $ativosAntes = $stmt->fetch()['total'];
    
    $stmt = $localDb->query("SELECT COUNT(*) as total FROM courses WHERE status = 'inativo'");
    $inativosAntes = $stmt->fetch()['total'];
    
    echo "  Total de cursos: {$totalAntes}\n";
    echo "  Cursos ativos: {$ativosAntes}\n";
    echo "  Cursos inativos: {$inativosAntes}\n\n";
    
    // Executar sincronização
    echo "=== Executando sincronização ===\n";
    $syncService = new RemoteSyncService($localDb, $remoteDb);
    $result = $syncService->syncAllCourses('cursos_site', 500);
    
    echo "\nStatus: {$result['status']}\n";
    echo "Mensagem: {$result['mensagem']}\n\n";
    
    if (isset($result['stats'])) {
        echo "Estatísticas:\n";
        foreach ($result['stats'] as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }
    
    // Contagem após sincronização
    echo "\n=== Estado após sincronização ===\n";
    $stmt = $localDb->query('SELECT COUNT(*) as total FROM courses');
    $totalDepois = $stmt->fetch()['total'];
    
    $stmt = $localDb->query("SELECT COUNT(*) as total FROM courses WHERE status = 'ativo'");
    $ativosDepois = $stmt->fetch()['total'];
    
    $stmt = $localDb->query("SELECT COUNT(*) as total FROM courses WHERE status = 'inativo'");
    $inativosDepois = $stmt->fetch()['total'];
    
    echo "  Total de cursos: {$totalDepois}\n";
    echo "  Cursos ativos: {$ativosDepois}\n";
    echo "  Cursos inativos: {$inativosDepois}\n\n";
    
    // Verificar mapeamento de categorias
    echo "=== Verificação de categorias ===\n";
    $stmt = $localDb->query("SELECT category_id, COUNT(*) as total FROM courses GROUP BY category_id");
    $catCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($catCounts as $cc) {
        $catNome = 'Desconhecida';
        foreach ($categories as $cat) {
            if ($cat['id'] == $cc['category_id']) {
                $catNome = $cat['nome'];
                break;
            }
        }
        echo "  category_id={$cc['category_id']} ({$catNome}): {$cc['total']} cursos\n";
    }
    
    // Amostra de cursos sincronizados
    echo "\n=== Amostra de cursos sincronizados ===\n";
    $stmt = $localDb->query('SELECT id, nome, category_id, status FROM courses ORDER BY id DESC LIMIT 5');
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cursos as $curso) {
        $catNome = 'Desconhecida';
        foreach ($categories as $cat) {
            if ($cat['id'] == $curso['category_id']) {
                $catNome = $cat['nome'];
                break;
            }
        }
        echo "  [{$curso['id']}] {$curso['nome']}\n";
        echo "      category_id={$curso['category_id']} ({$catNome}), status={$curso['status']}\n";
    }
    
    echo "\n=== Teste Concluído ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
