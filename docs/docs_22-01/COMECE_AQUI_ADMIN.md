# 🎯 GUIA RÁPIDO - ÁREA ADMINISTRATIVA

## ⚡ Comece Agora (3 cliques!)

### 1️⃣ Teste o Sistema
**URL:** http://localhost/projeto5/admin/test.php

Verificará se tudo está instalado corretamente.

### 2️⃣ Fazer Login
**URL:** http://localhost/projeto5/admin/

```
Usuário: admin
Senha: faesma2024!@#
```

### 3️⃣ Sincronizar Agora
Clique no botão **"🔄 Atualizar Agora"** no painel

---

## 📂 Estrutura Criada

```
projeto5/
├── admin/                          ← 🔐 ÁREA ADMINISTRATIVA
│   ├── login.php                   ← Página de login
│   ├── index.php                   ← 🎯 Painel principal (AQUI!)
│   └── test.php                    ← Teste de sistema
│
├── includes/
│   ├── AdminAuth.php               ← Autenticação
│   ├── RemoteSyncService.php       ← (já existia)
│   └── RemoteSyncMapping.php       ← (já existia)
│
├── scripts/
│   ├── sync_cron.php               ← ⏰ Script automático
│   └── (já existia)
│
├── logs/                           ← 📝 Logs de sincronização
│   └── sync_YYYY-MM-DD.log
│
└── docs/
    └── CONFIGURACAO_CRON.md        ← 📖 Guia completo
```

---

## 🔑 Credenciais

| Campo | Valor |
|-------|-------|
| **Usuário** | `admin` |
| **Senha** | `faesma2024!@#` |

⚠️ **Mudar em produção!**

---

## ⏰ Configurar Automação (2 opções)

### Opção A: Linux/Mac
```bash
crontab -e
# Adicionar:
0 2 * * * /usr/bin/php /var/www/html/projeto5/scripts/sync_cron.php
```

### Opção B: Windows
1. Abrir: Task Scheduler (Win + R → `taskschd.msc`)
2. Criar tarefa: 02:00 todos os dias
3. Executar: `php.exe` com argumento `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`

**Detalhes em:** `docs/CONFIGURACAO_CRON.md`

---

## ✨ Recursos

- ✅ **Login/Logout** - Autenticação segura
- ✅ **Manual** - Botão "Atualizar Agora"
- ✅ **Automático** - Roda às 02:00 da madrugada
- ✅ **Logs** - Histórico completo de sincronizações
- ✅ **Dashboard** - Estatísticas em tempo real
- ✅ **Responsivo** - Funciona em celular/tablet

---

## 🧪 Testar Tudo em 1 Minuto

```
1. Abrir: http://localhost/projeto5/admin/test.php
   → Verificar se tudo está ✅

2. Abrir: http://localhost/projeto5/admin/
   → Fazer login

3. Clicar: "🔄 Atualizar Agora"
   → Sincronizar cursos

4. Abrir: C:\xampp\htdocs\projeto5\logs\sync_2026-01-22.log
   → Ver resultados
```

---

## 🆘 Problemas?

### Erro de login
- Verificar user/senha em AdminAuth.php
- Limpar cookies do navegador

### Sincronização não funciona
- Ver logs em `logs/sync_*.log`
- Verificar conexão com banco remoto

### Cron não executa no Windows
- Verificar se Task Scheduler está ativo
- Usar caminho COMPLETO do php.exe

**Ajuda completa em:** `docs/CONFIGURACAO_CRON.md`

---

## 📞 Arquivos Importantes

| Arquivo | Propósito |
|---------|-----------|
| `admin/index.php` | 🎯 Painel principal |
| `includes/AdminAuth.php` | 🔐 Autenticação |
| `scripts/sync_cron.php` | ⏰ Execução automática |
| `docs/CONFIGURACAO_CRON.md` | 📖 Documentação |
| `AREA_ADMINISTRATIVA_README.md` | 📚 Guia completo |

---

## ✅ Checklist

- [ ] Acessei http://localhost/projeto5/admin/test.php
- [ ] Todos os testes passaram ✅
- [ ] Fiz login com admin/faesma2024!@#
- [ ] Cliquei "Atualizar Agora" e funcionou
- [ ] Alterei a senha padrão
- [ ] Configurei sincronização automática (cron)
- [ ] Verifiquei logs em `logs/sync_*.log`

---

**Data:** 22 de janeiro de 2026  
**Status:** ✅ Pronto para uso
