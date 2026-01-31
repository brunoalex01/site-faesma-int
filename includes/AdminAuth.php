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
