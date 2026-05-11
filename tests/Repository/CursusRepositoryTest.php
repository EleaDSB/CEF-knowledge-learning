<?php

namespace App\Tests\Repository;

use App\Repository\CursusRepository;
use App\Tests\TestHelper;

class CursusRepositoryTest extends TestHelper
{
    private CursusRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(CursusRepository::class);
    }

    public function testFindAllReturnsSixCursus(): void
    {
        $all = $this->repo->findAll();
        $this->assertCount(6, $all, 'Les fixtures doivent créer 6 cursus');
    }

    public function testFindBySlugReturnsCursus(): void
    {
        $cursus = $this->repo->findBySlug('cursus-guitare');
        $this->assertNotNull($cursus);
        $this->assertSame('50.00', $cursus->getPrice());
    }

    public function testFindBySlugReturnsNullForUnknownSlug(): void
    {
        $cursus = $this->repo->findBySlug('slug-inconnu');
        $this->assertNull($cursus);
    }

    public function testCursusHasLessons(): void
    {
        $cursus = $this->repo->findBySlug('cursus-dev-web');
        $this->assertNotNull($cursus);
        $this->assertCount(2, $cursus->getLessons());
    }
}
