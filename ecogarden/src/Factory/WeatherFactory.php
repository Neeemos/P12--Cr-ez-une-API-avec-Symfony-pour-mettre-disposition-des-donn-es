<?php

namespace App\Factory;

use App\Entity\Weather;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Weather>
 */
final class WeatherFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Weather::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'city' => self::faker()->city(),
            'conditions' => self::faker()->randomElement(['ensoleillé', 'pluvieux', 'nuageux', 'neigeux']),
            'lastUpdated' => self::faker()->dateTimeThisMonth(),
            'temperature' => self::faker()->randomFloat(2, -10, 35),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Weather $weather): void {})
        ;
    }
}
