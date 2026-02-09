<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Recipe;

class RecipeDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public array $ingredients,
        public string|array $steps
    ) {
        // Sanitização no construtor
        $this->title = $this->sanitizeTitle($title);
        $this->description = $this->sanitizeDescription($description);
        $this->ingredients = $this->sanitizeIngredients($ingredients);
        $this->steps = $this->sanitizeSteps($steps);
    }

    /**
     * Create from array (from FormRequest)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            ingredients: $data['ingredients'],
            steps: $data['steps'],
        );
    }

    /**
     * Create from Recipe model (for edit forms)
     */
    public static function fromModel(Recipe $recipe): self
    {
        return new self(
            title: $recipe->title,
            description: $recipe->description,
            ingredients: $recipe->ingredients,
            steps: $recipe->steps,
        );
    }

    /**
     * Convert to array for database
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'steps' => $this->steps,
        ];
    }

    /**
     * Sanitize title - limpar e formatar
     */
    private function sanitizeTitle(string $title): string
    {
        $title = trim(preg_replace('/\s+/', ' ', $title));
        return mb_strtolower($title, 'UTF-8');
    }

    /**
     * Sanitize description - limpar se existir
     */
    private function sanitizeDescription(?string $description): ?string
    {
        if (empty($description)) {
            return null;
        }
        return trim($description);
    }

    /**
     * Sanitize ingredients - limpar array e remover vazios
     */
    private function sanitizeIngredients(array $ingredients): array
    {
        $cleanIngredients = [];

        foreach ($ingredients as $ingredient) {
            $cleanIngredient = trim($ingredient);

            if ($cleanIngredient === '') {
                continue;
            }

            // Normaliza (pra "açúcar" e "Açúcar" virarem iguais)
            $cleanIngredient = mb_convert_case($cleanIngredient, MB_CASE_TITLE, 'UTF-8');

            $cleanIngredients[] = $cleanIngredient;
        }

        return array_values(array_unique($cleanIngredients));
    }


    /**
     * Sanitize steps - converter string para array
     */
    private function sanitizeSteps(string|array $steps): array
    {
        $lines = is_array($steps) ? $steps : preg_split("/\R/u", $steps);
        $cleanSteps = [];

        foreach ($lines as $index => $line) {
            $cleanLine = trim((string)$line);

            if ($cleanLine !== '') {
                // Adicionar prefixo "Passo N:" se não existir
                if (!preg_match('/^Passo \d+:/i', $cleanLine)) {
                    $cleanLine = "Passo " . ($index + 1) . ": " . $cleanLine;
                }
                $cleanSteps[] = $cleanLine;
            }
        }

        return array_values(array_unique($cleanSteps));
    }
}
