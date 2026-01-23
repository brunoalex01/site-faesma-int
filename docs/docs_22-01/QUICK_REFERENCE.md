# 🎯 QUICK REFERENCE CARD - Sistema de Sincronização

## ⚡ Comandos Rápidos

### Executar Sincronização
```bash
# CLI
php sync_courses.php

# Com opções
php sync_courses.php --view=cursos_site --limit=500

# HTTP (Browser)
http://localhost/projeto5/sync_courses.php?token=TOKEN&view=cursos_site
```

### Executar Testes
```bash
php test_sync.php
```

### Ver Último Sync
```bash
cat logs/last_sync.txt
```

---

## 🗺️ Mapeamento Rápido

| Remoto | Local | Tipo |
|--------|-------|------|
| id_curso | cod_externo | String |
| nome_curso | nome | String |
| descricao | descricao_curta | Text |
| duracao_meses | duracao_meses | Int |
| carga_horaria | carga_horaria | Int |
| tcc_obrigatorio | tcc_obrigatorio | Bool |
| inscricao_online | inscricao_online | Bool |
| status_remoto | status | Enum |
| coordenador_nome | coordenador | String |
| imagem_url | imagem_destaque | String |
| valor_mensalidade | valor_mensalidade | Decimal |
| vagas_disponiveis | vagas_disponiveis | Int |

[Ver mapeamento completo →](docs/REMOTE_SYNC_GUIDE.md#-mapeamento-de-campos)

---

## 💻 Integração Código

### Forma 1: Simples
```php
<?php
require_once 'includes/RemoteSyncService.php';
require_once 'includes/db.php';

$sync = new RemoteSyncService($localDb, $remoteDb);
$result = $sync->syncAllCourses('cursos_site', 500);
echo json_encode($result);
?>
```

### Forma 2: Com Auto-Sync
```php
<?php
function getCoursesWithSync($filters = [], $autoSync = true) {
    if ($autoSync) {
        $sync = new RemoteSyncService($localDb, $remoteDb);
        $sync->syncDeltaCourses();
    }
    return getCourses($filters);
}
?>
```

### Forma 3: API
```php
<?php
// api/sync.php
require_once 'includes/RemoteSyncService.php';
$result = $syncService->syncAllCourses('cursos_site');
header('Content-Type: application/json');
echo json_encode($result);
?>
```

[Ver exemplos completos →](SYNC_INTEGRATION_EXAMPLES.php)

---

## 📋 Modos de Execução

| Modo | Comando | Quando usar |
|------|---------|-------------|
| **CLI** | `php sync_courses.php` | Manual, testes |
| **HTTP** | `?token=TOKEN` | Dashboard |
| **Cron** | `0 2 * * *` | Automático |
| **PHP** | `$sync->syncAllCourses()` | Programático |

---

## 🔐 Token de Acesso

```php
// Gerar token diário
$token = md5(SECURE_KEY . date('Y-m-d'));

// Usar na URL
http://localhost/projeto5/sync_courses.php?token={$token}
```

---

## 📊 Resposta de Sincronização

```json
{
  "status": "sucesso",
  "mensagem": "Sincronização concluída",
  "stats": {
    "criado": 5,
    "atualizado": 12,
    "falha": 0,
    "pulado": 3
  },
  "log": ["[Criado] Curso 1", "[Atualizado] Curso 2", ...]
}
```

---

## 🚨 Status Possíveis

| Status | Significado |
|--------|-------------|
| `sucesso` | Tudo OK |
| `erro` | Algo deu errado |

---

## ❌ Erros Comuns & Soluções

| Erro | Solução |
|------|---------|
| "Nenhum curso encontrado" | Verificar credenciais em `db.php` |
| "Acesso não autorizado" | Token inválido, regenerar |
| "Campo obrigatório ausente" | Verificar dados remotos |
| "Conexão recusada" | Verificar se banco remoto está online |

[Ver troubleshooting completo →](docs/REMOTE_SYNC_GUIDE.md#-troubleshooting)

---

## 📁 Arquivos Principais

| Arquivo | Descrição |
|---------|-----------|
| `includes/RemoteSyncMapping.php` | Mapeamento e transformação |
| `includes/RemoteSyncService.php` | Lógica de sincronização |
| `sync_courses.php` | Script executável |
| `test_sync.php` | Testes |
| `docs/REMOTE_SYNC_GUIDE.md` | Documentação completa |
| `docs/SYNC_ARCHITECTURE.md` | Diagramas |

---

## 🔧 Customização Comum

### Adicionar novo campo
```php
// Em RemoteSyncMapping.php
'novo_campo_remoto' => 'novo_campo_local'
```

### Adicionar transformação
```php
// Em RemoteSyncMapping.php
'novo_campo' => [
    'valor1' => 'mapeado1',
    'valor2' => 'mapeado2',
]
```

[Ver customização completa →](docs/REMOTE_SYNC_GUIDE.md#-customização)

---

## 📊 Campos Sincronizados

```
✓ Identificadores (2)
  - cod_externo (ID remoto)
  - cd_oferta

✓ Informações Básicas (3)
  - nome
  - descricao_curta
  - descricao_completa

✓ Estrutura (3)
  - duracao_meses
  - duracao_texto
  - carga_horaria

✓ Conteúdo (4)
  - objetivos
  - perfil_egresso
  - mercado_trabalho
  - publico_alvo

✓ Administrativo (5)
  - coordenador
  - imagem_destaque
  - nota_mec
  - status
  - link_oferta

✓ Especiais (2)
  - tcc_obrigatorio (Bool)
  - inscricao_online (Bool)

TOTAL: 21 campos
```

---

## 🔍 Validação

### Campos Obrigatórios
- `id_curso` - ID remoto
- `nome_curso` - Nome do curso

### Validações Automáticas
- ✓ Presença de obrigatórios
- ✓ Conversão de tipos
- ✓ Mapeamento de status
- ✓ Geração de slug

---

## ⚙️ Performance

- **Batch size**: 500 cursos/vez
- **Tempo típico**: 2-5s para 100 cursos
- **Campos indexados**: cod_externo, slug, nome

---

## 🔐 Segurança

- ✓ Prepared statements (SQL Injection)
- ✓ Validação dupla
- ✓ Token diário (HTTP)
- ✓ Proteção de campos (id, slug, created_at)

---

## 📈 Checklist de Setup

```
[ ] Ler README_SYNC.md
[ ] Executar php test_sync.php
[ ] Validar credenciais em db.php
[ ] Testar sync: php sync_courses.php
[ ] Integrar com funções
[ ] Configurar cron job
[ ] Monitorar logs
```

---

## 📞 Documentação

| Doc | Tempo | Para |
|-----|-------|------|
| [README_SYNC.md](README_SYNC.md) | 5 min | Overview |
| [REMOTE_SYNC_GUIDE.md](docs/REMOTE_SYNC_GUIDE.md) | 20 min | Referência |
| [SYNC_ARCHITECTURE.md](docs/SYNC_ARCHITECTURE.md) | 15 min | Arquitetura |
| [SYNC_INTEGRATION_EXAMPLES.php](SYNC_INTEGRATION_EXAMPLES.php) | 15 min | Código |
| [INDEX.md](INDEX.md) | 10 min | Navegação |

---

## 🎯 Atalhos por Tarefa

### "Sincronizar agora"
```bash
php sync_courses.php
```

### "Ver status"
```bash
cat logs/last_sync.txt
```

### "Adicionar campo"
1. Edite: `includes/RemoteSyncMapping.php`
2. Teste: `php test_sync.php`

### "Agendar automático"
```bash
# Cron (diariamente às 2 AM)
0 2 * * * php /path/projeto5/sync_courses.php
```

### "Integrar com código"
Copie exemplos de [SYNC_INTEGRATION_EXAMPLES.php](SYNC_INTEGRATION_EXAMPLES.php)

---

## 🆘 Suporte Rápido

**P: Como executo?**
R: `php sync_courses.php`

**P: Qual é o token?**
R: `md5(SECURE_KEY . date('Y-m-d'))`

**P: Onde ver logs?**
R: `logs/last_sync.txt`

**P: Como adiciono campo?**
R: Edite `RemoteSyncMapping.php`, adicione ao `$fieldMapping`

**P: Qual banco é sincronizado?**
R: `site.cursos_site` → `faesma_db.courses`

**P: Quantos campos?**
R: 21 campos mapeados

**P: Preciso de segurança?**
R: Já tem: prepared statements + validação

---

## 📋 Métodos Principais

```php
// RemoteSyncMapping
RemoteSyncMapping::getMapping()
RemoteSyncMapping::mapField($field)
RemoteSyncMapping::transformValue($field, $value)
RemoteSyncMapping::validateRemoteData($row)
RemoteSyncMapping::convertRemoteToLocal($row)
RemoteSyncMapping::buildInsertQuery($data)
RemoteSyncMapping::buildUpdateQuery($data, $id)

// RemoteSyncService
$sync = new RemoteSyncService($localDb, $remoteDb)
$sync->syncAllCourses($view, $limit)
$sync->syncCourse($remoteRow)
$sync->syncDeltaCourses($view)
$sync->getLog()
$sync->getLastSyncTime()
```

---

## 🎓 Tempo de Aprendizado

| Nível | Conteúdo | Tempo |
|-------|----------|-------|
| Básico | README_SYNC | 5 min |
| Intermediário | + REMOTE_SYNC_GUIDE | 20 min |
| Avançado | + SYNC_ARCHITECTURE | 30 min |
| Expert | + Código + Customização | 60 min |

---

**Último update:** Janeiro 2026  
**Versão:** 1.0  
**Status:** Pronto para produção ✅

Para mais detalhes → [INDEX.md](INDEX.md)
