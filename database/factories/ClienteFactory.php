<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiposDocumento = ['CC', 'NIT', 'CE', 'PASAPORTE'];
        
        return [
            'nombre' => $this->faker->name(),
            'direccion' => $this->faker->address(),
            'tipo_documento' => $this->faker->randomElement($tiposDocumento),
            'numero_documento' => $this->generateDocumentNumber(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }

    /**
     * Generate a document number based on document type.
     */
    private function generateDocumentNumber(): string
    {
        $type = $this->faker->randomElement(['CC', 'NIT', 'CE', 'PASAPORTE']);
        
        return match($type) {
            'CC' => $this->faker->numerify('##########'),
            'NIT' => $this->faker->numerify('############-#'),
            'CE' => $this->faker->bothify('??########'),
            'PASAPORTE' => $this->faker->bothify('??########'),
            default => $this->faker->numerify('##########'),
        };
    }

    /**
     * Indicate that the client has CC document type.
     */
    public function cc(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_documento' => 'CC',
            'numero_documento' => $this->faker->numerify('##########'),
        ]);
    }

    /**
     * Indicate that the client has NIT document type.
     */
    public function nit(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_documento' => 'NIT',
            'numero_documento' => $this->faker->numerify('############-#'),
        ]);
    }

    /**
     * Indicate that the client is a company.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->company(),
            'tipo_documento' => 'NIT',
            'numero_documento' => $this->faker->numerify('############-#'),
        ]);
    }
}