<?php

namespace App\Repository;

use App\Entity\Purchase;
use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    public function userHasCursus(User $user, Cursus $cursus): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.user = :user AND p.cursus = :cursus')
            ->setParameter('user', $user)
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function userHasLesson(User $user, Lesson $lesson): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.user = :user AND (p.lesson = :lesson OR p.cursus = :cursus)')
            ->setParameter('user', $user)
            ->setParameter('lesson', $lesson)
            ->setParameter('cursus', $lesson->getCursus())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['purchasedAt' => 'DESC']);
    }
}
