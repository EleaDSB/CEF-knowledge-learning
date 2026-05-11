<?php

namespace App\Tests\Repository;

use App\Entity\Certification;
use App\Repository\CertificationRepository;
use App\Repository\ThemeRepository;
use App\Tests\TestHelper;

class CertificationRepositoryTest extends TestHelper
{
    private CertificationRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(CertificationRepository::class);
    }

    public function testFindByUserReturnsEmptyWhenNoCertification(): void
    {
        $user = $this->createVerifiedUser('nocert@test.com');
        $certs = $this->repo->findByUser($user);
        $this->assertEmpty($certs);
    }

    public function testFindByUserReturnsCertifications(): void
    {
        $user  = $this->createVerifiedUser('withcert@test.com');
        $theme = static::getContainer()->get(ThemeRepository::class)->findBySlug('jardinage');

        $cert = new Certification();
        $cert->setUser($user);
        $cert->setTheme($theme);
        self::$em->persist($cert);
        self::$em->flush();

        $certs = $this->repo->findByUser($user);
        $this->assertCount(1, $certs);
        $this->assertSame('Jardinage', $certs[0]->getTheme()->getName());
    }

    public function testUserHasCertificationReturnsFalseWhenNone(): void
    {
        $user  = $this->createVerifiedUser('nocert2@test.com');
        $theme = static::getContainer()->get(ThemeRepository::class)->findBySlug('cuisine');

        $this->assertFalse($this->repo->userHasCertification($user, $theme));
    }

    public function testUserHasCertificationReturnsTrueWhenExists(): void
    {
        $user  = $this->createVerifiedUser('hascert@test.com');
        $theme = static::getContainer()->get(ThemeRepository::class)->findBySlug('musique');

        $cert = new Certification();
        $cert->setUser($user);
        $cert->setTheme($theme);
        self::$em->persist($cert);
        self::$em->flush();

        $this->assertTrue($this->repo->userHasCertification($user, $theme));
    }
}
