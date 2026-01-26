# Passo a passo: executar o cron diário no Windows (2h)

Este guia configura uma tarefa agendada no Windows para executar o script
de sincronização diariamente às 02:00.

## 1) Verifique pré‑requisitos
- Tenha o `curl.exe` disponível no Windows (já vem no Windows 10/11).
- Garanta que o XAMPP/Apache esteja rodando.
- Confirme que a URL funciona no navegador:
  - http://localhost/projeto5/teste.php

## 2) Abra o PowerShell como Administrador
1. Clique no menu Iniciar.
2. Digite “PowerShell”.
3. Clique com o botão direito e selecione “Executar como administrador”.

## 3) Crie/edite o script do Agendador
Abra o arquivo `sync_scheduler.ps1` na raiz do projeto e cole:

```powershell
$action = New-ScheduledTaskAction -Execute "curl.exe" `
  -Argument "http://localhost/projeto5/teste.php"

$trigger = New-ScheduledTaskTrigger -Daily -At 2am

Register-ScheduledTask -TaskName "FAESMA Sync" `
  -Action $action `
  -Trigger $trigger `
  -RunLevel Highest
```

## 4) Execute o script de agendamento
No PowerShell (Administrador), rode:

```powershell
powershell -ExecutionPolicy Bypass -File "C:\xampp\htdocs\projeto5\sync_scheduler.ps1"
```

## 5) Verifique a tarefa criada
1. Abra o “Agendador de Tarefas” (Task Scheduler).
2. Procure por **FAESMA Sync** na Biblioteca.
3. Clique com o botão direito > **Run** para testar.

## 6) Validar logs
Após a execução automática, confira:
- `C:\xampp\htdocs\projeto5\logs\sync.log`
- `C:\xampp\htdocs\projeto5\logs\last_sync.txt`

## 7) Ajustar horário (opcional)
Se quiser outro horário, altere o `-At 2am` no `sync_scheduler.ps1`
para o horário desejado (ex.: `-At 03:30`).
