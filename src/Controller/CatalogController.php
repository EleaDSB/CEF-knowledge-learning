<?php

namespace App\Controller;

use App\Repository\ThemeRepository;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    #[Route('/theme/{slug}', name: 'app_theme')]
    public function theme(string $slug, ThemeRepository $themeRepository): Response
    {
        $theme = $themeRepository->findBySlug($slug);
        if (!$theme) {
            throw $this->createNotFoundException('Thème introuvable.');
        }

        return $this->render('catalog/theme.html.twig', [
            'theme' => $theme,
        ]);
    }

    #[Route('/cursus/{slug}', name: 'app_cursus')]
    public function cursus(string $slug, CursusRepository $cursusRepository): Response
    {
        $cursus = $cursusRepository->findBySlug($slug);
        if (!$cursus) {
            throw $this->createNotFoundException('Cursus introuvable.');
        }

        return $this->render('catalog/cursus.html.twig', [
            'cursus' => $cursus,
        ]);
    }

    #[Route('/lecon/{slug}', name: 'app_lesson')]
    public function lesson(string $slug, LessonRepository $lessonRepository): Response
    {
        $lesson = $lessonRepository->findBySlug($slug);
        if (!$lesson) {
            throw $this->createNotFoundException('Leçon introuvable.');
        }

        return $this->render('catalog/lesson.html.twig', [
            'lesson' => $lesson,
        ]);
    }
}
