<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::of($this->faker->unique()->words(1, true))
                ->title()
                ->toString()
                .' Agent',
            'description' => $this->faker->sentence,
            'class' => $this->faker->word,
            'url' => $this->faker->url,
            'skills' => json_encode($this->faker->words(3)),
            'is_public' => $this->faker->boolean,
            'handle' => Str::slug($this->faker->unique()->words(2, true)),
        ];
    }
}
