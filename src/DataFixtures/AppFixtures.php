<?php

namespace App\DataFixtures;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $themes = $this->loadThemes($manager);
        $manager->flush();

        $admin = $this->createAdmin($manager);
        $client = $this->createClient($manager);
        $manager->flush();

        $firstCursus = $themes['Musique']->getCursus()->first();
        if ($firstCursus) {
            $this->createSamplePurchase($manager, $client, $firstCursus);
        }
        $manager->flush();
    }

    private function loadThemes(ObjectManager $manager): array
    {
        $data = [
            'Musique' => [
                'slug' => 'musique',
                'cursus' => [
                    ['name' => 'Cursus d\'initiation à la guitare', 'slug' => 'cursus-guitare', 'price' => '50.00', 'lessons' => [
                        ['name' => 'Découverte de l\'instrument', 'slug' => 'decouverte-guitare', 'price' => '26.00', 'position' => 1],
                        ['name' => 'Les accords et les gammes', 'slug' => 'accords-gammes-guitare', 'price' => '26.00', 'position' => 2],
                    ]],
                    ['name' => 'Cursus d\'initiation au piano', 'slug' => 'cursus-piano', 'price' => '50.00', 'lessons' => [
                        ['name' => 'Découverte de l\'instrument', 'slug' => 'decouverte-piano', 'price' => '26.00', 'position' => 1],
                        ['name' => 'Les accords et les gammes', 'slug' => 'accords-gammes-piano', 'price' => '26.00', 'position' => 2],
                    ]],
                ],
            ],
            'Informatique' => [
                'slug' => 'informatique',
                'cursus' => [
                    ['name' => 'Cursus d\'initiation au développement web', 'slug' => 'cursus-dev-web', 'price' => '60.00', 'lessons' => [
                        ['name' => 'Les langages Html et CSS', 'slug' => 'html-css', 'price' => '32.00', 'position' => 1],
                        ['name' => 'Dynamiser votre site avec Javascript', 'slug' => 'javascript', 'price' => '32.00', 'position' => 2],
                    ]],
                ],
            ],
            'Jardinage' => [
                'slug' => 'jardinage',
                'cursus' => [
                    ['name' => 'Cursus d\'initiation au jardinage', 'slug' => 'cursus-jardinage', 'price' => '30.00', 'lessons' => [
                        ['name' => 'Les outils du jardinier', 'slug' => 'outils-jardinier', 'price' => '16.00', 'position' => 1],
                        ['name' => 'Jardiner avec la lune', 'slug' => 'jardiner-lune', 'price' => '16.00', 'position' => 2],
                    ]],
                ],
            ],
            'Cuisine' => [
                'slug' => 'cuisine',
                'cursus' => [
                    ['name' => 'Cursus d\'initiation à la cuisine', 'slug' => 'cursus-cuisine', 'price' => '44.00', 'lessons' => [
                        ['name' => 'Les modes de cuisson', 'slug' => 'modes-cuisson', 'price' => '23.00', 'position' => 1],
                        ['name' => 'Les saveurs', 'slug' => 'saveurs', 'price' => '23.00', 'position' => 2],
                    ]],
                    ['name' => 'Cursus d\'initiation à l\'art du dressage culinaire', 'slug' => 'cursus-dressage', 'price' => '48.00', 'lessons' => [
                        ['name' => 'Mettre en œuvre le style dans l\'assiette', 'slug' => 'style-assiette', 'price' => '26.00', 'position' => 1],
                        ['name' => 'Harmoniser un repas à quatre plats', 'slug' => 'harmoniser-repas', 'price' => '26.00', 'position' => 2],
                    ]],
                ],
            ],
        ];

        $themes = [];
        foreach ($data as $themeName => $themeData) {
            $theme = new Theme();
            $theme->setName($themeName);
            $theme->setSlug($themeData['slug']);
            $manager->persist($theme);
            $themes[$themeName] = $theme;

            foreach ($themeData['cursus'] as $cursusData) {
                $cursus = new Cursus();
                $cursus->setName($cursusData['name']);
                $cursus->setSlug($cursusData['slug']);
                $cursus->setPrice($cursusData['price']);
                $cursus->setTheme($theme);
                $manager->persist($cursus);

                foreach ($cursusData['lessons'] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonData['name']);
                    $lesson->setSlug($lessonData['slug']);
                    $lesson->setPrice($lessonData['price']);
                    $lesson->setPosition($lessonData['position']);
                    $lesson->setCursus($cursus);
                    $lesson->setContent($this->getLoremIpsum());
                    $manager->persist($lesson);
                }
            }
        }

        return $themes;
    }

    private function createAdmin(ObjectManager $manager): User
    {
        $admin = new User();
        $admin->setEmail('admin@knowledge-learning.fr');
        $admin->setFirstname('Admin');
        $admin->setLastname('Knowledge');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);
        return $admin;
    }

    private function createClient(ObjectManager $manager): User
    {
        $client = new User();
        $client->setEmail('client@example.com');
        $client->setFirstname('Jean');
        $client->setLastname('Dupont');
        $client->setIsVerified(true);
        $client->setPassword($this->hasher->hashPassword($client, 'Client1234!'));
        $manager->persist($client);
        return $client;
    }

    private function createSamplePurchase(ObjectManager $manager, User $user, Cursus $cursus): void
    {
        $purchase = new Purchase();
        $purchase->setUser($user);
        $purchase->setCursus($cursus);
        $purchase->setType(Purchase::TYPE_CURSUS);
        $purchase->setAmount($cursus->getPrice());
        $purchase->setStripePaymentId('pi_test_sample');
        $manager->persist($purchase);
    }

    private function getLoremIpsum(): string
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.

Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.';
    }
}
