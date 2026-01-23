<?php
/**
 * FAESMA - Admin Authentication
 * 
 * Sistema de autenticação para área administrativa
 * Permite login seguro e gerenciamento de sessão
 * 
 * @package FAESMA
 * @version 1.0
 */

class AdminAuth
{
    /**
     * Credenciais padrão (em produção, usar banco de dados)
     * @var array
     */
    private static $validCredentials = [
        'admin' => 'faesma2024!@#', // Usuário padrão com senha
    ];

    /**
     * Tempo de sessão em segundos (30 minutos)
     * @var int
     */
    private static $sessionTimeout = 1800;

    /**
     * Nome da sessão
     * @var string
     */
    private static $sessionName = 'FAESMA_ADMIN_SESSION';

    /**
     * Inicializar sessão
     * 
     * @return void
     */
    public static function initSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verificar se usuário está autenticado
     * 
     * @return bool
     */
    public static function isAuthenticated()
    {
        self::initSession();

        // Verificar se existe sessão válida
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

        // Atualizar timestamp de última atividade
        $_SESSION[self::$sessionName]['timestamp'] = time();

        return true;
    }

    /**
     * Fazer login com usuário e senha
     * 
     * @param string $username Nome de usuário
     * @param string $password Senha
     * @return array ['success' => bool, 'message' => string]
     */
    public static function login($username, $password)
    {
        self::initSession();

        // Validar credenciais
        if (!isset(self::$validCredentials[$username])) {
            return [
                'success' => false,
                'message' => 'Usuário ou senha incorretos'
            ];
        }

        if (self::$validCredentials[$username] !== $password) {
            return [
                'success' => false,
                'message' => 'Usuário ou senha incorretos'
            ];
        }

        // Criar sessão
        $_SESSION[self::$sessionName] = [
            'username' => $username,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        return [
            'success' => true,
            'message' => 'Login realizado com sucesso'
        ];
    }

    /**
     * Fazer logout
     * 
     * @return void
     */
    public static function logout()
    {
        self::initSession();
        unset($_SESSION[self::$sessionName]);
        session_destroy();
    }

    /**
     * Obter usuário autenticado
     * 
     * @return string|null
     */
    public static function getUsername()
    {
        if (self::isAuthenticated()) {
            return $_SESSION[self::$sessionName]['username'] ?? null;
        }
        return null;
    }

    /**
     * Redirecionar para login se não autenticado
     * 
     * @return void
     */
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            header('Location: /projeto5/admin/login.php');
            exit;
        }
    }

    /**
     * Obter informações de sessão
     * 
     * @return array|null
     */
    public static function getSessionInfo()
    {
        if (self::isAuthenticated()) {
            return $_SESSION[self::$sessionName];
        }
        return null;
    }

    /**
     * Alterar senha (para implementação futura com DB)
     * 
     * @param string $username Nome de usuário
     * @param string $oldPassword Senha antiga
     * @param string $newPassword Senha nova
     * @return array ['success' => bool, 'message' => string]
     */
    public static function changePassword($username, $oldPassword, $newPassword)
    {
        // Validar credenciais antigas
        if (!isset(self::$validCredentials[$username])) {
            return [
                'success' => false,
                'message' => 'Usuário não encontrado'
            ];
        }

        if (self::$validCredentials[$username] !== $oldPassword) {
            return [
                'success' => false,
                'message' => 'Senha antiga incorreta'
            ];
        }

        // Atualizar senha (em arquivo para compatibilidade)
        self::$validCredentials[$username] = $newPassword;

        return [
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ];
    }
}
