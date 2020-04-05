<?php

namespace App\Form;

use App\Entity\Form;
use App\Entity\Workflow;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WorkflowType
 * @package App\Form
 */
class WorkflowType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('workflowTriggers', CollectionType::class, [
            'entry_type' => WorkflowTriggerType::class,
            'entry_options' => ['label' => false, 'portal' => $options['portal']],
            'allow_add' => true,
            'label' => false,
            'error_bubbling' => false,
            'prototype' => true,
            'prototype_name' => '__prototype_one__',
            'by_reference' => false,
        ]);


       /* $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getData();
            $this->modifyForm($event->getForm(), $form->getSubmitAction());
        });
*/


    }

    /**
     * @param FormInterface $form
     * @param $submitAction
     */
    private function modifyForm(FormInterface $form, $submitAction) {

        if($submitAction === Form::SUBMIT_ACTION_MESSAGE) {
            $form->add('submitMessage', CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1'

                ),
                'input_sync' => true,
                'attr' => [
                    'class' => 'js-form-field'
                ]
            ));
            $form->remove('redirectUrl');
        } elseif ($submitAction === Form::SUBMIT_ACTION_REDIRECT) {
            $form->add('redirectUrl', TextType::class, array(
                'attr' => [
                    'class' => 'js-form-field'
                ]
            ));
            $form->remove('submitMessage');
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Workflow::class,
        ));

        $resolver->setRequired(['portal']);
    }
}