<?php

namespace App\Tests\Repository;

use App\Repository\ThemeRepository;
use App\Tests\TestHelper;

class ThemeRepositoryTest extends TestHelper
{
    private ThemeRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(ThemeRepository::class);
    }

    public function testFindAllReturnsThemes(): void
    {
        $themes = $this->repo->findAll();
        $this->assertCount(4, $themes, 'Les fixtures doivent créer 4 thèmes');
    }

    public function testFindBySlugReturnsTheme(): void
    {
        $theme = $this->repo->findBySlug('musique');
        $this->assertNotNull($theme);
        $this->assertSame('Musique', $theme->getName());
    }

    public function testFindBySlugReturnsNullForUnknownSlug(): void
    {
        $theme = $this->repo->findBySlug('slug-inexistant');
        $this->assertNull($theme);
    }

    public function testThemeHasCursus(): void
    {
        $theme = $this->repo->findBySlug('musique');
        $this->assertGreaterThan(0, $theme->getCursus()->count());
    }
}
