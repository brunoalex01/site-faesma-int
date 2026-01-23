# 🔍 CHECKLIST - Diagnóstico de Campos Obrigatórios

## Passo 1: Verificar a View Remota

```sql
-- Abra seu cliente MySQL (ex: HeidiSQL, Workbench, phpMyAdmin)
-- E execute esta query no banco REMOTO (143.0.121.152)

USE site;

-- Quantos registros têm dados incompletos?
SELECT COUNT(*) as total_incompletos 
FROM cursos_site 
WHERE (id_curso IS NULL OR id_curso = '')
   OR (nome_curso IS NULL OR nome_curso = '');

-- Exemplos de registros problemáticos
SELECT 
    id_curso,
    nome_curso,
    descricao,
    duracao_meses
FROM cursos_site 
WHERE (id_curso IS NULL OR id_curso = '')
   OR (nome_curso IS NULL OR nome_curso = '')
LIMIT 5;
```

---

## Passo 2: Verificar Quantos Registros SÃO Válidos

```sql
-- Quantos registros têm campos obrigatórios preenchidos?
SELECT COUNT(*) as registros_validos 
FROM cursos_site 
WHERE id_curso IS NOT NULL 
  AND id_curso != '' 
  AND nome_curso IS NOT NULL 
  AND nome_curso != '';

-- Exemplo deles
SELECT 
    id_curso,
    nome_curso,
    descricao
FROM cursos_site 
WHERE id_curso IS NOT NULL 
  AND id_curso != '' 
  AND nome_curso IS NOT NULL 
  AND nome_curso != ''
LIMIT 5;
```

---

## Passo 3: Testar a Sincronização

### Via Navegador
1. Abra: `http://localhost/projeto5/teste.php`
2. Aguarde a sincronização
3. Revise as estatísticas:
   - **Criados:** Novos cursos
   - **Atualizados:** Cursos modificados
   - **Pulados:** Registros incompletos
   - **Erros:** Problemas de sincronização

### Via Log
```bash
# Ver últimas operações
tail -50 logs/sync.log

# Procurar por registros pulados
grep "PULADO" logs/sync.log

# Ver apenas erros
grep "ERRO" logs/sync.log
```

---

## Passo 4: Interpretar os Resultados

### ✅ Tudo OK
```
Criados: 5
Atualizados: 3
Pulados: 0
Erros: 0
```
→ Todos os registros foram sincronizados com sucesso!

### ⚠️ Alguns Registros Pulados
```
Criados: 5
Atualizados: 3
Pulados: 2
Erros: 0
```
→ Existem 2 registros incompletos que foram automaticamente ignorados

### ❌ Muitos Registros Pulados
```
Criados: 0
Atualizados: 0
Pulados: 50
Erros: 0
```
→ A maioria dos registros está incompleta (ver opções abaixo)

---

## Opções de Ação

### OPÇÃO 1: Se 80%+ dos registros forem inválidos
**Problema:** A View contém muitos dados incompletos

**Soluções:**

a) **Corrigir a View Remota** (RECOMENDADO)
```sql
-- Remover registros sem ID
DELETE FROM cursos_site 
WHERE id_curso IS NULL OR id_curso = '';

-- Remover registros sem Nome
DELETE FROM cursos_site 
WHERE nome_curso IS NULL OR nome_curso = '';

-- Preencher campos vazios com valor padrão
UPDATE cursos_site 
SET id_curso = CONCAT('CURSO_', id) 
WHERE id_curso IS NULL OR id_curso = '';
```

b) **Contatar administrador da View**
   - Solicitar limpeza dos dados
   - Pedir para adicionar valores padrão
   - Validar dados de entrada

c) **Relaxar Validação** (não recomendado)
   - Contatar desenvolvedor do sistema
   - Modificar quais campos são obrigatórios

---

### OPÇÃO 2: Se poucos registros forem inválidos
**Problema:** Apenas alguns registros incompletos

**Solução:**

Isso é normal! O sistema:
- ✅ Filtra automaticamente
- ✅ Sincroniza apenas registros válidos
- ✅ Pula registros incompletos
- ✅ Log mostra quais foram pulados

**Ação recomendada:**
- Deixar como está
- Corrigir manualmente os dados inválidos na View quando possível

---

### OPÇÃO 3: Ver Detalhes dos Registros Pulados

```bash
# Buscar no log exatamente quais foram pulados
grep "PULADO" logs/sync.log | head -20

# Exemplo de output:
# [PULADO] ID: SEM_ID, Nome: SEM_NOME - Campo obrigatório ausente: ID do curso (id_curso)
# [PULADO] ID: 123, Nome: SEM_NOME - Campo obrigatório ausente: Nome do curso (nome_curso)
```

---

## Script de Verificação Automática

Se quiser criar um script PHP para verificar:

```php
<?php
// verificar_campos.php
require 'includes/db.php';

$remoteDb = db();

// Contar registros
$total = $remoteDb->query("SELECT COUNT(*) as cnt FROM cursos_site")->fetch()['cnt'];
$validos = $remoteDb->query("SELECT COUNT(*) as cnt FROM cursos_site 
    WHERE id_curso IS NOT NULL AND id_curso != '' 
    AND nome_curso IS NOT NULL AND nome_curso != ''")->fetch()['cnt'];
$invalidos = $total - $validos;

echo "Total de registros: $total\n";
echo "Registros válidos: $validos\n";
echo "Registros inválidos: $invalidos\n";
echo "Percentual de validade: " . round(($validos/$total)*100, 2) . "%\n";

// Exemplos de inválidos
$stmt = $remoteDb->query("SELECT id_curso, nome_curso FROM cursos_site 
    WHERE id_curso IS NULL OR id_curso = '' 
    OR nome_curso IS NULL OR nome_curso = '' 
    LIMIT 3");

echo "\nExemplos de registros inválidos:\n";
foreach ($stmt->fetchAll() as $row) {
    echo "- ID: " . ($row['id_curso'] ?? 'NULL') . " | Nome: " . ($row['nome_curso'] ?? 'NULL') . "\n";
}
?>
```

Execute via CLI:
```bash
php verificar_campos.php
```

---

## Sumário Rápido

| Situação | Ação |
|----------|------|
| Todos os registros válidos | ✅ Nada fazer, tudo OK |
| Alguns registros inválidos (< 10%) | ✅ Normal, sistema pula automaticamente |
| Muitos registros inválidos (> 50%) | ⚠️ Investigar View remota |
| Erro na conexão com View | ❌ Verificar credenciais em `includes/db.php` |
| Erro após sincronizar | 📋 Revisar `logs/sync.log` |

---

## Próximos Passos

1. ✅ Execute `teste.php`
2. ✅ Revise o relatório de sincronização
3. ✅ Se tiver dúvidas, consulte este guia
4. ✅ Se problema persistir, consulte `SOLUCAO_CAMPOS_OBRIGATORIOS.md`

---

**Data:** 2026-01-22
**Versão:** 1.0
**Status:** ✅ Pronto para usar
