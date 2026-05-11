<?php

namespace App\Tests\Repository;

use App\Repository\LessonRepository;
use App\Tests\TestHelper;

class LessonRepositoryTest extends TestHelper
{
    private LessonRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(LessonRepository::class);
    }

    public function testFindAllReturnsTwelveLessons(): void
    {
        $lessons = $this->repo->findAll();
        $this->assertCount(12, $lessons, 'Les fixtures doivent créer 12 leçons');
    }

    public function testFindBySlugReturnsLesson(): void
    {
        $lesson = $this->repo->findBySlug('html-css');
        $this->assertNotNull($lesson);
        $this->assertSame('32.00', $lesson->getPrice());
    }

    public function testFindBySlugReturnsNullForUnknownSlug(): void
    {
        $lesson = $this->repo->findBySlug('slug-inconnu');
        $this->assertNull($lesson);
    }

    public function testLessonHasContent(): void
    {
        $lesson = $this->repo->findBySlug('html-css');
        $this->assertNotNull($lesson->getContent());
        $this->assertNotEmpty($lesson->getContent());
    }
}
