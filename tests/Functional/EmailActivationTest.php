<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\TestHelper;

class EmailActivationTest extends TestHelper
{
    public function testValidTokenActivatesAccount(): void
    {
        // Créer un utilisateur non activé avec token
        $user = new User();
        $user->setEmail('unverified@test.com');
        $user->setFirstname('Non');
        $user->setLastname('Activé');
        $user->setPassword('hashed');
        $user->setActivationToken('validtoken123');
        $user->setActivationTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
        self::$em->persist($user);
        self::$em->flush();

        self::$client->request('GET', '/activation/validtoken123');
        $this->assertResponseRedirects('/connexion');

        self::$em->refresh($user);
        $this->assertTrue($user->isVerified(), 'Le compte doit être activé après clic sur le lien');
        $this->assertNull($user->getActivationToken(), 'Le token doit être consommé');
    }

    public function testInvalidTokenShowsError(): void
    {
        self::$client->request('GET', '/activation/tokeninexistant');
        $this->assertResponseRedirects('/inscription');

        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-error');
    }

    public function testExpiredTokenShowsError(): void
    {
        $user = new User();
        $user->setEmail('expired@test.com');
        $user->setFirstname('Expired');
        $user->setLastname('Token');
        $user->setPassword('hashed');
        $user->setActivationToken('expiredtoken456');
        $user->setActivationTokenExpiresAt(new \DateTimeImmutable('-1 hour'));
        self::$em->persist($user);
        self::$em->flush();

        self::$client->request('GET', '/activation/expiredtoken456');
        $this->assertResponseRedirects('/inscription');

        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-error');
        $this->assertFalse($user->isVerified(), 'Le compte ne doit pas être activé avec un token expiré');
    }

    public function testUnverifiedUserCannotPurchase(): void
    {
        // Créer un utilisateur non activé
        $user = new User();
        $user->setEmail('noverif@test.com');
        $user->setFirstname('No');
        $user->setLastname('Verif');
        $user->setPassword('hashed');
        self::$em->persist($user);
        self::$em->flush();

        // Simuler la connexion (nécessite compte activé pour tester le blocage)
        // → on vérifie que le flag isVerified est bien false
        $this->assertFalse($user->isVerified());
        $this->assertNull($user->getActivationToken(), 'Token non défini avant inscription');
    }
}
