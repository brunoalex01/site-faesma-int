<?php
/**
 * FAESMA - Script de Sincronização de Cursos
 * 
 * Sincroniza cursos da view remota (site.cursos_site) com a tabela local (cursos)
 * Execute via CLI ou acesse via navegador para disparar sincronização manual
 * 
 * Uso:
 * - CLI: php sync_courses.php
 * - Web: http://localhost/projeto5/sync_courses.php
 * 
 * @package FAESMA
 * @version 1.0
 */

// Determinar se é CLI ou Web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    // Em ambiente web, incluir configuração e headers
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . '/includes/RemoteSyncService.php';
    
    header('Content-Type: application/json; charset=utf-8');
    
    // Validação básica de acesso (você pode melhorar isso com autenticação)
    $allowed_token = isset($_GET['token']) ? $_GET['token'] : null;
    $expected_token = md5(SECURE_KEY . date('Y-m-d')); // Token diário baseado em SECURE_KEY
    
    if ($allowed_token !== $expected_token) {
        http_response_code(403);
        die(json_encode([
            'status' => 'erro',
            'mensagem' => 'Acesso não autorizado',
        ]));
    }
} else {
    // CLI setup
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . '/includes/RemoteSyncService.php';
}

try {
    // Conectar ao banco local
    $localDb = Database::getInstance()->getConnection();
    
    // Conectar ao banco remoto
    require_once __DIR__ . '/includes/db.php';
    $remoteDb = db();
    
    // Criar serviço de sincronização
    $syncService = new RemoteSyncService($localDb, $remoteDb);
    
    // Parâmetros opcionais
    $viewName = $_GET['view'] ?? $_POST['view'] ?? 'cursos_site';
    $limit = (int)($_GET['limit'] ?? $_POST['limit'] ?? 500);
    $mode = $_GET['mode'] ?? $_POST['mode'] ?? 'sync'; // 'sync' ou 'delta'
    
    // Executar sincronização
    if ($mode === 'delta') {
        $result = $syncService->syncDeltaCourses($viewName);
    } else {
        $result = $syncService->syncAllCourses($viewName, $limit);
    }
    
    // Enviar resposta
    if ($isCLI) {
        // Output em CLI
        echo "\n";
        echo "===========================================\n";
        echo "FAESMA - Sincronização de Cursos\n";
        echo "===========================================\n\n";
        
        echo "Status: " . strtoupper($result['status']) . "\n";
        echo "Mensagem: " . $result['mensagem'] . "\n";
        
        if (isset($result['stats'])) {
            echo "\n--- Estatísticas ---\n";
            echo "Cursos Criados: " . $result['stats']['criado'] . "\n";
            echo "Cursos Atualizados: " . $result['stats']['atualizado'] . "\n";
            echo "Cursos Pulados: " . $result['stats']['pulado'] . "\n";
            echo "Erros: " . $result['stats']['falha'] . "\n";
        }
        
        if (!empty($result['log'])) {
            echo "\n--- Log de Operações ---\n";
            foreach ($result['log'] as $logLine) {
                echo $logLine . "\n";
            }
        }
        
        echo "\n";
        exit(0);
    } else {
        // Output em JSON para web
        http_response_code($result['status'] === 'sucesso' ? 200 : 500);
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    $errorResponse = [
        'status' => 'erro',
        'mensagem' => $e->getMessage(),
    ];
    
    if ($isCLI) {
        echo "\n❌ ERRO: " . $e->getMessage() . "\n\n";
        exit(1);
    } else {
        http_response_code(500);
        echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
