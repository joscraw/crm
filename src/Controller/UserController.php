<?php

namespace App\Controller;

use App\Entity\Portal;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 *
 * @Route("/{internalIdentifier}/users")
 *
 */
class UserController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route(name="user_settings", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal) {

        return $this->render('user/index.html.twig', array(
            'portal' => $portal,
        ));
    }

}