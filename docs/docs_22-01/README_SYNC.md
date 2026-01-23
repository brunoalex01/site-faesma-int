# 🔄 Sistema de Sincronização de Cursos FAESMA

## Visão Geral

Sistema completo de mapeamento e sincronização entre a view remota (`site.cursos_site`) e o banco de dados local (`faesma_db.cursos`).

## 📦 Arquivos Criados

```
projeto5/
├── includes/
│   ├── RemoteSyncMapping.php      # Classe de mapeamento de campos
│   └── RemoteSyncService.php      # Serviço de sincronização
├── docs/
│   └── REMOTE_SYNC_GUIDE.md       # Documentação completa
├── sync_courses.php               # Script de sincronização
├── test_sync.php                  # Testes e validações
└── SYNC_INTEGRATION_EXAMPLES.php  # Exemplos de integração
```

## 🗺️ Mapeamento de Campos

| Remoto | Local |
|--------|-------|
| `id_curso` | `cod_externo` |
| `nome_curso` | `nome` |
| `descricao` | `descricao_curta` |
| `duracao_meses` | `duracao_meses` |
| `carga_horaria` | `carga_horaria` |
| `tcc_obrigatorio` | `tcc_obrigatorio` |
| `inscricao_online` | `inscricao_online` |
| `status_remoto` | `status` |
| E mais 13 campos... | |

**Veja documentação completa em:** `docs/REMOTE_SYNC_GUIDE.md`

## 🚀 Quickstart

### 1. Testar Mapeamento
```bash
php test_sync.php
```

### 2. Sincronizar Cursos (CLI)
```bash
php sync_courses.php
```

### 3. Sincronizar Cursos (HTTP)
```
http://localhost/projeto5/sync_courses.php?token=TOKEN_DIARIO
```

## 🔗 Integração Rápida

```php
<?php
// Em qualquer arquivo PHP
require_once 'includes/RemoteSyncService.php';
require_once 'includes/db.php';

$localDb = Database::getInstance()->getConnection();
$remoteDb = db();

$sync = new RemoteSyncService($localDb, $remoteDb);
$result = $sync->syncAllCourses('cursos_site', 500);

echo json_encode($result);
?>
```

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
  "log": [
    "Iniciando sincronização de 20 curso(s)",
    "[Criado] Administração (ID: 001)",
    "[Atualizado] Direito (ID: 002)"
  ]
}
```

## ⚙️ Configuração Avançada

### Adicionar Novo Campo ao Mapeamento

Edite `includes/RemoteSyncMapping.php`:

```php
private static $fieldMapping = [
    'novo_campo_remoto' => 'novo_campo_local',
    // ...
];
```

### Adicionar Transformação

```php
private static $transformations = [
    'novo_campo_local' => [
        'valor_remoto' => 'valor_local',
    ],
];
```

## 🔐 Segurança

- ✅ Prepared Statements contra SQL Injection
- ✅ Validação de dados
- ✅ Token diário para acesso HTTP
- ✅ Proteção de campos (id, slug, created_at não são sobrescritos)

## 📝 Logging

- Arquivo: `logs/last_sync.txt`
- Registra: timestamp última sincronização
- Rastreamento: criação, atualização, erros de cada curso

## 🐛 Troubleshooting

| Problema | Solução |
|----------|---------|
| "Nenhum curso encontrado" | Verificar credenciais em `db.php` |
| "Acesso não autorizado" | Gerar token com `md5(SECURE_KEY . date('Y-m-d'))` |
| Cursos não sincronizam | Verificar logs e validação em testes |

## 📚 Documentação

- **Completa:** [docs/REMOTE_SYNC_GUIDE.md](docs/REMOTE_SYNC_GUIDE.md)
- **Exemplos:** [SYNC_INTEGRATION_EXAMPLES.php](SYNC_INTEGRATION_EXAMPLES.php)
- **Testes:** Execute `php test_sync.php`

## 🔄 Agendamento Automático (Cron)

```bash
# Sincronizar diariamente às 2AM
0 2 * * * cd /path/to/projeto5 && php sync_courses.php
```

## 📈 Performance

- Batch processing até 500 cursos/vez
- Índices em campos de busca
- Prepared statements para segurança e velocidade

## 🎯 Próximos Passos

1. ✅ Criar classes de mapeamento e serviço
2. ✅ Implementar validação e transformação
3. ✅ Criar script de sincronização
4. ✅ Documentação completa
5. 🔲 Integrar com dashboard admin
6. 🔲 Configurar cron jobs
7. 🔲 Monitorar em produção

---

**Versão:** 1.0  
**Data:** Janeiro 2026  
**Ambiente:** XAMPP + FAESMA Website
