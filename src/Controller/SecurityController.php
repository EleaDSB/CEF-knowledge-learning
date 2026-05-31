<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Handles user authentication (login and logout).
 */
class SecurityController extends AbstractController
{
    /**
     * Displays the login form and handles authentication errors.
     *
     * Redirects already authenticated users to the home page.
     *
     * @param AuthenticationUtils $authenticationUtils Provides last username and authentication error.
     * @return Response The rendered login page or a redirect if already logged in.
     */
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Logout endpoint — intercepted by the Symfony security firewall.
     *
     * This method is never executed; the firewall handles the logout process.
     */
    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepté par le firewall Symfony.');
    }
}
