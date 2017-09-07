<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/signin", name="signin")
     */
    public function signInAction()
    {
        return $this->render('auth/login.html.twig');
    }

    /**
     * @Route("/signup", name="signup")
     */
    public function signUpAction()
    {
        return $this->render('auth/register.html.twig');
    }
}