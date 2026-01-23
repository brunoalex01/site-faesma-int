# 🎓 FAESMA - Sistema de Sincronização de Cursos v2.0

**Status:** ✅ Implementado e Pronto para Produção

## 🎯 Resumo Executivo

Sistema completamente configurado que:
- ✅ Sincroniza cursos da view remota `site.cursos_site` para banco local `faesma_db`
- ✅ Extrai automaticamente categorias e modalidades de um único campo
- ✅ Deduplica dados para evitar duplicatas em sincronizações repetidas
- ✅ Permite acesso ao site sem depender de conexão remota
- ✅ Executa sincronização automática via cron ou manualmente

## 🚀 Início Rápido

### 1️⃣ Testar Sincronização (2 minutos)

```bash
cd c:\xampp\htdocs\projeto5
php sync_test_validacao.php
```

**Resultado esperado:**
```
✅ Teste completo de sincronização - FAESMA
✅ Banco de dados local conectado
✅ Banco de dados remoto conectado
✅ Sincronização de categorias concluída com sucesso!
✅ Sincronização de modalidades concluída com sucesso!
✅ Sincronização de cursos concluída com sucesso!
```

### 2️⃣ Verificar Dados no Navegador

```
http://localhost/projeto5/cursos.php
```

- Devem aparecer cursos do banco local
- Categorias e modalidades devem funcionar como filtros
- Sem atraso aguardando servidor remoto

### 3️⃣ Configurar Sincronização Automática (Opcional)

**Windows (Task Scheduler):**
1. Abrir Task Scheduler
2. Criar Nova Tarefa: "FAESMA Sync"
3. Acionador: Diariamente às 02:00
4. Ação: Executar `C:\xampp\php\php.exe` com argumento `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`

**Linux/macOS (Cron):**
```bash
crontab -e
# Adicionar:
0 2 * * * /usr/bin/php /path/to/projeto5/scripts/sync_cron.php
```

## 📊 O Que Foi Implementado

### Métodos de Sincronização

| Método | Descrição | Status |
|--------|-----------|--------|
| `syncCategories()` | Extrai categorias únicas de cursos_site | ✅ Ativo |
| `syncModalities()` | Extrai modalidades únicas de cursos_site | ✅ Ativo |
| `syncAllCourses()` | Sincroniza cursos com relacionamentos | ✅ Ativo |
| `syncCurriculum()` | Aviso (dados não disponível) | ⚠️ Stub |

### Arquivos Modificados

| Arquivo | Mudanças |
|---------|----------|
| `includes/RemoteSyncService.php` | ✅ Refatorado para extrair de cursos_site |
| `scripts/sync_cron.php` | ✅ Atualizado com nova ordem |
| `cursos.php` | ✅ Usa funções do banco local |

### Arquivos Criados

| Arquivo | Descrição |
|---------|-----------|
| `sync_test_validacao.php` | ⭐ Teste interativo com saída colorida |
| `SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md` | ⭐ Guia completo (obrigatório ler) |
| `RESUMO_TECNICO_SINCRONIZACAO_V2.md` | ⭐ Detalhes técnicos |
| `CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md` | ⭐ Passo-a-passo de validação |
| `ESTRUTURA_FINAL_PROJETO.md` | Visão geral da estrutura |

## 🔄 Como Funciona

```
┌─────────────────────────────────────────────────┐
│ View Remota: site.cursos_site                  │
│ Servidor: 143.0.121.152 (automático)           │
│ Contém: categorias, modalidades, cursos        │
└────────────┬────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────┐
│ RemoteSyncService (sincroniza em 3 etapas)     │
├─────────────────────────────────────────────────┤
│ 1. syncCategories()                             │
│    Extrai: categoria_nome, categoria_slug       │
│    Deduplica: agrupa por nome                   │
│    Resultado: course_categories (BD local)      │
│                                                  │
│ 2. syncModalities()                             │
│    Extrai: modalidade_nome, modalidade_slug     │
│    Deduplica: agrupa por nome                   │
│    Resultado: course_modalities (BD local)      │
│                                                  │
│ 3. syncAllCourses()                             │
│    Busca: cursos com todas as informações       │
│    Relaciona: com categorias e modalidades      │
│    Resultado: courses (BD local)                │
└────────────┬────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────┐
│ Banco Local: faesma_db                          │
│ ├─ course_categories (✅ preenchido)            │
│ ├─ course_modalities (✅ preenchido)            │
│ └─ courses (✅ preenchido com FKs)              │
└────────────┬────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────┐
│ Site Web (cursos.php, index.php, etc)          │
│ Consome dados do banco local ✅                 │
│ Sem dependência do servidor remoto ✅            │
└─────────────────────────────────────────────────┘
```

## 📂 Estrutura de Arquivos Principais

```
projeto5/
├── 📄 SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md ⭐ LEIA PRIMEIRO
├── 📄 RESUMO_TECNICO_SINCRONIZACAO_V2.md
├── 📄 CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md
├── 📄 ESTRUTURA_FINAL_PROJETO.md
│
├── 📂 includes/
│   ├── RemoteSyncService.php (sincronização)
│   ├── Database.php (BD local)
│   ├── db.php (BD remoto)
│   └── functions.php (funções do site)
│
├── 📂 scripts/
│   └── sync_cron.php (cron automático)
│
├── 📂 logs/
│   └── sync_YYYY-MM-DD.log (logs diários)
│
├── 🧪 sync_test_validacao.php (TESTE)
├── 🌐 cursos.php (usa BD local ✅)
├── 🌐 index.php (usa BD local ✅)
└── ...
```

## 🗄️ Dados de Conexão

**Banco Local (Consumido pelo Site):**
```
Host: localhost
Database: faesma_db
User: root
Password: (vazio)
```

**Banco Remoto (Apenas Sincronização):**
```
Host: 143.0.121.152
Database: site
View: cursos_site
User: site_faesma
Password: [configurado em db.php]
```

## ✅ Validação Rápida

### Verificar se sincronização funcionou

```bash
# 1. Listar categorias sincronizadas
mysql -u root faesma_db -e "SELECT COUNT(*) as total FROM course_categories;"

# 2. Listar modalidades sincronizadas
mysql -u root faesma_db -e "SELECT COUNT(*) as total FROM course_modalities;"

# 3. Listar cursos sincronizados
mysql -u root faesma_db -e "SELECT COUNT(*) as total FROM courses;"

# 4. Verificar integridade (cursos com categoria)
mysql -u root faesma_db -e "
  SELECT COUNT(*) as cursos_com_categoria 
  FROM courses c 
  WHERE c.category_id IS NOT NULL;
"
```

**Resultado esperado:** Números > 0

## 📝 Documentação Completa

### 📖 Guias Principais

1. **[SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md](SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md)** ⭐ LEIA PRIMEIRO
   - Explicação detalhada da arquitetura
   - Campos que são extraídos
   - Como usar manualmente
   - Troubleshooting

2. **[RESUMO_TECNICO_SINCRONIZACAO_V2.md](RESUMO_TECNICO_SINCRONIZACAO_V2.md)**
   - Mudanças técnicas detalhadas
   - Código-chave
   - Exemplos de dados
   - Diferenças da versão anterior

3. **[CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md](CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md)**
   - Passo-a-passo para validação
   - Testes para executar
   - Troubleshooting

4. **[ESTRUTURA_FINAL_PROJETO.md](ESTRUTURA_FINAL_PROJETO.md)**
   - Visão geral completa do projeto
   - Tabelas do banco de dados
   - Localização de todos os arquivos

## 🧪 Teste Manual

### Opção 1: Teste Completo (Recomendado)

```bash
php sync_test_validacao.php
```

Saída incluirá:
- Conexões testadas
- Categorias sincronizadas
- Modalidades sincronizadas
- Cursos sincronizados
- Verificação de integridade
- Detecção de duplicatas

### Opção 2: Teste Rápido via Web

```
http://localhost/projeto5/admin/test.php
```

### Opção 3: Sincronização Manual

```php
<?php
require_once 'config/config.php';
require_once 'includes/RemoteSyncService.php';
require_once 'includes/Database.php';
require_once 'includes/db.php';

$sync = new RemoteSyncService(
    Database::getInstance()->getConnection(),
    db()
);

echo "Sincronizando categorias...\n";
$r1 = $sync->syncCategories();
echo "Criadas: " . $r1['stats']['criado'] . "\n";

echo "Sincronizando modalidades...\n";
$r2 = $sync->syncModalities();
echo "Criadas: " . $r2['stats']['criado'] . "\n";

echo "Sincronizando cursos...\n";
$r3 = $sync->syncAllCourses();
echo "Criados: " . $r3['stats']['criado'] . "\n";
?>
```

## 🔐 Dados de Extração

### De `site.cursos_site` para Banco Local

**Categorias:**
- Campo remoto: `categoria_nome` → Tabela local: `course_categories.nome`
- Campo remoto: `categoria_slug` → Tabela local: `course_categories.slug` (gerado se vazio)
- Campo remoto: `categoria_descricao` → Tabela local: `course_categories.descricao`
- Campo remoto: `categoria_ordem` → Tabela local: `course_categories.ordem`

**Modalidades:**
- Campo remoto: `modalidade_nome` → Tabela local: `course_modalities.nome`
- Campo remoto: `modalidade_slug` → Tabela local: `course_modalities.slug` (gerado se vazio)
- Campo remoto: `modalidade_descricao` → Tabela local: `course_modalities.descricao`

**Cursos:**
- Campo remoto: `nome` → Tabela local: `courses.nome`
- Campo remoto: `cod_externo` → Tabela local: `courses.cod_externo`
- Campo remoto: `descricao` → Tabela local: `courses.descricao`
- Campo remoto: `categoria_nome` → Tabela local: `courses.category_id` (lookup em course_categories)
- Campo remoto: `modalidade_nome` → Tabela local: `courses.modality_id` (lookup em course_modalities)

## 📊 Logs

Sincronizações geram logs automáticos em `logs/sync_YYYY-MM-DD.log`

```bash
# Ver últimos 50 linhas de log
tail -n 50 logs/sync_*.log

# Procurar por erros
grep ERROR logs/sync_*.log

# Contar sucessos
grep SUCCESS logs/sync_*.log | wc -l
```

## 🚀 Próximos Passos

1. **Leia:** `SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md`
2. **Teste:** `php sync_test_validacao.php`
3. **Verifique:** Dados em `cursos.php`
4. **Configure:** Cron/Task Scheduler para sincronização automática
5. **Monitore:** Logs em `logs/`

## 💡 FAQs

**P: O site ainda depende do servidor remoto?**
R: Não! Todos os dados estão no banco local. Sincronização é apenas para atualizar os dados.

**P: Posso sincronizar manualmente?**
R: Sim! Execute `php sync_test_validacao.php` ou `php scripts/sync_cron.php`

**P: E se houver duplicatas?**
R: O sistema deduplica automaticamente por slug. Sincronizações repetidas não criam duplicatas.

**P: Como monitorar se sincronização funcionou?**
R: Verifique `logs/sync_*.log` ou execute o teste: `php sync_test_validacao.php`

**P: E os dados de currículo/disciplinas?**
R: Não estão disponíveis na view `cursos_site`. Se adicionados, será implementada sincronização.

## 📞 Suporte

1. Leia a documentação completa em `SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md`
2. Execute teste: `php sync_test_validacao.php`
3. Verifique logs: `logs/sync_*.log`
4. Consulte `CHECKLIST_IMPLEMENTACAO_SINCRONIZACAO.md` para troubleshooting

---

**Versão:** 2.0  
**Última Atualização:** 2024  
**Status:** ✅ Pronto para Produção

**Próximo Passo:** Abra [SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md](SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md) para guia completo.
