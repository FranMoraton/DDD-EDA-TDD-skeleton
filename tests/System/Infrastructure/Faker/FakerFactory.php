<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Faker;

use App\Tests\System\Infrastructure\Faker\Providers\CustomFaker;
use Faker\Factory;
use Faker\Generator;

final class FakerFactory extends Factory
{
    public const DEFAULT_LOCALE = 'es_ES';

    private static ?Generator $generator = null;

    protected static $defaultProviders = [
        'Address',
        'Barcode',
        'Biased',
        'Color',
        'Company',
        'DateTime',
        'File',
        'HtmlLorem',
        'Image',
        'Internet',
        'Lorem',
        'Medical',
        'Miscellaneous',
        'Payment',
        'Person',
        'PhoneNumber',
        'Text',
        'UserAgent',
        'Uuid',
        'CustomFaker',
    ];

    /** @return Generator | CustomFaker */
    public static function create($locale = self::DEFAULT_LOCALE): Generator
    {
        if (null !== self::$generator) {
            return self::$generator;
        }

        $generator = new Generator();

        foreach (self::$defaultProviders as $provider) {
            $providerClassName = self::getProviderClassname($provider, $locale);
            $generator->addProvider(new $providerClassName($generator));
        }

        self::$generator = $generator;

        return $generator;
    }

    protected static function getProviderClassname($provider, $locale = '')
    {
        $providerClass = self::findLocalProviderClassPath($provider);

        if (null !== $providerClass) {
            return $providerClass;
        }

        return parent::getProviderClassname($provider, $locale);
    }

    private static function findLocalProviderClassPath(string $provider): ?string
    {
        $providerClassPath = \sprintf(
            'App\Tests\System\Infrastructure\Faker\Providers\%s',
            $provider,
        );

        if (\class_exists($providerClassPath, true)) {
            return $providerClassPath;
        }

        return null;
    }
}
