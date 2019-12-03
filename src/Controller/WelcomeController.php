<?php

namespace App\Controller;

use NSCSBundle\KnpUIpsum;
use NSCSBundle\Repository\RecordRepository;
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
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * WelcomeController constructor.
     * @param RecordRepository $recordRepository
     */
    public function __construct(RecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    /**
     * @Route("/", name="welcome_page", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Request $request) {

        $records = $this->recordRepository->getContactByEmailAndInvitationCode('joshcrawmer4@yahoo.com', '12345');

        return $this->redirectToRoute('login');
    }

}