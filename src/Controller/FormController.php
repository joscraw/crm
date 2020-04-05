<?php

namespace App\Controller;

use App\Entity\Form;
use App\Entity\Portal;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FormController
 * @package App\Controller
 */
class FormController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * RecordController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
    }

    /**
     * @Route("/{internalIdentifier}/forms/object", name="form_object", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function objectAction(Portal $portal) {

        return $this->render('form/object.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/forms/editor/{uid}/edit/form", name="editor_edit_form", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editorEditFormAction(Portal $portal, Form $form) {

        return $this->render('form/editor_edit_form.html.twig', array(
            'portal' => $portal,
            'form' => $form,
        ));
    }

    /**
     * @Route("/{internalIdentifier}/forms/editor/{uid}/edit/options", name="editor_edit_options", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editorEditOptionsAction(Portal $portal, Form $form) {

        return $this->render('form/editor_edit_options.html.twig', array(
            'portal' => $portal,
            'form' => $form,
        ));
    }

    /**
     * @Route("/{internalIdentifier}/forms/{routing}", name="form_settings", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formSettingsAction(Portal $portal) {

        return $this->render('form/settings.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/forms/{uid}", name="form", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Form $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction(Form $form) {

        return $this->render('form/form.html.twig', array(
            'form' => $form
        ));
    }

}