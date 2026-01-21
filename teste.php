<?php
/**
 * FAESMA - Test Script for Database Connection
 * Displays courses from the database view
 */

require_once __DIR__ . '/includes/db.php';

try {
    $pdo = db();
    
    // Fetch data from the courses view
    $dados = fetchAllFromView($pdo, 'cursos_site', 500);
    
    // Check if we got results
    if (empty($dados)) {
        throw new Exception('Nenhum curso encontrado na view. Verifique o nome da view no banco de dados.');
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
            background: linear-gradient(135deg, #2c3e50, #34495e);
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
            color: #060;
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
            <h1>🎓 FAESMA - Teste de Conexão</h1>
            <p>Verificação de Integração com Banco de Dados</p>
        </div>
        
        <?php if (isset($erro)): ?>
            <!-- Error Message -->
            <div class="error">
                <strong>❌ Erro na Conexão:</strong><br>
                <?php echo htmlspecialchars($erro); ?>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">📋 Verificações a Realizar:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        ✓ Verifique se o banco de dados está rodando
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        ✓ Confirme as credenciais em <code>includes/db.php</code>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        ✓ Verifique se a view <code>cursos_site</code> existe no banco
                    </li>
                    <li style="padding: 10px 0;">
                        ✓ Verifique se há dados na view
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <!-- Success Message -->
            <div class="success">
                ✅ Conexão estabelecida com sucesso! <?php echo count($dados); ?> curso(s) encontrado(s).
            </div>
            
            <!-- Statistics -->
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($dados); ?></div>
                    <div class="stat-label">Total de Cursos</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($dados) > 0 ? count($dados[0]) : 0; ?></div>
                    <div class="stat-label">Campos por Curso</div>
                </div>
            </div>
            
            <!-- Courses Grid -->
            <div class="courses-grid">
                <?php foreach ($dados as $index => $curso): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <h3>#<?php echo $index + 1; ?></h3>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <?php foreach ($curso as $campo => $valor): ?>
                                    <div class="info-item">
                                        <span class="info-label"><?php echo htmlspecialchars($campo); ?>:</span>
                                        <span class="info-value">
                                            <?php 
                                                if ($valor === null) {
                                                    echo '<em style="color: #999;">NULL</em>';
                                                } else if (is_array($valor)) {
                                                    echo '<code>' . htmlspecialchars(json_encode($valor)) . '</code>';
                                                } else {
                                                    echo htmlspecialchars(substr($valor, 0, 100));
                                                    if (strlen($valor) > 100) echo '...';
                                                }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- JSON View -->
            <div class="json-view">
                <h3>📊 Dados em Formato JSON</h3>
                <pre><?php echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
