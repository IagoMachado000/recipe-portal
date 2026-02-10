# Checklist de implementação

## Domínio (migrations + models + relacionamentos)

- `Recipe`
    - `title`
        - Indexado para acelerar buscas por nome da receita
        - Índice funcional (lower(title)) garante busca case-insensitive
    - `rating_avg`
        - Indexado para otimizar ordenação por avaliação
        - Atende ordenação ASC e DESC com um único índice
        - Índice parcial ignora registros soft-deleted
    - `steps`
        - Essa coluna está com tipo `jsonb` ao invés de `text` ou `varchar`
        - Essa escolha foi tomada para reforçar a segurança, evitando o uso da sintaxe `{!! !!}` (renderiza HTML bruto) que pode abrir brechas para ataques XSS
    - `slug`
        - A coluna `slug` foi adicionada para url amigável
- `Comment`
    - `recipe_id, created_at`
        - Indexado para listar comentários de uma receita
        - Otimiza ordenação por data (mais recentes primeiro)
- `Rating`
    - `CHECK (score BETWEEN 1 AND 5)`
        - Assegura que avaliações estejam sempre no intervalo permitido (1–5)

## Factories

- `RecipeFactory`
- `CommentFactory`
- `RatingFactory`

## Testes

### Domínio

#### Configuração

- Ativar RefreshDatabase no Pest.php
- Melhorar TestCase.php com trait RefreshDatabase
- Banco SQLite para testes (configuração phpunit.xml)

#### Feature Tests

- `DatabaseSeederTest`
    - Validar criação de 10 usuários e 10 receitas
    - Verificar volumes: 5-10 comentários/receita, 10-20 avaliações/receita
    - Validar constraint unique em ratings (recipe_id, user_id)
    - Verificar cálculo automático de médias de avaliação

#### Unit Tests

- `RecipeTest`
    - Relacionamentos: User (belongsTo), Comments/Ratings (hasMany)
    - Casts: ingredients (array), steps (array)
    - Soft deletes: delete, restore, withTrashed

- `RatingTest`
    - Relacionamentos: User, Recipe (belongsTo)
    - Validações: score (1-5) com CHECK constraint
    - Constraint unique: (recipe_id, user_id)
    - Cálculos: média automática em Recipe

- `CommentTest`
    - Relacionamentos: User, Recipe (belongsTo)
    - Validações: body (max 1000 chars)
    - Ordenação: created_at DESC (índice composto)

#### Testes do CRUD Recipe

- Feature tests do controller
- Unit tests de Service, DTO, FormRequest
- Policy tests
- Cobertura completa de validação e autorização

#### Decisões Técnicas

- Estrutura: Feature tests (integração) + Unit tests (models)
- Coverage: Validação completa do domínio sem testes de UI
- Limpeza: Remover testes de exemplo (ExampleTest.php)

#### Correções Aplicadas

- RecipeFactory: ingredients/steps como array direto (não JSON/string)
- Recipe Model: adicionar trait SoftDeletes
- Recipe Model: adicionar casts rating_avg (float), rating_count (int)
- Transações PostgreSQL: separar testes de constraint unique
- DatabaseSeederTest: converter sintaxe PHPUnit para Pest PHP

## CRUD Recipe

### FormRequest

- `StoreRecipeRequest`
- `UpdateRecipeRequest`
    - `Rule::unique('recipes', 'title')->ignore($this->recipe)`
        - Regra para ignorar o id do próprio registro
    - ```php
        protected function prepareForValidation(): void
        {
            if ($this->has('title')) {
                $this->merge([
                    'title' => mb_strtolower(trim($this->title)),
                ]);
            }
        }
      ```

        - Esse método auxiliar é necessário para tratar a normalização do titulo, sem isso, o título atual não é ignorado na validação

### DTO

- Recebe os dados da recita, faz o tratamento, padroniza o formato e entrega para o Service

### Service

- Classe Services para a criação, atualização, e deleção das receitas
- `DB::transaction` usado para garantir atomicidade. O novo registro só é persistido caso todo o processa seja bem sucedido, caso contrário, volta ao estado anterior

### Policy

- Garante que apenas o usuário que criou a receita possa editar/excluir

### Controller

- Implementação dos métodos CRUD
- Injeção de dependência do Service
- Autorização via Policy

### Rotas

- Resource routes separados (públicos vs autenticados)
- Middleware de autenticação
- Prefix dashboard para rotas protegidas

### Correções Aplicadas

- Adição de coluna `slug` para url amigável
- Alteração do tipo de dados de `varchar` para `jsonb` na coluna `recipes.steps`
- Adição de valor default para a coluna `recipes.rating_avg`

## Gestão de Comentários

### FormRequest

- `StoreCommentRequest`
    - Validação: `recipe_id` (required|exists), `body` (required|string|min:1|max:1000)
    - Autorização: `true` (middleware auth já garante)
    - Mensagens customizadas em PT-BR

### DTO

- `CommentDTO`
    - Sanitização automática: `strip_tags()`, whitespace normalization
    - Construtor com type hints strict
    - Métodos: `fromArray()`, `fromModel()`, `toArray()`
    - XSS prevention no construtor

### Service

- `CommentService`
    - `DB::transaction` para garantir atomicidade
    - Validação redundante de autenticação
    - Envio automático de notificações
    - Autorização: apenas autor pode excluir
    - Logging completo de auditoria

### Notification

- `NewCommentNotification`
    - Database channel apenas (simplicidade)
    - Dados estruturados: title, message, IDs, URL
    - Não envia notificação para autor do próprio comentário
    - Metadados para UI: type, icon, url

### Controller

- `CommentController`
    - Dependency injection do Service
    - Suporte AJAX (`expectsJson()`)
    - Resposta JSON para SPA feel
    - Error handling consistente (JSON + redirect)
    - Autorização via service layer

### Rotas

- `POST /comments`: criar comentário (auth)
- `DELETE /comments/{comment}`: excluir comentário (auth)
- `POST /notifications/{notification}/read`: marcar como lida
- `POST /notifications/read-all`: marcar todas como lidas

### Database

- Migration `create_notifications_table`
    - UUID primary key
    - Morphs para polimorfismo
    - JSON data column
    - Timestamp `read_at` nullable

### Correções Aplicadas

- CommentService: double check de autenticação (segurança)
- Notification: não enviada para autor do comentário
- DTO: sanitização XSS no construtor
- Controller: respostas AJAX para UX moderna

## Avaliação

### FormRequest

- `StoreRatingRequest`
    - Validação: recipe_id (required|exists), score (required|integer|min:1|max:5)
    - Autorização: true (middleware auth já garante)
    - Mensagens customizadas em PT-BR para score min/max

### DTO

- `RatingDTO`
    - Sanitização automática: score range 1-5 no construtor
    - Construtor: type hints strict (int recipeId, userId, score)
    - Métodos padrão: fromArray(), fromModel(), toArray()

### Service

- `RatingService`
    - DB::transaction para garantir atomicidade
    - updateOrCreate() respeita constraint unique automaticamente
    - Recálculo automático: SUM/COUNT + round() com 2 casas decimais
    - Notificação automática para autor da receita (exceto auto-avaliação)
    - Logging completo para auditoria

### Notification

- `NewRatingNotification`
    - Database channel apenas (simplicidade)
    - Dados estruturados: title, message, IDs, score
    - Metadados para UI: type, icon, url
    - Não envia notificação para autor avaliar própria receita

### Controller

- `RatingController`
    - Dependency injection do RatingService
    - AJAX responses (expectsJson()) para UX moderna
    - Error handling consistente (JSON + redirect)
    - Resposta com: new_average, total_ratings, was_created

### Database

- Migration `create_ratings_table`
    - Constraint unique: (recipe_id, user_id) para avaliação única
    - CHECK constraint: score BETWEEN 1 AND 5
    - Índices otimizados para performance de queries

### Correções Aplicadas

- RatingService: cálculo manual via SUM/COUNT (evita problemas PostgreSQL)
- RatingDTO: sanitização de score para garantir range 1-5
- Notification: verificação para não notificar autor da própria avaliação
- Controller: respostas JSON compatíveis com frontend existente

## Pesquisa e Filtragem

### FormRequest

- `RecipeSearchRequest`
    - Validação: search (nullable|string|max:100), filter (nullable|in:date,title,rating), sort_order (nullable|in:asc,desc)
    - Autorização: true (busca pública)
    - Preparação: normalização de busca e conversão de filtros em sort_by/sort_order

### Service

- `RecipeService`
    - getPublishedRecipes() com suporte a filtros e ordenação
    - Aplicação de filtro: LIKE em título, whereNotNull() para campos específicos
    - Aplicação de ordenação: múltiplos critérios (created_at, title, rating_avg)
    - Queries otimizadas com eager loading e índices

### Frontend

- Interface de pesquisa e filtros em recipes/index.blade.php
    - Campo de busca com placeholder e ícone
    - Select de filtros rápidos (data, nome, avaliação)
    - Botão de ordenação com toggle (↑/↓) e ícone dinâmico
    - JavaScript para eventos de mudança e atualização de estado
    - Valores preservados nos inputs durante reload
    - URLs limpas e amigáveis com parâmetros preservados
    - Botão simples para limpeza de filtros, redirecionamento novamente para `recipes.index`

### Rotas

- GET /recipes com suporte a parâmetros de busca e filtros
- Paginação mantida com preservação de filtros
- URLs amigáveis: ?search=termo&filter=tipo&sort_order=direcao

### Features Implementadas

- Pesquisa por título (case insensitive)
- Filtros rápidos: data de criação, nome da receita, avaliações
- Ordenação flexível: ascendente/descendente para cada tipo
- Preservação de estado entre reloads
- Interface responsiva e intuitiva
- Performance otimizada com índices existentes

### Correções Aplicadas

- Validação case insensitive através de ILIKE no PostgreSQL
- Normalização de busca para remover espaços extras
- Tratamento defensivo de parâmetros não existentes
- Validação cruzada entre parâmetros de filtro e ordenação

* AJUSTAR PAGINATE
