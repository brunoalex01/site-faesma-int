<?php
/**
 * Test script to debug curso-detalhes.php issue
 */

define('FAESMA_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

echo "=== DEBUG: BUSCA DE CURSOS ===\n\n";

// Test 1: Show what's in the database
echo "1. Todos os cursos no banco:\n";
global $db;
$db = Database::getInstance();
$courses = $db->fetchAll('SELECT id, nome, slug FROM courses LIMIT 10');
foreach ($courses as $c) {
    echo "  - ID: {$c['id']}, Nome: {$c['nome']}, Slug: {$c['slug']}\n";
}

// Test 2: Decode the URL parameter
$encoded_param = 'Alfabetiza%C3%A7%C3%A3o%20e%20Letramento';
$decoded_param = urldecode($encoded_param);
echo "\n2. Parâmetro da URL:\n";
echo "  - Encoded: $encoded_param\n";
echo "  - Decoded: $decoded_param\n";

// Test 3: Try to find with the decoded name
echo "\n3. Buscando por nome (LIKE):\n";
$course = $db->fetchOne('SELECT id, nome, slug FROM courses WHERE nome LIKE ?', ["%$decoded_param%"]);
if ($course) {
    echo "  - Encontrado: Nome='{$course['nome']}', Slug='{$course['slug']}'\n";
} else {
    echo "  - NÃO ENCONTRADO com LIKE\n";
}

// Test 4: Try getCourse function with decoded name
echo "\n4. Tentando com função getCourse (slug):\n";
$slug_candidate = 'alfabetizacao-e-letramento';
$course = getCourse($slug_candidate, 'slug');
if ($course) {
    echo "  - Encontrado: {$course['nome']}\n";
} else {
    echo "  - NÃO ENCONTRADO com slug '$slug_candidate'\n";
}

// Test 5: Show what sanitize() does
echo "\n5. Função sanitize():\n";
$sanitized = sanitize($decoded_param);
echo "  - Original: $decoded_param\n";
echo "  - Sanitized: $sanitized\n";
$course = getCourse($sanitized, 'slug');
if ($course) {
    echo "  - Encontrado com sanitize: {$course['nome']}\n";
} else {
    echo "  - NÃO ENCONTRADO com sanitized value\n";
}

// Test 6: Try searching by name field
echo "\n6. Buscando função getCourse com campo 'nome':\n";
$course = getCourse($decoded_param, 'nome');
if ($course) {
    echo "  - Encontrado: ID={$course['id']}, Nome={$course['nome']}\n";
} else {
    echo "  - NÃO ENCONTRADO com nome '$decoded_param'\n";
}
?>
