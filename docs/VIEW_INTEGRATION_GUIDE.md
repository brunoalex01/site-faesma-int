# Guia de Integração da View de Cursos

## Visão Geral

O sistema FAESMA agora está integrado para buscar os cursos diretamente da view `cursos_site` do banco de dados, permitindo exibir os dados na página `cursos.php` de forma dinâmica.

## Arquitetura da Integração

### 1. Camada de Conexão (`includes/db.php`)
- Função `db()`: Estabelece conexão PDO com o banco de dados
- Função `fetchAllFromView()`: Busca dados de uma view específica
- **Credenciais**:
  - Host: `143.0.121.152`
  - Database: `site`
  - User: `site_faesma`
  - Password: `YwsGps1rBusBmWvPrzj9`

### 2. Novas Funções em `includes/functions.php`

#### `getCoursesFromView($filters, $limit, $offset)`
Busca cursos da view `cursos_site` com filtros opcionais.

**Parâmetros:**
- `$filters`: Array com filtros (category_id, modality_id, search)
- `$limit`: Limite de resultados (null para sem limite)
- `$offset`: Deslocamento para paginação

**Retorno:** Array de cursos

**Exemplo:**
```php
$filters = ['search' => 'Administração'];
$courses = getCoursesFromView($filters, 10, 0);
```

#### `getCourseFromView($identifier, $field)`
Busca um curso específico da view.

**Parâmetros:**
- `$identifier`: ID ou slug do curso
- `$field`: Campo para buscar ('id' ou 'slug')

**Retorno:** Array com dados do curso ou false

**Exemplo:**
```php
$course = getCourseFromView('administracao', 'slug');
```

#### `getCourseCountFromView($filters)`
Retorna o total de cursos com filtros aplicados.

**Parâmetros:**
- `$filters`: Array com filtros (mesmo formato que getCoursesFromView)

**Retorno:** Inteiro com total de cursos

**Exemplo:**
```php
$total = getCourseCountFromView(['category_id' => 1]);
```

#### `getCourseCategoriesFromView()`
Extrai categorias únicas da view.

**Retorno:** Array com categorias disponíveis

**Exemplo:**
```php
$categories = getCourseCategoriesFromView();
```

#### `getCourseModalitiesFromView()`
Extrai modalidades únicas da view.

**Retorno:** Array com modalidades disponíveis

**Exemplo:**
```php
$modalities = getCourseModalitiesFromView();
```

### 3. Página `cursos.php` Atualizada

A página foi modificada para usar as novas funções:
- Busca cursos da view em tempo real
- Aplica filtros por categoria, modalidade e busca
- Implementa paginação
- Extrai categorias e modalidades dinamicamente

### 4. Teste da Integração (`teste.php`)

Página de teste que valida a conexão e exibe os cursos em formato visual:
- Acesso: `http://localhost/projeto5/teste.php`
- Mostra estatísticas de conexão
- Exibe cursos em cards
- Exporta dados em JSON para debug

## Fluxo de Dados

```
View "cursos_site" (Banco de Dados)
        ↓
    db.php (conexão PDO)
        ↓
functions.php (funções de integração)
        ↓
cursos.php (exibição para usuário)
```

## Estrutura Esperada da View

A view `cursos_site` deve retornar as seguintes colunas:

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único do curso |
| `nome` | VARCHAR | Nome do curso |
| `slug` | VARCHAR | Slug para URL |
| `descricao_curta` | TEXT | Descrição resumida |
| `descricao_completa` | LONGTEXT | Descrição completa |
| `category_id` | INT | ID da categoria |
| `categoria_nome` | VARCHAR | Nome da categoria |
| `categoria_slug` | VARCHAR | Slug da categoria |
| `modality_id` | INT | ID da modalidade |
| `modalidade_nome` | VARCHAR | Nome da modalidade |
| `modalidade_slug` | VARCHAR | Slug da modalidade |
| `duracao_texto` | VARCHAR | Duração formatada |
| `carga_horaria` | INT | Carga horária total |
| `valor_mensalidade` | DECIMAL | Valor mensal |
| `imagem_destaque` | VARCHAR | Arquivo de imagem |
| `objetivos` | LONGTEXT | Objetivos do curso |
| `perfil_egresso` | LONGTEXT | Perfil do egresso |
| `mercado_trabalho` | LONGTEXT | Informações de mercado |

## Filtros Suportados

### Busca por Texto
```php
$filters = ['search' => 'administração'];
```

### Filtro por Categoria
```php
$filters = ['category_id' => 1];
```

### Filtro por Modalidade
```php
$filters = ['modality_id' => 2];
```

### Combinação de Filtros
```php
$filters = [
    'category_id' => 1,
    'modality_id' => 2,
    'search' => 'cursos'
];
```

## Tratamento de Erros

Todas as funções incluem try-catch e registram erros em log:
- Erros são capturados e registrados
- Funções retornam valores seguros (array vazio ou false)
- Página continua funcionando mesmo se houver erro de conexão

## Performance

- Caching em memória: Dados da view são carregados uma vez por requisição
- Filtros são aplicados em PHP (não na query)
- Paginação otimizada com array_slice()

## Próximas Melhorias

1. **Cache em Redis**: Implementar cache para reduzir queries ao banco
2. **Query Parametrizada**: Se possível, mover filtros para a query SQL
3. **Índices de Database**: Adicionar índices na view para melhor performance
4. **Sincronização**: Criar cronjob para sincronizar dados entre tabelas

## Suporte e Debug

Se houver problemas:

1. **Verifique a conexão**: Acesse `teste.php`
2. **Verifique logs**: Veja `error_log()` no arquivo de log do PHP
3. **Valide a view**: Confirme que `cursos_site` existe no banco
4. **Verifique dados**: Confirme que há dados na view

## Referências

- [db.php](../includes/db.php) - Camada de conexão
- [functions.php](../includes/functions.php) - Funções de integração
- [cursos.php](../cursos.php) - Página de listagem
- [teste.php](../teste.php) - Página de teste
