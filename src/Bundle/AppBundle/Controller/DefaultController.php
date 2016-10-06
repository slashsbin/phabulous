<?php

namespace Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@App/gantt.html.twig');
    }

    public function refreshAction()
    {
        $appCache = $this->get('cache.app');
        $appCache->clear();

        return $this->redirectToRoute('root');
    }
}
