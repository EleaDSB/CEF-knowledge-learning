<?php

namespace App\Repository;

use App\Entity\LessonProgress;
use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LessonProgress>
 */
class LessonProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonProgress::class);
    }

    public function findOneByUserAndLesson(User $user, Lesson $lesson): ?LessonProgress
    {
        return $this->findOneBy(['user' => $user, 'lesson' => $lesson]);
    }

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
