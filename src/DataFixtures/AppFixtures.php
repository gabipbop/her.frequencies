<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $stepper = new Category();
        $stepper->setName('Stepper');
        $stepper->setSlug('stepper');
        $manager->persist($stepper);

        $electronic = new Category();
        $electronic->setName('Electronic');
        $electronic->setSlug('electronic');
        $manager->persist($electronic);

        $sisd = new Artist();
        $sisd->setName('Sis D');
        $sisd->setCountry('Germany');
        $sisd->setCity('Munich');
        $sisd->setDescription('description sis D');
        $sisd->setPicture('sisd.png');
        $sisd->addCategory($stepper);
        $sisd->setAcceptedAt(new \DateTimeImmutable());
        $manager->persist($sisd);

        $esaia = new Artist();
        $esaia->setName('Esaia');
        $esaia->setCountry('Suisse');
        $esaia->setCity('Genève');
        $esaia->setDescription('description esaia');
        $esaia->setPicture('esaia.png');
        $esaia->addCategory($electronic);
        $esaia->setAcceptedAt(new \DateTimeImmutable());
        $manager->persist($esaia);

        $admin = new User();
        $admin->setEmail('admin@her.com');
        $admin->setPassword('admin');
        $admin->setName('Admin');
        $admin->setRole('');
        $admin->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin);

        $manager->flush();
    }
}
