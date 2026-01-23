# 🚀 QUICK START - Sincronização Automática FAESMA

## ⚡ Em 3 Passos

### Passo 1: Acessar a Página
```
Navegue para: http://localhost/projeto5/teste.php
```

### Passo 2: Verificar Resultados
```
✅ Ver estatísticas (Criados, Atualizados, Pulados, Erros)
✅ Revisar log detalhado
✅ Confirmar cursos sincronizados
```

### Passo 3: Automatizar (Opcional)
```bash
# Adicione ao crontab (executa diariamente às 2h):
0 2 * * * curl http://localhost/projeto5/teste.php > /dev/null 2>&1
```

---

## 📍 Locais Importantes

| Item | Localização |
|------|---|
| 🔄 **Sincronização** | `teste.php` |
| 🔧 **Mapeamento** | `includes/RemoteSyncMapping.php` |
| ⚙️ **Serviço** | `includes/RemoteSyncService.php` |
| 🔑 **Credenciais Remoto** | `includes/db.php` |
| 📊 **Logs** | `logs/sync.log` |
| 📋 **Documentação** | `SYNC_USAGE.md` |
| 🧪 **Testes** | `test_sync.php` |

---

## 🎯 Tarefas Comuns

### Executar Sincronização Manualmente
```
1. Abra navegador
2. Acesse: http://localhost/projeto5/teste.php
3. Aguarde conclusão
4. Revise resultados
```

### Ver Últimas Sincronizações
```bash
tail -20 logs/sync.log
```

### Verificar Timestamp da Última Exec
```bash
cat logs/last_sync.txt
```

### Rodar Testes
```bash
php test_sync.php
```

### Sincronizar via CLI
```bash
php sync_courses.php
```

### Ajustar Credenciais Remoto
```bash
Editar: includes/db.php
```

---

## 🔍 Campos Sincronizados (21 Total)

```
Identifiers:
  • id_curso → cod_externo
  • cd_oferta → codigo_curso

Básico:
  • nome_curso → nome
  • descricao → descricao_curta
  • descricao_completa → descricao_detalhada

Estrutura:
  • duracao_meses → duracao_meses
  • duracao_texto → duracao_texto
  • carga_horaria → carga_horaria

Conteúdo:
  • objetivos → objetivos
  • perfil_egresso → perfil_egresso
  • mercado_trabalho → mercado_trabalho
  • publico_alvo → publico_alvo

Administrativo:
  • coordenador → coordenador_nome
  • imagem_destaque → imagem_url
  • nota_mec → nota_mec
  • valor_mensalidade → valor_mensalidade
  • vagas_disponiveis → vagas_disponiveis

Especial:
  • tcc_obrigatorio → tcc_obrigatorio (boolean)
  • inscricao_online → inscricao_online (boolean)
  • link_oferta → link_oferta
  • status → status_remoto (mapeado)
```

---

## 📊 Entender os Números

### Criados: 5
Novos cursos inseridos no banco local

### Atualizados: 3
Cursos existentes com dados modificados

### Pulados: 2
Cursos já sincronizados sem alterações

### Erros: 0
Problemas durante sincronização

---

## 🚨 Problemas Rápidos

| Problema | Solução |
|----------|---------|
| "Erro na Sincronização" | Verifique `includes/db.php` |
| "Nenhum curso criado" | Confirme se view remota tem dados |
| Página branca | Abra `logs/sync.log` |
| Sem dados na view | Teste: `mysql -h 143.0.121.152 -u user -p site` |
| Cron não funciona | Use: `0 2 * * * curl http://localhost/projeto5/teste.php` |

---

## 🔐 Segurança

✅ Prepared Statements (SQL Injection)
✅ Campos protegidos (id, slug, created_at)
✅ Detecção de duplicatas
✅ Validação de dados
✅ Log de todas operações

---

## 📈 Performance

- **Max por execução:** 500 registros
- **Tempo:** ~2-5 segundos
- **Memória:** ~5-10 MB
- **Ideal:** Executar 1x por dia (off-peak)

---

## 🔗 URLs Úteis

```
Sincronização: http://localhost/projeto5/teste.php
Testes:        http://localhost/projeto5/test_sync.php
Banco Remoto:  143.0.121.152:3306 (site.cursos_site)
Banco Local:   localhost (faesma_db.courses)
```

---

## 📞 Checklist Instalação

```
☑ arquivo teste.php modificado
☑ RemoteSyncMapping.php criado
☑ RemoteSyncService.php criado
☑ includes/db.php com credenciais
☑ logs/ diretório existe
☑ Banco local faesma_db.courses criado
☑ Banco remoto site.cursos_site acessível
☑ testes passando (7/7)
☑ teste.php acessível pelo navegador
☑ Sincronização funcionando
```

---

## 🎓 Próximas Leituras

1. **SYNC_USAGE.md** - Guia completo
2. **docs/SYNC_ARCHITECTURE.md** - Como funciona
3. **docs/REMOTE_SYNC_GUIDE.md** - Detalhes técnicos
4. **RESUMO_FINAL.md** - Visão geral do projeto

---

## 💡 Dicas

1. **Teste primeiro manualmente** antes de agendar cron
2. **Revise logs regularmente** para detectar problemas
3. **Faça backup** antes de primeira sincronização em produção
4. **Monitore banco remoto** para detectar mudanças
5. **Considere executar** no horário de baixo uso (ex: 2h da manhã)

---

## 🆘 Precisa de Ajuda?

1. Verifique a página: `teste.php` - vê tudo!
2. Leia o log: `logs/sync.log`
3. Teste manualmente: `php test_sync.php`
4. Consulte docs: `SYNC_USAGE.md`
5. Valide credenciais: `includes/db.php`

---

**Versão:** 1.0
**Status:** ✅ Pronto para Uso
**Atualizado:** 2024

🚀 **Você está pronto! Acesse teste.php e aproveite!**
