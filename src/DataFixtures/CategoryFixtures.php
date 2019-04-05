<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Picture;
use App\Entity\Production;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        $categories = [];

        for ($iCategory=0; $iCategory<4; $iCategory++) {
            $categories[$iCategory] = new Category();
            $categories[$iCategory]->setName($faker->word);

            $productions = [];
            for ($iProduction=0; $iProduction<$faker->numberBetween(1, 10); $iProduction++) {
                $productions[$iProduction] = new Production();
                $productions[$iProduction]->setName($faker->unique()->word)
                                         ->setDescription($faker->paragraph(5));

                $pictures = [];
                for ($iPicture=0; $iPicture<$faker->numberBetween(1, 5); $iPicture++) {
                    $pictures[$iPicture] = new Picture();
                    $pictures[$iPicture]->setName($faker->imageUrl(400, 400))
                                        ->setIdProduction($productions[$iProduction]);

                    $productions[$iProduction]->addPicture($pictures[$iPicture]);

                    $manager->persist($pictures[$iPicture]);
                }

                $categories[$iCategory]->addProduction($productions[$iProduction]);
                $productions[$iProduction]->addCategory($categories[$iCategory]);

                $manager->persist($productions[$iProduction]);
            }

            $manager->persist($categories[$iCategory]);
        }

        $manager->flush();
    }
}
