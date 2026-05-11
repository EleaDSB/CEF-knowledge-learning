<?php

namespace App\Repository;

use App\Entity\Cursus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Cursus entities.
 *
 * Provides data-access methods for training cursus, including slug-based lookup
 * used by catalog and shop routes.
 *
 * @extends ServiceEntityRepository<Cursus>
 */
class CursusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cursus::class);
    }

    /**
     * Finds a cursus by its URL slug.
     *
     * @param string $slug The unique slug identifying the cursus.
     * @return Cursus|null The matching cursus, or null if not found.
     */
    public function findBySlug(string $slug): ?Cursus
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
