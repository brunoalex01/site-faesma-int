<?php
/**
 * FAESMA - Core Functions
 * 
 * Main utility functions for the website
 * Includes course management, content retrieval, security, and helpers
 * 
 * @package FAESMA
 * @version 1.0
 */

// Prevent direct access
defined('FAESMA_ACCESS') or die('Direct access not permitted');

// Get database instance
$db = Database::getInstance();

// ============================================
// COURSE FUNCTIONS
// ============================================

/**
 * Get all active courses with optional filtering
 * 
 * @param array $filters Filters: category_id, modality_id, search, status
 * @param int $limit Limit number of results
 * @param int $offset Offset for pagination
 * @return array Array of courses
 */
function getCourses($filters = [], $limit = null, $offset = 0)
{
    global $db;

        $sql = "SELECT c.*, 
            cat.nome as categoria_nome, 
            cat.slug as categoria_slug,
            m.nome as modalidade_nome,
            m.slug as modalidade_slug
            FROM courses c
            INNER JOIN course_categories cat ON c.category_id = cat.id
            INNER JOIN course_modalities m ON c.modality_id = m.id
            WHERE 1=1";

    $params = [];

    // Apply filters
    if (isset($filters['category_id']) && !empty($filters['category_id'])) {
        $sql .= " AND c.category_id = :category_id";
        $params[':category_id'] = $filters['category_id'];
    }

    if (isset($filters['modality_id']) && !empty($filters['modality_id'])) {
        $sql .= " AND c.modality_id = :modality_id";
        $params[':modality_id'] = $filters['modality_id'];
    }

    if (isset($filters['status']) && !empty($filters['status'])) {
        $sql .= " AND c.status = :status";
        $params[':status'] = $filters['status'];
    } else {
        // Default to active courses only
        $sql .= " AND c.status = 'ativo'";
    }

    if (isset($filters['search']) && !empty($filters['search'])) {
        $sql .= " AND (c.nome LIKE :search OR c.descricao_curta LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }

    if (isset($filters['destaque']) && $filters['destaque'] === true) {
        $sql .= " AND c.destaque = 1";
    }

    $sql .= " ORDER BY c.ordem ASC, c.nome ASC";

    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int) $limit;
        $params[':offset'] = (int) $offset;
    }

    return $db->fetchAll($sql, $params);
}

/**
 * Get single course by slug or ID
 * 
 * @param string|int $identifier Course slug or ID
 * @param string $field Field to search by (slug or id)
 * @return array|false Course data or false
 */
function getCourse($identifier, $field = 'slug')
{
    global $db;

        $sql = "SELECT c.*, 
            cat.nome as categoria_nome, 
            cat.slug as categoria_slug,
            m.nome as modalidade_nome,
            m.slug as modalidade_slug
            FROM courses c
            INNER JOIN course_categories cat ON c.category_id = cat.id
            INNER JOIN course_modalities m ON c.modality_id = m.id
            WHERE c.$field = :identifier";

    return $db->fetchOne($sql, [':identifier' => $identifier]);
}

/**
 * Get course curriculum
 * 
 * @param int $course_id Course ID
 * @return array Array of curriculum items grouped by semester
 */
function getCourseCurriculum($course_id)
{
    global $db;

    $sql = "SELECT * FROM course_curriculum 
            WHERE course_id = :course_id 
            ORDER BY semestre ASC, ordem ASC";

    $items = $db->fetchAll($sql, [':course_id' => $course_id]);

    // Group by semester
    $curriculum = [];
    foreach ($items as $item) {
        $semester = $item['semestre'] ?? 0;
        if (!isset($curriculum[$semester])) {
            $curriculum[$semester] = [];
        }
        $curriculum[$semester][] = $item;
    }

    ksort($curriculum);
    return $curriculum;
}

/**
 * Get total course count with filters
 * 
 * @param array $filters Same filters as getCourses()
 * @return int Total count
 */
function getCourseCount($filters = [])
{
    global $db;

    $sql = "SELECT COUNT(*) as total FROM courses c WHERE 1=1";
    $params = [];

    if (isset($filters['category_id']) && !empty($filters['category_id'])) {
        $sql .= " AND c.category_id = :category_id";
        $params[':category_id'] = $filters['category_id'];
    }

    if (isset($filters['modality_id']) && !empty($filters['modality_id'])) {
        $sql .= " AND c.modality_id = :modality_id";
        $params[':modality_id'] = $filters['modality_id'];
    }

    if (isset($filters['status']) && !empty($filters['status'])) {
        $sql .= " AND c.status = :status";
        $params[':status'] = $filters['status'];
    } else {
        $sql .= " AND c.status = 'ativo'";
    }

    if (isset($filters['search']) && !empty($filters['search'])) {
        $sql .= " AND (c.nome LIKE :search OR c.descricao_curta LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }

    $result = $db->fetchOne($sql, $params);
    return $result ? (int) $result['total'] : 0;
}

/**
 * Get all course categories
 * 
 * @param bool $active_only Only active categories
 * @return array Array of categories
 */
function getCourseCategories($active_only = true)
{
    global $db;

    $sql = "SELECT * FROM course_categories";

    if ($active_only) {
        $sql .= " WHERE ativo = 1";
    }

    $sql .= " ORDER BY ordem ASC, nome ASC";

    return $db->fetchAll($sql);
}

/**
 * Get all course modalities
 * 
 * @param bool $active_only Only active modalities
 * @return array Array of modalities
 */
function getCourseModalities($active_only = true)
{
    global $db;

    $sql = "SELECT * FROM course_modalities";

    if ($active_only) {
        $sql .= " WHERE ativo = 1";
    }

    $sql .= " ORDER BY nome ASC";

    return $db->fetchAll($sql);
}

// ============================================
// NEWS FUNCTIONS
// ============================================

/**
 * Get news articles
 * 
 * @param array $filters Filters: status, destaque
 * @param int $limit Limit number of results
 * @param int $offset Offset for pagination
 * @return array Array of news
 */
function getNews($filters = [], $limit = null, $offset = 0)
{
    global $db;

    $sql = "SELECT * FROM news WHERE 1=1";
    $params = [];

    if (isset($filters['status']) && !empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    } else {
        $sql .= " AND status = 'publicado'";
    }

    if (isset($filters['destaque']) && $filters['destaque'] === true) {
        $sql .= " AND destaque = 1";
    }

    $sql .= " ORDER BY data_publicacao DESC";

    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int) $limit;
        $params[':offset'] = (int) $offset;
    }

    return $db->fetchAll($sql, $params);
}

/**
 * Get single news article by slug
 * 
 * @param string $slug News slug
 * @return array|false News data or false
 */
function getNewsArticle($slug)
{
    global $db;

    $sql = "SELECT * FROM news WHERE slug = :slug";
    $article = $db->fetchOne($sql, [':slug' => $slug]);

    // Increment view count
    if ($article) {
        $updateSql = "UPDATE news SET visualizacoes = visualizacoes + 1 WHERE id = :id";
        $db->query($updateSql, [':id' => $article['id']]);
    }

    return $article;
}

// ============================================
// EVENT FUNCTIONS
// ============================================

/**
 * Get events
 * 
 * @param array $filters Filters: status, upcoming
 * @param int $limit Limit number of results
 * @param int $offset Offset for pagination
 * @return array Array of events
 */
function getEvents($filters = [], $limit = null, $offset = 0)
{
    global $db;

    $sql = "SELECT * FROM events WHERE 1=1";
    $params = [];

    if (isset($filters['status']) && !empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    } else {
        $sql .= " AND status = 'ativo'";
    }

    if (isset($filters['upcoming']) && $filters['upcoming'] === true) {
        $sql .= " AND data_inicio >= NOW()";
    }

    if (isset($filters['destaque']) && $filters['destaque'] === true) {
        $sql .= " AND destaque = 1";
    }

    $sql .= " ORDER BY data_inicio ASC";

    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int) $limit;
        $params[':offset'] = (int) $offset;
    }

    return $db->fetchAll($sql, $params);
}

/**
 * Get single event by slug
 * 
 * @param string $slug Event slug
 * @return array|false Event data or false
 */
function getEvent($slug)
{
    global $db;

    $sql = "SELECT * FROM events WHERE slug = :slug";
    return $db->fetchOne($sql, [':slug' => $slug]);
}

// ============================================
// CONTACT FUNCTIONS
// ============================================

/**
 * Save contact form submission
 * 
 * @param array $data Contact form data
 * @return bool Success status
 */
function saveContact($data)
{
    global $db;

    $sql = "INSERT INTO contacts (nome, email, telefone, assunto, mensagem, ip_address, user_agent, created_at)
            VALUES (:nome, :email, :telefone, :assunto, :mensagem, :ip, :user_agent, NOW())";

    $params = [
        ':nome' => $data['nome'],
        ':email' => $data['email'],
        ':telefone' => $data['telefone'] ?? null,
        ':assunto' => $data['assunto'] ?? null,
        ':mensagem' => $data['mensagem'],
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    return $db->query($sql, $params) !== false;
}

// ============================================
// SETTINGS FUNCTIONS
// ============================================

/**
 * Get site setting by key
 * 
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getSetting($key, $default = null)
{
    global $db;

    $sql = "SELECT setting_value FROM settings WHERE setting_key = :key";
    $result = $db->fetchOne($sql, [':key' => $key]);

    return $result ? $result['setting_value'] : $default;
}

/**
 * Get all settings as associative array
 * 
 * @return array Settings array
 */
function getAllSettings()
{
    global $db;

    $sql = "SELECT setting_key, setting_value FROM settings";
    $results = $db->fetchAll($sql);

    $settings = [];
    foreach ($results as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

// ============================================
// PAGE FUNCTIONS
// ============================================

/**
 * Get page content by slug
 * 
 * @param string $slug Page slug
 * @return array|false Page data or false
 */
function getPage($slug)
{
    global $db;

    $sql = "SELECT * FROM pages WHERE slug = :slug AND status = 'publicado'";
    return $db->fetchOne($sql, [':slug' => $slug]);
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Sanitize input string
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * 
 * @param string $email Email address
 * @return bool Valid or not
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate CPF (Brazilian ID)
 * 
 * @param string $cpf CPF number
 * @return bool Valid or not
 */
function validateCPF($cpf)
{
    // Remove non-numeric characters
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Check if has 11 digits
    if (strlen($cpf) != 11) {
        return false;
    }

    // Check if all digits are the same
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // Validate first digit
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += $cpf[$i] * (10 - $i);
    }
    $digit1 = 11 - ($sum % 11);
    $digit1 = ($digit1 >= 10) ? 0 : $digit1;

    if ($cpf[9] != $digit1) {
        return false;
    }

    // Validate second digit
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += $cpf[$i] * (11 - $i);
    }
    $digit2 = 11 - ($sum % 11);
    $digit2 = ($digit2 >= 10) ? 0 : $digit2;

    return $cpf[10] == $digit2;
}

/**
 * Generate CSRF token
 * 
 * @return string Token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool Valid or not
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Format currency (Brazilian Real)
 * 
 * @param float $value Value to format
 * @return string Formatted currency
 */
function formatCurrency($value)
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Format date (Brazilian format)
 * 
 * @param string $date Date string
 * @param string $format Format (short, long, full)
 * @return string Formatted date
 */
function formatDate($date, $format = 'short')
{
    if (empty($date))
        return '';

    $timestamp = strtotime($date);

    switch ($format) {
        case 'short':
            return date('d/m/Y', $timestamp);
        case 'long':
            return strftime('%d de %B de %Y', $timestamp);
        case 'full':
            return strftime('%d de %B de %Y às %H:%M', $timestamp);
        default:
            return date('d/m/Y H:i', $timestamp);
    }
}

/**
 * Truncate text to specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add
 * @return string Truncated text
 */
function truncateText($text, $length = 150, $suffix = '...')
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate slug from string
 * 
 * @param string $text Text to convert
 * @return string Slug
 */
function generateSlug($text)
{
    // Convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');

    // Replace accented characters
    $replacements = [
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ç' => 'c',
        'ñ' => 'n'
    ];
    $text = strtr($text, $replacements);

    // Remove special characters
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);

    // Replace spaces and multiple hyphens with single hyphen
    $text = preg_replace('/[\s-]+/', '-', $text);

    // Remove leading/trailing hyphens
    $text = trim($text, '-');

    return $text;
}

/**
 * Check if user is logged in (for admin areas)
 * 
 * @return bool Logged in status
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 * @param int $code HTTP status code
 */
function redirect($url, $code = 302)
{
    header("Location: $url", true, $code);
    exit;
}

/**
 * Get current page URL
 * 
 * @return string Current URL
 */
function getCurrentUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Generate meta description from content
 * 
 * @param string $content Content text
 * @param int $length Maximum length
 * @return string Meta description
 */
function generateMetaDescription($content, $length = 160)
{
    $text = strip_tags($content);
    $text = preg_replace('/\s+/', ' ', $text);
    return truncateText($text, $length, '');
}

// ============================================
// INTEGRATED COURSE FUNCTIONS FROM VIEW
// ============================================

/**
 * Get all courses from the integrated view (cursos_site)
 * Fetches data directly from the database view
 * 
 * @param array $filters Filters: category_id, modality_id, search, status
 * @param int $limit Limit number of results
 * @param int $offset Offset for pagination
 * @return array Array of courses from view
 */
function getCoursesFromView($filters = [], $limit = null, $offset = 0)
{
    try {
        require_once __DIR__ . '/db.php';
        
        // Get all data from view
        $all_courses = fetchAllFromView(db(), 'cursos_site', 1000);
        
        if (empty($all_courses)) {
            return [];
        }
        
        $filtered_courses = $all_courses;
        
        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search_term = strtolower($filters['search']);
            $filtered_courses = array_filter($filtered_courses, function($course) use ($search_term) {
                return 
                    (isset($course['nome']) && strpos(strtolower($course['nome']), $search_term) !== false) ||
                    (isset($course['descricao_curta']) && strpos(strtolower($course['descricao_curta']), $search_term) !== false) ||
                    (isset($course['descricao_completa']) && strpos(strtolower($course['descricao_completa']), $search_term) !== false);
            });
        }
        
        // Apply category filter
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $category_id = $filters['category_id'];
            $filtered_courses = array_filter($filtered_courses, function($course) use ($category_id) {
                return isset($course['category_id']) && $course['category_id'] == $category_id;
            });
        }
        
        // Apply modality filter
        if (isset($filters['modality_id']) && !empty($filters['modality_id'])) {
            $modality_id = $filters['modality_id'];
            $filtered_courses = array_filter($filtered_courses, function($course) use ($modality_id) {
                return isset($course['modality_id']) && $course['modality_id'] == $modality_id;
            });
        }
        
        // Reindex array
        $filtered_courses = array_values($filtered_courses);
        
        // Apply pagination
        $total = count($filtered_courses);
        if ($limit !== null) {
            $filtered_courses = array_slice($filtered_courses, $offset, $limit);
        }
        
        return $filtered_courses;
        
    } catch (Throwable $e) {
        error_log('Error fetching courses from view: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get single course from view
 * 
 * @param string|int $identifier Course slug or ID
 * @param string $field Field to search by (slug or id)
 * @return array|false Course data or false
 */
function getCourseFromView($identifier, $field = 'slug')
{
    try {
        require_once __DIR__ . '/db.php';
        
        $all_courses = fetchAllFromView(db(), 'cursos_site', 1000);
        
        foreach ($all_courses as $course) {
            if (isset($course[$field]) && $course[$field] == $identifier) {
                return $course;
            }
        }
        
        return false;
        
    } catch (Throwable $e) {
        error_log('Error fetching course from view: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get total course count from view with filters
 * 
 * @param array $filters Same filters as getCoursesFromView()
 * @return int Total count
 */
function getCourseCountFromView($filters = [])
{
    try {
        require_once __DIR__ . '/db.php';
        
        $all_courses = fetchAllFromView(db(), 'cursos_site', 1000);
        
        if (empty($all_courses)) {
            return 0;
        }
        
        $filtered_courses = $all_courses;
        
        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search_term = strtolower($filters['search']);
            $filtered_courses = array_filter($filtered_courses, function($course) use ($search_term) {
                return 
                    (isset($course['nome']) && strpos(strtolower($course['nome']), $search_term) !== false) ||
                    (isset($course['descricao_curta']) && strpos(strtolower($course['descricao_curta']), $search_term) !== false);
            });
        }
        
        // Apply category filter
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $category_id = $filters['category_id'];
            $filtered_courses = array_filter($filtered_courses, function($course) use ($category_id) {
                return isset($course['category_id']) && $course['category_id'] == $category_id;
            });
        }
        
        // Apply modality filter
        if (isset($filters['modality_id']) && !empty($filters['modality_id'])) {
            $modality_id = $filters['modality_id'];
            $filtered_courses = array_filter($filtered_courses, function($course) use ($modality_id) {
                return isset($course['modality_id']) && $course['modality_id'] == $modality_id;
            });
        }
        
        return count($filtered_courses);
        
    } catch (Throwable $e) {
        error_log('Error counting courses from view: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get unique course categories from view
 * 
 * @return array Array of categories
 */
function getCourseCategoriesFromView()
{
    try {
        require_once __DIR__ . '/db.php';
        
        $all_courses = fetchAllFromView(db(), 'cursos_site', 1000);
        $categories = [];
        
        foreach ($all_courses as $course) {
            if (isset($course['category_id']) && isset($course['categoria_nome'])) {
                $cat_id = $course['category_id'];
                if (!isset($categories[$cat_id])) {
                    $categories[$cat_id] = [
                        'id' => $course['category_id'],
                        'nome' => $course['categoria_nome'] ?? 'Sem categoria',
                        'slug' => isset($course['categoria_slug']) ? $course['categoria_slug'] : sanitize($course['categoria_nome'] ?? ''),
                    ];
                }
            }
        }
        
        return array_values($categories);
        
    } catch (Throwable $e) {
        error_log('Error fetching categories from view: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get unique course modalities from view
 * 
 * @return array Array of modalities
 */
function getCourseModalitiesFromView()
{
    try {
        require_once __DIR__ . '/db.php';
        
        $all_courses = fetchAllFromView(db(), 'cursos_site', 1000);
        $modalities = [];
        
        foreach ($all_courses as $course) {
            if (isset($course['modality_id']) && isset($course['modalidade_nome'])) {
                $mod_id = $course['modality_id'];
                if (!isset($modalities[$mod_id])) {
                    $modalities[$mod_id] = [
                        'id' => $course['modality_id'],
                        'nome' => $course['modalidade_nome'] ?? 'Sem modalidade',
                        'slug' => isset($course['modalidade_slug']) ? $course['modalidade_slug'] : sanitize($course['modalidade_nome'] ?? ''),
                    ];
                }
            }
        }
        
        return array_values($modalities);
        
    } catch (Throwable $e) {
        error_log('Error fetching modalities from view: ' . $e->getMessage());
        return [];
    }
}
