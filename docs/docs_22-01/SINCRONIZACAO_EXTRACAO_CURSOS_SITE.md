# Sincronização Completa - Extração de cursos_site

## 📋 Resumo Executivo

Sistema de sincronização que extrai dados da view remota **`site.cursos_site`** e os sincroniza para o banco de dados local **`faesma_db`** em três tabelas principais:

- **`courses`** - Cursos principais
- **`course_categories`** - Categorias (extraídas de campos categoria_* da view)
- **`course_modalities`** - Modalidades (extraídas de campos modalidade_* da view)

> **NOTA:** Dados de currículo/disciplinas não estão presentes na view `cursos_site`. Se necessário, uma view adicional será requerida.

## 🏗️ Arquitetura

### Fluxo de Sincronização

```
┌─────────────────────────────────┐
│ View Remota: cursos_site        │
│ Servidor: 143.0.121.152         │
│ Database: site                  │
│ Usuário: site_faesma            │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ RemoteSyncService.php           │
│ ┌─────────────────────────────┐ │
│ │ syncCategories()            │ │
│ │ - Extrai categoria_nome     │ │
│ │ - Deduplica por slug        │ │
│ │ - INSERT/UPDATE local       │ │
│ └─────────────────────────────┘ │
│ ┌─────────────────────────────┐ │
│ │ syncModalities()            │ │
│ │ - Extrai modalidade_nome    │ │
│ │ - Deduplica por slug        │ │
│ │ - INSERT/UPDATE local       │ │
│ └─────────────────────────────┘ │
│ ┌─────────────────────────────┐ │
│ │ syncAllCourses()            │ │
│ │ - Sincroniza courses        │ │
│ │ - Relaciona com categories  │ │
│ └─────────────────────────────┘ │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ Banco Local: faesma_db          │
│ ┌──────────────────────────────┐│
│ │ course_categories            ││
│ │ - categoria_nome (extraído)  ││
│ │ - categoria_slug (gerado)    ││
│ │ - categoria_descricao        ││
│ └──────────────────────────────┘│
│ ┌──────────────────────────────┐│
│ │ course_modalities            ││
│ │ - modalidade_nome (extraído) ││
│ │ - modalidade_slug (gerado)   ││
│ │ - modalidade_descricao       ││
│ └──────────────────────────────┘│
│ ┌──────────────────────────────┐│
│ │ courses                       ││
│ │ - nome, cod_externo, descr... ││
│ │ - category_id (FK)           ││
│ │ - modality_id (FK)           ││
│ └──────────────────────────────┘│
└─────────────────────────────────┘
```

## 🔍 Detalhes de Extração

### 1. Sincronização de Categorias

**Onde extrair:** Campo `categoria_nome` de cada curso em `cursos_site`

**Campos esperados na view:**
```
- categoria_nome (string) - Nome da categoria
- categoria_slug (string, opcional) - Slug da categoria
- categoria_descricao (string, opcional) - Descrição
- categoria_ordem (int, opcional) - Ordem de exibição
```

**Processo:**
1. Buscar todos os cursos de `cursos_site`
2. Agrupar por `categoria_nome` (para deduplica)
3. Para cada categoria única:
   - Gerar slug a partir de `categoria_nome` se não existir
   - Buscar categoria existente no banco local (por slug, depois por nome)
   - Se existe: UPDATE
   - Se não existe: INSERT
4. Registrar resultados (criadas, atualizadas, erros)

**Código de Extração:**
```php
$categories = [];
foreach ($remoteCourses as $course) {
    if (!empty($course['categoria_nome']) && !isset($categories[$course['categoria_nome']])) {
        $categories[$course['categoria_nome']] = [
            'nome' => $course['categoria_nome'],
            'slug' => $course['categoria_slug'] ?? null,
            'descricao' => $course['categoria_descricao'] ?? null,
            'ordem' => $course['categoria_ordem'] ?? 0,
        ];
    }
}
```

### 2. Sincronização de Modalidades

**Onde extrair:** Campo `modalidade_nome` de cada curso em `cursos_site`

**Campos esperados na view:**
```
- modalidade_nome (string) - Nome da modalidade
- modalidade_slug (string, opcional) - Slug
- modalidade_descricao (string, opcional) - Descrição
```

**Processo:**
1. Buscar todos os cursos de `cursos_site`
2. Agrupar por `modalidade_nome` (para deduplica)
3. Para cada modalidade única:
   - Gerar slug a partir de `modalidade_nome` se não existir
   - Buscar modalidade existente (por slug, depois por nome)
   - Se existe: UPDATE
   - Se não existe: INSERT
4. Registrar resultados

**Código de Extração:**
```php
$modalities = [];
foreach ($remoteCourses as $course) {
    if (!empty($course['modalidade_nome']) && !isset($modalities[$course['modalidade_nome']])) {
        $modalities[$course['modalidade_nome']] = [
            'nome' => $course['modalidade_nome'],
            'slug' => $course['modalidade_slug'] ?? null,
            'descricao' => $course['modalidade_descricao'] ?? null,
        ];
    }
}
```

### 3. Sincronização de Cursos

**Onde extrair:** Todos os registros de `cursos_site`

**Campos mapeados:**
```
Remote → Local
nome → nome
descricao → descricao
cod_externo → cod_externo
categoria_nome → category_id (busca por categoria)
modalidade_nome → modality_id (busca por modalidade)
ativo → ativo
```

## 🛠️ Uso

### Opção 1: Sincronização Manual (Teste)

```bash
# No diretório do projeto
php sync_test_validacao.php
```

**Saída esperada:**
- Status de cada sincronização (categorias → modalidades → cursos)
- Contagem de registros criados/atualizados
- Lista de amostra de dados sincronizados
- Verificação de duplicatas e integridade

### Opção 2: Sincronização Automática (Cron)

**Linux/Mac:**
```bash
# Editar crontab
crontab -e

# Adicionar (sincroniza diariamente às 2:00 AM)
0 2 * * * /usr/bin/php /path/to/projeto5/scripts/sync_cron.php
```

**Windows (Task Scheduler):**
1. Abrir Task Scheduler
2. Criar Nova Tarefa
3. Nome: `FAESMA Sync`
4. Acionador: Diariamente às 02:00
5. Ação: Executar programa
   - Programa: `C:\xampp\php\php.exe`
   - Argumentos: `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`

### Opção 3: Sincronização via Web (Desenvolvimento)

```php
<?php
require_once 'config/config.php';
require_once 'includes/RemoteSyncService.php';
require_once 'includes/Database.php';
require_once 'includes/db.php';

$localDb = Database::getInstance()->getConnection();
$remoteDb = db();
$syncService = new RemoteSyncService($localDb, $remoteDb);

// Sincronizar tudo
$result1 = $syncService->syncCategories();
$result2 = $syncService->syncModalities();
$result3 = $syncService->syncAllCourses();

echo json_encode([
    'categories' => $result1,
    'modalities' => $result2,
    'courses' => $result3,
], JSON_PRETTY_PRINT);
?>
```

## 📊 Estrutura do Banco de Dados Local

### Tabela: course_categories
```sql
CREATE TABLE course_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    descricao TEXT,
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Deduplicação:** Por `slug` (primeiro), depois por `nome`

### Tabela: course_modalities
```sql
CREATE TABLE course_modalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    descricao TEXT,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Deduplicação:** Por `slug` (primeiro), depois por `nome`

### Tabela: courses
```sql
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cod_externo VARCHAR(50) UNIQUE,
    descricao TEXT,
    category_id INT,
    modality_id INT,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES course_categories(id),
    FOREIGN KEY (modality_id) REFERENCES course_modalities(id)
);
```

**Deduplicação:** Por `cod_externo`

## 🔐 Dados de Conexão Remota

**Servidor:** 143.0.121.152  
**Database:** `site`  
**Usuário:** `site_faesma`  
**Senha:** `YwsGps1rBusBmWvPrzj9`  
**View:** `site.cursos_site`

## 📝 Logs

Todos os logs são salvos em `logs/sync_YYYY-MM-DD.log`

**Exemplo de log:**
```
[2024-01-15 02:00:00] [INFO] === INICIANDO SINCRONIZAÇÃO AUTOMÁTICA ===
[2024-01-15 02:00:01] [INFO] Conectando à view remota...
[2024-01-15 02:00:02] [INFO] Iniciando sincronização de categorias...
[2024-01-15 02:00:05] [SUCCESS] Categorias criadas: 5
[2024-01-15 02:00:05] [SUCCESS] Categorias atualizadas: 2
[2024-01-15 02:00:06] [INFO] Iniciando sincronização de modalidades...
[2024-01-15 02:00:08] [SUCCESS] Modalidades criadas: 3
[2024-01-15 02:00:30] [SUCCESS] Cursos criados: 42
[2024-01-15 02:00:30] [SUCCESS] ✅ SINCRONIZAÇÃO CONCLUÍDA
```

## 🐛 Troubleshooting

### Problema: Categorias não sendo sincronizadas

**Verificação:**
1. Confirmar que `categoria_nome` existe em `cursos_site`
2. Verificar se há dados nesse campo: `SELECT DISTINCT categoria_nome FROM site.cursos_site LIMIT 5`
3. Verificar logs: `tail -f logs/sync_*.log`

### Problema: Duplicatas de categorias

**Causa:** Campo `slug` não está sendo gerado corretamente

**Solução:**
```php
// Verificar slugs nulos
SELECT id, nome, slug FROM course_categories WHERE slug IS NULL;

// Atualizar slugs nulos
UPDATE course_categories SET slug = LOWER(REPLACE(nome, ' ', '-')) WHERE slug IS NULL;
```

### Problema: Relacionamento category_id nulo

**Verificação:**
```sql
SELECT COUNT(*) FROM courses WHERE category_id IS NULL;
SELECT COUNT(*) FROM course_categories;
```

**Causa:** Categoria não foi sincronizada antes do curso

**Solução:** Executar sincronização novamente (ordem correta é importante)

## ✅ Verificação de Sucesso

```php
// Conectar ao banco local
$db = new PDO('mysql:host=localhost;dbname=faesma_db', 'root', '');

// Verificar categorias
$result = $db->query("SELECT COUNT(*) as total FROM course_categories")->fetch();
echo "Categorias: " . $result['total']; // Deve ser > 0

// Verificar modalidades  
$result = $db->query("SELECT COUNT(*) as total FROM course_modalities")->fetch();
echo "Modalidades: " . $result['total']; // Deve ser > 0

// Verificar cursos
$result = $db->query("SELECT COUNT(*) as total FROM courses")->fetch();
echo "Cursos: " . $result['total']; // Deve ser > 0

// Verificar integridade
$result = $db->query("
    SELECT COUNT(*) as total FROM courses 
    WHERE category_id IS NULL
")->fetch();
echo "Cursos sem categoria: " . $result['total']; // Deve ser 0 ou próximo a 0
```

## 📚 Campos Mapeados

### De `site.cursos_site` para Banco Local

| Descrição | Campo Remoto | Tabela Local | Campo Local | Regra |
|-----------|--------------|--------------|-------------|-------|
| Nome da categoria | categoria_nome | course_categories | nome | Extraído, agrupado |
| Slug da categoria | categoria_slug | course_categories | slug | Gerado se vazio |
| Descrição da categoria | categoria_descricao | course_categories | descricao | Opcional |
| Nome da modalidade | modalidade_nome | course_modalities | nome | Extraído, agrupado |
| Slug da modalidade | modalidade_slug | course_modalities | slug | Gerado se vazio |
| Descrição da modalidade | modalidade_descricao | course_modalities | descricao | Opcional |
| Nome do curso | nome | courses | nome | 1:1 |
| Código externo | cod_externo | courses | cod_externo | 1:1 |
| Descrição do curso | descricao | courses | descricao | 1:1 |
| Status do curso | ativo | courses | ativo | 1:1 |

## 🎯 Próximos Passos

1. ✅ Executar `php sync_test_validacao.php` para validar sincronização
2. ✅ Verificar se categorias e modalidades foram criadas
3. ✅ Verificar integridade dos relacionamentos
4. ✅ Configurar cron para sincronização automática
5. ⏳ (Futuro) Adicionar sincronização de currículo se dados disponível em view adicional

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs em `logs/`
2. Execute `php sync_test_validacao.php` com output completo
3. Verifique conexão remota: `php -r "require 'includes/db.php'; echo 'OK';"`
4. Verifique banco local: `php -r "require 'includes/Database.php'; echo 'OK';"`
