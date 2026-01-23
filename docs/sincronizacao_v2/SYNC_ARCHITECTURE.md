# 📊 Diagramas do Sistema de Sincronização

## 1. Arquitetura Geral

```
┌──────────────────────────────────────────────────────────────────┐
│                        SISTEMA FAESMA                            │
└──────────────────────────────────────────────────────────────────┘

                              │
                ┌─────────────┴──────────────┐
                │                            │
        ┌───────▼──────────┐        ┌────────▼────────┐
        │  BANCO REMOTO    │        │  BANCO LOCAL    │
        │  (site.cursos_   │        │  (faesma_db.    │
        │   site)          │        │   cursos)       │
        └───────┬──────────┘        └────────▲────────┘
                │                            │
                │  Fetch Data               │
                └────────────────┬──────────┘
                                 │
                    ┌────────────▼────────────┐
                    │  RemoteSyncService      │
                    │  + Validação            │
                    │  + Transformação        │
                    │  + Sincronização        │
                    └────────────┬────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │ RemoteSyncMapping       │
                    │ + Mapeamento de campos  │
                    │ + Transformações        │
                    │ + Builders de Query     │
                    └────────────────────────┘
```

## 2. Fluxo de Sincronização Detalhado

```
INÍCIO
  │
  ├─► [1] CONECTAR AOS BANCOS
  │   ├─ Banco Local (faesma_db)
  │   └─ Banco Remoto (site)
  │
  ├─► [2] BUSCAR DADOS DA VIEW
  │   └─ SELECT * FROM cursos_site LIMIT {limit}
  │
  ├─► [3] PROCESSAR CADA CURSO
  │   │
  │   ├─► [3.1] VALIDAR DADOS REMOTOS
  │   │   ├─ Campos obrigatórios?
  │   │   ├─ Tipos corretos?
  │   │   └─ Estrutura válida?
  │   │
  │   ├─► [3.2] CONVERTER PARA FORMATO LOCAL
  │   │   ├─ Mapear campos
  │   │   ├─ Transformar valores
  │   │   ├─ Gerar slug
  │   │   └─ Definir padrões
  │   │
  │   ├─► [3.3] PROCURAR CURSO EXISTENTE
  │   │   ├─ Buscar por cod_externo?
  │   │   ├─ Buscar por slug?
  │   │   └─ Buscar por nome?
  │   │
  │   ├─► [3.4] DECIDIR AÇÃO
  │   │   │
  │   │   ├─ SIM (encontrou) → [3.5] ATUALIZAR
  │   │   │                    ├─ UPDATE courses SET ...
  │   │   │                    └─ Registrar log
  │   │   │
  │   │   └─ NÃO (novo) → [3.6] CRIAR
  │   │                   ├─ INSERT INTO courses
  │   │                   └─ Registrar log
  │   │
  │   └─► [3.7] REGISTRAR RESULTADO
  │       ├─ Sucesso / Erro
  │       └─ Mensagem
  │
  ├─► [4] GERAR RELATÓRIO
  │   ├─ Total criados
  │   ├─ Total atualizados
  │   ├─ Total pulados
  │   └─ Total de erros
  │
  ├─► [5] SALVAR TIMESTAMP
  │   └─ logs/last_sync.txt
  │
  └─► FIM
```

## 3. Estrutura de Mapeamento

```
┌─────────────────────────────────────────────────────────────┐
│                    MAPEAMENTO DE CAMPOS                     │
└─────────────────────────────────────────────────────────────┘

CAMPO REMOTO (site.cursos_site)    →    CAMPO LOCAL (faesma_db.courses)
│                                         │
├─ id_curso                          →   cod_externo
├─ codigo_curso                      →   cd_oferta
├─ nome_curso                        →   nome
├─ descricao                         →   descricao_curta
├─ descricao_detalhada              →   descricao_completa
├─ duracao_meses                    →   duracao_meses
├─ duracao_texto                    →   duracao_texto
├─ carga_horaria                    →   carga_horaria
├─ objetivos                        →   objetivos
├─ perfil_egresso                   →   perfil_egresso
├─ mercado_trabalho                 →   mercado_trabalho
├─ publico_alvo                     →   publico_alvo
├─ valor_mensalidade                →   valor_mensalidade
├─ vagas_disponiveis                →   vagas_disponiveis
├─ coordenador_nome                 →   coordenador
├─ imagem_url                       →   imagem_destaque
├─ nota_mec                         →   nota_mec
├─ tcc_obrigatorio                  →   tcc_obrigatorio  [BOOL]
├─ inscricao_online                 →   inscricao_online [BOOL]
├─ link_oferta                      →   link_oferta
└─ status_remoto                    →   status          [MAPEADO]
```

## 4. Transformação de Valores

```
┌──────────────────────────────────────────┐
│         TRANSFORMAÇÃO DE STATUS          │
└──────────────────────────────────────────┘

ENTRADA (remoto) │ Mapeamento │ SAÍDA (local)
─────────────────┼────────────┼────────────────
ativo            →            → ativo
inativo          →            → inativo
breve            →            → breve
draft            →            → inativo
[OUTRO]          →            → [OUTRO]

┌──────────────────────────────────────────┐
│        TRANSFORMAÇÃO DE BOOLEANOS        │
└──────────────────────────────────────────┘

ENTRADA (remoto) │ Conversão  │ SAÍDA (local)
─────────────────┼────────────┼────────────────
"1"              →  strtolow  → true
"0"              →   filter   → false
"true"           →  validate  → true
"false"          →   BOOL     → false
"sim"            → _BOOLEAN   → true
"não"            →            → false
```

## 5. Ciclo de Vida do Curso

```
┌────────────────────────────────────────────────────────────┐
│              CICLO DE VIDA DO CURSO                        │
└────────────────────────────────────────────────────────────┘

PRIMEIRO ACESSO (Curso Novo)
  │
  ├─► Buscar em banco remoto (view cursos_site)
  │
  ├─► Validar dados
  │
  ├─► Procurar em banco local
  │   └─ NÃO ENCONTRADO
  │
  ├─► CRIAR novo registro em courses
  │   ├─ INSERT INTO courses (...)
  │   └─ ID gerado automaticamente
  │
  └─► ✅ CURSO CRIADO

ATUALIZAÇÕES POSTERIORES
  │
  ├─► Sincronização disparada (cron/manual)
  │
  ├─► Buscar novamente em banco remoto
  │
  ├─► Validar dados
  │
  ├─► Procurar em banco local
  │   └─ ENCONTRADO (por cod_externo, slug ou nome)
  │
  ├─► ATUALIZAR registro existente
  │   ├─ UPDATE courses SET ...
  │   ├─ Campos protegidos (id, slug, created_at) não mudam
  │   └─ updated_at atualizado automaticamente
  │
  └─► ✅ CURSO ATUALIZADO

CURSO INATIVO/DELETADO REMOTAMENTE
  │
  ├─► Sincronização disparada
  │
  ├─► Curso com status = 'inativo' / 'draft' em remoto
  │
  ├─► Encontrado em banco local
  │
  ├─► ATUALIZAR status para 'inativo'
  │   └─ UPDATE courses SET status = 'inativo'
  │
  └─► ✅ CURSO DESATIVADO LOCALMENTE
```

## 6. Estrutura de Dados

```
┌────────────────────────────────────────────────────────────┐
│             BANCO REMOTO - VIEW CURSOS                     │
│                   (site.cursos_site)                       │
└────────────────────────────────────────────────────────────┘

id_curso          │ INT        │ PK em remoto
codigo_curso      │ VARCHAR    │ Código da oferta
nome_curso        │ VARCHAR    │ Nome completo
descricao         │ TEXT       │ Breve descrição
descricao_detalhada │ LONGTEXT │ Descrição completa
duracao_meses     │ INT        │ Duração
duracao_texto     │ VARCHAR    │ Ex: "4 anos"
carga_horaria     │ INT        │ Total de horas
objetivos         │ TEXT       │ Objetivos
perfil_egresso    │ TEXT       │ Perfil do profissional
mercado_trabalho  │ TEXT       │ Informações de mercado
publico_alvo      │ TEXT       │ Público-alvo
valor_mensalidade │ DECIMAL    │ Preço mensal
vagas_disponiveis │ INT        │ Quantidade de vagas
coordenador_nome  │ VARCHAR    │ Responsável
imagem_url        │ VARCHAR    │ URL da imagem
nota_mec          │ DECIMAL    │ Avaliação MEC
tcc_obrigatorio   │ BOOL       │ TCC sim/não
inscricao_online  │ BOOL       │ Inscrição ativa
link_oferta       │ VARCHAR    │ URL de inscrição
status_remoto     │ ENUM       │ ativo/inativo/breve

┌────────────────────────────────────────────────────────────┐
│          BANCO LOCAL - TABELA COURSES                      │
│                (faesma_db.courses)                         │
└────────────────────────────────────────────────────────────┘

id                │ INT        │ PK (auto-increment)
category_id       │ INT        │ FK para categorias
modality_id       │ INT        │ FK para modalidades
nome              │ VARCHAR    │ Nome do curso
slug              │ VARCHAR    │ URL amigável
descricao_curta   │ TEXT       │ Breve descrição
descricao_completa│ LONGTEXT   │ Descrição completa
objetivos         │ TEXT       │ Objetivos
perfil_egresso    │ TEXT       │ Perfil
mercado_trabalho  │ TEXT       │ Mercado
publico_alvo      │ TEXT       │ Público-alvo
duracao_meses     │ INT        │ Meses
duracao_texto     │ VARCHAR    │ Texto (ex: "4 anos")
carga_horaria     │ INT        │ Horas totais
coordenador       │ VARCHAR    │ Responsável
valor_mensalidade │ DECIMAL    │ Preço
vagas_disponiveis │ INT        │ Vagas
imagem_destaque   │ VARCHAR    │ Imagem
cod_externo       │ VARCHAR    │ ID REMOTO ← Sincronizado
nota_mec          │ DECIMAL    │ Nota MEC
ds_valor          │ VARCHAR    │ Descrição valor
tcc_obrigatorio   │ BOOL       │ TCC ← Sincronizado
texto_apos_banner │ TEXT       │ Texto customizado
mercado           │ TEXT       │ Mercado
mercado_remuneracao_media │ DECIMAL │ Salário médio
inscricao_online  │ BOOL       │ Ativa ← Sincronizado
link_oferta       │ VARCHAR    │ URL ← Sincronizado
cd_oferta         │ VARCHAR    │ Código ← Sincronizado
status            │ ENUM       │ ativo/inativo/breve
destaque          │ BOOL       │ Destacado
ordem             │ INT        │ Ordem de exibição
created_at        │ TIMESTAMP  │ Criação [PROTEGIDO]
updated_at        │ TIMESTAMP  │ Atualização [AUTO]
```

## 7. Opções de Execução

```
┌────────────────────────────────────────────────────────────┐
│           FORMAS DE EXECUTAR SINCRONIZAÇÃO                 │
└────────────────────────────────────────────────────────────┘

1. VIA CLI (Linha de Comando)
   ┌─────────────────────────────────────┐
   │ $ php sync_courses.php              │
   │                                     │
   │ Output:                             │
   │ ✓ Status: SUCESSO                   │
   │ ✓ Criados: 5                        │
   │ ✓ Atualizados: 12                   │
   └─────────────────────────────────────┘

2. VIA HTTP (Browser)
   ┌──────────────────────────────────────────────────┐
   │ http://localhost/projeto5/sync_courses.php       │
   │   ?token=HASH&limit=500&mode=sync                │
   │                                                  │
   │ Output: JSON                                     │
   │ {                                                │
   │   "status": "sucesso",                           │
   │   "stats": {...},                                │
   │   "log": [...]                                   │
   │ }                                                │
   └──────────────────────────────────────────────────┘

3. VIA CRON (Agendado)
   ┌──────────────────────────────────────────┐
   │ 0 2 * * * php /path/sync_courses.php     │
   │                                          │
   │ Executa diariamente às 2:00 AM           │
   └──────────────────────────────────────────┘

4. VIA CÓDIGO PHP
   ┌────────────────────────────────────────┐
   │ $sync = new RemoteSyncService(...)      │
   │ $result = $sync->syncAllCourses(...)   │
   └────────────────────────────────────────┘
```

## 8. Fluxo de Decisão - Curso Existente ou Novo?

```
┌─────────────────────────────────────────────────────────┐
│     VERIFICAR SE CURSO JÁ EXISTE NO BANCO LOCAL        │
└─────────────────────────────────────────────────────────┘

CURSO REMOTO RECEBIDO
        │
        ├─► PASSO 1: Buscar por cod_externo (ID remoto)
        │   │
        │   ├─ ENCONTRADO? 
        │   │  └─ SIM → [ATUALIZAR] ✓
        │   │
        │   └─ NÃO → Continue
        │
        ├─► PASSO 2: Buscar por slug
        │   │
        │   ├─ ENCONTRADO?
        │   │  └─ SIM → [ATUALIZAR] ✓
        │   │
        │   └─ NÃO → Continue
        │
        ├─► PASSO 3: Buscar por nome (match exato)
        │   │
        │   ├─ ENCONTRADO?
        │   │  └─ SIM → [ATUALIZAR] ✓
        │   │
        │   └─ NÃO → Continue
        │
        └─► PASSO 4: Nenhuma correspondência
            │
            └─ [CRIAR NOVO] ✓
```

## 9. Tratamento de Erros

```
┌───────────────────────────────────────────────────────────┐
│          FLUXO DE TRATAMENTO DE ERROS                    │
└───────────────────────────────────────────────────────────┘

ERRO ENCONTRADO
    │
    ├─► Tipo: VALIDAÇÃO
    │   ├─ Mensagem: Campo obrigatório ausente
    │   ├─ Ação: Pular (skip)
    │   └─ Log: [Pulado] + razão
    │
    ├─► Tipo: CONVERSÃO
    │   ├─ Mensagem: Falha ao converter valor
    │   ├─ Ação: Usar valor padrão ou pular
    │   └─ Log: [Erro] + descrição
    │
    ├─► Tipo: BANCO DE DADOS
    │   ├─ Mensagem: Erro SQL
    │   ├─ Ação: Rollback + pular
    │   └─ Log: [Erro] + detalhe
    │
    ├─► Tipo: DUPLICATA
    │   ├─ Mensagem: Slug/email único violado
    │   ├─ Ação: Update em vez de insert
    │   └─ Log: [Atualizado]
    │
    └─► Ao Final
        ├─ Compilar estatísticas
        ├─ Preparar relatório
        └─ Retornar resultado
```

---

**Última Atualização:** Janeiro 2026  
**Versão:** 1.0
