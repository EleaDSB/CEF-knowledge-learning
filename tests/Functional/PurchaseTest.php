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
        // Create a logged-in but unverified user
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

        // Redirected to home with an error message (account not verified)
        $this->assertResponseRedirects('/');
        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-error');
    }

    public function testPurchaseSuccessCreatesPurchaseRecord(): void
    {
        // Log in with the fixture client (verified account)
        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'client@example.com',
            '_password' => 'Client1234!',
        ]);
        self::$client->followRedirect();

        // Use a cursus not yet purchased by the client (cursus-dev-web)
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

        // Use a lesson from an unpurchased cursus (html-css in cursus-dev-web)
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
        // The fixture client already purchased the first cursus
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
        // Create a verified user with no purchases
        $this->createVerifiedUser('nopurchase@test.com');

        self::$client->request('GET', '/connexion');
        self::$client->submitForm('Se connecter', [
            '_username' => 'nopurchase@test.com',
            '_password' => 'Test1234!',
        ]);
        self::$client->followRedirect();

        // Fetch a lesson from an unpurchased cursus
        $cursus = self::$em->getRepository(\App\Entity\Cursus::class)->findOneBy(['slug' => 'cursus-dev-web']);
        $lesson = $cursus->getLessons()->first();

        self::$client->request('GET', '/lecon/' . $lesson->getSlug());
        // Redirected to the cursus page with an error message
        $this->assertResponseRedirects('/cursus/' . $cursus->getSlug());
    }
}
