<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\TestHelper;

class UserRepositoryTest extends TestHelper
{
    private UserRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = static::getContainer()->get(UserRepository::class);
    }

    public function testFindByActivationTokenReturnsUser(): void
    {
        $user = new User();
        $user->setEmail('tokenuser@test.com');
        $user->setFirstname('Token');
        $user->setLastname('User');
        $user->setPassword('hashed');
        $user->setActivationToken('myuniqtoken');
        $user->setActivationTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
        self::$em->persist($user);
        self::$em->flush();

        $found = $this->repo->findByActivationToken('myuniqtoken');
        $this->assertNotNull($found);
        $this->assertSame('tokenuser@test.com', $found->getEmail());
    }

    public function testFindByActivationTokenReturnsNullForUnknownToken(): void
    {
        $found = $this->repo->findByActivationToken('tokeninexistant999');
        $this->assertNull($found);
    }

    public function testFindByEmailReturnsCorrectUser(): void
    {
        $user = $this->repo->findOneBy(['email' => 'admin@knowledge-learning.fr']);
        $this->assertNotNull($user);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }
}
