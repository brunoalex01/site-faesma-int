$action = New-ScheduledTaskAction -Execute "curl.exe" `
  -Argument "http://localhost/projeto5/teste.php"

$trigger = New-ScheduledTaskTrigger -Daily -At 2am

Register-ScheduledTask -TaskName "FAESMA Sync" `
  -Action $action `
  -Trigger $trigger `
  -RunLevel Highest
