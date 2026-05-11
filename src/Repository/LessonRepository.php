<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Lesson entities.
 *
 * Provides data-access methods for individual lessons within a cursus,
 * including slug-based lookup used by catalog and lesson-validation routes.
 *
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * Finds a lesson by its URL slug.
     *
     * @param string $slug The unique slug identifying the lesson.
     * @return Lesson|null The matching lesson, or null if not found.
     */
    public function findBySlug(string $slug): ?Lesson
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
