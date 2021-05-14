<?php

namespace App\Controller;

use App\Entity\Portal;
use App\Entity\Workflow;
use App\Utils\RandomStringGenerator;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WorkflowController
 * @package App\Controller
 */
class WorkflowController extends AbstractController
{
    use RandomStringGenerator;
    use ServiceHelper;

    /**
     * @Route("/{internalIdentifier}/workflows/type", name="workflow_type", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAction(Portal $portal) {

        return $this->render('workflow/type.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{uid}/object", name="workflow_object", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function objectAction(Portal $portal, Workflow $workflow) {

        return $this->render('workflow/object.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{uid}/triggers", name="workflow_trigger", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function triggersAction(Portal $portal, Workflow $workflow) {

        return $this->render('workflow/trigger.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{uid}/actions", name="workflow_action", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function actions(Portal $portal, Workflow $workflow) {

        return $this->render('workflow/action.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{routing}", name="workflow_settings", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workflowSettings(Portal $portal) {


        $property = $this->propertyRepository->findOneBy([
           'customObject' =>  1,
            'internalName' => 'drop'
        ]);


        return $this->render('workflow/settings.html.twig', array(
            'portal' => $portal
        ));
    }
}