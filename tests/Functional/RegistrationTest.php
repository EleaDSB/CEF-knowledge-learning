<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\TestHelper;

class RegistrationTest extends TestHelper
{
    public function testRegistrationPageIsAccessible(): void
    {
        self::$client->request('GET', '/inscription');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSuccessfulRegistration(): void
    {
        self::$client->request('GET', '/inscription');
        self::$client->submitForm('Créer mon compte', [
            'registration_form[firstname]'            => 'Alice',
            'registration_form[lastname]'             => 'Dupont',
            'registration_form[email]'                => 'alice@test.com',
            'registration_form[plainPassword][first]' => 'AlicePass1!',
            'registration_form[plainPassword][second]'=> 'AlicePass1!',
            'registration_form[agreeTerms]'           => true,
        ]);

        $this->assertResponseRedirects('/connexion');

        $user = self::$em->getRepository(User::class)->findOneBy(['email' => 'alice@test.com']);
        $this->assertNotNull($user, 'L\'utilisateur doit être créé en base');
        $this->assertFalse($user->isVerified(), 'Le compte ne doit pas être activé immédiatement');
        $this->assertNotNull($user->getActivationToken(), 'Un token d\'activation doit être généré');
    }

    public function testRegistrationWithExistingEmailFails(): void
    {
        self::$client->request('GET', '/inscription');
        self::$client->submitForm('Créer mon compte', [
            'registration_form[firstname]'            => 'Admin',
            'registration_form[lastname]'             => 'Test',
            'registration_form[email]'                => 'admin@knowledge-learning.fr',
            'registration_form[plainPassword][first]' => 'Admin1234!',
            'registration_form[plainPassword][second]'=> 'Admin1234!',
            'registration_form[agreeTerms]'           => true,
        ]);

        // Symfony 7 returns 422 for form validation errors
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('.form-error, ul li, .invalid-feedback', 'Une erreur de validation doit s\'afficher');
    }

    public function testRegistrationWithShortPasswordFails(): void
    {
        self::$client->request('GET', '/inscription');
        self::$client->submitForm('Créer mon compte', [
            'registration_form[firstname]'            => 'Bob',
            'registration_form[lastname]'             => 'Test',
            'registration_form[email]'                => 'bob@test.com',
            'registration_form[plainPassword][first]' => 'short',
            'registration_form[plainPassword][second]'=> 'short',
            'registration_form[agreeTerms]'           => true,
        ]);

        // Symfony 7 returns 422 for form validation errors
        $this->assertResponseStatusCodeSame(422);
        $user = self::$em->getRepository(User::class)->findOneBy(['email' => 'bob@test.com']);
        $this->assertNull($user, 'L\'utilisateur ne doit pas être créé avec un mot de passe trop court');
    }
}
