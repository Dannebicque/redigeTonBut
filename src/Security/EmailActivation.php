<?php

namespace App\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailActivation
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmailConfirmation(UserInterface $user): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('[ORéBUT] Votre compte est activé')
            ->htmlTemplate('registration/activation_email.html.twig')
            ->context(['user' => $user]);


        $this->mailer->send($email);
    }
}
