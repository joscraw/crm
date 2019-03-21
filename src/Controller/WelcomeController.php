<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WelcomeController
 * @package App\Controller
 */
class WelcomeController extends AbstractController
{
    /**
     * @Route("/", name="welcome_page", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {

        return $this->redirectToRoute('login');
    }

}