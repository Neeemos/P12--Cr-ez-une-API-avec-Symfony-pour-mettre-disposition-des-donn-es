<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\AdviceFactory;
use App\Factory\UserFactory;
use App\Factory\WeatherFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(10);
        AdviceFactory::createMany(20);
        WeatherFactory::createMany(15);

        $manager->flush();
    }
}
