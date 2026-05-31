<?php

namespace App\Tests\Repository;

use App\Entity\LessonProgress;
use App\Repository\CursusRepository;
use App\Repository\LessonProgressRepository;
use App\Repository\LessonRepository;
use App\Tests\TestHelper;

class LessonProgressRepositoryTest extends TestHelper
{
    private LessonProgressRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(LessonProgressRepository::class);
    }

    public function testFindOneByUserAndLessonReturnsNullWhenNotStarted(): void
    {
        $user   = $this->createVerifiedUser('progress_test@test.com');
        $lesson = static::getContainer()->get(LessonRepository::class)->findBySlug('html-css');

        $progress = $this->repo->findOneByUserAndLesson($user, $lesson);
        $this->assertNull($progress);
    }

    public function testFindOneByUserAndLessonReturnsProgressWhenExists(): void
    {
        $user   = $this->createVerifiedUser('progress2@test.com');
        $lesson = static::getContainer()->get(LessonRepository::class)->findBySlug('html-css');

        $progress = new LessonProgress();
        $progress->setUser($user);
        $progress->setLesson($lesson);
        $progress->setIsCompleted(true);
        self::$em->persist($progress);
        self::$em->flush();

        $found = $this->repo->findOneByUserAndLesson($user, $lesson);
        $this->assertNotNull($found);
        $this->assertTrue($found->isCompleted());
    }

    public function testCountCompletedForCursusReturnsZeroWhenNoneCompleted(): void
    {
        $user   = $this->createVerifiedUser('count_test@test.com');
        $cursus = static::getContainer()->get(CursusRepository::class)->findBySlug('cursus-dev-web');

        $count = $this->repo->countCompletedForCursus($user, $cursus);
        $this->assertSame(0, $count);
    }

    public function testCountCompletedForCursusCountsCorrectly(): void
    {
        $user   = $this->createVerifiedUser('count2@test.com');
        $cursus = static::getContainer()->get(CursusRepository::class)->findBySlug('cursus-dev-web');

        // Complete 1 out of 2 lessons
        $lesson = $cursus->getLessons()->first();
        $progress = new LessonProgress();
        $progress->setUser($user);
        $progress->setLesson($lesson);
        $progress->setIsCompleted(true);
        self::$em->persist($progress);
        self::$em->flush();

        $count = $this->repo->countCompletedForCursus($user, $cursus);
        $this->assertSame(1, $count);
    }
}
