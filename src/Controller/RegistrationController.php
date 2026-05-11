<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\MailerService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles user registration and email-based account activation.
 *
 * On successful registration a signed activation token (valid 24 h) is generated
 * and sent by email. The account remains locked until the token is consumed.
 */
class RegistrationController extends AbstractController
{
    /**
     * Displays and processes the registration form.
     *
     * @param Request                     $request        The current HTTP request.
     * @param UserPasswordHasherInterface $passwordHasher Hashes the plain-text password.
     * @param EntityManagerInterface      $em             Persists the new user.
     * @param MailerService               $mailer         Sends the activation email.
     * @return Response The registration form or a redirect after success.
     */
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        MailerService $mailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            $token = bin2hex(random_bytes(32));
            $user->setActivationToken($token);
            $user->setActivationTokenExpiresAt(new \DateTimeImmutable('+24 hours'));

            $em->persist($user);
            $em->flush();

            $mailer->sendActivationEmail($user);

            $this->addFlash('success', 'Votre compte a été créé ! Vérifiez votre email pour l\'activer.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Activates a user account by consuming a time-limited email token.
     *
     * @param string                 $token          The activation token from the email link.
     * @param UserRepository         $userRepository Looks up the user by token.
     * @param EntityManagerInterface $em             Persists the verified state.
     * @return Response A redirect to login on success, or to registration on failure.
     */
    #[Route('/activation/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->findByActivationToken($token);

        if (!$user) {
            $this->addFlash('error', 'Lien d\'activation invalide.');
            return $this->redirectToRoute('app_register');
        }

        if ($user->getActivationTokenExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Lien d\'activation expiré. Veuillez vous réinscrire.');
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $user->setActivationToken(null);
        $user->setActivationTokenExpiresAt(null);
        $em->flush();

        $this->addFlash('success', 'Votre compte est activé ! Vous pouvez vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}
