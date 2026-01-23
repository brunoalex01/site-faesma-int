# 🌐 URLS E ACESSOS RÁPIDOS

## 🚀 ACESSO IMEDIATO

| Função | URL | Descrição |
|--------|-----|-----------|
| **Teste do Sistema** | http://localhost/projeto5/admin/test.php | Verificar instalação |
| **Fazer Login** | http://localhost/projeto5/admin/login.php | Página de login |
| **Painel Principal** | http://localhost/projeto5/admin/ | Dashboard (protegido) |

## 🔐 Credenciais de Acesso

```
Usuário: admin
Senha:   faesma2024!@#
```

## 📁 Arquivos Importantes

| Arquivo | Propósito |
|---------|-----------|
| [admin/index.php](admin/index.php) | 🎯 Painel administrativo |
| [admin/login.php](admin/login.php) | 🔐 Página de login |
| [admin/test.php](admin/test.php) | 🧪 Teste de sistema |
| [includes/AdminAuth.php](includes/AdminAuth.php) | 🔒 Sistema de autenticação |
| [scripts/sync_cron.php](scripts/sync_cron.php) | ⏰ Script cron automático |
| [docs/CONFIGURACAO_CRON.md](docs/CONFIGURACAO_CRON.md) | 📖 Documentação completa |

## 📖 Documentação

| Arquivo | Leitura Recomendada |
|---------|---------------------|
| [COMECE_AQUI_ADMIN.md](COMECE_AQUI_ADMIN.md) | ⭐ **COMECE AQUI** - 5 minutos |
| [AREA_ADMINISTRATIVA_README.md](AREA_ADMINISTRATIVA_README.md) | Guia completo - 15 minutos |
| [docs/CONFIGURACAO_CRON.md](docs/CONFIGURACAO_CRON.md) | Setup cron - 10 minutos |
| [ADMIN_SETUP.txt](ADMIN_SETUP.txt) | Quick reference - 3 minutos |
| [SETUP_COMPLETO.txt](SETUP_COMPLETO.txt) | Resumo executivo - 5 minutos |

## 🧪 Teste Rápido (60 segundos)

```
1. Abrir em navegador:
   http://localhost/projeto5/admin/test.php

2. Deve mostrar: ✅ 12/12 testes passaram

3. Se tudo OK, ir para:
   http://localhost/projeto5/admin/

4. Login:
   admin / faesma2024!@#

5. Clicar: 🔄 Atualizar Agora

6. Ver resultados em tempo real!
```

## 📊 Estrutura de Logs

```
logs/
├── sync_2026-01-22.log
├── sync_2026-01-21.log
├── sync_2026-01-20.log
└── ...
```

**Exemplo de conteúdo:**
```
[2026-01-22 02:00:01] [INFO] === INICIANDO SINCRONIZAÇÃO AUTOMÁTICA ===
[2026-01-22 02:00:05] [SUCCESS] ✅ Sincronização concluída com sucesso!
[2026-01-22 02:00:05] [SUCCESS]    - Cursos criados: 5
[2026-01-22 02:00:05] [SUCCESS]    - Cursos atualizados: 12
```

## 🔑 Alterar Senha (Produção)

1. Abrir: `includes/AdminAuth.php`
2. Ir para linha ~19
3. Alterar: `'faesma2024!@#'` para sua senha
4. Salvar

## ⏰ Configurar Cron

### Linux/Mac
```bash
crontab -e
# Adicionar:
0 2 * * * /usr/bin/php /var/www/html/projeto5/scripts/sync_cron.php
```

### Windows
1. Win + R → `taskschd.msc`
2. Criar tarefa para 02:00
3. Executar: `C:\xampp\php\php.exe`
4. Argumento: `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`

## 📞 Suporte Rápido

### Erro de Login
```
Solução: Verificar arquivo includes/AdminAuth.php linha 19
```

### Sincronização não funciona
```
Ver: logs/sync_2026-01-22.log
```

### Cron não executa
```
Linux:   which php
Windows: taskschd.msc
```

---

**Última atualização:** 22 de janeiro de 2026
**Status:** ✅ Pronto para uso
