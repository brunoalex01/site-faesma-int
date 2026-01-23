<?php
/**
 * FAESMA - Admin Dashboard
 * 
 * Dashboard da área administrativa para gerenciar sincronização
 * 
 * @package FAESMA
 * @version 1.0
 */

require_once '../config/config.php';
require_once '../includes/AdminAuth.php';
require_once '../includes/RemoteSyncService.php';
require_once '../includes/Database.php';

// Verificar autenticação
AdminAuth::requireAuth();

$user = AdminAuth::getUsername();

// Obter conexões ao banco de dados
$localDb = Database::getInstance()->getConnection();
$remoteDb = db(); // Conexão remota

// Criar serviço de sincronização
$syncService = new RemoteSyncService($localDb, $remoteDb);
$lastSyncTime = null;
$syncStats = null;

// Verificar última sincronização (arquivo de log)
$logFile = '../logs/sync_' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $lastSyncTime = filemtime($logFile);
}

// Processar requisição AJAX de sincronização manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync') {
    header('Content-Type: application/json');
    
    try {
        $result = $syncService->syncAllCourses();
        
        // Extrair dados das estatísticas
        $data = [
            'created' => $result['stats']['criado'] ?? 0,
            'updated' => $result['stats']['atualizado'] ?? 0,
            'deactivated' => $result['stats']['desativado'] ?? 0,
            'skipped' => $result['stats']['pulado'] ?? 0,
            'errors' => $result['stats']['falha'] ?? 0,
            'total' => ($result['stats']['criado'] ?? 0) + ($result['stats']['atualizado'] ?? 0),
        ];
        
        echo json_encode([
            'success' => true,
            'message' => $result['mensagem'] ?? 'Sincronização concluída com sucesso',
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro na sincronização: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Processar logout
if (isset($_GET['logout'])) {
    AdminAuth::logout();
    header('Location: /projeto5/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - FAESMA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #008125, #0d0158);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .header-info {
            font-size: 14px;
            opacity: 0.9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .welcome-section h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
            margin-bottom: 20px;
        }

        .sync-controls {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .sync-controls h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .sync-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .sync-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .sync-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .sync-button .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .sync-button.syncing .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .status-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .status-section h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .status-card {
            background: #f5f7fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #667eea;
        }

        .status-card.success {
            border-left-color: #10b981;
        }

        .status-card.warning {
            border-left-color: #f59e0b;
        }

        .status-card.error {
            border-left-color: #ef4444;
        }

        .status-card h3 {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .status-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .status-card .label {
            font-size: 13px;
            color: #999;
            margin-top: 5px;
        }

        .logs-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .logs-section h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .log-content {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 5px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            line-height: 1.5;
        }

        .log-entry {
            margin-bottom: 8px;
        }

        .log-entry.error {
            color: #f48771;
        }

        .log-entry.success {
            color: #89d185;
        }

        .log-entry.info {
            color: #75beff;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .cron-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #856404;
        }

        .cron-info strong {
            color: #333;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .info-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }

        .info-table td:first-child {
            font-weight: 600;
            color: #333;
            width: 200px;
        }

        .info-table td:last-child {
            color: #666;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1>🎓 Painel Administrativo FAESMA</h1>
            </div>
            <div class="header-info">
                <p>Usuário: <strong><?php echo htmlspecialchars($user); ?></strong></p>
                <a href="?logout=1" class="logout-btn">Sair</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-section">
            <h2>Bem-vindo ao Painel Administrativo!</h2>
            <p>Gerenciador de sincronização automática de cursos entre a view remota e banco de dados local.</p>
            <p style="font-size: 13px; color: #999;">Última atualização da página: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
            <h3 style="color: #856404; margin-bottom: 10px;">📌 Informação Importante</h3>
            <p style="color: #856404; margin: 0;">
                <strong>Sincronização com Desativação de Cursos!</strong><br>
                O site agora funciona da seguinte forma:
            </p>
            <ul style="color: #856404; margin-top: 10px; padding-left: 20px;">
                <li><strong>Durante a sincronização:</strong> Todos os cursos são marcados como <code style="background: rgba(0,0,0,0.1); padding: 2px 6px;">inativo</code></li>
                <li><strong>Em seguida:</strong> Apenas cursos encontrados na view remota são marcados como <code style="background: rgba(0,0,0,0.1); padding: 2px 6px;">ativo</code></li>
                <li><strong>Resultado:</strong> Cursos não encontrados na view ficam <code style="background: rgba(0,0,0,0.1); padding: 2px 6px;">inativo</code> (não são exibidos)</li>
            </ul>
            <p style="color: #856404; font-size: 12px; margin-top: 10px; margin-bottom: 0;">
                ✅ A sincronização ocorre quando: <strong>Botão "Atualizar Agora"</strong> ou <strong>Cron diário às 02:00 AM</strong>
            </p>
        </div>

        <div class="sync-controls">
            <h2>🔄 Sincronização de Cursos</h2>
            
            <div class="cron-info">
                <strong>⏰ Execução Automática:</strong> A sincronização é executada automaticamente todos os dias às 02:00 (horário do servidor).
            </div>

            <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
                Clique no botão abaixo para sincronizar os cursos manualmente, capturando todas as mudanças da view remota.
            </p>

            <button class="sync-button" id="syncBtn" onclick="performSync()">
                <span class="spinner"></span>
                <span>🔄 Atualizar Agora</span>
            </button>
        </div>

        <div id="syncResult"></div>

        <div class="status-section">
            <h2>📊 Informações do Sistema</h2>
            <div class="info-table">
                <table>
                    <tr>
                        <td>Usuário Autenticado:</td>
                        <td><?php echo htmlspecialchars($user); ?></td>
                    </tr>
                    <tr>
                        <td>Hora do Servidor:</td>
                        <td><?php echo date('d/m/Y H:i:s'); ?></td>
                    </tr>
                    <tr>
                        <td>Fuso Horário:</td>
                        <td><?php echo date_default_timezone_get(); ?></td>
                    </tr>
                    <tr>
                        <td>Versão PHP:</td>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td>Ambiente:</td>
                        <td><?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
        function performSync() {
            const btn = document.getElementById('syncBtn');
            const resultDiv = document.getElementById('syncResult');

            if (btn.disabled) return;

            btn.disabled = true;
            btn.classList.add('syncing');
            resultDiv.innerHTML = '';

            const formData = new FormData();
            formData.append('action', 'sync');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.classList.remove('syncing');

                let alertClass = data.success ? 'alert-success' : 'alert-error';
                let alertHtml = `<div class="alert ${alertClass}">`;
                
                if (data.success) {
                    alertHtml += `✅ ${data.message}<br>`;
                    if (data.data) {
                        alertHtml += `
                            <table style="margin-top: 15px; width: 100%; font-size: 13px;">
                                <tr>
                                    <td><strong>Criados:</strong></td>
                                    <td>${data.data.created || 0}</td>
                                </tr>
                                <tr>
                                    <td><strong>Atualizados:</strong></td>
                                    <td>${data.data.updated || 0}</td>
                                </tr>
                                <tr>
                                    <td><strong>Desativados:</strong></td>
                                    <td>${data.data.deactivated || 0}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ignorados:</strong></td>
                                    <td>${data.data.skipped || 0}</td>
                                </tr>
                                <tr style="background: rgba(0,0,0,0.05);">
                                    <td><strong>Processados:</strong></td>
                                    <td><strong>${data.data.total || 0}</strong></td>
                                </tr>
                            </table>
                        `;
                        if (data.data.errors && data.data.errors > 0) {
                            alertHtml += `<br><strong style="color: #991b1b;">⚠️ Erros encontrados: ${data.data.errors}</strong>`;
                        }
                    }
                } else {
                    alertHtml += `❌ ${data.message}`;
                }
                
                alertHtml += '</div>';
                resultDiv.innerHTML = alertHtml;

                // Auto scroll para resultado
                document.querySelector('.alert').scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                btn.disabled = false;
                btn.classList.remove('syncing');
                resultDiv.innerHTML = `<div class="alert alert-error">❌ Erro na requisição: ${error.message}</div>`;
            });
        }

        // Auto-refresh a cada 60 segundos
        setInterval(function() {
            // Atualizar hora apenas
            const now = new Date();
            const formatted = now.toLocaleString('pt-BR');
            console.log('Refresh em:', formatted);
        }, 60000);
    </script>
</body>
</html>
