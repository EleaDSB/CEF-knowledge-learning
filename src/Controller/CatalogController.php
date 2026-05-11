<?php

namespace App\Controller;

use App\Entity\LessonProgress;
use App\Entity\User;
use App\Repository\CertificationRepository;
use App\Repository\CursusRepository;
use App\Repository\LessonProgressRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ThemeRepository;
use App\Security\LessonVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles the public course catalog: themes, cursus, lessons and lesson validation.
 *
 * Lesson access is controlled by LessonVoter (requires a valid purchase).
 * Lesson validation triggers the automatic certification logic.
 */
class CatalogController extends AbstractController
{
    /**
     * Displays all cursus belonging to a theme.
     *
     * @param string          $slug            The theme's URL slug.
     * @param ThemeRepository $themeRepository Data access for themes.
     * @return Response The rendered theme page or a 404 response.
     */
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
    public function cursus(
        string $slug,
        CursusRepository $cursusRepository,
        PurchaseRepository $purchaseRepository,
    ): Response {
        $cursus = $cursusRepository->findBySlug($slug);
        if (!$cursus) {
            throw $this->createNotFoundException('Cursus introuvable.');
        }

        $user = $this->getUser();
        $hasCursus = false;
        $ownedLessons = [];

        if ($user instanceof User) {
            $hasCursus = $purchaseRepository->userHasCursus($user, $cursus);
            if (!$hasCursus) {
                foreach ($cursus->getLessons() as $lesson) {
                    if ($purchaseRepository->userHasLesson($user, $lesson)) {
                        $ownedLessons[$lesson->getId()] = true;
                    }
                }
            }
        }

        return $this->render('catalog/cursus.html.twig', [
            'cursus' => $cursus,
            'hasCursus' => $hasCursus,
            'ownedLessons' => $ownedLessons,
        ]);
    }

    #[Route('/lecon/{slug}', name: 'app_lesson')]
    public function lesson(string $slug, LessonRepository $lessonRepository): Response
    {
        $lesson = $lessonRepository->findBySlug($slug);
        if (!$lesson) {
            throw $this->createNotFoundException('Leçon introuvable.');
        }

        if (!$this->isGranted(LessonVoter::VIEW, $lesson)) {
            $this->addFlash('error', 'Vous devez acheter cette leçon ou son cursus pour y accéder.');
            return $this->redirectToRoute('app_cursus', ['slug' => $lesson->getCursus()->getSlug()]);
        }

        return $this->render('catalog/lesson.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/lecon/{slug}/valider', name: 'app_lesson_validate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function validateLesson(
        string $slug,
        LessonRepository $lessonRepository,
        LessonProgressRepository $progressRepository,
        CertificationRepository $certificationRepository,
        EntityManagerInterface $em,
        Request $request,
    ): Response {
        $lesson = $lessonRepository->findBySlug($slug);
        if (!$lesson) {
            throw $this->createNotFoundException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('validate_lesson_' . $lesson->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_lesson', ['slug' => $slug]);
        }

        if (!$this->isGranted(LessonVoter::VIEW, $lesson)) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_cursus', ['slug' => $lesson->getCursus()->getSlug()]);
        }

        $progress = $progressRepository->findOneByUserAndLesson($user, $lesson);
        if (!$progress) {
            $progress = new LessonProgress();
            $progress->setUser($user);
            $progress->setLesson($lesson);
            $em->persist($progress);
        }
        $progress->setIsCompleted(true);
        $em->flush();

        // Auto-validation du cursus si toutes les leçons sont validées
        $cursus = $lesson->getCursus();
        $totalLessons = $cursus->getLessons()->count();
        $completedLessons = $progressRepository->countCompletedForCursus($user, $cursus);

        if ($completedLessons >= $totalLessons) {
            $theme = $cursus->getTheme();
            $allCursusInTheme = $theme->getCursus();
            $allThemeLessonsCompleted = true;

            foreach ($allCursusInTheme as $themeCursus) {
                $totalInCursus = $themeCursus->getLessons()->count();
                $completedInCursus = $progressRepository->countCompletedForCursus($user, $themeCursus);
                if ($completedInCursus < $totalInCursus) {
                    $allThemeLessonsCompleted = false;
                    break;
                }
            }

            if ($allThemeLessonsCompleted && !$certificationRepository->userHasCertification($user, $theme)) {
                $certification = new \App\Entity\Certification();
                $certification->setUser($user);
                $certification->setTheme($theme);
                $em->persist($certification);
                $em->flush();

                $this->addFlash('success', '🎓 Félicitations ! Vous avez obtenu la certification "' . $theme->getName() . '" !');
                return $this->redirectToRoute('app_certifications');
            }

            $this->addFlash('success', 'Cursus "' . $cursus->getName() . '" complété !');
        } else {
            $this->addFlash('success', 'Leçon validée (' . $completedLessons . '/' . $totalLessons . ' leçons du cursus).');
        }

        return $this->redirectToRoute('app_lesson', ['slug' => $slug]);
    }
}
