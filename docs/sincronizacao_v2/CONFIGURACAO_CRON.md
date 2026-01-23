# 🔄 Sincronização Automática - Setup Cron

## 📋 Visão Geral

O sistema de sincronização agora pode rodar:
- ✅ **Manual:** Via painel administrativo (botão "Atualizar Agora")
- ✅ **Automático:** Todos os dias às 02:00 da manhã

---

## 🔐 Acesso ao Painel Administrativo

### URL
```
http://localhost/projeto5/admin/
```

### Credenciais Padrão
- **Usuário:** `admin`
- **Senha:** `faesma2024!@#`

> ⚠️ **IMPORTANTE:** Altere a senha em produção!

### Recursos do Painel
- 🔄 Botão "Atualizar Agora" para sincronização manual
- 📊 Status da última sincronização
- 📈 Estatísticas de criados/atualizados/ignorados
- 🔒 Sessão segura com timeout de 30 minutos

---

## ⏰ Configurar Sincronização Automática

### Opção 1: Linux / Mac (Cron)

#### 1. Abrir editor crontab
```bash
crontab -e
```

#### 2. Adicionar linha ao final
```bash
# Sincronização FAESMA - Todos os dias às 02:00
0 2 * * * /usr/bin/php /var/www/html/projeto5/scripts/sync_cron.php >> /var/www/html/projeto5/logs/cron.log 2>&1
```

#### 3. Explicar campos
```
┌───────────── minuto (0 - 59)
│ ┌───────────── hora (0 - 23)
│ │ ┌───────────── dia do mês (1 - 31)
│ │ │ ┌───────────── mês (1 - 12)
│ │ │ │ ┌───────────── dia da semana (0 - 6) (domingo a sábado)
│ │ │ │ │
│ │ │ │ │
0 2 * * * comando
```

#### 4. Verificar se está funcionando
```bash
# Ver logs do cron
tail -f /var/www/html/projeto5/logs/cron.log

# Listar crontabs instalados
crontab -l
```

---

### Opção 2: Windows (Task Scheduler)

#### 1. Abrir Agendador de Tarefas
- Pressione `Win + R`
- Digite `taskschd.msc` e pressione Enter

#### 2. Criar nova tarefa agendada
- Clique em "Criar Tarefa..." no painel direito
- Preencha os dados:

#### 3. Guia "Geral"
```
Nome: FAESMA - Sincronização de Cursos
Descrição: Sincroniza cursos automaticamente às 02:00
Segurança:
  ☐ Executar apenas quando o usuário está conectado
  ☑ Executar se o usuário estiver conectado ou não
Privilégios: ☑ Executar com privilégios mais altos
```

#### 4. Guia "Gatilhos"
- Clique em "Novo..."
- **Configuração:**
  - Tipo: "Diariamente"
  - Hora de início: 02:00:00
  - Repetir a tarefa a cada: 1 dia

#### 5. Guia "Ações"
- Clique em "Novo..."
- **Configuração:**
  - Programa/script: `C:\xampp\php\php.exe`
  - Adicionar argumentos: `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`
  - Iniciar em: `C:\xampp\htdocs\projeto5\scripts`

#### 6. Testar
```
# No Prompt de Comando (cmd.exe)
C:\xampp\php\php.exe C:\xampp\htdocs\projeto5\scripts\sync_cron.php
```

---

## 📝 Verificar Logs

Os logs de sincronização são salvos em:
```
logs/sync_YYYY-MM-DD.log
```

Exemplo de conteúdo:
```
[2026-01-22 02:00:01] [INFO] === INICIANDO SINCRONIZAÇÃO AUTOMÁTICA ===
[2026-01-22 02:00:01] [INFO] Conectando à view remota...
[2026-01-22 02:00:05] [SUCCESS] ✅ Sincronização concluída com sucesso!
[2026-01-22 02:00:05] [SUCCESS]    - Cursos criados: 5
[2026-01-22 02:00:05] [SUCCESS]    - Cursos atualizados: 12
[2026-01-22 02:00:05] [SUCCESS]    - Cursos ignorados: 2
[2026-01-22 02:00:05] [SUCCESS]    - Total processado: 19
```

---

## 🛠️ Troubleshooting

### Problema: Cron não está executando

**Causa 1: PHP não encontrado**
```bash
# Verificar caminho do PHP
which php
# Resultado: /usr/bin/php

# Usar no crontab:
0 2 * * * /usr/bin/php /path/to/script.php
```

**Causa 2: Permissões incorretas**
```bash
# Dar permissão de leitura/escrita ao diretório logs
chmod 755 /var/www/html/projeto5/logs
chmod 755 /var/www/html/projeto5/scripts
```

**Causa 3: Variáveis de ambiente**
```bash
# Adicionar ao crontab com PATH completo
0 2 * * * /usr/bin/env php /var/www/html/projeto5/scripts/sync_cron.php
```

### Problema: Windows Task Scheduler não executa

**Solução:**
1. Verificar se a tarefa está ativada
2. Clicar em "Executar" para testar manualmente
3. Ver histórico da tarefa para erros
4. Verificar se `php.exe` está no PATH ou usar caminho absoluto

### Problema: "Permissão negada" no Linux

```bash
# Dar permissão ao arquivo
chmod +x /var/www/html/projeto5/scripts/sync_cron.php

# Testar diretamente
/usr/bin/php /var/www/html/projeto5/scripts/sync_cron.php
```

---

## 📱 Integração com Task Manager (Avançado)

Para notificações quando a sincronização falhar:

### Linux
```bash
# Adicionar verificação ao crontab
0 2 * * * /usr/bin/php /path/to/sync_cron.php || echo "FAESMA Sync Failed" | mail -s "Alert" admin@example.com
```

### Windows
Configurar a tarefa para enviar email em caso de falha (ver opções de "Ações" no Agendador).

---

## 🔒 Segurança

### Proteger arquivo de script
```bash
# Linux/Mac
chmod 600 /var/www/html/projeto5/scripts/sync_cron.php
chown www-data:www-data /var/www/html/projeto5/scripts/sync_cron.php
```

### Limpar logs antigos
Adicionar script para remover logs com mais de 30 dias:
```bash
# No crontab:
0 3 * * * find /var/www/html/projeto5/logs -name "sync_*.log" -mtime +30 -delete
```

---

## 📊 Monitoramento

### Criar script de status
```php
<?php
$logFile = 'logs/sync_' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    if (strpos($content, 'FINALIZADA COM SUCESSO') !== false) {
        echo "✅ Status: Sincronização de hoje foi bem-sucedida";
    } else {
        echo "❌ Status: Sincronização de hoje falhou ou ainda não rodou";
    }
} else {
    echo "⚠️ Status: Sem sincronização registrada para hoje";
}
?>
```

---

## ✅ Checklist Final

- [ ] Painel administrativo acessível em `/admin/`
- [ ] Login funciona com credenciais corretas
- [ ] Botão "Atualizar Agora" funciona
- [ ] Cron/Task está configurado para rodar às 02:00
- [ ] Logs estão sendo criados em `logs/sync_*.log`
- [ ] Pelo menos um teste de sincronização bem-sucedido
- [ ] Senha padrão alterada em produção
- [ ] Permissões de arquivo/diretório corretas

---

**Versão:** 1.0
**Data:** 22 de janeiro de 2026
**Status:** ✅ Pronto para produção
