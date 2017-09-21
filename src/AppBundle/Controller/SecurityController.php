<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\NewPasswordType;
use AppBundle\Form\UserEmailType;
use AppBundle\Form\UserType;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/auth/sign-in", name="sign_in")
     */
    public function signInAction(AuthenticationUtils $authUtils)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();
        return $this->render('auth/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/auth/sign-up", name="sign_up")
     */
    public function signUpAction(Request $request, UserManager $userManager)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['validation_groups' => 'registration']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->registerNewUser($user);
            return $this->renderMessage(
                'auth/message.twig',
                ['email' => $user->getEmail()],
                'auth/messages/registration_finished.html.twig',
                'Registration continue'
            );
        }
        return $this->renderFormErrors($form, 'auth/register.html.twig');
    }

    /**
     * @Route("/auth/reset-password", name="reset_password")
     */
    public function resetPasswordAction(Request $request, UserManager $userManager)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }
        $user = new User();
        $form = $this->createForm(UserEmailType::class, $user, ['validation_groups' => 'checkEmail']);
        $form->handleRequest($request);
        if ($this->isEmailCheckFormValid($form, $user, $userManager)) {
            $userManager->setResetPasswordTokenForUser($user);
            return $this->renderMessage(
                'auth/message.twig',
                ['email' => $user->getEmail()],
                'auth/messages/reset_password_continue.html.twig',
                'Reset password continue'
            );
        }
        return $this->renderFormErrors($form, 'auth/email_check.html.twig');
    }

    /**
     * @Route("auth/new-password/{id}/{token}", name="new_password", requirements={"id": "\d+"})
     */
    public function newPasswordAction(Request $request, int $id, string $token, UserManager $userManager)
    {
        if ($userManager->isResetPasswordTokenValid($id, $token)) {
            return $this->workWithNewPasswordForm($request, $id, $userManager);
        } else {
            return $this->renderMessage(
                'auth/message.twig',
                [],
                'auth/messages/password_changed.html.twig',
                'Access denied'
            );
        }
    }

    /**
     * @Route("auth/activation/{id}/{token}", name="account_activation", requirements={"id": "\d+"})
     */
    public function accountActivationAction(int $id, string $token, UserManager $userManager)
    {
        if ($userManager->isUserAccountActivationSucceed($id, $token)) {
            return $this->renderMessage(
                'auth/message.twig',
                [],
                'auth/messages/activation_success.html.twig',
                'Account activation success'
            );
        } else {
            return $this->renderMessage(
                'auth/message.twig',
                [],
                'auth/messages/activation_fail.html.twig',
                'Account activation fail'
            );
        }
    }

    private function workWithNewPasswordForm(Request $request, int $tokenId, UserManager $userManager)
    {
        $user = new User();
        $form = $this->createForm(NewPasswordType::class, $user, ['validation_groups' => 'passwordReset']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->resetPasswordForUser($tokenId, $user);
            return $this->renderMessage(
                'auth/message.twig',
                [],
                'auth/messages/password_changed.html.twig',
                'Password changed'
            );
        }
        return $this->renderFormErrors($form, 'auth/new_password.html.twig');
    }

    private function isEmailCheckFormValid(Form $form, User $user, UserManager $userManager): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if ($userManager->isUserAlreadyExists($user)) {
                return true;
            } else {
                $form->addError(new FormError('No account with the same e-mail'));
                return false;
            }
        } else {
            return false;
        }
    }

    private function renderMessage(string $baseTemplate, array $params, string $messageTemplate, string $title)
    {
        return $this->render($baseTemplate, [
            'params' => $params,
            'message_template' => $messageTemplate,
            'title' => $title
        ]);
    }

    private function renderFormErrors(Form $form, string $template)
    {
        return $this->render($template, [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true, true)
        ]);
    }
}
