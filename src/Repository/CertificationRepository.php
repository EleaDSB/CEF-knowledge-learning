<?php

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\User;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certification>
 */
class CertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certification::class);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['obtainedAt' => 'DESC']);
    }

    public function userHasCertification(User $user, Theme $theme): bool
    {
        return (bool) $this->findOneBy(['user' => $user, 'theme' => $theme]);
    }
}
