<?php

namespace App\Fakers;

class FakerExample implements GoogleSheetFaker
{
    public function needRowsAmount(): int
    {
        return 407;
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'user_email' => fake()->email,
            'country' => fake()->country,
            'job_title' => fake()->jobTitle,
            'number' => rand(1000, 100000000),
            'company' => fake()->company,
            'street' => fake()->streetName,
            'city' => fake()->city,
            'date' => fake()->dateTime()->format('d-m-Y H:i:s'),
        ];
    }
}