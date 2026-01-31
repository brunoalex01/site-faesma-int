<?php
/**
 * Verificação de implementação de segurança
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

echo "=== Verificação de Segurança ===\n\n";

$pdo = Database::getInstance()->getConnection();
$allPassed = true;

// Teste 1: Tabela admin_users existe
echo "1. Verificando tabela admin_users... ";
try {
    $stmt = $pdo->query("DESCRIBE admin_users");
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ FALHOU - Tabela não existe\n";
    $allPassed = false;
}

// Teste 2: Tabela admin_audit_log existe
echo "2. Verificando tabela admin_audit_log... ";
try {
    $stmt = $pdo->query("DESCRIBE admin_audit_log");
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ FALHOU - Tabela não existe\n";
    $allPassed = false;
}

// Teste 3: Usuário admin existe
echo "3. Verificando usuário admin... ";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_users WHERE is_active = 1");
$result = $stmt->fetch();
if ($result['total'] > 0) {
    echo "✅ OK ({$result['total']} usuário(s) ativo(s))\n";
} else {
    echo "❌ FALHOU - Nenhum usuário ativo\n";
    $allPassed = false;
}

// Teste 4: Senha está com hash
echo "4. Verificando hash de senha... ";
$stmt = $pdo->query("SELECT password_hash FROM admin_users LIMIT 1");
$user = $stmt->fetch();
if ($user && strlen($user['password_hash']) > 50 && strpos($user['password_hash'], '$argon2') === 0) {
    echo "✅ OK (Argon2id)\n";
} elseif ($user && strlen($user['password_hash']) > 50) {
    echo "✅ OK (Hash detectado)\n";
} else {
    echo "❌ FALHOU - Senha pode estar em texto plano\n";
    $allPassed = false;
}

// Teste 5: Classe AdminAuth atualizada
echo "5. Verificando AdminAuth atualizado... ";
require_once __DIR__ . '/../includes/AdminAuth.php';
if (method_exists('AdminAuth', 'getUserId')) {
    echo "✅ OK (Versão 2.0)\n";
} else {
    echo "⚠️  ATENÇÃO - Versão antiga detectada\n";
    $allPassed = false;
}

echo "\n" . str_repeat("=", 40) . "\n";
if ($allPassed) {
    echo "✅ TODAS AS VERIFICAÇÕES PASSARAM!\n";
    echo "   Sistema pronto para produção.\n";
} else {
    echo "❌ ALGUMAS VERIFICAÇÕES FALHARAM!\n";
    echo "   Revise os itens acima antes do deploy.\n";
}
