<?php

namespace App\Fakers;

interface GoogleSheetFaker
{
    public function needRowsAmount(): int;

    public function definition(): array;
}