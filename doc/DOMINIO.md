# Domínio e decisões de modelagem

Este documento descreve as decisões de modelagem do banco de dados e os critérios adotados para estruturar o domínio da aplicação.

A modelagem prioriza **integridade de dados**, **simplicidade** e **performance para leitura**, considerando os casos de uso propostos no escopo do teste.

---

## Visão geral do domínio

O sistema é composto por três entidades principais:

- **recipes**: entidade central do domínio, representa uma receita criada por um usuário.
- **comments**: interações textuais dos usuários com as receitas.
- **ratings**: avaliações numéricas (1 a 5) feitas por usuários em receitas.

---

## recipes

A tabela `recipes` concentra os dados principais da receita.

### Decisões

- Cada receita pertence a um único usuário (`user_id`), permitindo controle claro de autoria e aplicação de regras de autorização.
- O campo `title` possui tamanho limitado e índice funcional (`lower(title)`), viabilizando busca _search-as-you-type_ case-insensitive.
- O campo `ingredients` utiliza JSON para manter flexibilidade estrutural, evitando normalização excessiva.
- A média e a quantidade de avaliações são **cacheadas** (`rating_avg`, `rating_count`) para evitar operações custosas (`JOIN` e `GROUP BY`) em listagens.
- O campo `created_at` é indexado para ordenação por recência.
- O uso de `deleted_at` permite exclusão lógica (soft delete), preservando histórico de dados.

### Objetivo

Garantir boa performance em listagens, filtros e ordenações frequentes, mantendo o modelo simples e extensível.

---

## comments

A tabela `comments` representa comentários feitos por usuários em receitas.

### Decisões

- Cada comentário pertence a uma receita e a um usuário.
- Um usuário pode comentar mais de uma vez na mesma receita, refletindo um comportamento natural de conversação.
- O índice composto (`recipe_id`, `created_at DESC`) atende ao principal caso de uso:
    - listar comentários de uma receita do mais recente para o mais antigo.

### Objetivo

Otimizar a leitura de comentários dentro da página da receita, sem complexidade adicional.

---

## ratings

A tabela `ratings` armazena avaliações numéricas das receitas.

### Decisões

- Um usuário pode avaliar uma receita apenas uma vez, garantido por `UNIQUE (recipe_id, user_id)`.
- O campo `score` é validado no banco (valores entre 1 e 5), garantindo integridade dos dados.
- Os valores agregados de avaliação não são calculados em tempo real, mas refletidos diretamente na tabela `recipes`.
- A estrutura garante que cada usuário possa avaliar uma receita apenas uma vez, evitando duplicidades e simplificando o fluxo de avaliação.

### Objetivo

Manter consistência nas avaliações e permitir ordenação eficiente por nota média.

---

## Relacionamentos do domínio

Os relacionamentos foram definidos com base nas regras de negócio e no comportamento esperado dos usuários.

- Um **usuário pode criar várias receitas**.
- Cada **receita pertence a um único usuário**.
- Uma **receita pode possuir vários comentários** e **várias avaliações**.
- Um **usuário pode comentar e avaliar várias receitas**.
- Cada **avaliação é única por par usuário/receita**.

Esses relacionamentos garantem integridade dos dados e refletem diretamente as regras do sistema.

---

## Comentários sem encadeamento (replies)

Os comentários não possuem respostas encadeadas.

Essa decisão foi tomada para manter o escopo alinhado ao proposto no teste, que exige apenas a criação e listagem de comentários associados às receitas.

A ausência de encadeamento simplifica:

- o modelo de dados
- as consultas de leitura
- a interface do usuário

O domínio foi estruturado de forma que, caso necessário no futuro, o encadeamento de comentários possa ser implementado sem refatorações estruturais relevantes.

---

## Considerações finais

A modelagem foi pensada para:

- refletir regras de negócio diretamente no banco de dados
- evitar cálculos custosos em consultas frequentes
- manter o domínio simples e coerente

---

## Estrutura das tabelas

### recipes

- `id` — **bigint** — **PK**
- `user_id` — **bigint** — **FK → users.id** — **INDEX**
- `title` — **varchar(120)** — **INDEX (funcional: lower(title))**
- `description` — **varchar(500), nullable**
- `ingredients` — **jsonb**
- `steps` — **jsonb**
- `rating_avg` — **decimal(3,2)** — default **0** — **INDEX**
- `rating_count` — **integer** — default **0**
- `slug` - **varchar(255)**
- `created_at` — **timestamp** — **INDEX**
- `updated_at` — **timestamp**
- `deleted_at` — **timestamp, nullable** _(soft delete)_

### comments

- `id` — **bigint** — **PK**
- `recipe_id` — **bigint** — **FK → recipes.id** — **INDEX**
- `user_id` — **bigint** — **FK → users.id** — **INDEX**
- `body` — **text**
- `created_at` — **timestamp**

**Índice adicional (principal caso de uso):**

- **INDEX (recipe_id, created_at DESC)**

### ratings

- `id` — **bigint** — **PK**
- `recipe_id` — **bigint** — **FK → recipes.id** — **INDEX**
- `user_id` — **bigint** — **FK → users.id** — **INDEX**
- `score` — **smallint** — **CHECK (score between 1 and 5)**
- `created_at` — **timestamp**

**Restrição (regra de negócio):**

- **UNIQUE (recipe_id, user_id)** _(um usuário avalia uma receita apenas uma vez)_
