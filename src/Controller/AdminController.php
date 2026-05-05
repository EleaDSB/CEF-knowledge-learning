<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(UserRepository $userRepository, PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => count($userRepository->findAll()),
            'totalPurchases' => count($purchaseRepository->findAll()),
            'recentUsers' => $userRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
