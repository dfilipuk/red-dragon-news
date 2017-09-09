<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
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
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->registerNewUser($user);
            return $this->renderRegistrationFinishedMessage($user);
        }
        return $this->renderRegistrationFormErrors($form);
    }

    /**
     * @Route("auth/activation/{id}/{token}", name="account_activation", requirements={"id": "\d+"})
     */
    public function accountActivationAction(int $id, string $token, UserManager $userManager)
    {
        if ($userManager->activateUserAccount($id, $token)) {
            return $this->renderActivationSuccessMessage();
        } else {
            return $this->renderActivationFailMessage();
        }
    }

    private function renderActivationSuccessMessage()
    {
        return $this->render('auth/message.twig', [
            'params' => [],
            'message_template' => 'auth/messages/activation_success.html.twig',
            'title' => 'Account activation success'
        ]);
    }

    private function renderActivationFailMessage()
    {
        return $this->render('auth/message.twig', [
            'params' => [],
            'message_template' => 'auth/messages/activation_fail.html.twig',
            'title' => 'Account activation fail'
        ]);
    }

    private function renderRegistrationFormErrors(Form $form)
    {
        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true, true)
        ]);
    }

    private function renderRegistrationFinishedMessage(User $user)
    {
        return $this->render('auth/message.twig', [
            'params' => [
                'email' => $user->getEmail()
            ],
            'message_template' => 'auth/messages/registration_finished.html.twig',
            'title' => 'Registration continue'
        ]);
    }
}
