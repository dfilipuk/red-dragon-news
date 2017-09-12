<?php

namespace AppBundle\Service;

use AppBundle\Entity\Token;
use AppBundle\Entity\User;
use Symfony\Component\Templating\EngineInterface;

class MailManager
{
    private const WELCOME_HEADER = 'Welcome to Red Dragon!';
    private const RESET_PASSWORD_HEADER = 'Red Dragon - Reset password';

    private $twigEngine;
    private $mailer;
    private $mailerUser;

    public function __construct(string $mailerUser, EngineInterface $twigEngine, \Swift_Mailer $mailer)
    {
        $this->twigEngine = $twigEngine;
        $this->mailer = $mailer;
        $this->mailerUser = $mailerUser;
    }

    public function sendActivationEmail(User $user, Token $activationToken)
    {
        $message = (new \Swift_Message(self::WELCOME_HEADER))
            ->setFrom($this->mailerUser)
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderActivationEmailBody($activationToken),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendResetPasswordEmail(User $user, Token $activationToken)
    {
        $message = (new \Swift_Message(self::RESET_PASSWORD_HEADER))
            ->setFrom($this->mailerUser)
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderResetPasswordEmailBody($activationToken),
                'text/html'
            );
        $this->mailer->send($message);
    }

    private function renderActivationEmailBody(Token $token)
    {
        return $this->twigEngine->render('auth/activation_email.html.twig', [
            'token' => $token
        ]);
    }

    private function renderResetPasswordEmailBody(Token $token)
    {
        return $this->twigEngine->render('auth/reset_password_email.html.twig', [
            'token' => $token
        ]);
    }
}
