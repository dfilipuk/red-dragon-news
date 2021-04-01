<?php

namespace AppBundle\Service;

use AppBundle\Entity\Token;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class MailManager
{
    private const WELCOME_HEADER = 'Welcome to Red Dragon!';
    private const RESET_PASSWORD_HEADER = 'Red Dragon - Reset password';
    private const SUBSCRIPTION_HEADER = 'Red Dragon - news';
    private const HOST_NAME = 'localhost:8000';

    private $twigEngine;
    private $mailer;
    private $mailerUser;

    /**
     * MailManager constructor.
     * @param string $mailerUser
     * @param EngineInterface $twigEngine
     * @param \Swift_Mailer $mailer
     */
    public function __construct(string $mailerUser, EngineInterface $twigEngine, \Swift_Mailer $mailer)
    {
        $this->twigEngine = $twigEngine;
        $this->mailer = $mailer;
        $this->mailerUser = $mailerUser;
    }

    /**
     * @param User $user
     * @param Token $activationToken
     */
    public function sendActivationEmail(User $user, Token $activationToken): void
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

    /**'
     * @param User $user
     * @param Token $activationToken
     */
    public function sendResetPasswordEmail(User $user, Token $activationToken): void
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

    /**
     * @param Token $token
     * @return string
     */
    private function renderActivationEmailBody(Token $token): string
    {
        return $this->twigEngine->render('auth/activation_email.html.twig', [
            'token' => $token
        ]);
    }

    /**
     * @param Token $token
     * @return string
     */
    private function renderResetPasswordEmailBody(Token $token): string
    {
        return $this->twigEngine->render('auth/reset_password_email.html.twig', [
            'token' => $token
        ]);
    }

    /**
     * @param User $user
     * @param string $mailBody
     */
    public function sendSubscriptionEmail(User $user, string $mailBody): void
    {
        $message = (new \Swift_Message(self::SUBSCRIPTION_HEADER))
            ->setFrom($this->mailerUser)
            ->setTo($user->getEmail())
            ->setBody(
                $mailBody,
                'text/html'
            );
        $this->mailer->send($message);
    }

    /**
     * @param array $posts
     * @param string $type
     * @return string
     */
    public function generateSubscriptionMailBody(array $posts, string $type): string
    {
        return $this->twigEngine->render('subscription/mail.html.twig', [
            'posts' => $posts,
            'type' => $type,
            'host' => self::HOST_NAME
        ]);
    }
}
