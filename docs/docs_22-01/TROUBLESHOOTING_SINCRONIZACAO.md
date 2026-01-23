# 🔧 GUIA DE TROUBLESHOOTING - SINCRONIZAÇÃO

## Problemas Comuns e Soluções

---

## ❌ PROBLEMA 1: "View não encontrada"

### Sintoma
```
[ERRO] Linha X: SQLSTATE[42S02]: Table or view not found: 1146 'site.categorias_site'
```

### Causa
As views `categorias_site`, `modalidades_site` ou `curriculo_site` não existem no banco remoto.

### Solução

**Opção A:** Criar as views no banco remoto
```sql
-- Executar no banco remoto (site)
CREATE VIEW categorias_site AS
SELECT 
    id,
    nome,
    slug,
    descricao,
    ordem,
    ativo
FROM course_categories;

CREATE VIEW modalidades_site AS
SELECT 
    id,
    nome,
    slug,
    descricao,
    ativo
FROM course_modalities;

CREATE VIEW curriculo_site AS
SELECT 
    id,
    course_id,
    semestre,
    disciplina,
    carga_horaria,
    ementa,
    ordem
FROM course_curriculum;
```

**Opção B:** Desabilitar sincronização de componentes faltantes
```php
// Em sync_cron.php, comentar as linhas:
// $syncService->syncCategories();  // ← Comentar se view não existe
// $syncService->syncModalities();  // ← Comentar se view não existe
// $syncService->syncCurriculum();  // ← Comentar se view não existe
```

---

## ❌ PROBLEMA 2: "Erro de conexão remota"

### Sintoma
```
[ERRO] Erro de conexão: SQLSTATE[HY000]: General error: 2003 Can't connect to MySQL server
```

### Causa
Falha na conexão com o servidor remoto (143.0.121.152)

### Solução

1. **Verificar conectividade**
```bash
# Linux/Mac
ping 143.0.121.152

# Windows
ping 143.0.121.152
```

2. **Verificar credenciais** em `includes/db.php`
```php
$host = '143.0.121.152';  // Verifique o IP
$name = 'site';            // Verifique o banco
$user = 'site_faesma';     // Verifique user
$pass = 'YwsGps1rBusBmWvPrzj9';  // Verifique senha
```

3. **Verificar firewall**
- Porta 3306 aberta?
- Host remoto permite conexão?

4. **Modo offline** (sincronizar apenas banco local)
```php
// Em sync_cron.php, remover chamadas de sync remote
// Usar getCourses() ao invés de getCoursesFromView()
```

---

## ❌ PROBLEMA 3: "Nenhuma categoria encontrada"

### Sintoma
```
[AVISO] Nenhuma categoria encontrada na view remota
```

### Causa
A view existe, mas está vazia ou não retorna dados

### Solução

1. **Testar a view manualmente**
```sql
-- No banco remoto
SELECT * FROM categorias_site LIMIT 10;

-- Se vazio, inserir dados:
INSERT INTO course_categories (nome, slug, descricao, ativo)
VALUES ('Graduação', 'graduacao', 'Cursos de Graduação', 1);
```

2. **Verificar permissões SQL**
```sql
-- O usuário site_faesma tem SELECT na view?
GRANT SELECT ON site.* TO 'site_faesma'@'%';
FLUSH PRIVILEGES;
```

---

## ❌ PROBLEMA 4: "Course ID não encontrado"

### Sintoma
```
[Pulado] Disciplina "X" - Curso não encontrado no banco local
```

### Causa
O `course_id` remoto não tem correspondência no banco local.

### Solução

1. **Verificar se curso foi sincronizado**
```sql
SELECT id, cod_externo, nome FROM courses WHERE id = X;
```

2. **Sincronizar cursos antes do currículo**
```bash
# Executar na ordem correta:
1. php -r "syncCategories()"
2. php -r "syncModalities()"
3. php -r "syncCourses()"
4. php -r "syncCurriculum()"
```

3. **Mapear course_id manualmente**
```sql
-- Se o ID remoto não mapeia, editar manualmente:
UPDATE course_curriculum 
SET course_id = (SELECT id FROM courses WHERE cod_externo = 'X')
WHERE course_id = 0;
```

---

## ❌ PROBLEMA 5: "Duplicação de categorias/modalidades"

### Sintoma
Mesma categoria/modalidade aparece múltiplas vezes

### Causa
Slugs diferentes para mesmo nome, ou dados inconsistentes

### Solução

1. **Limpar duplicatas**
```sql
-- Remover categorias duplicadas
DELETE FROM course_categories 
WHERE id NOT IN (
    SELECT MIN(id) FROM course_categories 
    GROUP BY nome
);

-- Idem para modalidades
DELETE FROM course_modalities 
WHERE id NOT IN (
    SELECT MIN(id) FROM course_modalities 
    GROUP BY nome
);
```

2. **Sincronizar novamente com dados limpos**
```bash
php sync_test_complete.php
```

---

## ❌ PROBLEMA 6: "Memory exhausted"

### Sintoma
```
Fatal error: Allowed memory size of X bytes exhausted
```

### Causa
Muitos registros para sincronizar simultaneamente

### Solução

1. **Aumentar limite de memória** em `sync_cron.php`
```php
ini_set('memory_limit', '512M');  // Aumentar de padrão
```

2. **Sincronizar em lotes menores**
```php
$syncService->syncCategories('categorias_site', 50);   // Reduzir limit
$syncService->syncModalities('modalidades_site', 25);
$syncService->syncAllCourses('cursos_site', 100);
$syncService->syncCurriculum('curriculo_site', 200);
```

3. **Aumentar timeout**
```php
set_time_limit(600);  // 10 minutos ao invés de 5
```

---

## ❌ PROBLEMA 7: "Foreign Key Constraint Failed"

### Sintoma
```
SQLSTATE[HY000]: General error: 1452 Cannot add or update a child row
```

### Causa
Curso com `category_id` ou `modality_id` que não existe

### Solução

1. **Sincronizar categorias e modalidades ANTES dos cursos**
```bash
php sync_test_complete.php  # Respeita a ordem
```

2. **Desabilitar constraint temporariamente** (não recomendado)
```php
$localDb->exec('SET FOREIGN_KEY_CHECKS=0');
// ... sincronizar ...
$localDb->exec('SET FOREIGN_KEY_CHECKS=1');
```

3. **Verificar integridade**
```sql
-- Cursos com category_id inválido
SELECT * FROM courses WHERE category_id NOT IN (SELECT id FROM course_categories);

-- Cursos com modality_id inválido
SELECT * FROM courses WHERE modality_id NOT IN (SELECT id FROM course_modalities);
```

---

## ❌ PROBLEMA 8: "Timeout na sincronização"

### Sintoma
```
[ERRO] Script timeout após 300 segundos
```

### Causa
Muitos registros, queries lentas, ou servidor lento

### Solução

1. **Aumentar timeout**
```php
set_time_limit(900);  // 15 minutos
```

2. **Otimizar queries** (para DB admin)
```sql
-- Adicionar índices
ALTER TABLE course_curriculum ADD INDEX idx_course (course_id);
ALTER TABLE course_curriculum ADD INDEX idx_semestre (semestre);
ALTER TABLE courses ADD INDEX idx_cod_externo (cod_externo);
```

3. **Sincronizar em horário menos movimentado**
```bash
# Alterar cron para 3:00 AM ao invés de 2:00 AM
0 3 * * * /usr/bin/php /path/to/sync_cron.php
```

---

## ✅ DIAGNOSTICAR E TESTAR

### Script de Diagnóstico
```php
<?php
// diagnostico.php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/db.php';

echo "🔍 DIAGNÓSTICO DO SISTEMA\n";
echo str_repeat("─", 50) . "\n\n";

// 1. Conexão local
try {
    $local = Database::getInstance()->getConnection();
    echo "✅ Conexão local OK\n";
} catch (Exception $e) {
    echo "❌ Conexão local FALHOU: " . $e->getMessage() . "\n";
}

// 2. Conexão remota
try {
    $remote = db();
    $remote->query("SELECT 1")->fetch();
    echo "✅ Conexão remota OK\n";
} catch (Exception $e) {
    echo "❌ Conexão remota FALHOU: " . $e->getMessage() . "\n";
}

// 3. Tabelas locais
$tables = ['course_categories', 'course_modalities', 'courses', 'course_curriculum'];
foreach ($tables as $table) {
    try {
        $stmt = $local->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ Tabela $table: $count registros\n";
    } catch (Exception $e) {
        echo "❌ Tabela $table NÃO ENCONTRADA\n";
    }
}

// 4. Views remotas
$views = ['categorias_site', 'modalidades_site', 'curriculo_site', 'cursos_site'];
foreach ($views as $view) {
    try {
        $stmt = $remote->query("SELECT COUNT(*) FROM $view");
        $count = $stmt->fetchColumn();
        echo "✅ View $view: $count registros\n";
    } catch (Exception $e) {
        echo "⚠️ View $view NÃO ENCONTRADA\n";
    }
}
?>
```

---

## 📞 QUANDO PEDIR AJUDA

Se o problema persistir, forneça:

1. **Log completo** de `logs/sync_YYYY-MM-DD.log`
2. **Output do diagnóstico** acima
3. **Versão do PHP** (`php -v`)
4. **Versão do MySQL** (`mysql --version`)
5. **Erro exato** da mensagem
6. **Quando começou** a falhar

---

## ✨ DICAS DE PERFORMANCE

- ✅ Sincronizar à noite (horário de baixo uso)
- ✅ Usar índices no banco remoto
- ✅ Limpar logs antigos periodicamente
- ✅ Monitorar o espaço em disco
- ✅ Fazer backup antes de sincronizações grandes

---

## 📝 LOGS

Consultar logs de sincronização:

```bash
# Ver último log
cat logs/sync_$(date +%Y-%m-%d).log

# Seguir log em tempo real
tail -f logs/sync_$(date +%Y-%m-%d).log

# Ver todos os erros
grep ERROR logs/sync_*.log
```

