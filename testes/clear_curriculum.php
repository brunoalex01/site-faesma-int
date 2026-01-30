<?php
/**
 * Limpa e re-sincroniza disciplinas com todos os campos
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

$pdo = Database::getInstance()->getConnection();

echo "=== Limpando disciplinas para re-sincronização ===\n\n";

// Remover todas as disciplinas para re-sincronizar com todos os campos
$count = $pdo->exec('DELETE FROM course_curriculum');
echo "Disciplinas removidas: {$count}\n";

echo "\nAgora execute test_curriculum_sync.php para sincronizar novamente.\n";
