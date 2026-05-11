<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sends transactional emails for the Knowledge Learning platform.
 *
 * Wraps Symfony Mailer with application-specific templates and routing,
 * keeping email logic out of controllers.
 */
class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * Sends the account-activation email to a newly registered user.
     *
     * The email contains a signed URL (valid 24 h) that activates the account
     * by hitting the app_verify_email route.
     *
     * @param User $user The unverified user who just registered.
     */
    public function sendActivationEmail(User $user): void
    {
        $activationUrl = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $user->getActivationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@knowledge-learning.fr', 'Knowledge Learning'))
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Activez votre compte Knowledge Learning')
            ->htmlTemplate('emails/activation.html.twig')
            ->context([
                'user' => $user,
                'activation_url' => $activationUrl,
            ]);

        $this->mailer->send($email);
    }
}
