# 🔄 INTEGRAÇÃO COMPLETA DE SINCRONIZAÇÃO

## Data: 22 de janeiro de 2026

---

## 📋 RESUMO DAS MUDANÇAS

O sistema de sincronização foi expandido para sincronizar **não apenas cursos**, mas também:
- ✅ **Categorias de cursos** (course_categories)
- ✅ **Modalidades de cursos** (course_modalities)
- ✅ **Currículo dos cursos** (course_curriculum)

---

## 🔧 IMPLEMENTAÇÃO TÉCNICA

### Novos Métodos no RemoteSyncService

#### 1. Sincronização de Categorias
```php
syncCategories(string $viewName = 'categorias_site', int $limit = 200)
```

**Funcionalidades:**
- Sincroniza categorias da view remota `categorias_site`
- Cria novas categorias no banco local
- Atualiza categorias existentes
- Mapeia campos: nome, slug, descrição, ordem, ativo

**Busca por:**
- Slug (prioritário)
- Nome (fallback)

---

#### 2. Sincronização de Modalidades
```php
syncModalities(string $viewName = 'modalidades_site', int $limit = 100)
```

**Funcionalidades:**
- Sincroniza modalidades da view remota `modalidades_site`
- Cria novas modalidades no banco local
- Atualiza modalidades existentes
- Mapeia campos: nome, slug, descrição, ativo

**Busca por:**
- Slug (prioritário)
- Nome (fallback)

---

#### 3. Sincronização de Currículo
```php
syncCurriculum(string $viewName = 'curriculo_site', int $limit = 500)
```

**Funcionalidades:**
- Sincroniza disciplinas/currículo da view remota `curriculo_site`
- Vincula currículo ao curso correto via `course_id`
- Cria novas disciplinas
- Atualiza disciplinas existentes
- Mapeia campos: course_id, semestre, disciplina, carga_horaria, ementa, ordem

**Busca por:**
- Course ID + Disciplina + Semestre (composto)

**Tratamento de Course IDs:**
- Suporta tanto IDs locais quanto códigos externos (cod_externo)
- Realiza lookup automático de IDs remotos para IDs locais

---

## 📁 ESTRUTURA DE VIEWS REMOTAS

Esperadas as seguintes views no banco remoto (`site`):

```sql
-- Categorias
cursos_site.categoria_id (INT)
cursos_site.categoria_nome (VARCHAR)
cursos_site.categoria_slug (VARCHAR)

-- Modalidades
cursos_site.modality_id (INT)
cursos_site.modalidade_nome (VARCHAR)
cursos_site.modalidade_slug (VARCHAR)

-- Views Específicas (opcional, para sincronização direta)
categorias_site (id, nome, slug, descricao, ordem, ativo)
modalidades_site (id, nome, slug, descricao, ativo)
curriculo_site (id, course_id, semestre, disciplina, carga_horaria, ementa, ordem)
```

---

## 🔄 FLUXO DE SINCRONIZAÇÃO COMPLETO

```
┌─────────────────────────────────┐
│   Banco Remoto (site)           │
├─────────────────────────────────┤
│ • categorias_site               │
│ • modalidades_site              │
│ • cursos_site                   │
│ • curriculo_site                │
└────────────┬────────────────────┘
             │
             ↓ fetchAllFromView()
     
┌────────────────────────────────────────┐
│  RemoteSyncService                     │
├────────────────────────────────────────┤
│ 1. syncCategories()                   │
│ 2. syncModalities()                   │
│ 3. syncAllCourses()                   │
│ 4. syncCurriculum()                   │
└────────────┬─────────────────────────┘
             │
             ↓ Validação + Mapeamento
     
┌────────────────────────────────────────┐
│  Banco Local (faesma_db)               │
├────────────────────────────────────────┤
│ • course_categories                   │
│ • course_modalities                   │
│ • courses                             │
│ • course_curriculum                   │
└────────────────────────────────────────┘
```

---

## 📊 EXEMPLO DE SINCRONIZAÇÃO

### Antes

**Banco Remoto:**
```
Categoria ID: 1, Nome: "Graduação", Slug: "graduacao"
Modalidade ID: 1, Nome: "Presencial", Slug: "presencial"
Curso: "Administração", category_id: 1, modality_id: 1
Disciplina: "Administração I", course_id: 50, semestre: 1
```

**Banco Local:**
```
course_categories: vazio
course_modalities: vazio
courses: vazio
course_curriculum: vazio
```

### Depois

**Banco Local:**
```
course_categories:
  ID: 1, Nome: "Graduação", Slug: "graduacao"

course_modalities:
  ID: 1, Nome: "Presencial", Slug: "presencial"

courses:
  ID: 1, Nome: "Administração", category_id: 1, modality_id: 1, ...

course_curriculum:
  ID: 1, course_id: 1, semestre: 1, disciplina: "Administração I", ...
```

---

## 🚀 COMO USAR

### Execução Manual (CLI)

```bash
# Sincronizar tudo (categorias, modalidades, cursos, currículo)
php sync_test_complete.php

# Apenas via cron (automático)
php scripts/sync_cron.php
```

### Agendamento (Cron Job)

**Linux/Mac:**
```bash
0 2 * * * /usr/bin/php /path/to/projeto5/scripts/sync_cron.php >> /path/to/projeto5/logs/cron.log 2>&1
```

**Windows (Task Scheduler):**
```
Program: C:\xampp\php\php.exe
Arguments: C:\xampp\htdocs\projeto5\scripts\sync_cron.php
Schedule: Daily at 2:00 AM
```

---

## 📝 MODIFICAÇÕES NOS ARQUIVOS

### 1. `includes/RemoteSyncService.php`
- ✅ Adicionado método `syncCategories()`
- ✅ Adicionado método `syncModalities()`
- ✅ Adicionado método `syncCurriculum()`
- ✅ Adicionados métodos privados de suporte

### 2. `scripts/sync_cron.php`
- ✅ Atualizado para sincronizar categorias
- ✅ Atualizado para sincronizar modalidades
- ✅ Atualizado para sincronizar currículo
- ✅ Melhorado log de execução

### 3. `sync_test_complete.php` (novo)
- ✅ Script de teste da sincronização completa
- ✅ Relatório detalhado em CLI
- ✅ Validação de todas as etapas

---

## ✨ CARACTERÍSTICAS

### Validação Automática
- Verifica campos obrigatórios
- Valida tipos de dados
- Tratamento de valores NULL
- Sanitização de entrada

### Tratamento de Duplicação
- Busca por slug (preferencial)
- Busca por nome (fallback)
- Busca por ID externo
- Evita duplicação de dados

### Logging Detalhado
- Log de cada operação
- Registros de sucesso/erro
- Rastreabilidade completa
- Arquivo por data

### Performance
- Sincronização em lote
- Queries otimizadas
- Índices de banco de dados
- Timeout configurável (5 minutos)

---

## 🔍 ESTRUTURA DO BANCO LOCAL

### Tabela: course_categories
```sql
CREATE TABLE course_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_ativo (ativo)
);
```

### Tabela: course_modalities
```sql
CREATE TABLE course_modalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_ativo (ativo)
);
```

### Tabela: course_curriculum
```sql
CREATE TABLE course_curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    semestre INT,
    disciplina VARCHAR(200) NOT NULL,
    carga_horaria INT,
    ementa TEXT,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    INDEX idx_course (course_id),
    INDEX idx_semestre (semestre)
);
```

---

## 📊 ESTATÍSTICAS PÓS-SINCRONIZAÇÃO

O script `sync_test_complete.php` fornece:

```
📊 RESUMO GERAL DA SINCRONIZAÇÃO
════════════════════════════════════════════════════════════
✓ Registros Criados: X
✓ Registros Atualizados: Y
⚠ Registros com Erro: Z (se houver)
════════════════════════════════════════════════════════════
```

---

## ⚠️ NOTAS IMPORTANTES

1. **Views Remotas**: As views `categorias_site`, `modalidades_site` e `curriculo_site` devem ser criadas no banco remoto
2. **Ordem de Sincronização**: Categorias e modalidades devem ser sincronizadas **antes** dos cursos
3. **Currículo**: Deve ser sincronizado **após** os cursos (pois depende do course_id local)
4. **Integridade Referencial**: O banco local mantém chaves estrangeiras para integridade
5. **Índices**: Todos os campos críticos têm índices para performance

---

## 🔄 ROLLBACK

Se precisar desabilitar a sincronização, simplesmente comente as chamadas em `scripts/sync_cron.php`:

```php
// Comentar as linhas:
// $syncService->syncCategories();
// $syncService->syncModalities();
// $syncService->syncCurriculum();
```

---

## ✅ STATUS: IMPLEMENTAÇÃO CONCLUÍDA

A sincronização completa está funcional e pronta para produção.

