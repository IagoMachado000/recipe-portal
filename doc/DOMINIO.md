# Modelagem de Domínio e Banco de Dados

Este documento descreve as decisões de **modelagem do domínio e do banco de dados** adotadas no projeto **Portal de Receitas**.

A modelagem foi pensada para atender aos requisitos do teste técnico, priorizando **integridade dos dados**, **simplicidade** e **performance para leitura**, especialmente em listagens e agregações.

---

## Visão Geral do Domínio

O sistema é composto por três entidades principais:

- **recipes**: entidade central, representa uma receita criada por um usuário
- **comments**: comentários feitos por usuários em receitas
- **ratings**: avaliações numéricas (1 a 5) associadas às receitas

---

## recipes

A tabela `recipes` concentra os dados principais da aplicação.

### Decisões

- Cada receita pertence a um único usuário (`user_id`), permitindo controle claro de autoria.
- O campo `title` possui tamanho limitado e índice funcional (`LOWER(title)`), viabilizando buscas case-insensitive.
- O campo `ingredients` utiliza `jsonb` para manter flexibilidade estrutural, evitando normalização excessiva.
- Os campos `rating_avg` e `rating_count` armazenam valores agregados para evitar operações custosas (`JOIN` e `GROUP BY`) em listagens.
- O campo `created_at` é indexado para ordenação por recência.
- O uso de `deleted_at` permite exclusão lógica (soft delete), preservando histórico de dados.

### Objetivo

Garantir boa performance em listagens, filtros e ordenações frequentes, mantendo o modelo simples e extensível.

---

## comments

A tabela `comments` representa interações textuais dos usuários com as receitas.

### Decisões

- Cada comentário pertence a uma receita e a um usuário.
- Um usuário pode comentar mais de uma vez na mesma receita, refletindo um comportamento natural de conversação.
- O índice composto `(recipe_id, created_at DESC)` atende ao principal caso de uso:
    - listar comentários de uma receita do mais recente para o mais antigo.

### Objetivo

Otimizar a leitura de comentários na página de detalhe da receita, sem complexidade adicional.

---

## ratings

A tabela `ratings` armazena avaliações numéricas feitas pelos usuários.

### Decisões

- Um usuário pode avaliar uma receita apenas uma vez, garantido por `UNIQUE (recipe_id, user_id)`.
- O campo `score` possui constraint de banco (`CHECK (score BETWEEN 1 AND 5)`), garantindo integridade dos dados.
- Os valores agregados não são calculados em tempo real, mas refletidos diretamente na tabela `recipes`.

### Objetivo

Manter consistência nas avaliações e permitir ordenação eficiente por nota média.

---

## Relacionamentos do Domínio

- Um usuário pode criar várias receitas.
- Cada receita pertence a um único usuário.
- Uma receita pode possuir vários comentários e avaliações.
- Um usuário pode comentar e avaliar várias receitas.
- Cada avaliação é única por par usuário/receita.

Esses relacionamentos refletem diretamente as regras de negócio do sistema.

---

## Comentários sem Encadeamento

Os comentários não possuem respostas encadeadas.

Essa decisão mantém o escopo alinhado ao proposto no teste, simplificando:

- o modelo de dados
- as consultas de leitura
- a interface do usuário

A estrutura foi pensada para permitir encadeamento futuro sem refatorações relevantes.

---

## Estrutura das Tabelas

### recipes

- `id` — **bigint** — **PK**
- `user_id` — **bigint** — **FK → users.id** — **INDEX**
- `title` — **varchar(120)** — **INDEX (funcional: LOWER(title))**
- `description` — **varchar(500)** — nullable
- `ingredients` — **jsonb**
- `steps` — **jsonb**
- `rating_avg` — **decimal(3,2)** — default **0** — **INDEX**
- `rating_count` — **integer** — default **0**
- `slug` — **varchar(255)**
- `created_at` — **timestamp** — **INDEX**
- `updated_at` — **timestamp**
- `deleted_at` — **timestamp**, nullable

### comments

- `id` — **bigint** — **PK**
- `recipe_id` — **bigint** — **FK → recipes.id** — **INDEX**
- `user_id` — **bigint** — **FK → users.id** — **INDEX**
- `body` — **text**
- `created_at` — **timestamp**

**Índice adicional:**

- **INDEX (recipe_id, created_at DESC)**

### ratings

- `id` — **bigint** — **PK**
- `recipe_id` — **bigint** — **FK → recipes.id** — **INDEX**
- `user_id` — **bigint** — **FK → users.id** — **INDEX**
- `score` — **smallint** — **CHECK (score BETWEEN 1 AND 5)**
- `created_at` — **timestamp**

**Restrição de negócio:**

- **UNIQUE (recipe_id, user_id)** — um usuário avalia uma receita apenas uma vez

---

## Considerações Finais

A modelagem foi estruturada para refletir regras de negócio diretamente no banco, evitar cálculos custosos em consultas frequentes e manter o domínio simples, coerente e extensível.
