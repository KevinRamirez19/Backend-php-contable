<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(30000000, 120000000);
        $iva = $subtotal * 0.19;
        $total = $subtotal + $iva;

        return [
            'cliente_id' => Cliente::factory(),
            'numero_factura' => 'FV-' . now()->year . '-' . $this->faker->unique()->numberBetween(100000, 999999),
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'estado_dian' => $this->faker->randomElement(['PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'ENVIADA']),
            'cufe' => $this->faker->boolean(70) ? $this->faker->uuid() : null,
            'qr_code' => $this->faker->boolean(50) ? $this->faker->text(100) : null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the sale is pending DIAN approval.
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_dian' => 'PENDIENTE',
            'cufe' => null,
            'qr_code' => null,
        ]);
    }

    /**
     * Indicate that the sale is accepted by DIAN.
     */
    public function aceptada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_dian' => 'ACEPTADA',
            'cufe' => $this->faker->uuid(),
            'qr_code' => $this->faker->text(100),
        ]);
    }

    /**
     * Indicate that the sale is rejected by DIAN.
     */
    public function rechazada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_dian' => 'RECHAZADA',
            'cufe' => null,
            'qr_code' => null,
        ]);
    }

    /**
     * Indicate that the sale is sent to DIAN.
     */
    public function enviada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_dian' => 'ENVIADA',
            'cufe' => $this->faker->uuid(),
            'qr_code' => null,
        ]);
    }

    /**
     * Indicate a recent sale.
     */
    public function reciente(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_venta' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate an old sale.
     */
    public function antigua(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Venta $venta) {
            if (empty($venta->numero_factura)) {
                $venta->numero_factura = $venta->generarNumeroFactura();
            }
        });
    }
}