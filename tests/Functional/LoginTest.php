<?php

namespace App\Tests\Functional;

use App\Tests\TestHelper;

class LoginTest extends TestHelper
{
    public function testLoginPageIsAccessible(): void
    {
        self::$client->request('GET', '/connexion');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSuccessfulLogin(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);

        $this->assertResponseRedirects('/');
        self::$client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('nav', 'Déconnexion');
    }

    public function testLoginWithWrongPasswordFails(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'mauvaismdp',
        ]);

        $this->assertResponseRedirects('/connexion');
        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-error');
    }

    public function testLoginWithUnknownEmailFails(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'inconnu@test.com',
            '_password' => 'Test1234!',
        ]);

        $this->assertResponseRedirects('/connexion');
        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-error');
    }

    public function testAdminCanAccessBackoffice(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'admin@knowledge-learning.fr',
            '_password' => 'Admin1234!',
        ]);
        self::$client->followRedirect();

        self::$client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotAccessAdmin(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);
        self::$client->followRedirect();

        self::$client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testLogout(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);
        self::$client->followRedirect();

        self::$client->request('GET', '/deconnexion');
        $this->assertResponseRedirects();
    }
}
