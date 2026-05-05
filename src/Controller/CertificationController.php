<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CertificationRepository;
use App\Repository\LessonProgressRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CertificationController extends AbstractController
{
    #[Route('/mes-certifications', name: 'app_certifications')]
    public function index(
        CertificationRepository $certificationRepository,
        ThemeRepository $themeRepository,
        LessonProgressRepository $progressRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $certifications = $certificationRepository->findByUser($user);
        $certifiedThemeIds = array_map(fn($c) => $c->getTheme()->getId(), $certifications);

        // Pour chaque thème non certifié, calculer la progression
        $progressByTheme = [];
        foreach ($themeRepository->findAll() as $theme) {
            if (in_array($theme->getId(), $certifiedThemeIds)) {
                continue;
            }

            $totalLessons = 0;
            $completedLessons = 0;
            foreach ($theme->getCursus() as $cursus) {
                $totalLessons += $cursus->getLessons()->count();
                $completedLessons += $progressRepository->countCompletedForCursus($user, $cursus);
            }

            if ($totalLessons > 0) {
                $progressByTheme[$theme->getId()] = [
                    'theme' => $theme,
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'percent' => (int) round($completedLessons / $totalLessons * 100),
                ];
            }
        }

        return $this->render('certification/index.html.twig', [
            'certifications' => $certifications,
            'progressByTheme' => $progressByTheme,
        ]);
    }
}
