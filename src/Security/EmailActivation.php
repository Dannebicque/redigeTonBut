<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

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
