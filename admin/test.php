<?php
/**
 * FAESMA - Quick Test Helper
 * 
 * Ferramenta para testar o sistema administrativo
 * 
 * @package FAESMA
 * @version 1.0
 */

require_once '../config/config.php';
require_once '../includes/AdminAuth.php';
require_once '../includes/RemoteSyncService.php';

$testResults = [];

// ============================================
// TESTE 1: Sistema de Autenticação
// ============================================
$testResults['auth'] = [
    'name' => 'Sistema de Autenticação',
    'tests' => []
];

// Verificar se AdminAuth existe
if (class_exists('AdminAuth')) {
    $testResults['auth']['tests'][] = [
        'name' => 'Classe AdminAuth carregada',
        'status' => 'PASS'
    ];
} else {
    $testResults['auth']['tests'][] = [
        'name' => 'Classe AdminAuth carregada',
        'status' => 'FAIL',
        'error' => 'Classe não encontrada'
    ];
}

// Verificar método de autenticação
if (method_exists('AdminAuth', 'isAuthenticated')) {
    $testResults['auth']['tests'][] = [
        'name' => 'Método isAuthenticated existe',
        'status' => 'PASS'
    ];
}

// ============================================
// TESTE 2: Sistema de Sincronização
// ============================================
$testResults['sync'] = [
    'name' => 'Sistema de Sincronização',
    'tests' => []
];

if (class_exists('RemoteSyncService')) {
    $testResults['sync']['tests'][] = [
        'name' => 'Classe RemoteSyncService carregada',
        'status' => 'PASS'
    ];
} else {
    $testResults['sync']['tests'][] = [
        'name' => 'Classe RemoteSyncService carregada',
        'status' => 'FAIL',
        'error' => 'Classe não encontrada'
    ];
}

// ============================================
// TESTE 3: Arquivos Criados
// ============================================
$testResults['files'] = [
    'name' => 'Arquivos Necessários',
    'tests' => []
];

$requiredFiles = [
    '../includes/AdminAuth.php' => 'Sistema de autenticação',
    './login.php' => 'Página de login',
    './index.php' => 'Painel administrativo',
    '../scripts/sync_cron.php' => 'Script de sincronização automática',
    '../docs/CONFIGURACAO_CRON.md' => 'Documentação de cron',
];

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists($file);
    $testResults['files']['tests'][] = [
        'name' => $description . " ({$file})",
        'status' => $exists ? 'PASS' : 'FAIL',
        'error' => !$exists ? 'Arquivo não encontrado' : null
    ];
}

// ============================================
// TESTE 4: Diretórios
// ============================================
$testResults['dirs'] = [
    'name' => 'Diretórios Necessários',
    'tests' => []
];

$requiredDirs = [
    '../admin' => 'Diretório administrativo',
    '../logs' => 'Diretório de logs',
    '../scripts' => 'Diretório de scripts',
];

foreach ($requiredDirs as $dir => $description) {
    $exists = is_dir($dir);
    $testResults['dirs']['tests'][] = [
        'name' => $description . " ({$dir})",
        'status' => $exists ? 'PASS' : 'FAIL',
        'error' => !$exists ? 'Diretório não encontrado' : null
    ];
}

// ============================================
// Renderizar resultados
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Sistema - FAESMA Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .test-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .test-section h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .test-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-left: 4px solid #ddd;
        }

        .test-item.pass {
            background: #f0fdf4;
            border-left-color: #10b981;
        }

        .test-item.fail {
            background: #fef2f2;
            border-left-color: #ef4444;
        }

        .test-icon {
            font-size: 20px;
            margin-right: 15px;
            width: 24px;
        }

        .test-content {
            flex: 1;
        }

        .test-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .test-error {
            font-size: 12px;
            color: #ef4444;
            margin-top: 5px;
            font-style: italic;
        }

        .summary {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .summary-item {
            text-align: center;
            padding: 20px;
            background: #f5f7fa;
            border-radius: 8px;
        }

        .summary-item .value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        .summary-item .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .next-steps {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }

        .next-steps h3 {
            color: #856404;
            margin-bottom: 15px;
        }

        .next-steps ol {
            margin-left: 20px;
            color: #856404;
            font-size: 14px;
            line-height: 1.8;
        }

        .next-steps li {
            margin-bottom: 10px;
        }

        .next-steps code {
            background: rgba(0, 0, 0, 0.05);
            padding: 4px 8px;
            border-radius: 3px;
            font-family: monospace;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.success {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Teste de Sistema - Área Administrativa</h1>
            <p>Verificação de instalação e configuração</p>
        </div>

        <?php foreach ($testResults as $sectionKey => $section): ?>
            <div class="test-section">
                <h2><?php echo htmlspecialchars($section['name']); ?></h2>
                
                <?php foreach ($section['tests'] as $test): ?>
                    <div class="test-item <?php echo strtolower($test['status']); ?>">
                        <div class="test-icon">
                            <?php echo ($test['status'] === 'PASS') ? '✅' : '❌'; ?>
                        </div>
                        <div class="test-content">
                            <div class="test-name"><?php echo htmlspecialchars($test['name']); ?></div>
                            <?php if (isset($test['error'])): ?>
                                <div class="test-error"><?php echo htmlspecialchars($test['error']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="summary">
            <h2 style="margin-bottom: 20px; color: #333;">📊 Resumo</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="value">
                        <?php
                        $totalPass = 0;
                        foreach ($testResults as $section) {
                            foreach ($section['tests'] as $test) {
                                if ($test['status'] === 'PASS') $totalPass++;
                            }
                        }
                        echo $totalPass;
                        ?>
                    </div>
                    <div class="label">✅ Testes Passaram</div>
                </div>
                <div class="summary-item">
                    <div class="value">
                        <?php
                        $totalFail = 0;
                        foreach ($testResults as $section) {
                            foreach ($section['tests'] as $test) {
                                if ($test['status'] === 'FAIL') $totalFail++;
                            }
                        }
                        echo $totalFail;
                        ?>
                    </div>
                    <div class="label">❌ Testes Falharam</div>
                </div>
                <div class="summary-item">
                    <div class="value">
                        <?php
                        $total = 0;
                        foreach ($testResults as $section) {
                            $total += count($section['tests']);
                        }
                        echo $total;
                        ?>
                    </div>
                    <div class="label">📈 Total de Testes</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
