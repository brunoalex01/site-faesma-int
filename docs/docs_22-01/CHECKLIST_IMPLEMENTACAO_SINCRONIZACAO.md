# ✅ Checklist de Implementação - Sincronização v2.0

## 📋 Pré-Requisitos

- [ ] Acesso ao servidor remoto com a view `site.cursos_site`
- [ ] Banco de dados local `faesma_db` criado
- [ ] Tabelas locais existentes: `courses`, `course_categories`, `course_modalities`
- [ ] PHP CLI disponível (para testes e cron)
- [ ] Permissões de escrita no diretório `/logs`

## 🔧 Fase 1: Configuração

- [x] Banco de dados local configurado
- [x] Conexão remota testada em `includes/db.php`
- [x] RemoteSyncService implementado
- [x] Métodos de sincronização criados

**Ação requerida:** Nenhuma, já configurado

## 🎯 Fase 2: Teste Manual

### Antes de Sincronizar

**1. Verificar Conexões**
```bash
# Verificar conexão local
php -r "require 'includes/Database.php'; echo Database::getInstance()->getConnection() ? 'OK' : 'ERRO';"

# Verificar conexão remota
php -r "require 'includes/db.php'; echo db() ? 'OK' : 'ERRO';"
```

**Status:**
- [ ] Conexão local OK
- [ ] Conexão remota OK

**2. Verificar View Remota**
```bash
# Verificar se view existe
mysql -h 143.0.121.152 -u site_faesma -pYwsGps1rBusBmWvPrzj9 -e "SELECT COUNT(*) FROM site.cursos_site;" site
```

**Resultado esperado:** Número > 0

- [ ] View `cursos_site` acessível
- [ ] View contém dados (COUNT > 0)

**3. Verificar Campos Necessários**
```bash
# Verificar campos de categoria
mysql -h 143.0.121.152 -u site_faesma -pYwsGps1rBusBmWvPrzj9 -e "SELECT categoria_nome, categoria_slug FROM site.cursos_site LIMIT 1;" site

# Verificar campos de modalidade
mysql -h 143.0.121.152 -u site_faesma -pYwsGps1rBusBmWvPrzj9 -e "SELECT modalidade_nome, modalidade_slug FROM site.cursos_site LIMIT 1;" site
```

- [ ] Campo `categoria_nome` existe
- [ ] Campo `modalidade_nome` existe
- [ ] Campos contêm dados

**4. Verificar Banco Local**
```bash
# Contar registros antes
mysql -u root -e "SELECT COUNT(*) FROM faesma_db.course_categories;"
mysql -u root -e "SELECT COUNT(*) FROM faesma_db.course_modalities;"
mysql -u root -e "SELECT COUNT(*) FROM faesma_db.courses;"
```

- [ ] course_categories: `_____` registros
- [ ] course_modalities: `_____` registros
- [ ] courses: `_____` registros

### Executar Teste de Sincronização

```bash
# No diretório do projeto
php sync_test_validacao.php
```

**Saída esperada:**
```
✅ Teste completo de sincronização - FAESMA

ℹ️  Conectando ao banco de dados local...
✅ Banco de dados local conectado

... [mais linhas] ...

✅ Sincronização de categorias concluída com sucesso!
✅ Sincronização de modalidades concluída com sucesso!
✅ Sincronização de cursos concluída com sucesso!
```

**Checklist do Teste:**
- [ ] Script executa sem erros PHP
- [ ] Categorias são extraídas (número > 0)
- [ ] Modalidades são extraídas (número > 0)
- [ ] Cursos são sincronizados (número > 0)
- [ ] Nenhuma duplicata detectada
- [ ] Relacionamentos estão válidos

### Verificar Dados Sincronizados

```bash
# Contar registros depois
mysql -u root -e "SELECT COUNT(*) as total FROM faesma_db.course_categories;" 
# Deve ser > 0

mysql -u root -e "SELECT COUNT(*) as total FROM faesma_db.course_modalities;"
# Deve ser > 0

mysql -u root -e "SELECT COUNT(*) as total FROM faesma_db.courses;"
# Deve ser > 0

# Verificar integridade
mysql -u root -e "
  SELECT COUNT(*) as cursos_sem_categoria 
  FROM faesma_db.courses 
  WHERE category_id IS NULL;
"
# Deve ser perto de 0

# Verificar slugs
mysql -u root -e "
  SELECT id, nome, slug FROM faesma_db.course_categories LIMIT 5;
"
# Slugs não devem ser NULL ou vazios
```

**Status:**
- [ ] Categorias criadas: `_____`
- [ ] Modalidades criadas: `_____`
- [ ] Cursos sincronizados: `_____`
- [ ] Cursos sem categoria: `_____` (idealmente 0)
- [ ] Slugs preenchidos: SIM/NÃO

## 🔄 Fase 3: Sincronização Repetida (Teste de Idempotência)

**Objetivo:** Garantir que sincronizações repetidas não criam duplicatas

```bash
# Executar teste novamente
php sync_test_validacao.php
```

**Esperado:**
- Números de "Criado" = 0 (já existem)
- Números de "Atualizado" = 0 (sem mudanças)
- Sem erros

**Status:**
- [ ] Teste executado novamente
- [ ] Sem novas duplicatas criadas
- [ ] Sem erros reportados

## 🌐 Fase 4: Verificar Consumo pelo Site

### Teste 1: Página de Cursos
```bash
# Abrir no navegador
http://localhost/projeto5/cursos.php
```

**Verificações:**
- [ ] Página carrega sem erros
- [ ] Cursos são exibidos
- [ ] Categorias funcionam (filtro)
- [ ] Modalidades funcionam (filtro)
- [ ] Paginação funciona

### Teste 2: Página de Detalhes
```bash
# Abrir no navegador (substitua ID por um válido)
http://localhost/projeto5/curso-detalhes.php?id=1
```

**Verificações:**
- [ ] Página carrega
- [ ] Detalhes do curso são exibidos
- [ ] Categoria está preenchida
- [ ] Modalidade está preenchida

### Teste 3: Índice Principal
```bash
# Abrir no navegador
http://localhost/projeto5/index.php
```

**Verificações:**
- [ ] Página carrega
- [ ] Cursos destacados aparecem
- [ ] Sem erros de conexão remota
- [ ] Performance OK (não aguarda servidor remoto)

## 📅 Fase 5: Configurar Sincronização Automática

### Opção A: Linux/macOS com Cron

```bash
# Editar crontab
crontab -e

# Adicionar linha (sincronizar diariamente às 2:00 AM)
0 2 * * * /usr/bin/php /path/to/projeto5/scripts/sync_cron.php >> /path/to/projeto5/logs/cron.log 2>&1
```

**Verificação:**
```bash
# Verificar se cron foi adicionado
crontab -l | grep sync

# Ver conteúdo do log depois de próxima execução agendada
tail -f /path/to/projeto5/logs/cron.log
```

**Status:**
- [ ] Cron adicionado com sucesso
- [ ] Executável testado manualmente: `php scripts/sync_cron.php`

### Opção B: Windows com Task Scheduler

1. [ ] Abrir Task Scheduler
2. [ ] Clicar em "Create Basic Task"
3. [ ] Nome: "FAESMA Sync"
4. [ ] Descrição: "Sincroniza dados de cursos remotos"
5. [ ] Trigger: Diário às 02:00
6. [ ] Action: Executar programa
   - Programa: `C:\xampp\php\php.exe`
   - Argumentos: `C:\xampp\htdocs\projeto5\scripts\sync_cron.php`
   - Iniciar em: `C:\xampp\htdocs\projeto5`
7. [ ] Salvar e testar

**Teste:**
```bash
# Abrir PowerShell como administrador
# Procurar pela tarefa
Get-ScheduledTask -TaskName "FAESMA Sync"

# Executar manualmente (opcional)
Start-ScheduledTask -TaskName "FAESMA Sync"
```

- [ ] Task criada com sucesso
- [ ] Task testada manualmente

## 📊 Fase 6: Monitoramento

### Verificar Logs

```bash
# Ver logs mais recentes
tail -n 50 logs/sync_*.log

# Ver erros apenas
grep ERROR logs/sync_*.log

# Contar linhas de sucesso
grep SUCCESS logs/sync_*.log | wc -l
```

**Status:**
- [ ] Logs sendo gerados
- [ ] Nenhum erro crítico
- [ ] Logs indicam sincronização bem-sucedida

### Monitorar Integridade

```bash
# Script de monitoramento (executar periodicamente)
mysql -u root -e "
SELECT 
  (SELECT COUNT(*) FROM faesma_db.courses) as total_cursos,
  (SELECT COUNT(*) FROM faesma_db.course_categories) as total_categorias,
  (SELECT COUNT(*) FROM faesma_db.course_modalities) as total_modalidades,
  (SELECT COUNT(*) FROM faesma_db.courses WHERE category_id IS NULL) as cursos_sem_categoria;
"
```

- [ ] Total de cursos estável
- [ ] Total de categorias estável
- [ ] Total de modalidades estável
- [ ] Cursos sem categoria = 0 ou muito baixo

## 🚨 Fase 7: Troubleshooting

### Problema: Sincronização não funciona

**Checklist:**
- [ ] Verificar conexão remota: `php -r "require 'includes/db.php'; var_dump(db()->query('SELECT 1'));"`
- [ ] Verificar se arquivo de configuração existe: `config/config.php`
- [ ] Verificar permissões de escrita em `logs/`
- [ ] Ver logs mais recentes para mensagens de erro
- [ ] Verificar se `RemoteSyncService.php` foi modificado corretamente

### Problema: Categorias não são sincronizadas

**Checklist:**
- [ ] Verificar se campo `categoria_nome` existe na view: 
  ```sql
  SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='site' AND TABLE_NAME='cursos_site';
  ```
- [ ] Verificar se há dados no campo: 
  ```sql
  SELECT DISTINCT categoria_nome FROM site.cursos_site LIMIT 10;
  ```
- [ ] Verificar logs de sincronização para mensagens de erro
- [ ] Executar teste manualmente com output: `php sync_test_validacao.php`

### Problema: Duplicatas em categorias

**Checklist:**
- [ ] Buscar duplicatas: 
  ```sql
  SELECT slug, COUNT(*) FROM course_categories 
  WHERE slug IS NOT NULL 
  GROUP BY slug HAVING COUNT(*) > 1;
  ```
- [ ] Se encontrar, limpar manualmente:
  ```sql
  -- Backup primeiro
  CREATE TABLE course_categories_backup AS SELECT * FROM course_categories;
  
  -- Deletar duplicatas mantendo a mais recente
  DELETE FROM course_categories 
  WHERE id NOT IN (
    SELECT MAX(id) FROM course_categories GROUP BY slug
  ) AND slug IS NOT NULL;
  ```
- [ ] Executar sincronização novamente

### Problema: Logs muito grandes

**Solução:**
```bash
# Arquivar logs antigos
gzip logs/sync_*.log

# Limpar logs com mais de 30 dias
find logs -name "sync_*.log" -mtime +30 -delete
```

- [ ] Logs arquivados/limpos

## ✅ Fase 8: Validação Final

### Checklist de Conclusão

**Funcionalidades:**
- [ ] Sincronização de categorias funciona
- [ ] Sincronização de modalidades funciona
- [ ] Sincronização de cursos funciona
- [ ] Deduplicação previne duplicatas
- [ ] Slugs são gerados automaticamente
- [ ] Logs são registrados corretamente

**Site:**
- [ ] Página de cursos carrega dados locais (não remoto)
- [ ] Filtros funcionam
- [ ] Detalhes de curso carregam
- [ ] Sem erros de conexão remota

**Automação:**
- [ ] Cron/Task Scheduler configurado
- [ ] Próxima execução agendada
- [ ] Logs gerados automaticamente

**Integridade:**
- [ ] Nenhuma duplicata
- [ ] Relacionamentos válidos
- [ ] Dados consistentes

### Assinatura de Conclusão

```
Data: _______________
Responsável: _______________
Observações: _______________
```

## 📝 Próximos Passos (Futuro)

- [ ] Se dados de currículo forem adicionados a view remota, implementar sincronização
- [ ] Adicionar API de consulta para aplicações terceiras
- [ ] Implementar cache em Redis (opcional, para melhor performance)
- [ ] Adicionar alertas por email em caso de erro na sincronização
- [ ] Criar dashboard de monitoramento

## 📞 Contato para Suporte

Para problemas ou dúvidas:
1. Verifique a documentação: `SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md`
2. Examine os logs: `logs/sync_*.log`
3. Execute teste manual: `php sync_test_validacao.php`
4. Verifique console do navegador para erros front-end

---

**Versão do Checklist:** 2.0  
**Última Atualização:** 2024  
**Status:** Pronto para Produção
