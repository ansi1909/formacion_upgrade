<?php

namespace Link\ComunBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LinkComunBundle:Default:index.html.twig');
    }
}
