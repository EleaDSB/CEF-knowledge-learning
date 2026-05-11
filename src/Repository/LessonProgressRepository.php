<?php

namespace App\Repository;

use App\Entity\LessonProgress;
use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for LessonProgress entities.
 *
 * Tracks which lessons a user has completed and exposes aggregate counts
 * used by the automatic certification logic.
 *
 * @extends ServiceEntityRepository<LessonProgress>
 */
class LessonProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonProgress::class);
    }

    /**
     * Finds the progress record for a specific user/lesson pair.
     *
     * @param User   $user   The authenticated user.
     * @param Lesson $lesson The lesson to look up.
     * @return LessonProgress|null The progress record, or null if the lesson has not been started.
     */
    public function findOneByUserAndLesson(User $user, Lesson $lesson): ?LessonProgress
    {
        return $this->findOneBy(['user' => $user, 'lesson' => $lesson]);
    }

    /**
     * Counts the number of completed lessons within a cursus for the given user.
     *
     * Used by the certification logic to determine whether all lessons of a cursus
     * have been validated.
     *
     * @param User   $user   The authenticated user.
     * @param Cursus $cursus The cursus whose lessons are counted.
     * @return int Number of lessons marked as completed.
     */
    public function countCompletedForCursus(User $user, Cursus $cursus): int
    {
        return (int) $this->createQueryBuilder('lp')
            ->select('COUNT(lp.id)')
            ->join('lp.lesson', 'l')
            ->where('lp.user = :user AND l.cursus = :cursus AND lp.isCompleted = true')
            ->setParameter('user', $user)
            ->setParameter('cursus', $cursus)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
