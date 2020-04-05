<?php

namespace App\Controller;

use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WelcomeController
 * @package App\Controller
 */
class WelcomeController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/", name="welcome_page", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Request $request) {
        return $this->redirectToRoute('login');
    }

}