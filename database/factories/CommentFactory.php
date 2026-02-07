<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'user_id' => User::factory(),
            'body' => $this->generateRealisticComment(),
        ];
    }

    private function generateRealisticComment(): string
    {
        $commentTypes = [
            'positive' => [
                'Adorei esta receita! Ficou perfeita.',
                'Excelente! Já fiz várias vezes e sempre dá certo.',
                'Maravilhoso! Minha família adorou.',
                'Perfeito! Os ingredientes combinam muito bem.',
                'Sensacional! Superou minhas expectativas.',
            ],
            'constructive' => [
                'Ficou bom, mas acho que poderia reduzir um pouco o sal.',
                'Gostei! Na próxima vez vou adicionar mais temperos.',
                'Bom resultado! Precisei assar por mais 10 minutos.',
                'Interessante! Vou tentar com outros ingredientes.',
                'Legal! Achei um pouco doce, vou diminuir o açúcar.',
            ],
            'question' => [
                'Posso substituir o fermento por bicarbonato?',
                'Quanto tempo dura na geladeira?',
                'Posso congelar? Como faço o descongelamento?',
                'Qual o melhor forno: elétrico ou a gás?',
                'Dá pra fazer versão vegetariana?',
            ],
        ];
        $type = $this->faker->randomElement(['positive', 'constructive', 'question']);
        $comments = $commentTypes[$type];
        return $this->faker->randomElement($comments);
    }

    public function positive(): static
    {
        return $this->state(fn(array $attributes) => [
            'body' => $this->faker->randomElement([
                'Adorei esta receita! Ficou perfeita.',
                'Excelente! Já fiz várias vezes e sempre dá certo.',
                'Maravilhoso! Minha família adorou.',
            ]),
        ]);
    }

    public function constructive(): static
    {
        return $this->state(fn(array $attributes) => [
            'body' => $this->faker->randomElement([
                'Ficou bom, mas acho que poderia reduzir um pouco o sal.',
                'Gostei! Na próxima vez vou adicionar mais temperos.',
                'Bom resultado! Precisei assar por mais 10 minutos.',
            ]),
        ]);
    }
}
