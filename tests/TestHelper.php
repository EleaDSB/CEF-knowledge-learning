<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class TestHelper extends WebTestCase
{
    protected static EntityManagerInterface $em;
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        self::$em = static::getContainer()->get(EntityManagerInterface::class);
        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(static::getContainer()->get(AppFixtures::class));

        $executor = new ORMExecutor(self::$em, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    protected function getAdminUser(): User
    {
        return self::$em->getRepository(User::class)->findOneBy(['email' => 'admin@knowledge-learning.fr']);
    }

    protected function getClientUser(): User
    {
        return self::$em->getRepository(User::class)->findOneBy(['email' => 'client@example.com']);
    }

    protected function createVerifiedUser(string $email = 'test@example.com', string $password = 'Test1234!'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname('Test');
        $user->setLastname('User');
        $user->setIsVerified(true);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, $password));
        self::$em->persist($user);
        self::$em->flush();
        return $user;
    }

    protected function login(string $email, string $password): void
    {
        self::$client->request('POST', '/connexion', [
            '_username' => $email,
            '_password' => $password,
            '_csrf_token' => $this->getCsrfToken('authenticate'),
        ]);
    }

    private function getCsrfToken(string $id): string
    {
        $tokenManager = static::getContainer()->get('security.csrf.token_manager');
        return $tokenManager->getToken($id)->getValue();
    }

    protected function getFirstCursus(): Cursus
    {
        return self::$em->getRepository(Cursus::class)->findOneBy([]);
    }

    protected function getFirstLesson(): Lesson
    {
        return self::$em->getRepository(Lesson::class)->findOneBy([]);
    }
}
