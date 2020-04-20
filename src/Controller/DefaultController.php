<?php

namespace App\Controller;

use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ListController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/{reactRouting}", name="default_index", defaults={"reactRouting": null})
     */
    public function createAction(Request $request) {

        $name = "Josh";
        return $this->render('default/index.html.twig');

    }
}