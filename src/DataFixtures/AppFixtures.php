<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $categories = $this->createCategories($manager);
        $this->createArtists($manager, $categories);
        $this->createAdminUser($manager);
        $manager->flush();
    }

    private function createCategories(ObjectManager $manager): array
    {
        $data = [
            'soundsystem'     => 'Soundsystem',
            'producer'        => 'Producer',
            'selector'        => 'Selector',
            'singer-mc'       => 'Singer • MC',
            'record-label'    => 'Record Label',
            'instrumentalist' => 'Instrumentalist',
            'promoter'        => 'Promoter',
            'live-act'        => 'Live Act',
        ];

        $categories = [];
        foreach ($data as $slug => $name) {
            $cat = new Category();
            $cat->setName($name)->setSlug($slug);
            $manager->persist($cat);
            $categories[$slug] = $cat;
        }

        return $categories;
    }

    private function createArtists(ObjectManager $manager, array $cats): void
    {
        $artists = [
            ['Ites Vibration', 'Brighton', 'England', ['selector', 'singer-mc', 'record-label']],
            ['Reemshot', 'Lille', 'France', ['selector']],
            ['Edica+', 'New York', 'USA', ['selector', 'promoter']],
            ['Sis D', 'Munich', 'Germany', ['soundsystem', 'selector', 'promoter']],
            ['Esaia', 'Geneva', 'Switzerland', ['producer', 'live-act']],
            ['Sanga Mama Afrika Soundsystem', 'Paris', 'France', ['soundsystem', 'selector']],
            ['Black Omolo', 'Rotterdam', 'Netherlands', ['singer-mc']],
            ['Maca Carnevali', 'Madrid', 'Spain', ['soundsystem', 'selector']],
            ['Siren Sisters Soundsystem', 'Freising', 'Germany', ['soundsystem', 'selector', 'promoter']],
            ['Melie Mel', 'Strasbourg', 'France', ['soundsystem', 'selector', 'promoter']],
            ['Vixen Sound', 'Glasgow', 'Scotland', ['soundsystem', 'producer', 'selector', 'singer-mc', 'promoter']],
            ['MariJah', 'Ghent', 'Belgium', ['singer-mc', 'live-act']],
            ['Jah Vibes Soundsystem', 'Cologne', 'Germany', ['soundsystem', 'selector']],
            ['Wends MC', 'Edinburgh', 'Scotland', ['promoter']],
            ['Set Ambessa', 'Munich', 'Germany', ['selector', 'promoter']],
            ['Matilda Dona', 'Bordeaux', 'France', ['selector']],
            ['Hanameicy', 'Berlin', 'Germany', ['selector', 'promoter']],
            ['Empress Ayeola', 'London', 'England', ['soundsystem', 'selector', 'singer-mc']],
            ['DJ Kayla Kush', 'Amsterdam', 'Netherlands', ['selector', 'promoter']],
            ['Ariwa Empress', 'Bristol', 'England', ['singer-mc', 'record-label']],
        ];

        foreach ($artists as [$name, $city, $country, $slugs]) {
            $artist = new Artist();
            $artist->setName($name)
                ->setCity($city)
                ->setCountry($country)
                ->setDescription('')
                ->setPicture('')
                ->setAcceptedAt(new \DateTimeImmutable());

            foreach (array_unique($slugs) as $slug) {
                if (isset($cats[$slug])) {
                    $artist->addCategory($cats[$slug]);
                }
            }

            $manager->persist($artist);
        }
    }

    private function createAdminUser(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@her.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setName('Admin');
        $admin->setRole(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin);
    }
}
