<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehiculo>
 */
class VehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $marcas = ['Toyota', 'Nissan', 'Mazda', 'Hyundai', 'Kia', 'Chevrolet', 'Ford', 'Renault'];
        $modelos = [
            'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Hilux', 'Fortuner'],
            'Nissan' => ['Versa', 'Sentra', 'X-Trail', 'Frontier', 'Kicks'],
            'Mazda' => ['Mazda3', 'Mazda6', 'CX-5', 'CX-9', 'CX-30'],
            'Hyundai' => ['Accent', 'Elantra', 'Tucson', 'Santa Fe', 'Creta'],
            'Kia' => ['Rio', 'Forte', 'Seltos', 'Sportage', 'Sorento'],
            'Chevrolet' => ['Spark', 'Aveo', 'Tracker', 'Equinox', 'Colorado'],
            'Ford' => ['Fiesta', 'Focus', 'Escape', 'Explorer', 'Ranger'],
            'Renault' => ['Sandero', 'Logan', 'Duster', 'Koleos', 'Captur'],
        ];

        $marca = $this->faker->randomElement($marcas);
        $modelo = $this->faker->randomElement($modelos[$marca]);

        $precioCompra = $this->faker->numberBetween(20000000, 80000000);
        $precioVenta = $precioCompra * 1.15; // 15% margen

        return [
            'proveedor_id' => Proveedor::factory(),
            'marca' => $marca,
            'modelo' => $modelo,
            'año' => $this->faker->numberBetween(2018, 2024),
            'color' => $this->faker->safeColorName(),
            'placa' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'vin' => $this->faker->unique()->regexify('[A-HJ-NPR-Z0-9]{17}'),
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta,
            'estado' => 'DISPONIBLE',
            'stock' => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * Indicate that the vehicle is available.
     */
    public function disponible(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'DISPONIBLE',
            'stock' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the vehicle is sold out.
     */
    public function vendido(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'VENDIDO',
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the vehicle is in maintenance.
     */
    public function mantenimiento(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'MANTENIMIENTO',
            'stock' => $this->faker->numberBetween(0, 5),
        ]);
    }

    /**
     * Indicate that the vehicle has low stock.
     */
    public function bajoStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 1,
        ]);
    }

    /**
     * Indicate that the vehicle has high stock.
     */
    public function altoStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(5, 20),
        ]);
    }

    /**
     * Indicate the vehicle's brand.
     */
    public function marca(string $marca): static
    {
        return $this->state(fn (array $attributes) => [
            'marca' => $marca,
        ]);
    }

    /**
     * Indicate the vehicle's year.
     */
    public function año(int $año): static
    {
        return $this->state(fn (array $attributes) => [
            'año' => $año,
        ]);
    }
}