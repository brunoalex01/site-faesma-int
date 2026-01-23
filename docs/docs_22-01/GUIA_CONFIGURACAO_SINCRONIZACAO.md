# 🚀 GUIA DE CONFIGURAÇÃO - SINCRONIZAÇÃO

## Pré-requisitos

- ✅ PHP 7.4+ instalado
- ✅ Banco local (faesma_db) criado
- ✅ Banco remoto (site) acessível
- ✅ Conexão PDO MySQL ativa

---

## 1️⃣ PREPARAR BANCO REMOTO

### Criar Views Remotas

Executar no **banco remoto** (`site`) via phpMyAdmin ou CLI:

```sql
-- ============================================
-- VIEW: categorias_site
-- ============================================
CREATE OR REPLACE VIEW categorias_site AS
SELECT 
    id,
    nome,
    slug,
    descricao,
    ordem,
    ativo
FROM course_categories
WHERE ativo = 1;

-- ============================================
-- VIEW: modalidades_site
-- ============================================
CREATE OR REPLACE VIEW modalidades_site AS
SELECT 
    id,
    nome,
    slug,
    descricao,
    ativo
FROM course_modalities
WHERE ativo = 1;

-- ============================================
-- VIEW: curriculo_site
-- ============================================
CREATE OR REPLACE VIEW curriculo_site AS
SELECT 
    id,
    course_id,
    semestre,
    disciplina,
    carga_horaria,
    ementa,
    ordem
FROM course_curriculum
ORDER BY course_id, semestre, ordem;
```

### Verificar Permissões

```sql
-- Garantir que usuário site_faesma tem acesso
GRANT SELECT ON site.* TO 'site_faesma'@'%';
FLUSH PRIVILEGES;

-- Verificar acesso
SELECT * FROM categorias_site LIMIT 1;
SELECT * FROM modalidades_site LIMIT 1;
SELECT * FROM curriculo_site LIMIT 1;
```

---

## 2️⃣ VERIFICAR CONFIGURAÇÃO LOCAL

### Verificar `config/config.php`

```php
// Deve estar assim:
define('DB_HOST', 'localhost');
define('DB_NAME', 'faesma_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### Verificar `includes/db.php`

```php
// Banco remoto
$host = '143.0.121.152';   // IP do servidor remoto
$name = 'site';             // Nome do banco remoto
$user = 'site_faesma';      // Usuário remoto
$pass = 'YwsGps1rBusBmWvPrzj9';  // Senha remota
```

---

## 3️⃣ TESTAR CONECTIVIDADE

### Teste de Conexão Local

```bash
# No terminal
mysql -u root -h localhost faesma_db -e "SELECT COUNT(*) FROM course_categories;"
```

### Teste de Conexão Remota

```bash
# No terminal
mysql -u site_faesma -p'YwsGps1rBusBmWvPrzj9' -h 143.0.121.152 site -e "SELECT COUNT(*) FROM categorias_site;"
```

### Teste via PHP

```bash
cd /caminho/para/projeto5
php -r "
define('FAESMA_ACCESS', true);
require 'includes/db.php';
\$db = db();
echo 'Conexão remota OK: ' . \$db->query('SELECT COUNT(*) FROM cursos_site')->fetchColumn() . ' cursos\\n';
"
```

---

## 4️⃣ CRIAR DIRETÓRIO DE LOGS

```bash
# Criar pasta se não existir
mkdir -p /caminho/para/projeto5/logs
chmod 755 /caminho/para/projeto5/logs
```

---

## 5️⃣ TESTAR SINCRONIZAÇÃO

### Teste Rápido

```bash
cd /caminho/para/projeto5

# Executar teste completo
php sync_test_complete.php
```

### Saída Esperada

```
════════════════════════════════════════════════════════════
   FAESMA - Teste de Sincronização Completa
════════════════════════════════════════════════════════════

📡 Conectando aos bancos de dados...
✓ Conexão estabelecida

📁 Sincronizando Categorias...
────────────────────────────────────────────────────────────
✓ Sincronização de categorias concluída!
  • Criadas: X
  • Atualizadas: Y

🎓 Sincronizando Modalidades...
────────────────────────────────────────────────────────────
✓ Sincronização de modalidades concluída!
  • Criadas: X
  • Atualizadas: Y

📚 Sincronizando Cursos...
────────────────────────────────────────────────────────────
✓ Sincronização de cursos concluída!
  • Criados: X
  • Atualizados: Y
  • Ignorados: Z

📖 Sincronizando Currículo...
────────────────────────────────────────────────────────────
✓ Sincronização de currículo concluída!
  • Disciplinas criadas: X
  • Disciplinas atualizadas: Y

════════════════════════════════════════════════════════════
📊 RESUMO GERAL DA SINCRONIZAÇÃO
════════════════════════════════════════════════════════════
✓ Registros Criados: TOTAL
✓ Registros Atualizados: TOTAL
════════════════════════════════════════════════════════════

✅ Sincronização completa finalizada com sucesso!
```

---

## 6️⃣ AGENDAR SINCRONIZAÇÃO AUTOMÁTICA

### Linux/Mac (Cron)

```bash
# Abrir editor de cron
crontab -e

# Adicionar linha (sincronizar todos os dias às 2:00 AM)
0 2 * * * /usr/bin/php /caminho/para/projeto5/scripts/sync_cron.php >> /caminho/para/projeto5/logs/cron.log 2>&1

# Salvar e sair (Ctrl+X, Y, Enter)

# Verificar se foi adicionado
crontab -l
```

### Windows (Task Scheduler)

**Método 1: Via GUI**
1. Abrir Task Scheduler
2. Criar Tarefa Básica
3. Nome: "FAESMA Sincronização"
4. Gatilho: "Diário" às 2:00 AM
5. Ação:
   - Programa: `C:\xampp\php\php.exe`
   - Argumentos: `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`
   - Iniciar em: `C:\xampp\htdocs\projeto5`
6. Salvar

**Método 2: Via PowerShell**
```powershell
# Executar como Administrator
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\projeto5\scripts\sync_cron.php"
$trigger = New-ScheduledTaskTrigger -Daily -At 2:00AM
Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "FAESMA Sincronização" -Description "Sincronizar dados de cursos"
```

### Verificar Execução

**Linux/Mac:**
```bash
# Ver logs de cron
cat /caminho/para/projeto5/logs/cron.log

# Ver logs de sincronização
tail -f /caminho/para/projeto5/logs/sync_*.log
```

**Windows:**
```powershell
# Ver no Task Scheduler
Get-ScheduledTask -TaskName "FAESMA Sincronização"

# Ver logs
Get-EventLog -LogName System -Source TaskScheduler
```

---

## 7️⃣ MONITORAR SINCRONIZAÇÃO

### Ver Logs

```bash
# Último log do dia
cat logs/sync_$(date +%Y-%m-%d).log

# Acompanhar em tempo real
tail -f logs/sync_$(date +%Y-%m-%d).log

# Ver todos os erros de hoje
grep ERROR logs/sync_$(date +%Y-%m-%d).log

# Ver erros de último mês
grep ERROR logs/sync_*.log | head -50
```

### Analisar Resultados

```sql
-- Contar registros sincronizados
SELECT 
    (SELECT COUNT(*) FROM course_categories) as categorias,
    (SELECT COUNT(*) FROM course_modalities) as modalidades,
    (SELECT COUNT(*) FROM courses) as cursos,
    (SELECT COUNT(*) FROM course_curriculum) as disciplinas;
```

---

## 8️⃣ TROUBLESHOOTING

### View Não Encontrada
```
❌ Se ver erro: "Table or view 'site.categorias_site' doesn't exist"

✅ Solução: Criar view no banco remoto (veja seção 1)
```

### Conexão Remota Falha
```
❌ Se ver erro: "Can't connect to MySQL server"

✅ Solução:
1. Verificar IP correto: 143.0.121.152
2. Verificar porta 3306 aberta
3. Verificar credenciais
4. Fazer ping: ping 143.0.121.152
```

### Nenhum Dado Sincronizado
```
❌ Se sincronização executa mas não insere dados

✅ Solução:
1. Verificar se views têm dados: SELECT COUNT(*) FROM categorias_site
2. Verificar permissões: GRANT SELECT ON site.* TO 'site_faesma'@'%'
3. Verificar tabelas locais existem
```

---

## 9️⃣ MANUTENÇÃO PERIÓDICA

### Limpeza de Logs (Opcional)

```bash
# Manter apenas últimos 30 dias
find logs/ -name "sync_*.log" -mtime +30 -delete

# Ver tamanho dos logs
du -sh logs/
```

### Backup Antes de Sincronização

```bash
# Backup do banco local
mysqldump -u root faesma_db > backup_$(date +%Y%m%d).sql

# Restaurar se necessário
mysql -u root faesma_db < backup_20260122.sql
```

---

## 🔟 VERIFICAÇÃO FINAL

Checklist para confirmar funcionamento:

- ✅ Views remotas criadas
- ✅ Conexão local testada
- ✅ Conexão remota testada
- ✅ Diretório `logs/` criado
- ✅ `sync_test_complete.php` executa sem erros
- ✅ Dados aparecem em `course_categories`, etc
- ✅ Cron job agendado
- ✅ Logs sendo gerados

---

## 📋 CHECKLIST INSTALAÇÃO

```
[ ] 1. Preparar banco remoto
    [ ] Criar view categorias_site
    [ ] Criar view modalidades_site
    [ ] Criar view curriculo_site
    [ ] Verificar permissões SQL

[ ] 2. Verificar configuração local
    [ ] Revisar config/config.php
    [ ] Revisar includes/db.php
    [ ] Testar conexões

[ ] 3. Criar diretório de logs
    [ ] mkdir -p logs/
    [ ] chmod 755 logs/

[ ] 4. Testar sincronização
    [ ] php sync_test_complete.php
    [ ] Verificar sem erros
    [ ] Ver dados inseridos

[ ] 5. Agendar cron
    [ ] Crontab (Linux) ou Task Scheduler (Windows)
    [ ] Testar agendamento

[ ] 6. Monitorar logs
    [ ] Primeira execução OK?
    [ ] Logs sendo gerados?
    [ ] Dados sendo sincronizados?

[ ] 7. Documentação
    [ ] Equipe informada
    [ ] Runbook criado
    [ ] Contato de suporte definido
```

---

## 📞 CONTATO E SUPORTE

Para dúvidas ou problemas:

1. Consultar [TROUBLESHOOTING_SINCRONIZACAO.md](TROUBLESHOOTING_SINCRONIZACAO.md)
2. Revisar logs em `logs/sync_*.log`
3. Executar `php sync_test_complete.php` para diagnóstico
4. Contatar administrador do banco remoto se necessário

---

**Status: ✅ Pronto para Produção**

