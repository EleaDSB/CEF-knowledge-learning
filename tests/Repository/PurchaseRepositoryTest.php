<?php

namespace App\Tests\Repository;

use App\Entity\Purchase;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use App\Tests\TestHelper;

class PurchaseRepositoryTest extends TestHelper
{
    private PurchaseRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(PurchaseRepository::class);
    }

    public function testUserHasCursusReturnsTrueForPurchased(): void
    {
        $user   = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@example.com']);
        $cursus = static::getContainer()->get(CursusRepository::class)->findBySlug('cursus-guitare');

        $this->assertTrue($this->repo->userHasCursus($user, $cursus), 'Le client doit posséder le cursus guitare (fixture)');
    }

    public function testUserHasCursusReturnsFalseForUnpurchased(): void
    {
        $user   = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@example.com']);
        $cursus = static::getContainer()->get(CursusRepository::class)->findBySlug('cursus-dev-web');

        $this->assertFalse($this->repo->userHasCursus($user, $cursus));
    }

    public function testUserHasLessonReturnsTrueWhenCursusPurchased(): void
    {
        $user   = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@example.com']);
        $lesson = static::getContainer()->get(LessonRepository::class)->findBySlug('decouverte-guitare');

        $this->assertTrue($this->repo->userHasLesson($user, $lesson), 'Accès à la leçon via achat du cursus');
    }

    public function testUserHasLessonReturnsFalseWhenNotPurchased(): void
    {
        $user   = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@example.com']);
        $lesson = static::getContainer()->get(LessonRepository::class)->findBySlug('html-css');

        $this->assertFalse($this->repo->userHasLesson($user, $lesson));
    }

    public function testFindByUserReturnsUserPurchases(): void
    {
        $user     = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@example.com']);
        $purchases = $this->repo->findByUser($user);

        $this->assertNotEmpty($purchases);
        foreach ($purchases as $purchase) {
            $this->assertSame($user->getId(), $purchase->getUser()->getId());
        }
    }

    public function testPurchaseHasCorrectAmount(): void
    {
        $user     = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@example.com']);
        $purchases = $this->repo->findByUser($user);

        $this->assertNotEmpty($purchases);
        $this->assertSame('50.00', $purchases[0]->getAmount());
    }
}
