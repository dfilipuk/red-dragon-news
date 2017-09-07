<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 13:19
 */

namespace AppBundle\Controller;


use AppBundle\Service\NewsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    /**
     * @Route("/main/", name="home")
     */
    public function indexAction(NewsManager $newsManager)
    {
        $news = $newsManager->findAll();
        return $this->render("main/index.html.twig", array('news' => $news));
    }
}