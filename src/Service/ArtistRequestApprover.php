<?php

namespace App\Service;

use App\Entity\ArtistRequest;
use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\Link;
use App\Enum\ArtistRequestStatus;
use Doctrine\ORM\EntityManagerInterface;

class ArtistRequestApprover
{
    public function __construct(private EntityManagerInterface $em)
    {}

    public function approve(ArtistRequest $artistRequest): Artist
    {
        $artist = new Artist();
        $artist->setName($artistRequest->getName());
        $artist->setCountry($artistRequest->getCountry());
        $artist->setCity($artistRequest->getCity());
        $artist->setDescription($artistRequest->getDescription());
        $artist->setPicture($artistRequest->getPicture());
        $artist->setAcceptedAt(new \DateTimeImmutable());

        // Catégories
        foreach ($artistRequest->getRawCategories() as $categoryId) {
            $category = $this->em->getRepository(Category::class)->find($categoryId);
            if ($category) {
                $artist->addCategory($category);
            }
        }

        // Liens
        foreach ($artistRequest->getRawLinks() as $rawLink) {
            $link = new Link();
            $link->setName($rawLink['name']);
            $link->setUrl($rawLink['url']);
            $this->em->persist($link);
            $artist->addLink($link);
        }

        $artistRequest->setStatus(ArtistRequestStatus::APPROVED);

        $this->em->persist($artist);
        $this->em->flush();

        return $artist;
    }

    public function reject(ArtistRequest $artistRequest): void
    {
        $artistRequest->setStatus(ArtistRequestStatus::REJECTED);
        $this->em->flush();
    }
}
