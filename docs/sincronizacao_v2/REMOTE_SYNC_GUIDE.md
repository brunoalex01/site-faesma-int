# Mapeamento e Sincronização de Cursos Remotos

## 📋 Visão Geral

Este sistema realiza sincronização automática entre a view remota (`site.cursos_site` no servidor remoto) e a tabela local (`cursos` no banco `faesma_db`). O mapeamento é totalmente configurável e oferece validação, transformação e logging de operações.

## 🗂️ Arquivos Principais

### 1. **RemoteSyncMapping.php**
Classe responsável pelo mapeamento de campos e transformação de dados.

```
includes/
├── RemoteSyncMapping.php      # Mapeamento de campos
├── RemoteSyncService.php      # Serviço de sincronização
└── db.php                      # Conexão ao banco remoto
```

### 2. **RemoteSyncService.php**
Serviço que executa a sincronização usando o mapeamento.

### 3. **sync_courses.php**
Script de sincronização executável via CLI ou HTTP.

---

## 🔗 Mapeamento de Campos

### Estrutura

| Campo Remoto | Campo Local | Descrição |
|--------------|-------------|-----------|
| `id_curso` | `cod_externo` | ID único do curso remoto |
| `codigo_curso` | `cd_oferta` | Código da oferta |
| `nome_curso` | `nome` | Nome do curso |
| `descricao` | `descricao_curta` | Descrição breve |
| `descricao_detalhada` | `descricao_completa` | Descrição completa |
| `duracao_meses` | `duracao_meses` | Duração em meses |
| `duracao_texto` | `duracao_texto` | Texto descritivo (ex: "4 anos") |
| `carga_horaria` | `carga_horaria` | Carga horária total |
| `objetivos` | `objetivos` | Objetivos do curso |
| `perfil_egresso` | `perfil_egresso` | Perfil profissional do egresso |
| `mercado_trabalho` | `mercado_trabalho` | Informações de mercado de trabalho |
| `publico_alvo` | `publico_alvo` | Público-alvo do curso |
| `valor_mensalidade` | `valor_mensalidade` | Valor da mensalidade |
| `vagas_disponiveis` | `vagas_disponiveis` | Vagas disponíveis |
| `coordenador_nome` | `coordenador` | Nome do coordenador |
| `imagem_url` | `imagem_destaque` | URL da imagem destaque |
| `nota_mec` | `nota_mec` | Nota do MEC |
| `tcc_obrigatorio` | `tcc_obrigatorio` | TCC obrigatório (booleano) |
| `inscricao_online` | `inscricao_online` | Inscrição online ativa (booleano) |
| `link_oferta` | `link_oferta` | Link da oferta |
| `status_remoto` | `status` | Status do curso |

### Campos Especiais

#### Status
Mapeamento de status remoto para local:
```
Remoto → Local
'ativo' → 'ativo'
'inativo' → 'inativo'
'breve' → 'breve'
'draft' → 'inativo'
```

#### Booleanos
Campos `tcc_obrigatorio` e `inscricao_online` são convertidos para booleanos automaticamente.

#### Slug
Se não fornecido, é gerado automaticamente a partir do `nome`.

---

## 🚀 Como Usar

### 1. **Sincronização via CLI**

```bash
# Sincronização completa
php sync_courses.php

# Com opções
php sync_courses.php --view=cursos_site --limit=500
```

### 2. **Sincronização via HTTP**

```url
# Sincronização completa
http://localhost/projeto5/sync_courses.php?token=TOKEN_DIARIO

# Com opções
http://localhost/projeto5/sync_courses.php?token=TOKEN&view=cursos_site&limit=500&mode=sync
```

**Token Diário**: Gerado automaticamente baseado em `SECURE_KEY` e data.
```php
$token = md5(SECURE_KEY . date('Y-m-d'));
// Com SECURE_KEY = 'faesma_secure_key_2026'
// Token de hoje: md5('faesma_secure_key_2026' . '2026-01-22')
```

### 3. **Sincronização Programática**

```php
<?php
require_once 'includes/Database.php';
require_once 'includes/db.php';
require_once 'includes/RemoteSyncService.php';

$localDb = Database::getInstance()->getConnection();
$remoteDb = db();

$syncService = new RemoteSyncService($localDb, $remoteDb);
$result = $syncService->syncAllCourses('cursos_site', 500);

echo json_encode($result);
?>
```

---

## 📊 Resposta da Sincronização

### Sucesso
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
    "[Atualizado] Direito (ID: 002)",
    ...
  ]
}
```

### Erro
```json
{
  "status": "erro",
  "mensagem": "Descrição do erro",
  "log": []
}
```

---

## 🔍 Validação de Dados

### Campos Obrigatórios
- `id_curso` - ID único remoto
- `nome_curso` - Nome do curso

### Validações Automáticas

1. **Presença de campos obrigatórios**
2. **Conversão de tipos** (booleanos, números)
3. **Mapeamento de status**
4. **Geração automática de slug**
5. **Prevenção de duplicatas** (busca por `cod_externo`, `slug` ou `nome`)

---

## 🔄 Fluxo de Sincronização

```
┌─────────────────────────────────────────────┐
│  Fetch All Data from Remote View            │
│  (site.cursos_site)                         │
└──────────────┬──────────────────────────────┘
               ↓
┌──────────────────────────────────────────┐
│  Validate Remote Row                     │
│  - Check required fields                 │
│  - Check data types                      │
└──────────────┬──────────────────────────┘
               ↓
┌──────────────────────────────────────────┐
│  Convert to Local Format                 │
│  - Map fields                            │
│  - Transform values                      │
│  - Generate missing fields (slug, etc)   │
└──────────────┬──────────────────────────┘
               ↓
┌──────────────────────────────────────────┐
│  Find Existing Course in Local DB        │
│  - Search by cod_externo                 │
│  - Search by slug                        │
│  - Search by nome                        │
└──────┬───────────────────────────┬───────┘
       │                           │
   EXISTS?                     NOT EXISTS
       │                           │
       ↓                           ↓
   ┌────────────────┐    ┌─────────────────┐
   │  UPDATE        │    │  INSERT         │
   │  (Update stats)│    │  (Create stats) │
   └────────────────┘    └─────────────────┘
       │                           │
       └───────────┬───────────────┘
                   ↓
       ┌───────────────────────┐
       │  Return Result        │
       │  - Action taken       │
       │  - Course ID          │
       └───────────────────────┘
```

---

## 🛡️ Segurança

### Proteção de Campos
Os seguintes campos não são atualizados em sincronizações posteriores:
- `id` - Identificador primário
- `slug` - URL amigável (não altera cursos existentes)
- `created_at` - Data de criação

### Validação de Acesso HTTP
Requer token diário baseado em `SECURE_KEY`:
```php
$token = md5(SECURE_KEY . date('Y-m-d'));
```

---

## 📝 Logging

### Localização
```
logs/
└── last_sync.txt    # Timestamp da última sincronização
```

### Informações Registradas
- Início e fim da sincronização
- Ações por curso (criado, atualizado, pulado)
- Erros e exceções
- Estatísticas gerais

---

## ⚙️ Customização

### Adicionar Novo Mapeamento

Edite `RemoteSyncMapping.php`:

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
        'valor1' => 'mapeado1',
        'valor2' => 'mapeado2',
    ],
];
```

### Mudar Lógica de Busca de Duplicatas

Edite `RemoteSyncService::findExistingCourse()` para alterar prioridade de busca.

---

## 🐛 Troubleshooting

### "Nenhum curso encontrado na view remota"
- Verificar se banco remoto está acessível
- Verificar credenciais em `includes/db.php`
- Verificar se view `cursos_site` existe
- Verificar se há dados na view

### "Acesso não autorizado" (HTTP)
- Token inválido ou expirado
- Gerar novo token:
  ```php
  md5('faesma_secure_key_2026' . date('Y-m-d'))
  ```

### Cursos não sincronizados
- Verificar validação de dados em `RemoteSyncMapping::validateRemoteData()`
- Verificar logs em `logs/`
- Verificar se campos obrigatórios estão presentes no remoto

---

## 📈 Performance

### Otimizações
- Batch processing (sincroniza até 500 cursos por vez)
- Prepared statements para segurança e velocidade
- Índices em `cod_externo`, `slug`, `nome` na tabela local

### Recomendações
- Executar sincronização em horários de baixo uso
- Usar `limit` apropriado para grandes datasets
- Monitorar logs regularmente

---

## 🔗 Integração com Código Existente

A sincronização integra-se seamlessly com o sistema:

```php
// Em cursos.php ou curso-detalhes.php
require_once __DIR__ . '/includes/Database.php';

$db = Database::getInstance();
$cursos = $db->fetchAll("SELECT * FROM courses WHERE status = 'ativo'");
// Dados sincronizados estarão disponíveis imediatamente
```

---

## 📅 Agendamento Automático (Cron)

Para sincronização automática diária:

```bash
# Editar crontab
crontab -e

# Adicionar linha para executar diariamente às 2AM
0 2 * * * cd /path/to/projeto5 && php sync_courses.php >> logs/sync.log 2>&1
```

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique o arquivo de log em `logs/`
2. Valide credenciais do banco remoto
3. Confirme estrutura da view remota
4. Verifique mapeamento em `RemoteSyncMapping.php`
