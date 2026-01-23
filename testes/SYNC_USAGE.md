# 🔄 Guia de Uso - Sincronização Automática de Cursos

## Visão Geral

O sistema de sincronização automática funciona como uma **intermediária** entre o banco de dados remoto e o banco de dados local:

```
View Remota (site.cursos_site)
    ↓
[teste.php - Sincronização Automática]
    ↓
Banco Local (faesma_db.courses)
    ↓
Website FAESMA (lê dados locais)
```

## Como Funciona

### 1. Página de Sincronização (teste.php)

A página `teste.php` agora é uma **intermediária automática** que:

1. **Lê** dados da View remota (`site.cursos_site`)
2. **Mapeia** 21 campos correspondentes
3. **Atualiza** automaticamente o banco de dados local
4. **Exibe** relatório visual com estatísticas

### 2. Operações Automáticas

Quando você acessa `teste.php`:

```
✅ Sincronização inicia automaticamente
✅ Compara dados remotos com banco local
✅ Cria novos cursos (se não existem)
✅ Atualiza cursos existentes
✅ Detecta duplicatas (evita redundâncias)
✅ Exibe relatório com resultados
```

### 3. Mapeamento de Campos (21 campos)

| Campo Remoto | Campo Local | Transformação |
|---|---|---|
| `id_curso` | `cod_externo` | - |
| `nome_curso` | `nome` | - |
| `descricao` | `descricao_curta` | - |
| `descricao_completa` | `descricao_detalhada` | - |
| `duracao_meses` | `duracao_meses` | - |
| `duracao_texto` | `duracao_texto` | - |
| `carga_horaria` | `carga_horaria` | - |
| `objetivos` | `objetivos` | - |
| `perfil_egresso` | `perfil_egresso` | - |
| `mercado_trabalho` | `mercado_trabalho` | - |
| `publico_alvo` | `publico_alvo` | - |
| `tcc_obrigatorio` | `tcc_obrigatorio` | Booleano |
| `inscricao_online` | `inscricao_online` | Booleano |
| `coordenador` | `coordenador_nome` | - |
| `imagem_destaque` | `imagem_url` | - |
| `nota_mec` | `nota_mec` | - |
| `valor_mensalidade` | `valor_mensalidade` | - |
| `vagas_disponiveis` | `vagas_disponiveis` | - |
| `cd_oferta` | `codigo_curso` | - |
| `status` | `status_remoto` | Mapeamento de status |
| `link_oferta` | `link_oferta` | - |

## Como Usar

### Opção 1: Acesso Manual

Simplesmente acesse a página no navegador:

```
http://localhost/projeto5/teste.php
```

A sincronização ocorre automaticamente e você verá:
- ✅ Status da sincronização
- 📊 Estatísticas (Criados, Atualizados, Pulados, Erros)
- 📋 Log detalhado de operações
- 📄 Lista dos cursos sincronizados

### Opção 2: Cron Job (Recomendado)

Para automatizar a sincronização diária, configure um cron job:

#### No Linux/Mac:

```bash
# Sincronizar todos os dias às 2h da manhã
0 2 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1

# Sincronizar a cada 6 horas
0 */6 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1

# Sincronizar a cada hora
0 * * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1
```

#### No Windows (Task Scheduler):

1. Abra **Task Scheduler**
2. Crie nova tarefa agendada
3. Configure gatilho: "Diariamente" (exemplo: 02:00)
4. Configure ação:
   - Programa: `curl.exe` (ou `powershell.exe` se curl não estiver disponível)
   - Argumentos: `http://localhost/projeto5/teste.php`

### Opção 3: Script PHP

```php
// sync_manual.php
<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/RemoteSyncService.php';
require_once __DIR__ . '/includes/db.php';

$localDb = Database::getInstance()->getConnection();
$remoteDb = db();
$syncService = new RemoteSyncService($localDb, $remoteDb);
$resultado = $syncService->syncAllCourses('cursos_site', 500);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
```

Execute via CLI:

```bash
php sync_manual.php
```

## Recursos de Segurança

### ✅ Validação

- ✓ Verifica se campos obrigatórios existem
- ✓ Valida tipos de dados
- ✓ Remove espaços em branco
- ✓ Converte booleanos corretamente

### ✅ Proteção

- ✓ Prepared Statements (SQL injection)
- ✓ Campos protegidos (id, slug, created_at)
- ✓ Detecção de duplicatas (3 níveis)
- ✓ Log detalhado de todas operações

### ✅ Transformações

- ✓ Slug gerado automaticamente
- ✓ Status mapeado corretamente
- ✓ Booleanos convertidos
- ✓ Acentos removidos

## Estatísticas de Sincronização

### Campos Criados (Criados)
Cursos que não existiam no banco local e foram criados

### Campos Atualizados (Atualizados)
Cursos que já existiam e tiveram dados atualizados

### Campos Pulados (Pulado)
Cursos que já existem com os mesmos dados (sem alterações necessárias)

### Erros (Falha)
Cursos que tiveram problemas durante sincronização

## Verificação de Sucesso

Você saberá que a sincronização funcionou quando:

1. ✅ Status shows "✅ Sincronização Concluída com Sucesso!"
2. ✅ Estatísticas mostram números (criados, atualizados, etc.)
3. ✅ Log mostra operações realizadas
4. ✅ Cursos aparecem na lista

## Verificação de Problemas

Se aparecer erro:

### ❌ "Erro na Sincronização"

Verifique:

1. **Banco remoto acessível**
   ```bash
   mysql -h 143.0.121.152 -u user -p site
   ```

2. **View exists**
   ```sql
   SELECT * FROM site.cursos_site LIMIT 1;
   ```

3. **Credenciais em includes/db.php**
   ```php
   define('REMOTE_HOST', '143.0.121.152');
   define('REMOTE_USER', 'seu_usuario');
   define('REMOTE_PASS', 'sua_senha');
   define('REMOTE_DB', 'site');
   ```

4. **Banco local configurado**
   ```bash
   mysql -u root faesma_db
   SELECT * FROM courses LIMIT 1;
   ```

## Integração com Website

O website FAESMA **não deve** ler diretamente da view remota.

### ❌ ERRADO (não fazer):
```php
$remoteDb = db();
$cursos = fetchAllFromView($remoteDb, 'cursos_site');
```

### ✅ CORRETO (fazer assim):
```php
$localDb = Database::getInstance()->getConnection();
$stmt = $localDb->prepare('SELECT * FROM courses WHERE status = ?');
$stmt->execute(['ativo']);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

## Logs e Histórico

Os logs de sincronização são armazenados em:

- `logs/sync.log` - Histórico de todas operações
- `logs/last_sync.txt` - Último timestamp de sincronização

### Visualizar últimas sincronizações:

```bash
tail -f logs/sync.log
```

## Troubleshooting

### Problema: Nenhum curso foi criado/atualizado

**Causas possíveis:**
- View remota está vazia
- Banco local já tem todos os cursos
- Erro de conexão ao banco remoto

**Solução:**
```sql
-- Verifique quantos cursos estão na view remota
SELECT COUNT(*) FROM site.cursos_site;

-- Verifique quantos estão no banco local
SELECT COUNT(*) FROM faesma_db.courses;
```

### Problema: Erros durante sincronização

**Verifique o log:**
```
Esta página exibe um log detalhado de cada operação realizada.
Procure por mensagens de erro específicas.
```

**Informações adicionais:**
- Verifique espaço em disco
- Verifique permissões de banco de dados
- Verifique conexão de rede

### Problema: Dados duplicados

**Solução:**
O sistema detecta duplicatas em 3 níveis:
1. Por ID externo (cod_externo)
2. Por slug
3. Por nome

Se encontrar duplicatas, o sistema as pula automaticamente.

## Próximos Passos

1. ✅ **Acessar teste.php** para executar sincronização
2. ✅ **Configurar cron job** para automatizar
3. ✅ **Monitorar logs** regularmente
4. ✅ **Atualizar website** para usar banco local

## Documentação Completa

Para mais detalhes técnicos, consulte:

- [TECHNICAL_DOCUMENTATION.md](docs/TECHNICAL_DOCUMENTATION.md) - Documentação técnica
- [SYNC_ARCHITECTURE.md](docs/SYNC_ARCHITECTURE.md) - Arquitetura do sistema
- [REMOTE_SYNC_GUIDE.md](docs/REMOTE_SYNC_GUIDE.md) - Guia de sincronização remota

## Suporte

Se encontrar problemas:

1. Verifique `teste.php` para ver o relatório completo
2. Consulte `logs/sync.log` para histórico
3. Valide credenciais em `includes/db.php`
4. Confirme que a view remota tem dados

---

**Última atualização:** <?php echo date('d/m/Y H:i:s'); ?>

**Sistema:** Sincronização Automática FAESMA v1.0
