<?php

namespace App\Form;


use App\Entity\CustomObject;
use App\Entity\Form;
use App\Entity\Portal;
use App\Entity\Workflow;
use App\Entity\WorkflowTrigger;
use App\Model\ForgotPassword;
use App\Model\PropertyBasedTrigger;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WorkflowTriggerType
 * @package App\Form
 */
class WorkflowTriggerType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('triggerType', ChoiceType::class, [
            'choices'  => WorkflowTrigger::$availableTriggers,
            'label' => 'Please select the trigger you would like to use.',
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'placeholder' => 'Select a trigger type please...',
            'attr' => [
                'class' => 'js-selectize-single-select js-workflow-trigger'
            ]
        ]);

/*        $builder->get('triggerType')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $portal = $event->getForm()->getParent()->getConfig()->getOption('portal');
            $data = $event->getData();

            if(!$data) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $data, $portal);
        });*/

        $builder->get('triggerType')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $portal = $event->getForm()->getParent()->getConfig()->getOption('portal');
            $triggerType = $event->getForm()->getData();
            $this->modifyForm($event->getForm()->getParent(), $triggerType, $portal);
        });

    }

    /**
     * @param FormInterface $form
     * @param $triggerType
     * @param Portal $portal
     */
    private function modifyForm(FormInterface $form, $triggerType, Portal $portal) {

        $fieldClass = null;
        $builderData = null;
        $options = [
            'auto_initialize' => false,
            'label' => false,
        ];

        switch($triggerType) {
            case WorkflowTrigger::PROPERTY_BASED_TRIGGER:
                $builderData = new PropertyBasedTrigger();
                $fieldClass = PropertyBasedTriggerType::class;
                $options['portal'] = $portal;
                break;
        }

        if(!$fieldClass) {
            return;
        }

        // This is a really important thing to NOTE!
        // event listeners can only be attached to a builder (FormBuilderInterface)
        // and NOT to a form (FormInterface). This is why we have to create our own builder
        // the builder is nothing more then a form field
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'trigger',
            $fieldClass,
            $builderData,
            $options
        );

        // last but not least, let's add the builder (form field) to the main form
        $form->add($builder->getForm());

        /*$form->add('redirectUrl', TextType::class, array(
            'attr' => [
                'class' => 'js-form-field'
            ]
        ));*/
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => WorkflowTrigger::class,
        ));

        $resolver->setRequired(['portal']);

    }
}