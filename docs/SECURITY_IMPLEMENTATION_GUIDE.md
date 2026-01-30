# 🔐 Guia de Implementação de Segurança - Área Administrativa

## Visão Geral

Este guia contém instruções passo a passo para implementar as melhorias de segurança **CRÍTICAS** necessárias antes do deploy em produção.

**Tempo estimado**: 2-4 horas  
**Nível técnico**: Intermediário  
**Prioridade**: CRÍTICA

---

## Pré-requisitos

- Acesso ao banco de dados MySQL
- Acesso aos arquivos do projeto
- PHP 7.4+ instalado
- Backup do banco de dados realizado

---

## Etapa 1: Criar Tabela de Usuários Administrativos

### 1.1 Criar arquivo de migração

Crie o arquivo `database/migration_admin_users.sql`:

```sql
-- ============================================
-- FAESMA - Migração: Tabela de Usuários Admin
-- Data: 2026-01-30
-- Prioridade: CRÍTICA
-- ============================================

USE faesma_db;

-- Criar tabela de usuários administrativos
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(150),
    is_active BOOLEAN DEFAULT TRUE,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    last_login DATETIME NULL,
    last_ip VARCHAR(45) NULL,
    password_changed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de log de auditoria
CREATE TABLE IF NOT EXISTS admin_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(50),
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.2 Executar migração

```bash
# Via linha de comando
mysql -u root -p faesma_db < database/migration_admin_users.sql

# Ou via phpMyAdmin: importar o arquivo SQL
```

---

## Etapa 2: Criar Usuário Admin Inicial

### 2.1 Criar script de setup

Crie o arquivo `scripts/setup_admin_user.php`:

```php
<?php
/**
 * Script para criar usuário admin inicial
 * EXECUTE APENAS UMA VEZ e depois DELETE este arquivo!
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

// ============================================
// CONFIGURAÇÃO DO USUÁRIO INICIAL
// ALTERE ESTES VALORES!
// ============================================
$adminUsername = 'admin';
$adminEmail = 'admin@faesma.edu.br';
$adminPassword = 'SuaSenhaSegura@2026!'; // ALTERE ISSO!
$adminNome = 'Administrador FAESMA';
// ============================================

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Verificar se já existe usuário
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if ($stmt->fetch()) {
        die("ERRO: Usuário '{$adminUsername}' já existe!\n");
    }
    
    // Gerar hash da senha com Argon2id (mais seguro)
    $passwordHash = password_hash($adminPassword, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
    
    // Inserir usuário
    $stmt = $pdo->prepare("
        INSERT INTO admin_users (username, email, password_hash, nome_completo, password_changed_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$adminUsername, $adminEmail, $passwordHash, $adminNome]);
    
    echo "✅ Usuário admin criado com sucesso!\n";
    echo "   Username: {$adminUsername}\n";
    echo "   Email: {$adminEmail}\n";
    echo "\n⚠️  IMPORTANTE: Delete este arquivo após a execução!\n";
    echo "   rm scripts/setup_admin_user.php\n";
    
} catch (Exception $e) {
    die("ERRO: " . $e->getMessage() . "\n");
}
```

### 2.2 Executar script

```bash
php scripts/setup_admin_user.php
```

### 2.3 IMPORTANTE: Deletar o script após execução

```bash
rm scripts/setup_admin_user.php
# ou no Windows:
del scripts\setup_admin_user.php
```

---

## Etapa 3: Atualizar Classe AdminAuth

### 3.1 Substituir o arquivo `includes/AdminAuth.php`

Crie um novo arquivo `includes/AdminAuth.php` com o código abaixo:

```php
<?php
/**
 * FAESMA - Admin Authentication (Versão Segura)
 * 
 * Sistema de autenticação seguro para área administrativa
 * Com proteção contra brute force, hash de senha e auditoria
 * 
 * @package FAESMA
 * @version 2.0
 */

require_once __DIR__ . '/Database.php';

class AdminAuth
{
    /**
     * Configurações de segurança
     */
    private static $sessionTimeout = 1800;        // 30 minutos
    private static $maxLoginAttempts = 5;         // Tentativas antes de bloquear
    private static $lockoutDuration = 900;        // 15 minutos de bloqueio
    private static $sessionName = 'FAESMA_ADMIN_SESSION';

    /**
     * Conexão com banco de dados
     * @var PDO
     */
    private static $db = null;

    /**
     * Obter conexão com banco de dados
     */
    private static function getDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    /**
     * Inicializar sessão com configurações seguras
     */
    public static function initSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurações seguras de cookie
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    /**
     * Verificar se usuário está autenticado
     */
    public static function isAuthenticated()
    {
        self::initSession();

        if (!isset($_SESSION[self::$sessionName])) {
            return false;
        }

        // Verificar timeout
        if (isset($_SESSION[self::$sessionName]['timestamp'])) {
            $elapsed = time() - $_SESSION[self::$sessionName]['timestamp'];
            if ($elapsed > self::$sessionTimeout) {
                self::logout();
                return false;
            }
        }

        // Validar fingerprint (IP + User Agent)
        $currentFingerprint = self::generateFingerprint();
        if (isset($_SESSION[self::$sessionName]['fingerprint'])) {
            if ($_SESSION[self::$sessionName]['fingerprint'] !== $currentFingerprint) {
                self::logout();
                self::logAction(null, $_SESSION[self::$sessionName]['username'] ?? 'unknown', 'session_hijack_attempt');
                return false;
            }
        }

        // Atualizar timestamp
        $_SESSION[self::$sessionName]['timestamp'] = time();

        return true;
    }

    /**
     * Gerar fingerprint do usuário
     */
    private static function generateFingerprint()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return hash('sha256', $ip . $ua);
    }

    /**
     * Verificar se conta está bloqueada
     */
    private static function isAccountLocked($user)
    {
        if ($user['login_attempts'] >= self::$maxLoginAttempts) {
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fazer login com usuário e senha
     */
    public static function login($username, $password)
    {
        self::initSession();

        $db = self::getDb();

        // Buscar usuário
        $stmt = $db->prepare("
            SELECT id, username, password_hash, is_active, login_attempts, locked_until 
            FROM admin_users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        // Usuário não encontrado
        if (!$user) {
            self::logAction(null, $username, 'login_failed', 'Usuário não encontrado');
            return [
                'success' => false,
                'message' => 'Usuário ou senha incorretos'
            ];
        }

        // Verificar se conta está ativa
        if (!$user['is_active']) {
            self::logAction($user['id'], $username, 'login_failed', 'Conta desativada');
            return [
                'success' => false,
                'message' => 'Esta conta está desativada'
            ];
        }

        // Verificar se conta está bloqueada
        if (self::isAccountLocked($user)) {
            $unlockTime = date('H:i', strtotime($user['locked_until']));
            self::logAction($user['id'], $username, 'login_blocked', 'Conta bloqueada');
            return [
                'success' => false,
                'message' => "Conta bloqueada temporariamente. Tente novamente após {$unlockTime}"
            ];
        }

        // Verificar senha
        if (!password_verify($password, $user['password_hash'])) {
            self::incrementLoginAttempts($user['id']);
            self::logAction($user['id'], $username, 'login_failed', 'Senha incorreta');
            
            $remainingAttempts = self::$maxLoginAttempts - ($user['login_attempts'] + 1);
            if ($remainingAttempts > 0) {
                return [
                    'success' => false,
                    'message' => "Usuário ou senha incorretos. {$remainingAttempts} tentativa(s) restante(s)"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Conta bloqueada por excesso de tentativas. Aguarde 15 minutos.'
                ];
            }
        }

        // Login bem-sucedido - resetar tentativas e criar sessão
        self::resetLoginAttempts($user['id']);
        self::updateLastLogin($user['id']);

        // Regenerar ID de sessão para prevenir fixation
        session_regenerate_id(true);

        // Criar sessão
        $_SESSION[self::$sessionName] = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'timestamp' => time(),
            'fingerprint' => self::generateFingerprint(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        self::logAction($user['id'], $user['username'], 'login_success');

        return [
            'success' => true,
            'message' => 'Login realizado com sucesso'
        ];
    }

    /**
     * Incrementar contador de tentativas de login
     */
    private static function incrementLoginAttempts($userId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE admin_users 
            SET login_attempts = login_attempts + 1,
                locked_until = CASE 
                    WHEN login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                    ELSE locked_until 
                END
            WHERE id = ?
        ");
        $stmt->execute([self::$maxLoginAttempts, self::$lockoutDuration, $userId]);
    }

    /**
     * Resetar contador de tentativas de login
     */
    private static function resetLoginAttempts($userId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE admin_users 
            SET login_attempts = 0, locked_until = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    /**
     * Atualizar último login
     */
    private static function updateLastLogin($userId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE admin_users 
            SET last_login = NOW(), last_ip = ? 
            WHERE id = ?
        ");
        $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? null, $userId]);
    }

    /**
     * Fazer logout
     */
    public static function logout()
    {
        self::initSession();
        
        if (isset($_SESSION[self::$sessionName])) {
            self::logAction(
                $_SESSION[self::$sessionName]['user_id'] ?? null,
                $_SESSION[self::$sessionName]['username'] ?? 'unknown',
                'logout'
            );
        }

        unset($_SESSION[self::$sessionName]);
        session_destroy();
        
        // Limpar cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    /**
     * Obter usuário autenticado
     */
    public static function getUsername()
    {
        if (self::isAuthenticated()) {
            return $_SESSION[self::$sessionName]['username'] ?? null;
        }
        return null;
    }

    /**
     * Obter ID do usuário autenticado
     */
    public static function getUserId()
    {
        if (self::isAuthenticated()) {
            return $_SESSION[self::$sessionName]['user_id'] ?? null;
        }
        return null;
    }

    /**
     * Redirecionar para login se não autenticado
     */
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            header('Location: /projeto5/admin/login.php');
            exit;
        }
    }

    /**
     * Alterar senha
     */
    public static function changePassword($userId, $oldPassword, $newPassword)
    {
        $db = self::getDb();

        // Buscar usuário
        $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuário não encontrado'];
        }

        // Verificar senha antiga
        if (!password_verify($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Senha atual incorreta'];
        }

        // Validar nova senha
        $validation = self::validatePassword($newPassword);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // Gerar novo hash
        $newHash = password_hash($newPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        // Atualizar senha
        $stmt = $db->prepare("
            UPDATE admin_users 
            SET password_hash = ?, password_changed_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newHash, $userId]);

        self::logAction($userId, self::getUsername(), 'password_changed');

        return ['success' => true, 'message' => 'Senha alterada com sucesso'];
    }

    /**
     * Validar força da senha
     */
    private static function validatePassword($password)
    {
        if (strlen($password) < 12) {
            return ['valid' => false, 'message' => 'A senha deve ter no mínimo 12 caracteres'];
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter ao menos uma letra maiúscula'];
        }
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter ao menos uma letra minúscula'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter ao menos um número'];
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter ao menos um caractere especial'];
        }
        return ['valid' => true, 'message' => 'Senha válida'];
    }

    /**
     * Registrar ação no log de auditoria
     */
    private static function logAction($userId, $username, $action, $details = null)
    {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                INSERT INTO admin_audit_log (user_id, username, action, ip_address, user_agent, details)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $username,
                $action,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
        } catch (Exception $e) {
            // Silenciar erros de log para não afetar funcionalidade
            error_log("Erro ao registrar auditoria: " . $e->getMessage());
        }
    }

    /**
     * Obter informações de sessão
     */
    public static function getSessionInfo()
    {
        if (self::isAuthenticated()) {
            return $_SESSION[self::$sessionName];
        }
        return null;
    }
}
```

---

## Etapa 4: Adicionar Proteção CSRF ao Login

### 4.1 Atualizar `admin/login.php`

Substitua o conteúdo do arquivo `admin/login.php`:

```php
<?php
/**
 * FAESMA - Admin Login (Versão Segura)
 * Página de login com proteção CSRF
 * 
 * @package FAESMA
 * @version 2.0
 */

require_once '../config/config.php';
require_once '../includes/AdminAuth.php';

// Iniciar sessão para CSRF
AdminAuth::initSession();

$error = null;

// Gerar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrfToken, $submittedToken)) {
        $error = 'Requisição inválida. Por favor, tente novamente.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Preencha todos os campos';
        } else {
            $result = AdminAuth::login($username, $password);

            if ($result['success']) {
                // Regenerar token CSRF após login
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: /projeto5/admin/');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Se já está autenticado, redirecionar
if (AdminAuth::isAuthenticated()) {
    header('Location: /projeto5/admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - Área Administrativa FAESMA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #008125, #000d58);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
        .login-header p { color: #666; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #008125, #000d58);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(92, 225, 229, 0.3);
        }
        .security-notice {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Área Administrativa</h1>
            <p>FAESMA - Faculdade Alcance</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <div class="form-group">
                <label for="username">Usuário ou E-mail</label>
                <input type="text" id="username" name="username" required 
                       autocomplete="username" maxlength="100">
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required 
                       autocomplete="current-password" maxlength="100">
            </div>

            <button type="submit">Entrar</button>
        </form>
        
        <div class="security-notice">
            🔒 Conexão segura • Acesso monitorado
        </div>
    </div>
</body>
</html>
```

---

## Etapa 5: Verificação e Testes

### 5.1 Criar script de verificação

Crie o arquivo `testes/verify_security.php`:

```php
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
```

### 5.2 Executar verificação

```bash
php testes/verify_security.php
```

---

## Etapa 6: Checklist Final

Antes de fazer deploy em produção, confirme:

- [ ] Migração SQL executada (`admin_users` e `admin_audit_log` criadas)
- [ ] Usuário admin inicial criado com senha forte
- [ ] Script `setup_admin_user.php` deletado
- [ ] Arquivo `AdminAuth.php` atualizado para versão 2.0
- [ ] Arquivo `login.php` atualizado com CSRF
- [ ] Verificação `verify_security.php` passou em todos os testes
- [ ] Backup do banco de dados realizado
- [ ] Senha padrão antiga (`faesma2024!@#`) não funciona mais
- [ ] HTTPS habilitado no servidor de produção

---

## Referência Rápida de Comandos

```bash
# 1. Executar migração do banco
mysql -u root -p faesma_db < database/migration_admin_users.sql

# 2. Criar usuário admin (edite a senha primeiro!)
php scripts/setup_admin_user.php

# 3. Deletar script de setup
rm scripts/setup_admin_user.php

# 4. Verificar implementação
php testes/verify_security.php
```

---

## Suporte

Em caso de problemas durante a implementação:

1. Verifique os logs em `logs/`
2. Consulte a tabela `admin_audit_log` para eventos de segurança
3. Restaure o backup se necessário

---

**Documento criado em**: 30/01/2026  
**Versão**: 1.0  
**Classificação**: CRÍTICO - PRÉ-DEPLOY
