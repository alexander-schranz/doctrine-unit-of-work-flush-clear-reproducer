<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $organisation = new \App\Entity\Organisation();

        $customerA = new \App\Entity\Customer('A');
        $customerB = new \App\Entity\Customer('B');

        $manager->persist($organisation);
        $manager->persist($customerA);
        $manager->persist($customerB);

        $manager->flush();
    }
}

