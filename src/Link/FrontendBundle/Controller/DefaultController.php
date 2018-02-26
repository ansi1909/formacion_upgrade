<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LinkFrontendBundle:Default:index.html.twig');
    }

    public function loginAction()
    {
        return $this->render('LinkFrontendBundle:Default:login.html.twig');
    }

}
