<?php
/**
 * FAESMA - Exemplos de Integração de Sincronização
 * 
 * Este arquivo mostra como integrar o serviço de sincronização
 * com o código existente do projeto
 * 
 * @package FAESMA
 * @version 1.0
 */

// ============================================
// EXEMPLO 1: Sincronizar antes de retornar cursos
// ============================================

/**
 * Get courses - com sincronização automática opcional
 * 
 * @param array $filters Filtros
 * @param bool $autoSync Sincronizar dados remotos antes de buscar
 * @param int $limit Limite
 * @param int $offset Offset
 * @return array Cursos
 */
function getCoursesWithSync($filters = [], $autoSync = false, $limit = null, $offset = 0)
{
    global $db;

    // Se autoSync ativo, sincronizar com banco remoto
    if ($autoSync) {
        try {
            require_once __DIR__ . '/RemoteSyncService.php';
            require_once __DIR__ . '/db.php';

            $remoteDb = db();
            $syncService = new RemoteSyncService($db, $remoteDb);
            $result = $syncService->syncDeltaCourses('cursos_site');

            // Log da sincronização (opcional)
            if ($result['status'] !== 'sucesso') {
                error_log('Sync Warning: ' . $result['mensagem']);
            }
        } catch (Exception $e) {
            error_log('Sync Error: ' . $e->getMessage());
            // Continuar mesmo se sincronização falhar
        }
    }

    // Buscar cursos normalmente
    return getCourses($filters, $limit, $offset);
}

// ============================================
// EXEMPLO 2: Endpoint de sincronização JSON
// ============================================

/**
 * Usar em um controller ou arquivo API
 * Exemplo em api/sync.php
 */
?>

<?php
// FILE: api/sync.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/RemoteSyncService.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Validar token
    $token = $_GET['token'] ?? $_POST['token'] ?? null;
    $expectedToken = md5(SECURE_KEY . date('Y-m-d'));

    if ($token !== $expectedToken) {
        http_response_code(403);
        die(json_encode(['status' => 'erro', 'mensagem' => 'Token inválido']));
    }

    // Obter parâmetros
    $action = $_GET['action'] ?? $_POST['action'] ?? 'sync';
    $viewName = $_GET['view'] ?? $_POST['view'] ?? 'cursos_site';
    $limit = (int)($_GET['limit'] ?? $_POST['limit'] ?? 500);

    // Conectar aos bancos
    $localDb = Database::getInstance()->getConnection();
    $remoteDb = db();

    // Criar serviço
    $syncService = new RemoteSyncService($localDb, $remoteDb);

    // Executar ação
    switch ($action) {
        case 'sync':
            $result = $syncService->syncAllCourses($viewName, $limit);
            break;

        case 'delta':
            $result = $syncService->syncDeltaCourses($viewName);
            break;

        case 'status':
            $result = [
                'status' => 'ok',
                'mensagem' => 'Sistema de sincronização ativo',
                'last_sync' => $syncService->getLastSyncTime(),
            ];
            break;

        default:
            throw new Exception('Ação inválida: ' . $action);
    }

    http_response_code(200);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>

<?php
// ============================================
// EXEMPLO 3: Mostrar status de sincronização no dashboard admin
// ============================================
?>

<div class="sync-status-widget">
    <h3>Status de Sincronização</h3>
    <div id="sync-status" class="status-box">
        <p>Carregando...</p>
    </div>
    <button onclick="syncNow()" class="btn btn-primary">Sincronizar Agora</button>
</div>

<script>
function getToken() {
    // Gerar token diário no PHP
    // var token = '<?php echo md5(SECURE_KEY . date('Y-m-d')); ?>';
    // Ou obter do servidor:
    return fetch('/api/sync.php?action=status&token=' + getStoredToken())
        .then(r => r.json());
}

function syncNow() {
    const token = getStoredToken();
    fetch(`/api/sync.php?action=sync&token=${token}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'sucesso') {
            alert('Sincronização concluída!');
            console.log('Stats:', data.stats);
        } else {
            alert('Erro: ' + data.mensagem);
        }
        updateSyncStatus();
    })
    .catch(error => {
        alert('Erro na requisição: ' + error);
    });
}

function updateSyncStatus() {
    fetch('/api/sync.php?action=status&token=' + getStoredToken())
        .then(response => response.json())
        .then(data => {
            const box = document.getElementById('sync-status');
            if (data.last_sync) {
                box.innerHTML = `<p>Última sincronização: <strong>${data.last_sync}</strong></p>`;
            } else {
                box.innerHTML = '<p>Nenhuma sincronização realizada ainda</p>';
            }
        });
}

// Carregar status ao abrir página
updateSyncStatus();
// Atualizar a cada 5 minutos
setInterval(updateSyncStatus, 5 * 60 * 1000);
</script>

<?php
// ============================================
// EXEMPLO 4: Hook para sincronização automática
// ============================================

/**
 * Adicionar em functions.php para sincronizar automaticamente
 * a cada 6 horas
 */
function autoSyncRemoteCourses()
{
    // Verificar se última sincronização foi há mais de 6 horas
    $lastSync = @file_get_contents(__DIR__ . '/../logs/last_sync.txt');

    if ($lastSync) {
        $lastSyncTime = strtotime($lastSync);
        $sixHoursAgo = time() - (6 * 3600);

        if ($lastSyncTime > $sixHoursAgo) {
            return; // Sincronização recente, não fazer novamente
        }
    }

    // Executar sincronização em background (async)
    try {
        require_once __DIR__ . '/RemoteSyncService.php';
        require_once __DIR__ . '/db.php';

        $localDb = Database::getInstance()->getConnection();
        $remoteDb = db();

        $syncService = new RemoteSyncService($localDb, $remoteDb);
        $result = $syncService->syncDeltaCourses('cursos_site');

        if ($result['status'] === 'sucesso') {
            error_log('Auto-sync concluído: ' . json_encode($result['stats']));
        } else {
            error_log('Auto-sync falhou: ' . $result['mensagem']);
        }
    } catch (Exception $e) {
        error_log('Auto-sync erro: ' . $e->getMessage());
    }
}

// Chamar no início de cada requisição (functions.php)
// autoSyncRemoteCourses();

?>

<?php
// ============================================
// EXEMPLO 5: Verificar dados mapeados em formulários
// ============================================
?>

<form id="course-form">
    <div class="form-group">
        <label for="cod_externo">ID Externo (Remoto)</label>
        <input type="text" name="cod_externo" id="cod_externo" 
               placeholder="De: id_curso" readonly>
        <small>Sincronizado automaticamente do banco remoto</small>
    </div>

    <div class="form-group">
        <label for="nome">Nome do Curso</label>
        <input type="text" name="nome" id="nome" 
               placeholder="De: nome_curso" required>
    </div>

    <div class="form-group">
        <label for="descricao_curta">Descrição Curta</label>
        <textarea name="descricao_curta" id="descricao_curta" 
                  placeholder="De: descricao"></textarea>
    </div>

    <div class="form-group">
        <label for="carga_horaria">Carga Horária</label>
        <input type="number" name="carga_horaria" id="carga_horaria" 
               placeholder="De: carga_horaria">
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="tcc_obrigatorio" id="tcc_obrigatorio">
            TCC Obrigatório (De: tcc_obrigatorio)
        </label>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="inscricao_online" id="inscricao_online">
            Inscrição Online (De: inscricao_online)
        </label>
    </div>

    <div class="alert alert-info">
        <strong>ℹ️ Informação:</strong> 
        Campos com "De: ..." são sincronizados automaticamente do banco remoto.
        As alterações locais podem ser sobrescritas na próxima sincronização.
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
</form>

<?php
// ============================================
// EXEMPLO 6: Validar mapeamento customizado
// ============================================

// Se precisar adicionar novo campo ao mapeamento:

// 1. Editar RemoteSyncMapping.php:
//    'novo_campo_remoto' => 'novo_campo_local',

// 2. Se campo requer transformação:
//    'novo_campo_local' => [
//        'valor1' => 'mapeado1',
//        'valor2' => 'mapeado2',
//    ],

// 3. Testar:
// $remoteData = ['novo_campo_remoto' => 'valor1'];
// $localData = RemoteSyncMapping::convertRemoteToLocal($remoteData);
// echo $localData['novo_campo_local']; // 'mapeado1'

?>
