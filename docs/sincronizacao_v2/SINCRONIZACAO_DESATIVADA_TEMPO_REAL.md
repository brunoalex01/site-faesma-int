# ⚠️ Sincronização em Tempo Real - DESATIVADA

## Data: 23 de janeiro de 2026

### O que foi alterado?

A sincronização **em tempo real** da view remota foi **desativada**. O site agora consome dados **APENAS do banco local sincronizado**.

### Funções Desativadas

As seguintes funções foram desativadas em `includes/functions.php`:

1. **`getCoursesFromView()`** → Use `getCourses()` em vez disso
2. **`getCourseFromView()`** → Use `getCourse()` em vez disso  
3. **`getCourseCountFromView()`** → Use `getCourseCount()` em vez disso
4. **`getCourseCategoriesFromView()`** → Use `getCourseCategories()` em vez disso
5. **`getCourseModalitiesFromView()`** → Use `getCourseModalities()` em vez disso

Todas essas funções agora retornam dados do banco local e registram um AVISO no log.

### Como a Sincronização Funciona Agora?

A sincronização ocorre em **apenas 2 cenários**:

#### 1️⃣ **Sincronização Manual** (Sob Demanda)
- Acessar: `http://seu-site.com/admin/` (Painel Administrativo)
- Clicar em: **"🔄 Atualizar Agora"**
- Resultado: Sincroniza imediatamente com a view remota

#### 2️⃣ **Sincronização Automática** (Rotina Agendada)
- **Hora**: Todos os dias às 02:00 AM
- **Script**: `scripts/sync_cron.php`
- **Método**: Cron (Linux/Mac) ou Task Scheduler (Windows)

### Mudanças no Painel Administrativo

Um aviso visual foi adicionado ao painel (`admin/index.php`):

```
📌 Informação Importante
Sincronização em Tempo Real Desativada!
```

Este aviso informa aos administradores sobre a nova política de sincronização.

### Logs

Sempre que uma função desativada for chamada, um AVISO é registrado:

```php
AVISO: getCoursesFromView() foi desativada. Use getCourses() para dados do banco local.
```

Verifique em: `logs/php-errors.log`

### Benefícios

✅ **Performance**: Sem consultas remotas a cada página  
✅ **Confiabilidade**: Dados locais garantem consistência  
✅ **Controle**: Sincronização sob demanda ou agendada  
✅ **Segurança**: Menos conexões externas

### Próximos Passos

1. ✅ Verificar se todas as páginas funcionam corretamente
2. ✅ Testar a sincronização manual no painel administrativo
3. ✅ Configurar o Cron/Task Scheduler para sincronização automática
4. ✅ Monitorar os logs para avisos

---

**Dúvidas?** Consulte: [SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md](docs/sincronizacao_v2/SINCRONIZACAO_EXTRACAO_CURSOS_SITE.md)
