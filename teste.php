<?php
/**
 * FAESMA - Sincronização Automática de Cursos
 * 
 * Página intermediária que:
 * 1. Lê cursos da View remota (site.cursos_site)
 * 2. Atualiza automaticamente o banco de dados local (faesma_db.courses)
 * 3. Mapeia todos os campos correspondentes
 * 4. Exibe relatório visual da sincronização
 * 
 * Use: Agende uma chamada diária via cron ou agendador
 * Cron: 0 2 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/RemoteSyncService.php';
require_once __DIR__ . '/includes/db.php';

// Variáveis de status
$erro = null;
$resultado_sync = null;
$resultado_curriculum = null;
$dados_remotos = null;

try {
    // Conectar aos bancos de dados
    $localDb = Database::getInstance()->getConnection();
    $remoteDb = db();
    
    // Criar serviço de sincronização
    $syncService = new RemoteSyncService($localDb, $remoteDb);
    
    // Executar sincronização automática de cursos
    $resultado_sync = $syncService->syncAllCourses('cursos_site', 500);
    
    // Executar sincronização de currículo/disciplinas
    $resultado_curriculum = $syncService->syncCurriculum('disciplinas_curso_site', 5000);
    
    // Se sucesso, buscar dados DO BANCO LOCAL para exibição
    if ($resultado_sync['status'] === 'sucesso') {
        // Buscar cursos ativos do banco LOCAL após sincronização
        $stmt = $localDb->prepare("
            SELECT c.*, 
                   cat.nome as categoria_nome, 
                   m.nome as modalidade_nome
            FROM courses c
            LEFT JOIN course_categories cat ON c.category_id = cat.id
            LEFT JOIN course_modalities m ON c.modality_id = m.id
            WHERE c.status = 'ativo'
            ORDER BY c.updated_at DESC
            LIMIT 50
        ");
        $stmt->execute();
        $dados_remotos = $stmt->fetchAll();
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    $erro = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Conexão com Banco de Dados</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #008125, #0d0158);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .error {
            background: #fee;
            border: 2px solid #f66;
            color: #c00;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .success {
            background: #efe;
            border: 2px solid #6f6;
            color: #008125;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .course-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .course-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
        }
        
        .course-header h3 {
            font-size: 1.3rem;
            margin-bottom: 8px;
        }
        
        .course-body {
            padding: 20px;
        }
        
        .course-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 0.95rem;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            min-width: 120px;
        }
        
        .info-value {
            color: #555;
            word-break: break-word;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.95rem;
        }
        
        .json-view {
            background: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            overflow-x: auto;
        }
        
        .json-view h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .json-view pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔄 FAESMA - Sincronização Automática</h1>
            <p>Intermediária de Atualização: View Remota → Banco de Dados Local</p>
        </div>
        
        <?php if (isset($erro)): ?>
            <!-- Error Message -->
            <div class="error">
                <strong>❌ Erro na Sincronização:</strong><br>
                <?php echo htmlspecialchars($erro); ?>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">📋 Verifique:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        ✓ Se o banco de dados remoto está acessível
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        ✓ Credenciais em <code>includes/db.php</code>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        ✓ Se a view <code>cursos_site</code> existe no banco remoto
                    </li>
                    <li style="padding: 10px 0;">
                        ✓ Se há dados na view remota
                    </li>
                </ul>
            </div>
        <?php elseif (isset($resultado_sync)): ?>
            <!-- Sync Result -->
            <div class="<?php echo $resultado_sync['status'] === 'sucesso' ? 'success' : 'error'; ?>">
                <?php if ($resultado_sync['status'] === 'sucesso'): ?>
                    <strong>✅ Sincronização Concluída com Sucesso!</strong><br>
                    <?php echo htmlspecialchars($resultado_sync['mensagem']); ?>
                <?php else: ?>
                    <strong>❌ Erro na Sincronização:</strong><br>
                    <?php echo htmlspecialchars($resultado_sync['mensagem']); ?>
                <?php endif; ?>
            </div>
            
            <!-- Statistics -->
            <?php if (isset($resultado_sync['stats'])): ?>
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number" style="color: #27ae60;"><?php echo $resultado_sync['stats']['criado']; ?></div>
                    <div class="stat-label">Cursos Criados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #3498db;"><?php echo $resultado_sync['stats']['atualizado']; ?></div>
                    <div class="stat-label">Cursos Atualizados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #f39c12;"><?php echo $resultado_sync['stats']['pulado']; ?></div>
                    <div class="stat-label">Cursos Pulados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #e74c3c;"><?php echo $resultado_sync['stats']['falha']; ?></div>
                    <div class="stat-label">Erros</div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Statistics Currículo -->
            <?php if (isset($resultado_curriculum['stats'])): ?>
            <h3 style="color: #2c3e50; margin: 30px 0 15px 0;">📚 Sincronização de Currículo/Disciplinas</h3>
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number" style="color: #27ae60;"><?php echo $resultado_curriculum['stats']['criado']; ?></div>
                    <div class="stat-label">Disciplinas Criadas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #3498db;"><?php echo $resultado_curriculum['stats']['atualizado']; ?></div>
                    <div class="stat-label">Disciplinas Atualizadas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #9b59b6;"><?php echo $resultado_curriculum['stats']['removido']; ?></div>
                    <div class="stat-label">Disciplinas Removidas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #e74c3c;"><?php echo $resultado_curriculum['stats']['falha']; ?></div>
                    <div class="stat-label">Erros</div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Log de Operações -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 30px 0;">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">📋 Log de Operações (Cursos)</h3>
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.9rem;">
                    <?php foreach ($resultado_sync['log'] as $log_line): ?>
                        <div style="padding: 5px 0; border-bottom: 1px solid #eee;">
                            <?php echo htmlspecialchars($log_line); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Log de Operações Currículo -->
            <?php if (isset($resultado_curriculum['log']) && !empty($resultado_curriculum['log'])): ?>
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 30px 0;">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">📋 Log de Operações (Currículo)</h3>
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.9rem;">
                    <?php foreach ($resultado_curriculum['log'] as $log_line): ?>
                        <div style="padding: 5px 0; border-bottom: 1px solid #eee;">
                            <?php echo htmlspecialchars($log_line); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Dados Sincronizados -->
            <?php if (!empty($dados_remotos)): ?>
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 30px 0;">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">📊 Cursos Sincronizados no Banco Local (<?php echo count($dados_remotos); ?> ativos)</h3>
                
                <div class="courses-grid">
                    <?php foreach ($dados_remotos as $index => $curso): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h3>#<?php echo $index + 1; ?> - <?php echo htmlspecialchars($curso['nome'] ?? 'Sem nome'); ?></h3>
                            </div>
                            <div class="course-body">
                                <div class="course-info">
                                    <div class="info-item">
                                        <span class="info-label">ID Local:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['id'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">ID Externo:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['cod_externo'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Slug:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['slug'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Descrição:</span>
                                        <span class="info-value"><?php echo htmlspecialchars(substr($curso['descricao_curta'] ?? '', 0, 60)); ?>...</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Categoria:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['categoria_nome'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Modalidade:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['modalidade_nome'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Duração:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['duracao_texto'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Carga Horária:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($curso['carga_horaria'] ?? 'N/A'); ?> h</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Status:</span>
                                        <span class="info-value" style="padding: 4px 8px; background: #e8f4f8; border-radius: 3px; display: inline-block;">
                                            <?php echo htmlspecialchars($curso['status'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Atualizado em:</span>
                                        <span class="info-value" style="font-size: 0.85rem; color: #999;">
                                            <?php echo isset($curso['updated_at']) ? date('d/m/Y H:i', strtotime($curso['updated_at'])) : 'N/A'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Próxima Sincronização -->
            <div style="background: #e8f4f8; padding: 15px; border-radius: 8px; margin-top: 30px; border-left: 4px solid #3498db;">
                <strong>ℹ️ Informação:</strong><br>
                Esta página foi executada em <strong><?php echo date('d/m/Y H:i:s'); ?></strong><br>
                <br>
                <strong>Dados Exibidos:</strong> Cursos do banco local (<code>faesma_db.courses</code>) com status <code>ativo</code><br>
                <br>
                <strong>Para automatizar:</strong> Configure uma tarefa cron para chamar esta página uma vez por dia.<br>
                <code>0 2 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1</code><br>
                <br>
                <strong>Detalhes da Sincronização:</strong><br>
                • Origem: View Remota <code>site.cursos_site</code><br>
                • Destino: Banco Local <code>faesma_db.courses</code><br>
                • Campos Sincronizados: 21+<br>
                • Comportamento: Desativa cursos não encontrados na view<br>
                • Campos: cod_externo, nome, slug, descricao_curta, categoria, modalidade, duração, carga_horária, status, etc.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
