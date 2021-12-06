<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROGRAMS = [
        'Stranger Things',
        'La Casa de Papel',
        'Maid',
        'The Witcher',
        'Qui a tué Sara ?',
    ];

    public function load(ObjectManager $manager)
    {

        foreach (self::PROGRAMS as $key => $programName) {
        $program = new Program();
        $program->setTitle($programName);
        $program->setSynopsis($programName . 'synopsis');
        $program->setCountry($programName . 'Country');
        $program->setYear(2010);
        $program->setCategory($this->getReference('category_3'));
        //ici les acteurs sont insérés via une boucle pour être DRY mais ce n'est pas obligatoire
        for ($i = 0; $i < count(ActorFixtures::ACTORS); $i++) {
            $program->addActor($this->getReference('actor_' . $i));
        }
            $this->addReference('program_' . $key, $program);
            $manager->persist($program);

    }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ActorFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
