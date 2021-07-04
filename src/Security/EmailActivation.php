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
    private EntityManagerInterface $entityManager;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $manager;
    }

    public function sendEmailConfirmation(UserInterface $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('contact@iut.fr', 'Application ORéBUT'))
            ->to($user->getEmail())
            ->subject('[ORéBUT] Votre compte est activé')
            ->htmlTemplate('registration/activation_email.html.twig')
            ->context(['user' => $user]);


        $this->mailer->send($email);
    }
}
