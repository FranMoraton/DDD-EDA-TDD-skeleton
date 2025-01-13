<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Faker\Providers;

use Faker\Provider\Base;

final class CustomFaker extends Base
{
    public function validPassword(): string
    {
        $uppercase = strtoupper($this->generator->randomLetter());
        $lowercase = strtolower($this->generator->randomLetter());
        $number = $this->generator->numberBetween(0, 9);
        $specialCharacter = $this->generator->randomElement(['@', '$', '!', '%', '*', '?', '&', '#']);
        $remainingLength = $this->generateRemainingCharacters(4);

        $components = [$uppercase, $lowercase, $number, $specialCharacter, $remainingLength];
        shuffle($components);

        return implode('', $components);
    }

    private function generateRemainingCharacters(int $length): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@$!%*?&#';
        $remaining = '';

        for ($i = 0; $i < $length; $i++) {
            $remaining .= $characters[\random_int(0, strlen($characters) - 1)];
        }

        return $remaining;
    }
}
