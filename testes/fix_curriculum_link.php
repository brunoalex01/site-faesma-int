<?php
define('FAESMA_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance()->getConnection();

echo "=== Corrigindo vínculo das disciplinas ===\n";

// Atualizar course_id de 13 para 12
$stmt = $db->prepare('UPDATE course_curriculum SET course_id = 12 WHERE course_id = 13');
$stmt->execute();
$affected = $stmt->rowCount();

echo "Disciplinas atualizadas: $affected\n";

// Verificar resultado
echo "\n=== Verificando ===\n";
$stmt = $db->query('SELECT cc.course_id, c.nome as curso_nome, COUNT(*) as total FROM course_curriculum cc JOIN courses c ON cc.course_id = c.id GROUP BY cc.course_id, c.nome');
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    echo "Curso ID {$row['course_id']} ({$row['curso_nome']}): {$row['total']} disciplinas\n";
}

echo "\n=== Detalhes do Curso ID 12 ===\n";
$stmt = $db->query('SELECT id, nome, slug FROM courses WHERE id = 12');
$curso = $stmt->fetch();
if ($curso) {
    echo "ID: {$curso['id']}\n";
    echo "Nome: {$curso['nome']}\n";
    echo "Slug: {$curso['slug']}\n";
}
