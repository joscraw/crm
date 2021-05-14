<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\WorkflowAction;
use App\Entity\Workflow;
use App\Model\WorkflowTrigger;
use App\Repository\CustomObjectRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class WorkflowSetupType extends AbstractType implements DataMapperInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var CustomObjectRepository
     */
    protected $customObjectRepository;

    /**
     * WorkflowSetupType constructor.
     * @param RequestStack $requestStack
     * @param SerializerInterface $serializer
     * @param CustomObjectRepository $customObjectRepository
     */
    public function __construct(
        RequestStack $requestStack,
        SerializerInterface $serializer,
        CustomObjectRepository $customObjectRepository
    ) {
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;
        $this->customObjectRepository = $customObjectRepository;
    }


    public function mapDataToForms($viewData, $forms)
    {
        // do nothing
        return;
    }

    public function mapFormsToData($forms, &$viewData)
    {
        /** @var Workflow $viewData */
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $triggers = $forms['triggers']->getData();
        $viewData->setTriggers($triggers);

        $customObject = $forms['customObject']->getData();
        $viewData->setCustomObject($customObject);

        $filterData = json_decode($forms['filterData']->getData(), true);
        $viewData->setFilterData($filterData);

        $workflowActions = array_merge(
            $forms['workflowPropertyUpdateActions']->getData(),
            $forms['workflowSendEmailActions']->getData()
        );

        if(!empty($workflowActions)) {
            foreach($workflowActions as $workflowAction) {
                $viewData->addWorkflowAction($workflowAction);
            }
        }
    }

    /**
     * todo need to add validation to the properties here. Have Robbie do this.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customObject', EntityType::class, [
            // looks for choices from this entity
            'class' => CustomObject::class,
        ])->add('workflowTrigger', TextType::class, [])
            ->add('workflowPropertyUpdateActions', CollectionType::class, array(
            'entry_type'   => WorkflowPropertyUpdateActionType::class,
            'allow_add' => true,
        ))->add('workflowSendEmailActions', CollectionType::class, array(
            'entry_type'   => WorkflowSendEmailActionType::class,
            'allow_add' => true,
        ))->add('filterData', TextType::class, [])
            ->setDataMapper($this);

        // todo add query property here as well.

        /**
         * This is a little bit tricky. But here's what's going on. Workflow Actions inherit
         * from the Abstract WorkflowAction class. You can't actually create a form type from
         * an abstract class. Rather then having the end user pass up all the actions as a
         * separate json property/value, We are allowing them all to be passed up in one array
         * "workflowActions" and then converting the data on the fly just prior to submit so
         * each action aligns with the proper class/form type.
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if(!empty($data['workflowActions'])) {
                foreach($data['workflowActions'] as $workflowAction) {
                    switch ($workflowAction['name']) {
                        case WorkflowAction::WORKFLOW_PROPERTY_UPDATE_ACTION:
                            $data['workflowPropertyUpdateActions'][] = $workflowAction;
                            break;
                        case WorkflowAction::WORKFLOW_SEND_EMAIL_ACTION:
                            $data['workflowSendEmailActions'][] = $workflowAction;
                            break;
                    }
                }
            }

            if(!empty($data['filterData'])) {
                $data['filterData'] = json_encode($data['filterData'], true);
            }

            unset($data['workflowActions']);

            $event->setData($data);
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $csrfProtection = true;
        $request = $this->requestStack->getCurrentRequest();
        $acceptHeader = AcceptHeader::fromString($request->headers->get('Accept'));
        if ($acceptHeader->has('application/json')) {
            $csrfProtection = false;
        }

        $resolver->setDefaults([
            'data_class' => Workflow::class,
            'csrf_protection' => $csrfProtection,
            'allow_extra_fields' => true,
        ]);
    }
}
