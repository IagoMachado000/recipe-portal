# Portal de Receitas — Documentação Técnica e Decisões de Arquitetura

Este documento descreve **as decisões técnicas, arquiteturais e de modelagem** adotadas no desenvolvimento do projeto **Portal de Receitas**, bem como o raciocínio por trás de cada escolha.
O objetivo não é apenas explicar _como_ o sistema foi implementado, mas principalmente _por que_ determinadas abordagens foram escolhidas, conforme solicitado no teste técnico.

> Algumas estruturas (DTO, Service Layer, Policies, índices avançados) extrapolam o mínimo exigido pelo teste. Elas foram adotadas de forma **consciente**, com o objetivo de demonstrar organização, boas práticas e preocupação com manutenção e escalabilidade.

## Documentação Técnica

Este projeto possui documentação complementar para explicar decisões técnicas e de modelagem:

- **Decisões Técnicas e Arquitetura**  
  Veja: `docs/TECHNICAL-DECISIONS.md`

- **Modelagem de Domínio e Banco de Dados**  
  Veja: `docs/DOMINIO.md`

---

## Sumário

1. Setup local e ambiente de desenvolvimento
2. Rodar o projeto localmente
3. Decisões de arquitetura
4. Modelagem de domínio e banco de dados
5. Testes automatizados
6. CRUD de Receitas
7. Comentários
8. Avaliações (Ratings)
9. Pesquisa, filtros e ordenação
10. Rotas e URLs amigáveis (Slug)
11. Feedback ao usuário
12. JavaScript e CSS
13. Limitações e trade-offs

---

## 1. Setup local e ambiente

O projeto utiliza **Laravel Sail**, garantindo um ambiente Dockerizado padronizado e evitando dependências locais de PHP, Composer ou Node.js.

Principais escolhas:

- Docker + Sail para padronização do ambiente
- PostgreSQL como banco de dados
- Mailpit para inspeção de e-mails em desenvolvimento
- Alias para facilitar o uso do comando `sail`

Essa abordagem reduz inconsistências entre ambientes e facilita o onboarding.

---

## 2. Rodar o projeto localmente

### Pré-requisitos

Antes de começar, certifique-se de ter instalado em sua máquina:

- **Docker** (versão recente)
- **Docker Compose** (v2 ou superior)
- **Git**
- Sistema operacional Linux ou macOS

    > No Windows, recomenda-se usar **WSL2**

> Não é necessário ter PHP, Composer ou Node.js instalados localmente.

---

### Clonando o repositório

Clone o projeto e acesse o diretório da aplicação:

```bash
git clone https://github.com/IagoMachado000/recipe-portal.git
cd recipe-portal
```

---

### Configurando variáveis de ambiente

Crie o arquivo `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Edite o arquivo `.env` e configure as seguintes variáveis:

### Banco de Dados

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=recipe-portal
DB_USERNAME=recipeportal
DB_PASSWORD=recipeportal
```

> O valor `DB_HOST=mysql` é obrigatório para funcionar corretamente com o container do Sail.

---

### Configuração de E-mail (ambiente local)

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> No ambiente local, os e-mails não são enviados de fato — eles serão registrados no log da aplicação.

---

### Instalando dependências PHP (Composer)

Use a imagem oficial do Laravel Sail para instalar as dependências PHP **sem precisar do Composer local**:

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd)":/var/www/html \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install
```

Esse comando irá:

- Criar a pasta `vendor/`
- Preparar o projeto para uso com o Sail

---

### Criando alias pro comando sail

No terminal, dentro da pasta do projeto, rodar o comando abaixo. Ele irá criar um apelido pro comando `./vendor/bin/sail`

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

---

### Subindo os containers com Sail

Após instalar as dependências, inicie os containers:

```bash
sail up -d (caso tenha criado o alias) ou

./vendor/bin/sail up -d
```

> Na primeira execução, o Docker pode levar alguns minutos para baixar as imagens.

---

### Gerando a chave da aplicação

Com os containers rodando, gere a chave da aplicação Laravel:

```bash
sail artisan key:generate (caso tenha criado o alias) ou

./vendor/bin/sail artisan key:generate
```

---

### Criando e populando o banco de dados

Execute as migrations e seeders:

```bash
sail artisan migrate:fresh --seed (caso tenha criado o alias) ou

./vendor/bin/sail artisan migrate:fresh --seed
```

Esse comando irá:

- Apagar o banco (caso exista)
- Criar todas as tabelas
- Popular o banco com dados iniciais

---

### Instalando dependências Front-end

Instale as dependências JavaScript:

```bash
sail npm install (caso tenha criado o alias) ou

./vendor/bin/sail npm install
```

---

### Rodando o front-end em modo desenvolvimento

Inicie o Vite para desenvolvimento:

```bash
sail npm run dev (caso tenha criado o alias) ou

./vendor/bin/sail npm run dev
```

> Esse comando mantém um processo ativo para hot reload de assets.

---

### Executando os testes automatizados

Para rodar todos os testes da aplicação:

```bash
sail artisan test (caso tenha criado o alias) ou

./vendor/bin/sail artisan test
```

---

### Aplicação pronta

Após executar todos os passos acima, a aplicação estará disponível em:

```
# Aplicação
http://localhost

# Servidor de E-mail
http://localhost:8025

# Login
- Conectar no banco de dados
- Abrir a tabela user
- Pegar um e-mail
- Usar a senha password padrão (para todos os usuários)
```

---

### Comandos úteis do Sail

```bash
./vendor/bin/sail down            # Para os containers
./vendor/bin/sail restart         # Reinicia os containers
./vendor/bin/sail ps              # Lista containers ativos
./vendor/bin/sail logs            # Visualiza logs
./vendor/bin/sail shell           # Acessa o container da aplicação
```

---

### Observações importantes

- Sempre utilize `./vendor/bin/sail` para rodar:
    - Artisan
    - NPM
    - Composer

- Não execute comandos diretamente no host (ex: `php artisan`).
- Caso tenha problemas com banco ou cache:

    ```bash
    ./vendor/bin/sail down -v
    ./vendor/bin/sail up -d
    ```

---

## 3. Decisões de Arquitetura

A aplicação segue uma separação clara de responsabilidades:

- **Controller**: camada fina de orquestração
- **FormRequest**: validação e autorização de entrada
- **DTO (Data Transfer Object)**: sanitização e padronização de dados
- **Service Layer**: regras de negócio e transações
- **Policy**: controle de autorização

Essa separação evita controllers inchados, melhora a testabilidade e torna o código mais previsível e manutenível.

---

## 4. Modelagem de Domínio e Banco de Dados

### Recipe

- `title`
    - Indexado para acelerar buscas por nome
    - Índice funcional `LOWER(title)` para busca case-insensitive

- `rating_avg`
    - Indexado para otimizar ordenações por avaliação
    - Índice parcial ignora registros soft-deleted

- `steps`
    - Tipo `jsonb`
    - Escolha feita para evitar renderização de HTML bruto (`{!! !!}`), reduzindo riscos de XSS

- `slug`
    - Criado para URLs amigáveis
    - Utilizado via Route Model Binding com chave customizada

### Comment

- Índice composto `(recipe_id, created_at DESC)`
    - Otimiza listagem de comentários por receita
    - Facilita ordenação por mais recentes

### Rating

- Constraint `CHECK (score BETWEEN 1 AND 5)`
    - Regra de domínio garantida no banco

- Constraint `UNIQUE (recipe_id, user_id)`
    - Garante que cada usuário avalie uma receita apenas uma vez

Essas decisões reforçam integridade de dados e performance.

---

## 5. Testes Automatizados

### Estratégia

- **Feature Tests**: validação do fluxo completo
- **Unit Tests**: validação isolada do domínio

A escolha foi priorizar o domínio e regras de negócio, evitando testes de UI.

### Configuração

- `RefreshDatabase` habilitado
- SQLite em memória para execução rápida

### Cobertura

- Relacionamentos entre models
- Casts (`ingredients`, `steps`)
- Soft deletes
- Constraints de banco
- Cálculo automático de médias

Algumas funcionalidades secundárias (comentários e avaliações) não possuem cobertura completa por limitação de tempo.

---

## 6. CRUD de Receitas

### FormRequest

- `StoreRecipeRequest`
- `UpdateRecipeRequest`

Uso de `prepareForValidation()` para normalizar título:

```php
$this->merge([
    'title' => mb_strtolower(trim($this->title)),
]);
```

Isso garante validação consistente e funcionamento correto da regra `unique`.

### DTO

Responsável por:

- Sanitizar dados
- Normalizar formatos
- Garantir tipos corretos

### Service

- Uso de `DB::transaction()`
- Garantia de atomicidade
- Nenhuma operação parcial é persistida em caso de erro

### Policy

- Apenas o autor pode editar ou excluir a receita

---

## 7. Comentários

### Validação

- Texto obrigatório
- Limite de caracteres
- Autenticação via middleware

### Service

- Transação para criação e exclusão
- Autorização: apenas autor pode excluir
- Logging para auditoria

### Segurança

- Sanitização com `strip_tags()`
- Normalização de espaços em branco

---

## 8. Avaliações (Ratings)

### Regras

- Score entre 1 e 5
- Avaliação única por usuário

### Service

- `updateOrCreate()` respeitando constraint unique
- Recalculo manual da média (`SUM / COUNT`)
- Arredondamento para duas casas decimais

### Notificações

- Autor da receita é notificado
- Auto-avaliações não geram notificação

---

## 9. Pesquisa, Filtros e Ordenação

- Busca por título (case-insensitive)
- Filtros: data, nome, avaliação
- Ordenação ascendente/descendente
- Preservação de estado via query string

Consultas otimizadas com índices existentes e eager loading.

---

## 10. Rotas e URLs Amigáveis

Uso de Route Model Binding com slug:

```php
Route::get('recipes/{recipe:slug}', [RecipeController::class, 'show']);
```

Melhora SEO, UX e legibilidade das URLs.

---

## 11. Feedback ao Usuário

- Uso de `with()` para mensagens de sucesso/erro
- Renderização condicional no Blade
- Feedback consistente para ações de CRUD

---

## 12. JavaScript e CSS

- Uso de `@stack` e `@push` para scripts pontuais
- Abordagem simples e adequada ao tamanho do projeto

Em projetos maiores, a abordagem ideal seria modularização completa.

---

## 13. Limitações e Trade-offs

- Validação apenas no servidor (sem JS client-side)
- Cobertura parcial de testes para comentários e avaliações
- Estrutura mais robusta do que o mínimo exigido

Essas decisões foram tomadas conscientemente devido ao tempo disponível e ao escopo do teste.

---

## Conclusão

O foco do projeto foi demonstrar **clareza de raciocínio, organização de código, boas práticas e domínio do Laravel**, priorizando manutenção, segurança e legibilidade ao invés de volume de funcionalidades.
