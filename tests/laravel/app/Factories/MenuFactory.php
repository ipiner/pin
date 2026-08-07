<?php

namespace App\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => uniqid(),
            'code' => uniqid(),
            'type' => Menu::MENU,
            'enabled' => Menu::ENABLED,
        ];
    }
}
