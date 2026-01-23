<?php
/**
 * FAESMA - Script de Teste de Sincronização Completa
 * 
 * Testa a sincronização de:
 * - Categorias
 * - Modalidades
 * - Cursos
 * - Currículo
 * 
 * Execute via CLI: php sync_test_complete.php
 */

define('PROJECT_PATH', dirname(__FILE__));

require_once PROJECT_PATH . '/config/config.php';
require_once PROJECT_PATH . '/includes/RemoteSyncService.php';
require_once PROJECT_PATH . '/includes/Database.php';
require_once PROJECT_PATH . '/includes/db.php';

// Cores para CLI
class Colors {
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const RESET = "\033[0m";
}

echo Colors::BLUE . "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "   FAESMA - Teste de Sincronização Completa\n";
echo "════════════════════════════════════════════════════════════\n";
echo Colors::RESET;

try {
    // Conectar aos bancos de dados
    echo Colors::CYAN . "\n📡 Conectando aos bancos de dados...\n" . Colors::RESET;
    $localDb = Database::getInstance()->getConnection();
    $remoteDb = db();
    echo Colors::GREEN . "✓ Conexão estabelecida\n" . Colors::RESET;

    // Criar serviço de sincronização
    $syncService = new RemoteSyncService($localDb, $remoteDb);

    // ========================================
    // SINCRONIZAR CATEGORIAS
    // ========================================
    echo Colors::CYAN . "\n📁 Sincronizando Categorias...\n" . Colors::RESET;
    echo str_repeat("─", 60) . "\n";
    
    $resultCategories = $syncService->syncCategories();
    
    if ($resultCategories['status'] === 'sucesso') {
        echo Colors::GREEN . "✓ Sincronização de categorias concluída!\n" . Colors::RESET;
        echo "  • Criadas: {$resultCategories['stats']['criado']}\n";
        echo "  • Atualizadas: {$resultCategories['stats']['atualizado']}\n";
        if ($resultCategories['stats']['falha'] > 0) {
            echo Colors::YELLOW . "  • Com erro: {$resultCategories['stats']['falha']}\n" . Colors::RESET;
        }
    } else {
        echo Colors::RED . "✗ Erro na sincronização de categorias\n" . Colors::RESET;
        echo "  {$resultCategories['mensagem']}\n";
    }

    // ========================================
    // SINCRONIZAR MODALIDADES
    // ========================================
    echo Colors::CYAN . "\n🎓 Sincronizando Modalidades...\n" . Colors::RESET;
    echo str_repeat("─", 60) . "\n";
    
    $resultModalities = $syncService->syncModalities();
    
    if ($resultModalities['status'] === 'sucesso') {
        echo Colors::GREEN . "✓ Sincronização de modalidades concluída!\n" . Colors::RESET;
        echo "  • Criadas: {$resultModalities['stats']['criado']}\n";
        echo "  • Atualizadas: {$resultModalities['stats']['atualizado']}\n";
        if ($resultModalities['stats']['falha'] > 0) {
            echo Colors::YELLOW . "  • Com erro: {$resultModalities['stats']['falha']}\n" . Colors::RESET;
        }
    } else {
        echo Colors::RED . "✗ Erro na sincronização de modalidades\n" . Colors::RESET;
        echo "  {$resultModalities['mensagem']}\n";
    }

    // ========================================
    // SINCRONIZAR CURSOS
    // ========================================
    echo Colors::CYAN . "\n📚 Sincronizando Cursos...\n" . Colors::RESET;
    echo str_repeat("─", 60) . "\n";
    
    $resultCourses = $syncService->syncAllCourses();
    
    if ($resultCourses['status'] === 'sucesso') {
        echo Colors::GREEN . "✓ Sincronização de cursos concluída!\n" . Colors::RESET;
        echo "  • Criados: {$resultCourses['stats']['criado']}\n";
        echo "  • Atualizados: {$resultCourses['stats']['atualizado']}\n";
        echo "  • Ignorados: {$resultCourses['stats']['pulado']}\n";
        if ($resultCourses['stats']['falha'] > 0) {
            echo Colors::YELLOW . "  • Com erro: {$resultCourses['stats']['falha']}\n" . Colors::RESET;
        }
    } else {
        echo Colors::RED . "✗ Erro na sincronização de cursos\n" . Colors::RESET;
        echo "  {$resultCourses['mensagem']}\n";
    }

    // ========================================
    // SINCRONIZAR CURRÍCULO
    // ========================================
    echo Colors::CYAN . "\n📖 Sincronizando Currículo...\n" . Colors::RESET;
    echo str_repeat("─", 60) . "\n";
    
    $resultCurriculum = $syncService->syncCurriculum();
    
    if ($resultCurriculum['status'] === 'sucesso') {
        echo Colors::GREEN . "✓ Sincronização de currículo concluída!\n" . Colors::RESET;
        echo "  • Disciplinas criadas: {$resultCurriculum['stats']['criado']}\n";
        echo "  • Disciplinas atualizadas: {$resultCurriculum['stats']['atualizado']}\n";
        if ($resultCurriculum['stats']['falha'] > 0) {
            echo Colors::YELLOW . "  • Com erro: {$resultCurriculum['stats']['falha']}\n" . Colors::RESET;
        }
    } else {
        echo Colors::YELLOW . "⚠ Sincronização de currículo\n" . Colors::RESET;
        echo "  {$resultCurriculum['mensagem']}\n";
    }

    // ========================================
    // RESUMO FINAL
    // ========================================
    echo Colors::CYAN . "\n" . str_repeat("═", 60) . "\n";
    echo "📊 RESUMO GERAL DA SINCRONIZAÇÃO\n";
    echo str_repeat("═", 60) . Colors::RESET . "\n";

    $totalCriado = 
        $resultCategories['stats']['criado'] + 
        $resultModalities['stats']['criado'] + 
        $resultCourses['stats']['criado'] + 
        $resultCurriculum['stats']['criado'];

    $totalAtualizado = 
        $resultCategories['stats']['atualizado'] + 
        $resultModalities['stats']['atualizado'] + 
        $resultCourses['stats']['atualizado'] + 
        $resultCurriculum['stats']['atualizado'];

    $totalErro = 
        $resultCategories['stats']['falha'] + 
        $resultModalities['stats']['falha'] + 
        $resultCourses['stats']['falha'] + 
        $resultCurriculum['stats']['falha'];

    echo Colors::GREEN . "✓ Registros Criados: $totalCriado\n" . Colors::RESET;
    echo Colors::GREEN . "✓ Registros Atualizados: $totalAtualizado\n" . Colors::RESET;
    
    if ($totalErro > 0) {
        echo Colors::YELLOW . "⚠ Registros com Erro: $totalErro\n" . Colors::RESET;
    }

    echo Colors::CYAN . str_repeat("═", 60) . "\n" . Colors::RESET;
    echo Colors::GREEN . "\n✅ Sincronização completa finalizada com sucesso!\n\n" . Colors::RESET;

} catch (Exception $e) {
    echo Colors::RED . "\n❌ ERRO NA SINCRONIZAÇÃO\n" . Colors::RESET;
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
?>
