<?php

namespace App\Controller;

use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the public home page.
 */
class HomeController extends AbstractController
{
    /**
     * Displays the home page listing all available training themes.
     *
     * @param ThemeRepository $themeRepository Data access for themes.
     * @return Response The rendered home page.
     */
    #[Route('/', name: 'app_home')]
    public function index(ThemeRepository $themeRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'themes' => $themeRepository->findAll(),
        ]);
    }
}
