<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->generateRealisticTitle();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'description' => $this->faker->optional(0.7)->sentence(10),
            'ingredients' => $this->generateIngredients(),
            'steps' => $this->generateSteps(),
            'slug' => Str::slug($title),
            'rating_avg' => 0.00,
            'rating_count' => 0,
        ];
    }

    private function generateRealisticTitle(): string
    {
        $titles = [
            'Bolo de Chocolate Fofinho',
            'Lasanha à Bolonhesa Tradicional',
            'Frango ao Curry com Arroz Basmati',
            'Salada Caprese Clássica',
            'Pizza Margherita Caseira',
            'Sopa de Cebola Caramelizada',
            'Risoto de Cogumelos Selvagens',
            'Tacos de Carne Moleda Mexicanos',
            'Bruschetta de Tomate e Manjericão',
            'Strogonoff de Carne',
        ];
        return $this->faker->randomElement($titles);
    }

    private function generateIngredients(): array
    {
        $allIngredients = [
            '100g farinha de trigo',
            '3 ovos',
            '1L de leite',
            '250g manteiga',
            '2xc açúcar',
            'sal a gosto',
            'pimenta do reino a gosto',
            '3 alho',
            '1 cebola',
            '200ml azeite de oliva',
            '3 tomates',
            '400g queijo parmesão',
            'manjericão a gosto',
            'orégano a gosto',
            '500g frango',
            '1Kg carne moída',
            '200g cogumelos',
            '2xc arroz',
            '4xc feijão',
            '5 batatas',
            '1 cenoura',
            '350g brócolis',
            'espinafre a gosto',
            '1 pimentão'
        ];
        return $this->faker->randomElements($allIngredients, $this->faker->numberBetween(5, 12));
    }

    private function generateSteps(): array
    {
        $steps = [];
        $stepCount = $this->faker->numberBetween(3, 8);
        for ($i = 1; $i <= $stepCount; $i++) {
            $steps[] = "Passo {$i}: " . $this->faker->sentence(8);
        }
        return $steps;
    }

    public function withoutDescription(): static
    {
        return $this->state(fn(array $attributes) => [
            'description' => null,
        ]);
    }

    public function withLongDescription(): static
    {
        return $this->state(fn(array $attributes) => [
            'description' => $this->faker->text(400),
        ]);
    }
}
