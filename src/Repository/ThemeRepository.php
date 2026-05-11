<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Theme entities.
 *
 * Provides data-access methods for the Theme aggregate root, including
 * slug-based lookup used by the catalog routes.
 *
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Finds a theme by its URL slug.
     *
     * @param string $slug The unique slug identifying the theme.
     * @return Theme|null The matching theme, or null if not found.
     */
    public function findBySlug(string $slug): ?Theme
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
