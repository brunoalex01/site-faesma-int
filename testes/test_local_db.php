<?php
define('FAESMA_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

echo "=== TEST: Verificando dados no banco local ===\n\n";

// Testar getCourses
$courses = getCourses([], 5);
echo "getCourses() - Encontrados: " . count($courses) . " cursos\n";
if (!empty($courses)) {
    echo "Primeiro curso: " . $courses[0]['nome'] . "\n";
}

echo "\n";

// Testar getCourseCount
$total = getCourseCount();
echo "getCourseCount() - Total: " . $total . " cursos\n";

echo "\n";

// Testar getCourseCategories
$categories = getCourseCategories();
echo "getCourseCategories() - Encontradas: " . count($categories) . " categorias\n";
if (!empty($categories)) {
    echo "Primeira categoria: " . $categories[0]['nome'] . "\n";
}

echo "\n";

// Testar getCourseModalities
$modalities = getCourseModalities();
echo "getCourseModalities() - Encontradas: " . count($modalities) . " modalidades\n";
if (!empty($modalities)) {
    echo "Primeira modalidade: " . $modalities[0]['nome'] . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?>
