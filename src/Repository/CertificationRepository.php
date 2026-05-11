<?php

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\User;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Certification entities.
 *
 * Provides queries to retrieve earned certifications and to check whether a user
 * has already obtained a certification for a given theme.
 *
 * @extends ServiceEntityRepository<Certification>
 */
class CertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certification::class);
    }

    /**
     * Returns all certifications earned by the given user, most recent first.
     *
     * @param User $user The authenticated user.
     * @return Certification[] An array of Certification entities.
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['obtainedAt' => 'DESC']);
    }

    /**
     * Checks whether the user already holds a certification for the given theme.
     *
     * @param User  $user  The authenticated user.
     * @param Theme $theme The theme to check.
     * @return bool True when a certification record exists for the user/theme pair.
     */
    public function userHasCertification(User $user, Theme $theme): bool
    {
        return (bool) $this->findOneBy(['user' => $user, 'theme' => $theme]);
    }
}
