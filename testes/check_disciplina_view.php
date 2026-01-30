<?php
/**
 * Script para verificar estrutura da view disciplinas_curso_site
 */

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db();
    
    echo "=== Estrutura da view disciplinas_curso_site ===\n\n";
    
    $desc = $pdo->query("DESCRIBE disciplinas_curso_site")->fetchAll();
    printf("%-25s | %-20s | %-6s\n", "Campo", "Tipo", "Nulo");
    echo str_repeat("-", 60) . "\n";
    foreach ($desc as $col) {
        printf("%-25s | %-20s | %-6s\n", $col['Field'], $col['Type'], $col['Null']);
    }
    
    echo "\n\n=== Dados únicos (id, cod_externo, curso_nome) ===\n\n";
    
    $sql = "SELECT DISTINCT id, cod_externo, curso_nome FROM disciplinas_curso_site ORDER BY id";
    $rows = $pdo->query($sql)->fetchAll();
    
    printf("%-10s | %-15s | %-50s\n", "ID", "cod_externo", "curso_nome");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($rows as $row) {
        printf("%-10s | %-15s | %-50s\n", 
            $row['id'] ?? 'NULL',
            $row['cod_externo'] ?? 'NULL',
            substr($row['curso_nome'] ?? '', 0, 50)
        );
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
