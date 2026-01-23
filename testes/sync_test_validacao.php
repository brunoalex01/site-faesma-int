<?php
/**
 * FAESMA - Teste Completo de Sincronização
 * 
 * Script para testar a sincronização de dados da view remota cursos_site
 * para as tabelas locais (courses, course_categories, course_modalities)
 * 
 * Execução: php sync_test_validacao.php
 * 
 * @package FAESMA
 * @version 2.0
 */

// Definir diretório de projeto
define('PROJECT_PATH', __DIR__);

// Incluir classes necessárias
require_once PROJECT_PATH . '/config/config.php';
require_once PROJECT_PATH . '/includes/RemoteSyncService.php';
require_once PROJECT_PATH . '/includes/Database.php';
require_once PROJECT_PATH . '/includes/db.php';

// Cores para terminal (ANSI)
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[91m";
    const GREEN = "\033[92m";
    const YELLOW = "\033[93m";
    const BLUE = "\033[94m";
    const CYAN = "\033[96m";
    const WHITE = "\033[97m";
}

/**
 * Imprimir linha formatada
 */
function printLine($message, $color = Colors::WHITE) {
    echo $color . $message . Colors::RESET . "\n";
}

/**
 * Imprimir seção
 */
function printSection($title) {
    echo "\n";
    printLine(str_repeat("=", 70), Colors::CYAN);
    printLine($title, Colors::CYAN);
    printLine(str_repeat("=", 70), Colors::CYAN);
}

/**
 * Imprimir sucesso
 */
function printSuccess($message) {
    printLine("✅ " . $message, Colors::GREEN);
}

/**
 * Imprimir erro
 */
function printError($message) {
    printLine("❌ " . $message, Colors::RED);
}

/**
 * Imprimir aviso
 */
function printWarning($message) {
    printLine("⚠️  " . $message, Colors::YELLOW);
}

/**
 * Imprimir info
 */
function printInfo($message) {
    printLine("ℹ️  " . $message, Colors::BLUE);
}

/**
 * Imprimir resultado de sincronização
 */
function printSyncResult($title, $result) {
    printInfo($title);
    
    if (isset($result['stats'])) {
        $stats = $result['stats'];
        printInfo("   Criado: " . ($stats['criado'] ?? 0));
        printInfo("   Atualizado: " . ($stats['atualizado'] ?? 0));
        if (isset($stats['falha']) && $stats['falha'] > 0) {
            printWarning("   Erros: " . $stats['falha']);
        }
        if (isset($stats['pulado']) && $stats['pulado'] > 0) {
            printInfo("   Pulado: " . $stats['pulado']);
        }
    }
    
    if (isset($result['mensagem'])) {
        printInfo("   Mensagem: " . $result['mensagem']);
    }
}

try {
    printSection("TESTE COMPLETO DE SINCRONIZAÇÃO - FAESMA");
    
    // Conectar ao banco de dados
    printInfo("Conectando ao banco de dados local...");
    $localDb = Database::getInstance()->getConnection();
    printSuccess("Banco de dados local conectado");
    
    printInfo("Conectando ao banco de dados remoto...");
    $remoteDb = db();
    printSuccess("Banco de dados remoto conectado");
    
    // Criar serviço de sincronização
    printInfo("Inicializando serviço de sincronização...");
    $syncService = new RemoteSyncService($localDb, $remoteDb);
    printSuccess("Serviço de sincronização inicializado");
    
    // =======================
    // TESTE 1: Sincronizar Categorias
    // =======================
    printSection("TESTE 1: Sincronização de Categorias");
    
    // Contar categorias antes
    $stmt = $localDb->prepare("SELECT COUNT(*) as total FROM course_categories");
    $stmt->execute();
    $before = $stmt->fetch()['total'];
    printInfo("Categorias no banco ANTES: " . $before);
    
    // Sincronizar
    printInfo("Iniciando sincronização de categorias da view cursos_site...");
    $resultCategories = $syncService->syncCategories();
    
    // Contar categorias depois
    $stmt = $localDb->prepare("SELECT COUNT(*) as total FROM course_categories");
    $stmt->execute();
    $after = $stmt->fetch()['total'];
    printInfo("Categorias no banco DEPOIS: " . $after);
    
    // Resultado
    printSyncResult("Resultado da sincronização de categorias:", $resultCategories);
    
    if ($resultCategories['status'] === 'sucesso') {
        printSuccess("Sincronização de categorias concluída com sucesso!");
    } else {
        printWarning("Sincronização de categorias retornou status: " . $resultCategories['status']);
    }
    
    // Listar categorias
    $stmt = $localDb->prepare("SELECT id, nome, slug, ativo FROM course_categories LIMIT 10");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    if (!empty($categories)) {
        printInfo("Amostra de categorias sincronizadas:");
        foreach ($categories as $cat) {
            echo "   - ID: {$cat['id']}, Nome: {$cat['nome']}, Slug: {$cat['slug']}, Ativo: " . ($cat['ativo'] ? 'Sim' : 'Não') . "\n";
        }
    }
    
    // =======================
    // TESTE 2: Sincronizar Modalidades
    // =======================
    printSection("TESTE 2: Sincronização de Modalidades");
    
    // Contar modalidades antes
    $stmt = $localDb->prepare("SELECT COUNT(*) as total FROM course_modalities");
    $stmt->execute();
    $before = $stmt->fetch()['total'];
    printInfo("Modalidades no banco ANTES: " . $before);
    
    // Sincronizar
    printInfo("Iniciando sincronização de modalidades da view cursos_site...");
    $resultModalities = $syncService->syncModalities();
    
    // Contar modalidades depois
    $stmt = $localDb->prepare("SELECT COUNT(*) as total FROM course_modalities");
    $stmt->execute();
    $after = $stmt->fetch()['total'];
    printInfo("Modalidades no banco DEPOIS: " . $after);
    
    // Resultado
    printSyncResult("Resultado da sincronização de modalidades:", $resultModalities);
    
    if ($resultModalities['status'] === 'sucesso') {
        printSuccess("Sincronização de modalidades concluída com sucesso!");
    } else {
        printWarning("Sincronização de modalidades retornou status: " . $resultModalities['status']);
    }
    
    // Listar modalidades
    $stmt = $localDb->prepare("SELECT id, nome, slug, ativo FROM course_modalities LIMIT 10");
    $stmt->execute();
    $modalities = $stmt->fetchAll();
    if (!empty($modalities)) {
        printInfo("Amostra de modalidades sincronizadas:");
        foreach ($modalities as $mod) {
            echo "   - ID: {$mod['id']}, Nome: {$mod['nome']}, Slug: {$mod['slug']}, Ativo: " . ($mod['ativo'] ? 'Sim' : 'Não') . "\n";
        }
    }
    
    // =======================
    // TESTE 3: Sincronizar Cursos
    // =======================
    printSection("TESTE 3: Sincronização de Cursos");
    
    // Contar cursos antes
    $stmt = $localDb->prepare("SELECT COUNT(*) as total FROM courses");
    $stmt->execute();
    $before = $stmt->fetch()['total'];
    printInfo("Cursos no banco ANTES: " . $before);
    
    // Sincronizar
    printInfo("Iniciando sincronização de cursos da view cursos_site...");
    $resultCourses = $syncService->syncAllCourses();
    
    // Contar cursos depois
    $stmt = $localDb->prepare("SELECT COUNT(*) as total FROM courses");
    $stmt->execute();
    $after = $stmt->fetch()['total'];
    printInfo("Cursos no banco DEPOIS: " . $after);
    
    // Resultado
    printSyncResult("Resultado da sincronização de cursos:", $resultCourses);
    
    if ($resultCourses['status'] === 'sucesso') {
        printSuccess("Sincronização de cursos concluída com sucesso!");
    } else {
        printError("Sincronização de cursos retornou status: " . $resultCourses['status']);
    }
    
    // Listar cursos
    $stmt = $localDb->prepare("SELECT id, nome, cod_externo FROM courses LIMIT 10");
    $stmt->execute();
    $courses = $stmt->fetchAll();
    if (!empty($courses)) {
        printInfo("Amostra de cursos sincronizados:");
        foreach ($courses as $course) {
            echo "   - ID: {$course['id']}, Nome: {$course['nome']}, Código: {$course['cod_externo']}\n";
        }
    }
    
    // =======================
    // TESTE 4: Verificar Integridade
    // =======================
    printSection("TESTE 4: Verificação de Integridade");
    
    // Verificar se há duplicatas
    printInfo("Verificando por duplicatas de categorias...");
    $stmt = $localDb->prepare("
        SELECT slug, COUNT(*) as total 
        FROM course_categories 
        WHERE slug IS NOT NULL AND slug != ''
        GROUP BY slug 
        HAVING COUNT(*) > 1
    ");
    $stmt->execute();
    $duplicates = $stmt->fetchAll();
    if (empty($duplicates)) {
        printSuccess("Nenhuma duplicata de categoria encontrada");
    } else {
        printWarning("Duplicatas encontradas: " . count($duplicates));
        foreach ($duplicates as $dup) {
            echo "   - Slug: {$dup['slug']}, Quantidade: {$dup['total']}\n";
        }
    }
    
    printInfo("Verificando por duplicatas de modalidades...");
    $stmt = $localDb->prepare("
        SELECT slug, COUNT(*) as total 
        FROM course_modalities 
        WHERE slug IS NOT NULL AND slug != ''
        GROUP BY slug 
        HAVING COUNT(*) > 1
    ");
    $stmt->execute();
    $duplicates = $stmt->fetchAll();
    if (empty($duplicates)) {
        printSuccess("Nenhuma duplicata de modalidade encontrada");
    } else {
        printWarning("Duplicatas encontradas: " . count($duplicates));
    }
    
    // Verificar relacionamentos
    printInfo("Verificando cursos com categoria inválida...");
    $stmt = $localDb->prepare("
        SELECT COUNT(*) as total 
        FROM courses c
        LEFT JOIN course_categories cc ON c.category_id = cc.id
        WHERE c.category_id IS NOT NULL AND cc.id IS NULL
    ");
    $stmt->execute();
    $invalid = $stmt->fetch()['total'];
    if ($invalid == 0) {
        printSuccess("Todos os relacionamentos de categorias estão válidos");
    } else {
        printWarning("Encontrados {$invalid} cursos com categoria inválida");
    }
    
    // =======================
    // RESUMO FINAL
    // =======================
    printSection("RESUMO FINAL");
    
    printInfo("Teste completado com sucesso!");
    printInfo("Dados sincronizados:");
    echo "  - Categorias criadas: " . ($resultCategories['stats']['criado'] ?? 0) . "\n";
    echo "  - Modalidades criadas: " . ($resultModalities['stats']['criado'] ?? 0) . "\n";
    echo "  - Cursos criados: " . ($resultCourses['stats']['criado'] ?? 0) . "\n";
    
    printSuccess("Sincronização de teste concluída!");
    
} catch (Exception $e) {
    printError("Erro durante teste: " . $e->getMessage());
    printError("Arquivo: " . $e->getFile() . ":" . $e->getLine());
    if (php_sapi_name() === 'cli') {
        printError("\nStack Trace:");
        echo $e->getTraceAsString() . "\n";
    }
    exit(1);
}

exit(0);
