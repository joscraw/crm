<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\Workflow;
use App\Entity\WorkflowPropertyUpdateAction;
use App\Form\WorkflowSetupType;
use App\Utils\FormHelper;
use App\Utils\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Class WorkflowSetupController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/api/workflows")
 */
class WorkflowSetupController extends AbstractController
{
    use ServiceHelper;
    use FormHelper;

    /**
     * @Route("/setup", name="workflow_setup", options = { "expose" = true }, methods={"POST"})
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function workflowSetupAction(Portal $portal, Request $request)
    {
        $user = $this->getUser();
        $workflow = new Workflow();
        $workflow->setPortal($portal);
        $form = $this->createForm(WorkflowSetupType::class, $workflow, []);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            $errors = $this->getErrorsFromForm($form);
            return new JsonResponse(
                [
                    'success' => false,
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST
            );
        } else {
            /** @var Workflow $workflow */
            $workflow = $form->getData();
            $this->entityManager->persist($workflow);
            $this->entityManager->flush();

            /**
             * all callback parameters are optional (you can omit the ones you don't use)
             * @see https://symfony.com/doc/current/components/serializer.html#using-callbacks-to-serialize-properties-with-object-instances
             * @param $innerObject
             * @param $outerObject
             * @param string $attributeName
             * @param string|null $format
             * @param array $context
             * @return int
             */
            $fieldCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                if($innerObject instanceof Property && $outerObject instanceof WorkflowPropertyUpdateAction) {
                    return $innerObject->getId();
                }
            };

            $defaultContext = [
                AbstractNormalizer::CALLBACKS => [
                    'field' => $fieldCallback
                ],
                'groups' => ['WORKFLOW'],
            ];

            $json = $this->serializer->serialize($workflow, 'json', $defaultContext);
            $workflow = json_decode($json, true);

            return new JsonResponse(
                [
                    'success' => true,
                    'workflow' => $workflow,
                ], Response::HTTP_OK
            );
        }
    }

}
