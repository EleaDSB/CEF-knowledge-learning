<?php

namespace App\Repository;

use App\Entity\Purchase;
use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Purchase entities.
 *
 * Centralises all access-control queries for purchased content: whether a user
 * owns a full cursus or an individual lesson (either directly or via a cursus purchase).
 *
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * Returns true if the user has purchased the given cursus.
     *
     * @param User   $user   The authenticated user.
     * @param Cursus $cursus The cursus to check.
     * @return bool True when a purchase record exists for the user/cursus pair.
     */
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

    /**
     * Returns true if the user has access to the given lesson.
     *
     * Access is granted when the user has either purchased the lesson individually
     * or purchased the cursus that contains it.
     *
     * @param User   $user   The authenticated user.
     * @param Lesson $lesson The lesson to check.
     * @return bool True when the user owns the lesson or its parent cursus.
     */
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

    /**
     * Returns all purchases for the given user, ordered by most recent first.
     *
     * @param User $user The authenticated user.
     * @return Purchase[] An array of Purchase entities.
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['purchasedAt' => 'DESC']);
    }
}
