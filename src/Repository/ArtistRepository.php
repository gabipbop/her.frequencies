<?php

namespace App\Repository;

use App\Entity\Artist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artist>
 */
class ArtistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artist::class);
    }

    /** @return Artist[] */
    public function findAcceptedByCategories(array $categorySlugs = []): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.acceptedAt IS NOT NULL')
            ->leftJoin('a.categories', 'c')
            ->addSelect('c')
            ->orderBy('a.name', 'ASC');

        if ($categorySlugs) {
            $qb->andWhere('c.slug IN (:slugs)')
               ->setParameter('slugs', $categorySlugs);
        }

        return $qb->getQuery()->getResult();
    }
}
