<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Form\Admin\CursusType;
use App\Form\Admin\LessonType;
use App\Form\Admin\ThemeType;
use App\Form\Admin\UserEditType;
use App\Repository\CursusRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles all back-office administration pages.
 *
 * Restricted to users with the ROLE_ADMIN role.
 * Provides CRUD operations for users, themes, cursus, lessons and purchases.
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // ── Dashboard ──────────────────────────────────────────────────────────

    /**
     * Displays the admin dashboard with global statistics.
     *
     * @param UserRepository     $userRepository     Data access for users.
     * @param PurchaseRepository $purchaseRepository Data access for purchases.
     * @return Response The rendered dashboard page.
     */
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(UserRepository $userRepository, PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalUsers'    => count($userRepository->findAll()),
            'totalPurchases'=> count($purchaseRepository->findAll()),
            'recentUsers'   => $userRepository->findBy([], ['createdAt' => 'DESC'], 10),
        ]);
    }

    // ── Utilisateurs ───────────────────────────────────────────────────────

    /**
     * Lists all registered users sorted by creation date.
     *
     * @param UserRepository $userRepository Data access for users.
     * @return Response The rendered users list page.
     */
    #[Route('/utilisateurs', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    /**
     * Displays and handles the edit form for a specific user.
     *
     * @param int                    $id             The user ID.
     * @param Request                $request        The current HTTP request.
     * @param UserRepository         $userRepository Data access for users.
     * @param EntityManagerInterface $em             The Doctrine entity manager.
     * @return Response The rendered edit form or a redirect on success.
     */
    #[Route('/utilisateurs/{id}/modifier', name: 'admin_user_edit')]
    public function editUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', ['user' => $user, 'form' => $form]);
    }

    /**
     * Deletes a user after validating the CSRF token.
     *
     * @param int                    $id             The user ID.
     * @param Request                $request        The current HTTP request.
     * @param UserRepository         $userRepository Data access for users.
     * @param EntityManagerInterface $em             The Doctrine entity manager.
     * @return Response A redirect to the users list.
     */
    #[Route('/utilisateurs/{id}/supprimer', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);
        if ($user && $this->isCsrfTokenValid('delete_user_' . $id, $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('admin_users');
    }

    // ── Thèmes ─────────────────────────────────────────────────────────────

    /**
     * Lists all training themes.
     *
     * @param ThemeRepository $themeRepository Data access for themes.
     * @return Response The rendered themes list page.
     */
    #[Route('/themes', name: 'admin_themes')]
    public function themes(ThemeRepository $themeRepository): Response
    {
        return $this->render('admin/themes/index.html.twig', [
            'themes' => $themeRepository->findAll(),
        ]);
    }

    /**
     * Displays and handles the creation form for a new theme.
     *
     * @param Request                $request The current HTTP request.
     * @param EntityManagerInterface $em      The Doctrine entity manager.
     * @return Response The rendered form or a redirect on success.
     */
    #[Route('/themes/nouveau', name: 'admin_theme_new')]
    public function newTheme(Request $request, EntityManagerInterface $em): Response
    {
        $theme = new Theme();
        $form  = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($theme);
            $em->flush();
            $this->addFlash('success', 'Thème créé.');
            return $this->redirectToRoute('admin_themes');
        }

        return $this->render('admin/themes/form.html.twig', ['form' => $form, 'title' => 'Nouveau thème']);
    }

    /**
     * Displays and handles the edit form for a specific theme.
     *
     * @param int                    $id              The theme ID.
     * @param Request                $request         The current HTTP request.
     * @param ThemeRepository        $themeRepository Data access for themes.
     * @param EntityManagerInterface $em              The Doctrine entity manager.
     * @return Response The rendered edit form or a redirect on success.
     */
    #[Route('/themes/{id}/modifier', name: 'admin_theme_edit')]
    public function editTheme(int $id, Request $request, ThemeRepository $themeRepository, EntityManagerInterface $em): Response
    {
        $theme = $themeRepository->find($id);
        if (!$theme) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Thème modifié.');
            return $this->redirectToRoute('admin_themes');
        }

        return $this->render('admin/themes/form.html.twig', ['form' => $form, 'title' => 'Modifier le thème']);
    }

    /**
     * Deletes a theme after validating the CSRF token.
     *
     * @param int                    $id              The theme ID.
     * @param Request                $request         The current HTTP request.
     * @param ThemeRepository        $themeRepository Data access for themes.
     * @param EntityManagerInterface $em              The Doctrine entity manager.
     * @return Response A redirect to the themes list.
     */
    #[Route('/themes/{id}/supprimer', name: 'admin_theme_delete', methods: ['POST'])]
    public function deleteTheme(int $id, Request $request, ThemeRepository $themeRepository, EntityManagerInterface $em): Response
    {
        $theme = $themeRepository->find($id);
        if ($theme && $this->isCsrfTokenValid('delete_theme_' . $id, $request->request->get('_token'))) {
            $em->remove($theme);
            $em->flush();
            $this->addFlash('success', 'Thème supprimé.');
        }
        return $this->redirectToRoute('admin_themes');
    }

    // ── Cursus ─────────────────────────────────────────────────────────────

    /**
     * Lists all cursus sorted by ID.
     *
     * @param CursusRepository $cursusRepository Data access for cursus.
     * @return Response The rendered cursus list page.
     */
    #[Route('/cursus', name: 'admin_cursus')]
    public function cursus(CursusRepository $cursusRepository): Response
    {
        return $this->render('admin/cursus/index.html.twig', [
            'cursusList' => $cursusRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    /**
     * Displays and handles the creation form for a new cursus.
     *
     * @param Request                $request The current HTTP request.
     * @param EntityManagerInterface $em      The Doctrine entity manager.
     * @return Response The rendered form or a redirect on success.
     */
    #[Route('/cursus/nouveau', name: 'admin_cursus_new')]
    public function newCursus(Request $request, EntityManagerInterface $em): Response
    {
        $cursus = new Cursus();
        $form   = $this->createForm(CursusType::class, $cursus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cursus);
            $em->flush();
            $this->addFlash('success', 'Cursus créé.');
            return $this->redirectToRoute('admin_cursus');
        }

        return $this->render('admin/cursus/form.html.twig', ['form' => $form, 'title' => 'Nouveau cursus']);
    }

    /**
     * Displays and handles the edit form for a specific cursus.
     *
     * @param int                    $id               The cursus ID.
     * @param Request                $request          The current HTTP request.
     * @param CursusRepository       $cursusRepository Data access for cursus.
     * @param EntityManagerInterface $em               The Doctrine entity manager.
     * @return Response The rendered edit form or a redirect on success.
     */
    #[Route('/cursus/{id}/modifier', name: 'admin_cursus_edit')]
    public function editCursus(int $id, Request $request, CursusRepository $cursusRepository, EntityManagerInterface $em): Response
    {
        $cursus = $cursusRepository->find($id);
        if (!$cursus) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CursusType::class, $cursus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Cursus modifié.');
            return $this->redirectToRoute('admin_cursus');
        }

        return $this->render('admin/cursus/form.html.twig', ['form' => $form, 'title' => 'Modifier le cursus']);
    }

    /**
     * Deletes a cursus after validating the CSRF token.
     *
     * @param int                    $id               The cursus ID.
     * @param Request                $request          The current HTTP request.
     * @param CursusRepository       $cursusRepository Data access for cursus.
     * @param EntityManagerInterface $em               The Doctrine entity manager.
     * @return Response A redirect to the cursus list.
     */
    #[Route('/cursus/{id}/supprimer', name: 'admin_cursus_delete', methods: ['POST'])]
    public function deleteCursus(int $id, Request $request, CursusRepository $cursusRepository, EntityManagerInterface $em): Response
    {
        $cursus = $cursusRepository->find($id);
        if ($cursus && $this->isCsrfTokenValid('delete_cursus_' . $id, $request->request->get('_token'))) {
            $em->remove($cursus);
            $em->flush();
            $this->addFlash('success', 'Cursus supprimé.');
        }
        return $this->redirectToRoute('admin_cursus');
    }

    // ── Leçons ─────────────────────────────────────────────────────────────

    /**
     * Lists all lessons sorted by ID.
     *
     * @param LessonRepository $lessonRepository Data access for lessons.
     * @return Response The rendered lessons list page.
     */
    #[Route('/lecons', name: 'admin_lessons')]
    public function lessons(LessonRepository $lessonRepository): Response
    {
        return $this->render('admin/lessons/index.html.twig', [
            'lessons' => $lessonRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    /**
     * Displays and handles the creation form for a new lesson.
     *
     * @param Request                $request The current HTTP request.
     * @param EntityManagerInterface $em      The Doctrine entity manager.
     * @return Response The rendered form or a redirect on success.
     */
    #[Route('/lecons/nouvelle', name: 'admin_lesson_new')]
    public function newLesson(Request $request, EntityManagerInterface $em): Response
    {
        $lesson = new Lesson();
        $form   = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lesson);
            $em->flush();
            $this->addFlash('success', 'Leçon créée.');
            return $this->redirectToRoute('admin_lessons');
        }

        return $this->render('admin/lessons/form.html.twig', ['form' => $form, 'title' => 'Nouvelle leçon']);
    }

    /**
     * Displays and handles the edit form for a specific lesson.
     *
     * @param int                    $id               The lesson ID.
     * @param Request                $request          The current HTTP request.
     * @param LessonRepository       $lessonRepository Data access for lessons.
     * @param EntityManagerInterface $em               The Doctrine entity manager.
     * @return Response The rendered edit form or a redirect on success.
     */
    #[Route('/lecons/{id}/modifier', name: 'admin_lesson_edit')]
    public function editLesson(int $id, Request $request, LessonRepository $lessonRepository, EntityManagerInterface $em): Response
    {
        $lesson = $lessonRepository->find($id);
        if (!$lesson) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Leçon modifiée.');
            return $this->redirectToRoute('admin_lessons');
        }

        return $this->render('admin/lessons/form.html.twig', ['form' => $form, 'title' => 'Modifier la leçon']);
    }

    /**
     * Deletes a lesson after validating the CSRF token.
     *
     * @param int                    $id               The lesson ID.
     * @param Request                $request          The current HTTP request.
     * @param LessonRepository       $lessonRepository Data access for lessons.
     * @param EntityManagerInterface $em               The Doctrine entity manager.
     * @return Response A redirect to the lessons list.
     */
    #[Route('/lecons/{id}/supprimer', name: 'admin_lesson_delete', methods: ['POST'])]
    public function deleteLesson(int $id, Request $request, LessonRepository $lessonRepository, EntityManagerInterface $em): Response
    {
        $lesson = $lessonRepository->find($id);
        if ($lesson && $this->isCsrfTokenValid('delete_lesson_' . $id, $request->request->get('_token'))) {
            $em->remove($lesson);
            $em->flush();
            $this->addFlash('success', 'Leçon supprimée.');
        }
        return $this->redirectToRoute('admin_lessons');
    }

    // ── Achats ─────────────────────────────────────────────────────────────

    /**
     * Lists all purchases sorted by purchase date (most recent first).
     *
     * @param PurchaseRepository $purchaseRepository Data access for purchases.
     * @return Response The rendered purchases list page.
     */
    #[Route('/achats', name: 'admin_purchases')]
    public function purchases(PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/purchases/index.html.twig', [
            'purchases' => $purchaseRepository->findBy([], ['purchasedAt' => 'DESC']),
        ]);
    }
}
