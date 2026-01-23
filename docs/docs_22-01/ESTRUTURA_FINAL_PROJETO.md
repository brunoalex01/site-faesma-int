# 📁 Estrutura Final do Projeto - FAESMA Sincronização v2.0

## Árvore de Diretórios Completa

```
projeto5/
│
├── 📄 Documentação Principal
│   ├── README_FINAL.txt
│   ├── LEIA_ME_PRIMEIRO.txt
│   ├── 00_COMECE_AQUI.txt
│   └── INDEX.md
│
├── 📄 Documentação de Sincronização (NOVO)
│   ├── SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md ⭐
│   │   └── Guia completo de uso da sincronização
│   │       Inclui arquitetura, fluxo, campos mapeados, troubleshooting
│   │
│   ├── RESUMO_TECNICO_SINCRONIZACAO_V2.md ⭐
│   │   └── Resumo técnico das mudanças
│   │       Arquivos modificados, detalhes de implementação, exemplos
│   │
│   └── CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md ⭐
│       └── Checklist passo-a-passo para validação
│           Testes, configuração de cron, monitoramento
│
├── 📂 config/
│   ├── config.php
│   │   └── Configurações gerais do projeto
│   │       - Conexão BD local (faesma_db)
│   │       - Timezone
│   │       - Variáveis de ambiente
│   │
│   └── [esquema do BD em database/]
│
├── 📂 database/
│   ├── schema.sql
│   │   └── Estrutura completa das tabelas
│   │       - courses
│   │       - course_categories
│   │       - course_modalities
│   │       - course_curriculum (se implementado)
│   │
│   └── seeds.sql
│       └── Dados iniciais (se necessário)
│
├── 📂 includes/
│   ├── Database.php
│   │   └── Classe Singleton para BD local
│   │
│   ├── db.php
│   │   └── Função db() para conexão remota
│   │       - Servidor: 143.0.121.152
│   │       - Database: site
│   │       - User: site_faesma
│   │
│   ├── RemoteSyncService.php ⭐ MODIFICADO V2.0
│   │   └── Serviço principal de sincronização
│   │       Métodos públicos:
│   │       - syncCategories() - Extrai de cursos_site
│   │       - syncModalities() - Extrai de cursos_site
│   │       - syncAllCourses() - Sincroniza cursos
│   │       - syncCurriculum() - Stub (não disponível)
│   │
│   │       Métodos privados:
│   │       - syncCategory($data) - INSERT/UPDATE categoria
│   │       - syncModality($data) - INSERT/UPDATE modalidade
│   │       - syncCurriculumItem($data) - INSERT/UPDATE disciplina
│   │       - findExistingCourse($cod_externo)
│   │       - createCourse($data) / updateCourse($data)
│   │       - sanitizeSlug($text)
│   │
│   ├── RemoteSyncMapping.php
│   │   └── Mapeamento de campos remote → local
│   │
│   ├── functions.php
│   │   └── Funções auxiliares
│   │       - getCourses() - Busca do banco local
│   │       - getCourseCategories() - Busca do banco local
│   │       - getCourseModalities() - Busca do banco local
│   │       - Etc.
│   │
│   ├── header.php
│   │   └── Template de cabeçalho
│   │
│   ├── footer.php
│   │   └── Template de rodapé
│   │
│   ├── AdminAuth.php
│   │   └── Autenticação de administrador
│   │
│   └── [outras classes]
│
├── 📂 scripts/
│   ├── sync_cron.php ⭐ MODIFICADO V2.0
│   │   └── Script para cron automático
│   │       Executa em ordem:
│   │       1. syncCategories()
│   │       2. syncModalities()
│   │       3. syncAllCourses()
│   │       4. syncCurriculum() [aviso]
│   │
│   │       Configuração:
│   │       - Linux: 0 2 * * * php /path/sync_cron.php
│   │       - Windows: Task Scheduler, 02:00 diariamente
│   │
│   └── [outros scripts]
│
├── 📂 logs/ 📝 NOVO
│   └── sync_YYYY-MM-DD.log
│       └── Logs de sincronização por data
│           Exemplo: sync_2024-01-15.log
│           Contém: INFO, SUCCESS, WARNING, ERROR
│
├── 📂 assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── js/
│   │   └── main.js
│   │
│   └── img/
│
├── 📂 admin/
│   ├── index.php
│   ├── login.php
│   └── test.php
│
├── 📂 api/
│   └── courses.php
│
├── 📂 docs/
│   ├── TECHNICAL_DOCUMENTATION.md
│   ├── DATABASE_SCHEMA_DOC.md
│   ├── ERP_INTEGRATION_GUIDE.md
│   ├── DEPLOYMENT_GUIDE.md
│   ├── MAINTENANCE_GUIDE.md
│   ├── SYNC_ARCHITECTURE.md
│   └── [mais documentação]
│
├── 🔄 Páginas Públicas (Consomem BD Local)
│   ├── index.php
│   │   └── Página inicial
│   │       Usa: getCourses() ✅ (banco local)
│   │
│   ├── cursos.php ⭐ MODIFICADO
│   │   └── Listagem de cursos com filtros
│   │       Antes: getCoursesFromView() ❌
│   │       Depois: getCourses() ✅ (banco local)
│   │
│   ├── curso-detalhes.php
│   │   └── Detalhes de um curso
│   │       Usa: getCourse() ✅ (banco local)
│   │
│   ├── categorias.php
│   │   └── (se existir) Listagem de categorias
│   │
│   ├── vestibular.php
│   ├── sobre.php
│   ├── privacidade.php
│   ├── termos.php
│   ├── contato.php
│   └── [outras páginas públicas]
│
├── 🧪 Scripts de Teste
│   ├── sync_test_validacao.php ⭐ NOVO
│   │   └── Teste completo interativo
│   │       Executa sincronização e valida resultado
│   │       - Testa categorias
│   │       - Testa modalidades
│   │       - Testa cursos
│   │       - Verifica integridade
│   │       - Verifica duplicatas
│   │       Uso: php sync_test_validacao.php
│   │
│   ├── sync_cron.php (acima em scripts/)
│   ├── test_sync.php
│   ├── teste.php
│   └── [outros testes]
│
└── 📄 Arquivo de Configuração
    └── .env (se usar)
        └── Variáveis de ambiente (opcional)
```

## 📊 Tabelas do Banco Local (faesma_db)

### ⭐ Novas Tabelas (v2.0)

#### `course_categories`
```sql
CREATE TABLE course_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) UNIQUE,
    descricao TEXT,
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_slug (slug),
    INDEX idx_ativo (ativo)
);
```

Dados sincronizados de: `site.cursos_site.categoria_nome` + `categoria_slug`, etc.

#### `course_modalities`
```sql
CREATE TABLE course_modalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) UNIQUE,
    descricao TEXT,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_slug (slug),
    INDEX idx_ativo (ativo)
);
```

Dados sincronizados de: `site.cursos_site.modalidade_nome` + `modalidade_slug`, etc.

#### `courses` (modificada)
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
    FOREIGN KEY (modality_id) REFERENCES course_modalities(id),
    INDEX idx_nome (nome),
    INDEX idx_cod_externo (cod_externo),
    INDEX idx_category_id (category_id),
    INDEX idx_modality_id (modality_id),
    INDEX idx_ativo (ativo)
);
```

Dados sincronizados de: `site.cursos_site.*`
Relacionamentos: com course_categories e course_modalities

#### `course_curriculum` (se implementado)
```sql
CREATE TABLE course_curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    semestre INT DEFAULT 1,
    disciplina VARCHAR(255) NOT NULL,
    carga_horaria INT DEFAULT 0,
    ementa TEXT,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY (course_id, semestre, disciplina),
    INDEX idx_course_id (course_id),
    INDEX idx_semestre (semestre)
);
```

Status: Não sincronizado na v2.0 (view não contém dados)

## 🔄 Fluxo de Sincronização (Resumo)

```
┌─ sync_cron.php ou sync_test_validacao.php (disparador)
│
├─► RemoteSyncService::syncCategories()
│   ├─ Busca: site.cursos_site (vista remota)
│   ├─ Extrai: categoria_nome, categoria_slug, categoria_descricao
│   ├─ Agrupa: por categoria_nome (evita duplicatas)
│   └─ Sincroniza: INSERT/UPDATE em course_categories
│
├─► RemoteSyncService::syncModalities()
│   ├─ Busca: site.cursos_site (vista remota)
│   ├─ Extrai: modalidade_nome, modalidade_slug, modalidade_descricao
│   ├─ Agrupa: por modalidade_nome (evita duplicatas)
│   └─ Sincroniza: INSERT/UPDATE em course_modalities
│
├─► RemoteSyncService::syncAllCourses()
│   ├─ Busca: site.cursos_site (vista remota)
│   ├─ Para cada curso:
│   │  ├─ Procura: categoria em course_categories (por categoria_nome)
│   │  ├─ Procura: modalidade em course_modalities (por modalidade_nome)
│   │  └─ INSERT/UPDATE em courses com category_id e modality_id
│   └─ Retorna: stats {criado, atualizado, pulado, falha}
│
└─► RemoteSyncService::syncCurriculum()
    └─ Retorna: aviso (não disponível em cursos_site)
```

## 🌐 Acesso ao Site (Consumo de Dados)

```
http://localhost/projeto5/

├─ index.php
│  └─ getCourses() ─► course_categories ✅ (local)
│                 └─► courses (local) ✅
│
├─ cursos.php
│  ├─ getCourses() ─► courses (local) ✅
│  ├─ getCourseCategories() ─► course_categories (local) ✅
│  ├─ getCourseModalities() ─► course_modalities (local) ✅
│  └─ getCourseCount() ─► courses (local) ✅
│
├─ curso-detalhes.php
│  └─ getCourse() ─► courses (local) ✅
│
└─ [outras páginas]
   └─ functions.php ─► SQL queries ao BD local ✅
```

**Nota:** Nenhuma página consulta a view remota diretamente ✅

## 🔐 Dados de Conexão

### Banco Local
```
Host: localhost
Database: faesma_db
User: root
Password: (vazio)
Arquivo de config: config/config.php
```

### Banco Remoto (Sincronização)
```
Host: 143.0.121.152
Database: site
User: site_faesma
Password: YwsGps1rBusBmWvPrzj9
View: site.cursos_site
Arquivo de config: includes/db.php
```

## 📝 Logs de Sincronização

**Localização:** `logs/sync_YYYY-MM-DD.log`

**Exemplo:**
```
[2024-01-15 02:00:00] [INFO] === INICIANDO SINCRONIZAÇÃO AUTOMÁTICA ===
[2024-01-15 02:00:01] [INFO] Conectando à view remota...
[2024-01-15 02:00:02] [SUCCESS] Categorias criadas: 12
[2024-01-15 02:00:03] [SUCCESS] Modalidades criadas: 4
[2024-01-15 02:00:25] [SUCCESS] Cursos criados: 42
[2024-01-15 02:00:26] [SUCCESS] ✅ SINCRONIZAÇÃO COMPLETA
```

**Níveis de Log:**
- `INFO` - Informações gerais
- `SUCCESS` - Operações bem-sucedidas
- `WARNING` - Alertas (não fatais)
- `ERROR` - Erros (operação pode ter falhado)

## 🛠️ Utilitários e Testes

| Arquivo | Propósito | Comando |
|---------|-----------|---------|
| `sync_test_validacao.php` | Teste interativo completo | `php sync_test_validacao.php` |
| `sync_cron.php` | Sincronização automática | `php scripts/sync_cron.php` ou Cron/Task Scheduler |
| `admin/test.php` | Testes diversos | Browser: `/admin/test.php` |
| `teste.php` | Testes rápidos | `php teste.php` ou `/teste.php` |

## ✅ Verificações Rápidas

```bash
# 1. Verificar sincronização mais recente
tail -n 30 logs/sync_*.log

# 2. Contar registros
mysql -u root -e "
SELECT 
  'Categorias' as tipo, COUNT(*) as total FROM faesma_db.course_categories
UNION ALL
SELECT 'Modalidades', COUNT(*) FROM faesma_db.course_modalities
UNION ALL
SELECT 'Cursos', COUNT(*) FROM faesma_db.courses;
"

# 3. Verificar integridade
mysql -u root -e "
SELECT COUNT(*) as cursos_sem_categoria 
FROM faesma_db.courses 
WHERE category_id IS NULL;
"

# 4. Verificar slugs
mysql -u root -e "
SELECT COUNT(*) as sem_slug 
FROM faesma_db.course_categories 
WHERE slug IS NULL OR slug = '';
"
```

## 🎯 Próximas Melhorias (Futuro)

- [ ] Implementar sincronização de currículo (se dados disponível)
- [ ] Adicionar cache Redis
- [ ] Criar API REST para consultas
- [ ] Implementar alertas por email em caso de erro
- [ ] Adicionar dashboard de monitoramento
- [ ] Versionar dados para auditoria

## 📞 Documentação de Referência

| Documento | Descrição |
|-----------|-----------|
| `SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md` | Guia completo de uso |
| `RESUMO_TECNICO_SINCRONIZACAO_V2.md` | Detalhes técnicos |
| `CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md` | Passo-a-passo |
| `docs/SYNC_ARCHITECTURE.md` | Arquitetura (anterior) |
| `database/schema.sql` | Schema do BD |

---

**Versão:** 2.0  
**Data:** 2024  
**Status:** ✅ Pronto para Produção
