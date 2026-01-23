#!/bin/bash
# sync_cron_setup.sh
# Script para configurar sincronização automática via cron job

# CONFIGURAÇÃO DE CRON JOB PARA SINCRONIZAÇÃO AUTOMÁTICA
# Este arquivo contém exemplos de como configurar a sincronização automática

# ============================================================================
# OPÇÃO 1: LINUX/MAC - Adicionar ao Crontab
# ============================================================================

# Abra o crontab:
# crontab -e

# Adicione uma das seguintes linhas (escolha a frequência desejada):

# Sincronizar uma vez por dia às 2h da manhã (RECOMENDADO)
# 0 2 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1

# Sincronizar a cada 6 horas
# 0 */6 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1

# Sincronizar a cada hora
# 0 * * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1

# Sincronizar a cada 30 minutos
# */30 * * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1

# Sincronizar com log detalhado
# 0 2 * * * curl http://localhost/projeto5/teste.php >> /var/log/faesma_sync.log 2>&1

# ============================================================================
# OPÇÃO 2: LINUX/MAC - Verificar Crontab
# ============================================================================

# Listar cron jobs configurados:
# crontab -l

# ============================================================================
# OPÇÃO 3: WINDOWS - Task Scheduler PowerShell
# ============================================================================

# Crie um arquivo: sync_scheduler.ps1

# $action = New-ScheduledTaskAction -Execute "curl.exe" `
#     -Argument "http://localhost/projeto5/teste.php"
# 
# $trigger = New-ScheduledTaskTrigger -Daily -At 2am
# 
# Register-ScheduledTask -TaskName "FAESMA Sync" `
#     -Action $action `
#     -Trigger $trigger `
#     -RunLevel Highest

# Ou execute via PowerShell (como administrador):
# powershell -ExecutionPolicy Bypass -File sync_scheduler.ps1

# ============================================================================
# OPÇÃO 4: DOCKER - Adicionar ao Dockerfile
# ============================================================================

# FROM php:8.2-apache
# 
# # Instalar curl
# RUN apt-get update && apt-get install -y curl
# 
# # Adicionar script de sincronização
# COPY sync_cron.sh /usr/local/bin/
# RUN chmod +x /usr/local/bin/sync_cron.sh
# 
# # Configurar cron
# RUN echo "0 2 * * * /usr/local/bin/sync_cron.sh" | crontab -

# ============================================================================
# OPÇÃO 5: VERIFICAR EXECUÇÃO
# ============================================================================

# Depois de configurar, você pode verificar:

# No Linux/Mac:
# - Verifique os logs: tail -f /var/log/faesma_sync.log
# - Monitore o sistema: watch 'curl http://localhost/projeto5/teste.php'

# No Windows:
# - Abra Task Scheduler
# - Procure por "FAESMA Sync"
# - Clique direito > "Run" para executar manualmente

# ============================================================================
# TESTE MANUAL
# ============================================================================

# Antes de configurar cron, teste manualmente:

# 1. Linux/Mac/PowerShell:
curl http://localhost/projeto5/teste.php

# 2. Navegador:
# Acesse: http://localhost/projeto5/teste.php

# 3. PHP CLI:
# php -r "include 'teste.php';"

# ============================================================================
# MONITORAMENTO
# ============================================================================

# Para ver todas execuções de cron:
# grep CRON /var/log/syslog  (Linux)
# log stream --predicate 'process == "cron"'  (Mac)

# Para verificar última execução:
# cat logs/last_sync.txt

# Para ver histórico completo:
# tail -100 logs/sync.log

# ============================================================================
# TROUBLESHOOTING
# ============================================================================

# Se cron não está executando:
#
# 1. Verifique se cron está rodando:
#    sudo service cron status
#
# 2. Verifique permissões:
#    ls -l /var/log/syslog
#
# 3. Teste manualmente:
#    curl http://localhost/projeto5/teste.php
#
# 4. Verifique sintaxe:
#    crontab -l
#
# 5. Adicione log mais detalhado:
#    0 2 * * * /usr/bin/curl -v http://localhost/projeto5/teste.php >> /tmp/sync.log 2>&1

# ============================================================================
# ALTERNATIVA: USAR wget EM VEZ DE curl
# ============================================================================

# Se curl não estiver disponível, use wget:
# 0 2 * * * wget -O /dev/null http://localhost/projeto5/teste.php > /dev/null 2>&1

# ============================================================================
# ALTERNATIVA: USAR PHP DIRETAMENTE
# ============================================================================

# Se preferir executar PHP diretamente:
# 0 2 * * * /usr/bin/php /var/www/html/projeto5/teste.php > /dev/null 2>&1

# ============================================================================
# APÓS CONFIGURAR
# ============================================================================

# 1. Aguarde o horário agendado
# 2. Verifique os logs em logs/sync.log
# 3. Confirme em logs/last_sync.txt o timestamp
# 4. Verifique o banco de dados se dados foram atualizados
# 5. (Opcional) Configure alertas por email em caso de erro

echo "Guia de configuração criado com sucesso!"
echo "Escolha uma das opções acima e configure de acordo com seu ambiente."
