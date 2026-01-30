<?php
/**
 * Script para verificar campos disponíveis na view cursos_site
 */

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db();
    
    echo "=== Campos da view cursos_site ===\n\n";
    
    // Buscar 1 registro para ver os campos
    $stmt = $pdo->query('SELECT * FROM cursos_site LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "Campos disponíveis:\n";
        foreach ($row as $field => $value) {
            $tipo = gettype($value);
            $valor = is_null($value) ? 'NULL' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value);
            echo "  - {$field}: {$valor} ({$tipo})\n";
        }
        
        // Verificar especificamente NR_GRAU
        echo "\n=== Verificar campo NR_GRAU ===\n";
        $stmt2 = $pdo->query('SELECT DISTINCT NR_GRAU FROM cursos_site');
        $graus = $stmt2->fetchAll(PDO::FETCH_COLUMN);
        echo "Valores únicos de NR_GRAU: " . implode(', ', $graus) . "\n";
        
        // Verificar inscricao_online
        echo "\n=== Verificar campo inscricao_online ===\n";
        $stmt3 = $pdo->query('SELECT DISTINCT inscricao_online FROM cursos_site');
        $inscricoes = $stmt3->fetchAll(PDO::FETCH_COLUMN);
        echo "Valores únicos de inscricao_online: " . implode(', ', array_map(function($v) { return $v ?? 'NULL'; }, $inscricoes)) . "\n";
        
    } else {
        echo "Nenhum registro encontrado na view.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
