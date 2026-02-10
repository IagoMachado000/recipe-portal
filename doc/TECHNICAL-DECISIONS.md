Criar novo projeto (PostgreSQL + MailPit)

curl -s "https://laravel.build/recipe-portal?with=pgsql,mailpit" | bash

cd recipe-portal && code .

===

criar alias pro comando sail

alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

===

git init
git checkout -b main
git add .
git commit -m "commit inicial"
git remote add origin git@github.com:IagoMachado000/recipe-portal.git
git push -u origin main
git checkout -b develop && git push -u origin develop
git pull

===

Optei por usar o php 8.4 por ser mais estável e não ter problemas com pacotes

git checkout -b chore/changing-version-php
alterar arquivo compose.yaml
context: "./vendor/laravel/sail/runtimes/8.5" -> context: "./vendor/laravel/sail/runtimes/8.4"
image: "sail-8.5/app" -> image: "sail-8.4/app"
git add compose.yaml
git commit -m "chore(sail): definindo versão do PHP para 8.4"
git push -u origin chore/changing-version-php
pr + merge (develop)
git checkout develop && git pull

===

subir containers

sail up -d

===

subir tabelas pro banco

sail artisan migrate

===

git checkout -b chore/testing-pest

trocando phpunit por pest

sail composer remove phpunit/phpunit (entrada no terminal: yes)
sail composer require pestphp/pest --dev
sail composer require pestphp/pest-plugin-laravel --dev
sail composer dump-autoload
sail artisan optimize:clear
sail pest --init (entrada no terminal: no)
sail test

git add composer.json composer.lock tests/Pest.php
git commit -m "chore(test): substituindo phpunit por pest"
git push -u origin chore/testing-pest
pr + merge (develop)
git checkout develop && git pull

===

instalando startkit laravel/ui (auth + bootstrap)

A escolha pelo pacote laravel/ui foi pela conveniência de já ter um setup pronto para o bootstrap. O breeze por default vem com tailwind, mesmo escolhendo o startkit blade. E para usar o bootstrap, seria necessário alterar todas as classes das views criadas nesse processo

git checkout -b feat/auth-laravel-ui-bootstrap

sail composer require laravel/ui
sail artisan ui bootstrap --auth (entrada no terminal: yes)
sail npm remove tailwindcss @tailwindcss/vite
alterar arquivo resources/css/app.css para apenas @import "bootstrap/dist/css/bootstrap.min.css";
alterar arquivo resources/js/app.js para import './bootstrap'; import 'bootstrap'; import '../css/app.css'
sail npm run dev

git add .
git commit -m "feat(auth) instalando laravel/ui e startkit auth com bootstrap"
git push -u origin feat/auth-laravel-ui-bootstrap
pr + merge (develop)
git checkout develop && git pull

===

Localização da aplicação para pt-br

git checkout -b feat/localization-pt-br

sail artisan lang:publish
sail composer require lucascudo/laravel-pt-br-localization --dev
sail artisan vendor:publish --tag=laravel-pt-br-localization
alterar o arquivo .env APP_LOCALE=en -> APP_LOCALE=pt_BR
sail artisan config:cache

git add .
git commit -m "feat(i18n) add localização pt_BR"
git push -u origin feat/localization-pt-br
pr + merge (develop)
git checkout develop && git pull

===

Criando a estrutura do banco de dados e relacionamentos

sail artisan make:model Recipe -m (esse comando criar o model + migration)
sail artisan make:model Comment -m
sail artisan make:model Rating -m
sail artisan make:migration add_softdelete_in_users --table=users

git add app/Models/_ database/migrations/_
git commit -m "feat(domain): add migrations e models"

git add app/Models/\*
git commit -m "feat(domain): add relacionamentos"
git add database/migrations/2026_02_07_161326_create_ratings_table.php
git commit -m "refactor(domain): retirando coluna duplicada created_at e updated_at"
sail artisan migrate:fresh

===

Criando factories (popular banco com dados fake)

sail artisan make:factory RecipeFactory
sail artisan make:factory CommentFactory
sail artisan make:factory RatingFactory

git add database/factories/_
git commit -m "feat(domain): add factories para popular db com dados fake"
git add app/Models/_
git commit -m "refactor(domain): add trait HasFactory para conexão entre models e factories"
git add app/Models/Recipe.php
git commit -m "refactor(domain): add cast em ingredients para conversão de json para array"
git add database/seeders/DatabaseSeeder.php
git commit -m "feat(domain): add lógica de criação de dados fake"
git push -u origin feat/domain
pr + merge
git checkout develop && git pull

===

Criando testes de domínio

git checkout -b feat/testing-domain
git add tests/CreatesApplication.php tests/Pest.php tests/TestCase.php

sail artisan make:test DatabaseSeederTest
sail pest tests/Feature/DatabaseSeederTest.php
sail pest

git add tests/Feature/DatabaseSeederTest.php
git commit -m "feat(test): testes para criação de dados fake"

git add database/factories/RecipeFactory.php
git commit -m "fix(test): corrigir geração de ingredients no RecipeFactory"

sail artisan make:test RecipeTest --unit
git add tests/Unit/RecipeTest.php
git commit -m "feat(test): testes para entidade recipe"

git add database/factories/RecipeFactory.php
git commit -m "fix(test): corrigir geração de steps no RecipeFactory"

git add app/Models/Recipe.php
git commit -m "fix(test): adicionar SoftDeletes trait ao Recipe model"

sail artisan make:test RatingTest --unit

git add app/Models/Recipe.php
git commit -m "fix(test): adicionando cast para rating_avg e rating_count"

git add tests/Unit/RatingTest.php
git commit -m "feat(test): testes para a entidade rating"

sail artisan make:test CommentTest --unit
git add tests/Unit/CommentTest.php
git commit -m "feat(test): testes para entidade comment"

git add tests/Feature/DatabaseSeederTest.php
git commit -m "refactor(test): reescrevendo teste DatabaseSeederTest com sintaxe Pest"

rm tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php

git add tests/Pest.php
git commit -m "chore(test): limpeza e finalização testes domínio"

git add -u
git commit -m "chore(test): excluindo arquivos de testes de exemplo"

git push -u origin feat/testing-domain
pr + merge
git checkout develop && git pull

===

Criando CRUD Recipe

git checkout -b feat/recipe-crud

sail artisan make:request StoreRecipeRequest
sail artisan make:request UpdateRecipeRequest
sail artisan make:class DTOs/RecipeDTO
sail artisan make:class Services/RecipeService

sail artisan make:migration changing_column_type_steps_table_recipes --table=recipes
sail artisan migrate:fresh --seed

sail artisan make:migration add_slug_column_table_recipe --table=recipes
sail artisan migrate:fresh --seed

git add app/Models/Recipe.php database/factories/RecipeFactory.php tests/Unit/RecipeTest.php database/migrations/2026_02_09_112854_add_slug_column_table_recipe.php
git commit -m "refactor(domain): add coluna slug em recipes"

sail artisan make:controller RecipeController --resource --model=Recipe
sail artisan make:policy RecipePolicy --model=Recipe
sail artisan make:test Recipe/RecipeControllerTest.php
sail artisan make:test Recipe/RecipePolicyTest.php
sail artisan make:test Recipe/RecipeServiceTest.php --unit
sail artisan make:test Recipe/RecipeDTOTest.php --unit
sail artisan make:test Recipe/RecipeFormRequestTest.php --unit
sail artisan make:migration add_value_default_rating_avg_table_recipes --table=recipes

git add database/migrations/2026_02_09_104359_changing_column_type_steps_table_recipes.php
git commit -m "fix(domain): alterando tipo da coluna steps de varchar para jsonb

git add database/migrations/2026_02_09_143516_add_value_default_rating_avg_table_recipes.php
git commit -m "refactor(domain): definindo valor default para coluna rating_avg na tabela recipes"

git add app/Models/Recipe.php
git commit -m "fix(recipe): definido valores default para rating"

git add app/Http/Requests/\*
git commit -m "feat(recipe): add validação dos dados de entrada na criação e atualziação"

git add app/Policies/RecipePolicy.php
git commit -m "feat(recipe): add política de edição e exclusão de receitas"

git add tests/\*
git commit -m "feat(test): add testes para o CRUD recipes"

git add app/DTOs/RecipeDTO.php
git commit -m "feat(recipe): add DTO para padronização, sanitização e formatação dos dados de entrada"

git add app/Services/RecipeService.php
git commit -m "feat(recipe): add Service para centralizar regras de negócio e lógica de persistências dos dados"

git add routes/web.php
git commit -m "feat(recipe): add rotas públicas e privados para CRUD recipe"

git add app/Http/Controllers/RecipeController.php
git commit -m "feat(recipe): add métodos de criação, atualização e deleção de uma receita"

sail artisan make:controller CommentController
sail artisan make:controller RatingController

git add resources/views/recipes/\*
git commit -m "feat(ui): add telas para CRUD de recipes"

git add resources/views/auth/login.blade.php resources/views/auth/register.blade.php
git commit -m "refactor(ui): add botão sigin e register nas telas de login e register"

git add resources/views/layouts/app.blade.php
git commit -m "refactor(ui): ajustando visibilidade de links de acordo com estado do login"

git add app/Http/Controllers/Auth/LoginController.php app/Http/Controllers/Auth/RegisterController.php
git commit -m "refactor(recipe): alterando redirecionamento login logout"

git add app/Http/Requests/UpdateRecipeRequest.php
git commit -m "fix(recipe): ajustando lógica de unicidade de recipes.title"

git add routes/web.php
git commit -m "refactor(recipe): ajustando lógica de redirecionamento da rota /"

git add app/Services/RecipeService.php
git commit -m "refactor(recipe): add métodos de listagem pública e privada de receitas"

git add app/Http/Controllers/RecipeController.php
git commit -m "feat(recipe): add métodos faltantes pro CRUD recipes"

git push -u origin feat/recipe-crud
pr + merge
git checkout develop && git pull

===

Gestão de Comentários

git checkout -b feat/comment-management

sail artisan make:request StoreCommentRequest
sail artisan make:class DTOs/CommentDTO
sail artisan make:class Services/CommentService
sail artisan notifications:table
sail artisan migrate
sail artisan make:notification NewCommentNotification

git add .
git commit -m "feat(comment): add gestão completa de comentários"
git push -u origin feat/recipe-crud
pr + merge
git checkout develop && git pull

===

Gestão de Avaliações

git checkout -b feat/rating-management

sail artisan make:request StoreRatingRequest
sail artisan make:class DTOs/RatingDTO
sail artisan make:class Services/RatingService
sail artisan make:notification NewRatingNotification

git add .
git commit -m "feat(rating): add gestão completa de avaliações"
git push -u origin feat/ranting-management
pr + merge
git checkout develop && git pull

===

Pesquisa e filtragem

git checkout -b feat/search-and-filters

sail artisan make:request RecipeSearchRequest

git add .
git commit -m "feat(search-and-filter): add pesquisa por nome e filtragem por nome, data de criação e avaliação"
git push -u origin feat/search-and-filters
pr + merge
git checkout develop && git pull

===

Alterando parâmetro de rota do id para slug

git checkout -b refactor/add-slug
git add .
git commit -m "refactor(recipes): alterando parâmetro de rota de id para slug"
git push -u origin refactor/add-slug
pr + merge
git checkout develop && git pull

===

Ajustando componente de pagination

git checkout -b refactor/paginate

sail artisan vendor:publish --tag=laravel-pagination

git add .
git commit -m "refactor(paginate): ajustando layout do componente paginate"
git push -u origin refactor/paginate
pr + merge
git checkout develop && git pull

===

Ajustando responsividade da UI

git checkout -b refactor/ui-responsive
git add .
git commit -m "refactor(ui): ajustando responsividade"
git push -u origin refactor/ui-responsive
pr + merge
git checkout develop && git pull

===

Adicionando feedback ao criar e editar receitas

git checkout -b feat/feedback-recipe
git add .
git commit -m "feat(feedback): add mensagens de feedback"
git push -u origin feat/feedback-recipe
pr + merge
git checkout develop && git pull
