<?php

namespace App\Tests\Functional;

use App\Entity\Purchase;
use App\Entity\User;
use App\Tests\TestHelper;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PurchaseTest extends TestHelper
{
    public function testUnauthenticatedUserIsRedirectedToLogin(): void
    {
        $cursus = $this->getFirstCursus();
        self::$client->request('GET', '/acheter/cursus/' . $cursus->getId());
        $this->assertResponseRedirects('/connexion');
    }

    public function testUnverifiedUserCannotPurchase(): void
    {
        // Créer un utilisateur connecté mais non activé
        $user = new User();
        $user->setEmail('unverif_buyer@test.com');
        $user->setFirstname('Test');
        $user->setLastname('Buyer');
        $user->setIsVerified(false);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, 'Test1234!'));
        self::$em->persist($user);
        self::$em->flush();

        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'unverif_buyer@test.com',
            '_password' => 'Test1234!',
        ]);
        self::$client->followRedirect();

        $cursus = $this->getFirstCursus();
        self::$client->request('GET', '/acheter/cursus/' . $cursus->getId());

        // Redirigé vers home avec message d'erreur (compte non activé)
        $this->assertResponseRedirects('/');
        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-error');
    }

    public function testPurchaseSuccessCreatesPurchaseRecord(): void
    {
        // Se connecter avec le client (compte activé)
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);
        self::$client->followRedirect();

        // Utiliser un cursus non acheté par le client (cursus-dev-web)
        $cursus = self::$em->getRepository(\App\Entity\Cursus::class)->findOneBy(['slug' => 'cursus-dev-web']);

        $purchasesBefore = count(self::$em->getRepository(Purchase::class)->findAll());

        self::$client->request('GET', '/paiement/succes/cursus/' . $cursus->getId() . '?session_id=pi_test_123');
        $this->assertResponseIsSuccessful();

        self::$em->clear();
        $purchasesAfter = count(self::$em->getRepository(Purchase::class)->findAll());
        $this->assertGreaterThan($purchasesBefore, $purchasesAfter, 'Un Purchase doit être créé après le succès');
    }

    public function testPurchaseLessonSuccessCreatesPurchaseRecord(): void
    {
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);
        self::$client->followRedirect();

        // Utiliser une leçon d'un cursus non acheté (html-css dans cursus-dev-web)
        $lesson = self::$em->getRepository(\App\Entity\Lesson::class)->findOneBy(['slug' => 'html-css']);
        $purchasesBefore = count(self::$em->getRepository(Purchase::class)->findAll());

        self::$client->request('GET', '/paiement/succes/lesson/' . $lesson->getId() . '?session_id=pi_test_456');
        $this->assertResponseIsSuccessful();

        self::$em->clear();
        $purchasesAfter = count(self::$em->getRepository(Purchase::class)->findAll());
        $this->assertGreaterThan($purchasesBefore, $purchasesAfter, 'Un Purchase (leçon) doit être créé');
    }

    public function testAccessToPurchasedLesson(): void
    {
        // Le client de fixture a déjà acheté le premier cursus
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);
        self::$client->followRedirect();

        $cursus = $this->getFirstCursus();
        $lesson = $cursus->getLessons()->first();

        self::$client->request('GET', '/lecon/' . $lesson->getSlug());
        $this->assertResponseIsSuccessful();
    }

    public function testAccessToUnpurchasedLessonIsBlocked(): void
    {
        // Créer un utilisateur sans achats
        $this->createVerifiedUser('nopurchase@test.com');

        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'nopurchase@test.com',
            '_password' => 'Test1234!',
        ]);
        self::$client->followRedirect();

        // Récupérer une leçon d'un cursus non acheté
        $cursus = self::$em->getRepository(\App\Entity\Cursus::class)->findOneBy(['slug' => 'cursus-dev-web']);
        $lesson = $cursus->getLessons()->first();

        self::$client->request('GET', '/lecon/' . $lesson->getSlug());
        // Redirige vers la page cursus avec message d'erreur
        $this->assertResponseRedirects('/cursus/' . $cursus->getSlug());
    }
}
