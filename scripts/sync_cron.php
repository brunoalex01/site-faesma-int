<?php
/**
 * FAESMA - Cron Sync Script
 * 
 * Script para sincronização automática de cursos via Cron/Task Scheduler
 * Execute via cron/scheduler todos os dias às 02:00 da manhã
 * 
 * Cron (Linux/Mac):
 * 0 2 * * * /usr/bin/php /path/to/projeto5/scripts/sync_cron.php
 * 
 * Task Scheduler (Windows):
 * Executar: C:\xampp\php\php.exe C:\xampp\htdocs\projeto5\scripts\sync_cron.php
 * 
 * @package FAESMA
 * @version 1.0
 */

// Definir configurações de execução
set_time_limit(300); // 5 minutos máximo
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Diretório de projeto
define('PROJECT_PATH', dirname(dirname(__FILE__)));

// Incluir classes necessárias
require_once PROJECT_PATH . '/config/config.php';
require_once PROJECT_PATH . '/includes/RemoteSyncService.php';
require_once PROJECT_PATH . '/includes/Database.php';
require_once PROJECT_PATH . '/includes/db.php';

// Criar diretório de logs se não existir
$logsDir = PROJECT_PATH . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Arquivo de log do dia
$logFile = $logsDir . '/sync_' . date('Y-m-d') . '.log';
$logHandle = fopen($logFile, 'a');

/**
 * Log de mensagem
 * 
 * @param string $message Mensagem a registrar
 * @param string $level Nível de severidade (INFO, SUCCESS, WARNING, ERROR)
 */
function cronLog($message, $level = 'INFO') {
    global $logHandle;
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[{$timestamp}] [{$level}] {$message}\n";
    fwrite($logHandle, $logLine);
    echo $logLine;
}

try {
    cronLog('=== INICIANDO SINCRONIZAÇÃO AUTOMÁTICA ===', 'INFO');
    cronLog('Servidor: ' . ($_SERVER['SERVER_NAME'] ?? 'unknown'), 'INFO');
    cronLog('PHP Version: ' . phpversion(), 'INFO');

    // Obter conexões ao banco de dados
    $localDb = Database::getInstance()->getConnection();
    $remoteDb = db(); // Conexão remota

    // Criar serviço de sincronização
    $syncService = new RemoteSyncService($localDb, $remoteDb);

    // Registrar o começo
    cronLog('Conectando à view remota...', 'INFO');

    // Sincronizar categorias
    cronLog('Iniciando sincronização de categorias...', 'INFO');
    $resultCategories = $syncService->syncCategories();
    cronLog("   - Categorias criadas: {$resultCategories['stats']['criado']}", 'SUCCESS');
    cronLog("   - Categorias atualizadas: {$resultCategories['stats']['atualizado']}", 'SUCCESS');
    if ($resultCategories['stats']['falha'] > 0) {
        cronLog("   - ⚠️ Categorias com erro: {$resultCategories['stats']['falha']}", 'WARNING');
    }

    // Sincronizar modalidades
    cronLog('Iniciando sincronização de modalidades...', 'INFO');
    $resultModalities = $syncService->syncModalities();
    cronLog("   - Modalidades criadas: {$resultModalities['stats']['criado']}", 'SUCCESS');
    cronLog("   - Modalidades atualizadas: {$resultModalities['stats']['atualizado']}", 'SUCCESS');
    if ($resultModalities['stats']['falha'] > 0) {
        cronLog("   - ⚠️ Modalidades com erro: {$resultModalities['stats']['falha']}", 'WARNING');
    }

    // Sincronizar cursos
    cronLog('Iniciando sincronização de cursos...', 'INFO');
    $result = $syncService->syncAllCourses();

    // Sincronizar currículo
    cronLog('Iniciando sincronização de currículo...', 'INFO');
    $resultCurriculum = $syncService->syncCurriculum();
    cronLog("   - Disciplinas criadas: {$resultCurriculum['stats']['criado']}", 'SUCCESS');
    cronLog("   - Disciplinas atualizadas: {$resultCurriculum['stats']['atualizado']}", 'SUCCESS');
    if ($resultCurriculum['stats']['falha'] > 0) {
        cronLog("   - ⚠️ Disciplinas com erro: {$resultCurriculum['stats']['falha']}", 'WARNING');
    }

    // Registrar resultados finais
    cronLog("✅ SINCRONIZAÇÃO COMPLETA CONCLUÍDA COM SUCESSO!", 'SUCCESS');
    cronLog("📊 RESUMO FINAL:", 'SUCCESS');
    cronLog("   - Cursos criados: {$result['stats']['criado']}", 'SUCCESS');
    cronLog("   - Cursos atualizados: {$result['stats']['atualizado']}", 'SUCCESS');
    cronLog("   - Cursos ignorados: {$result['stats']['pulado']}", 'SUCCESS');

} catch (Exception $e) {
    cronLog('❌ ERRO NA SINCRONIZAÇÃO: ' . $e->getMessage(), 'ERROR');
    cronLog('Stack Trace: ' . $e->getTraceAsString(), 'ERROR');
    cronLog('=== SINCRONIZAÇÃO FINALIZADA COM ERRO ===', 'ERROR');
    exit(1);
} finally {
    if ($logHandle) {
        fclose($logHandle);
    }
}

exit(0);
