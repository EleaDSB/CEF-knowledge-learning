<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\User;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ShopController extends AbstractController
{
    public function __construct(private string $stripeSecretKey) {}

    #[Route('/acheter/cursus/{id}', name: 'app_checkout_cursus')]
    public function checkoutCursus(
        int $id,
        CursusRepository $cursusRepository,
        PurchaseRepository $purchaseRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte par email avant de pouvoir acheter.');
            return $this->redirectToRoute('app_home');
        }

        $cursus = $cursusRepository->find($id);
        if (!$cursus) {
            throw $this->createNotFoundException();
        }

        if ($purchaseRepository->userHasCursus($user, $cursus)) {
            $this->addFlash('error', 'Vous possédez déjà ce cursus.');
            return $this->redirectToRoute('app_cursus', ['slug' => $cursus->getSlug()]);
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ((float) $cursus->getPrice() * 100),
                    'product_data' => [
                        'name' => $cursus->getName(),
                        'description' => 'Cursus complet — ' . $cursus->getTheme()->getName(),
                    ],
                ],
            ]],
            'success_url' => $this->generateUrl('app_checkout_success', [
                'type' => 'cursus',
                'id' => $cursus->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_cursus', ['slug' => $cursus->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'user_id' => $user->getId(),
                'type' => 'cursus',
                'item_id' => $cursus->getId(),
            ],
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/acheter/lecon/{id}', name: 'app_checkout_lesson')]
    public function checkoutLesson(
        int $id,
        LessonRepository $lessonRepository,
        PurchaseRepository $purchaseRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte par email avant de pouvoir acheter.');
            return $this->redirectToRoute('app_home');
        }

        $lesson = $lessonRepository->find($id);
        if (!$lesson) {
            throw $this->createNotFoundException();
        }

        if ($purchaseRepository->userHasLesson($user, $lesson)) {
            $this->addFlash('error', 'Vous possédez déjà cette leçon.');
            return $this->redirectToRoute('app_cursus', ['slug' => $lesson->getCursus()->getSlug()]);
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ((float) $lesson->getPrice() * 100),
                    'product_data' => [
                        'name' => $lesson->getName(),
                        'description' => $lesson->getCursus()->getName() . ' — Leçon ' . $lesson->getPosition(),
                    ],
                ],
            ]],
            'success_url' => $this->generateUrl('app_checkout_success', [
                'type' => 'lesson',
                'id' => $lesson->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_cursus', ['slug' => $lesson->getCursus()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'user_id' => $user->getId(),
                'type' => 'lesson',
                'item_id' => $lesson->getId(),
            ],
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/paiement/succes/{type}/{id}', name: 'app_checkout_success')]
    public function success(
        string $type,
        int $id,
        CursusRepository $cursusRepository,
        LessonRepository $lessonRepository,
        PurchaseRepository $purchaseRepository,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($type === 'cursus') {
            $cursus = $cursusRepository->find($id);
            if (!$cursus || $purchaseRepository->userHasCursus($user, $cursus)) {
                return $this->redirectToRoute('app_home');
            }

            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setCursus($cursus);
            $purchase->setType(Purchase::TYPE_CURSUS);
            $purchase->setAmount($cursus->getPrice());
            $purchase->setStripePaymentId($_GET['session_id'] ?? null);
            $em->persist($purchase);
            $em->flush();

            $this->addFlash('success', 'Achat confirmé ! Vous avez accès au cursus "' . $cursus->getName() . '".');
            return $this->render('shop/success.html.twig', ['item' => $cursus, 'type' => 'cursus']);
        }

        if ($type === 'lesson') {
            $lesson = $lessonRepository->find($id);
            if (!$lesson || $purchaseRepository->userHasLesson($user, $lesson)) {
                return $this->redirectToRoute('app_home');
            }

            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setLesson($lesson);
            $purchase->setType(Purchase::TYPE_LESSON);
            $purchase->setAmount($lesson->getPrice());
            $purchase->setStripePaymentId($_GET['session_id'] ?? null);
            $em->persist($purchase);
            $em->flush();

            $this->addFlash('success', 'Achat confirmé ! Vous avez accès à la leçon "' . $lesson->getName() . '".');
            return $this->render('shop/success.html.twig', ['item' => $lesson, 'type' => 'lesson']);
        }

        return $this->redirectToRoute('app_home');
    }
}
