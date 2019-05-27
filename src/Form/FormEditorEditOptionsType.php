<?php

namespace App\Form;


use App\Entity\CustomObject;
use App\Entity\Form;
use App\Model\ForgotPassword;
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
 * Class FormEditorEditOptionsType
 * @package App\Form
 */
class FormEditorEditOptionsType extends AbstractType
{

    const COOKIE_TRACKING_MESSAGE = 'If cookie tracking is turned off, each form submission from the same browser will create a new record. You may want to turn cookie tracking off if people are going to fill out this form on the same device at an event, conference, or trade show.';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('submitAction',ChoiceType::class, [
            'label' => 'What should happen after a visitor submits this form',
            'choices'  => [
                'Display a thank you message' => Form::SUBMIT_ACTION_MESSAGE,
                'Redirect to another page' => Form::SUBMIT_ACTION_REDIRECT
            ],
            'expanded' => true,
            'multiple' => false,
        ]);

        $builder->add('cookieTracking', ChoiceType::class, [
            'label' => 'Cookie tracking',
            'choices'  => [
                'No' => 0,
                'Yes' => 1
            ],
            'help' => self::COOKIE_TRACKING_MESSAGE,
            'required' => true,
            'expanded' => true,
            'multiple' => false,
        ]);

        $builder->add('recaptcha', ChoiceType::class, [
            'label' => 'Captcha (Spam prevention)',
            'choices'  => [
                'No' => 0,
                'Yes' => 1
            ],
            'required' => true,
            'expanded' => true,
            'multiple' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getData();
            $this->modifyForm($event->getForm(), $form->getSubmitAction());
        });

        $builder->get('submitAction')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $submitAction = $event->getForm()->getData();
            $this->modifyForm($event->getForm()->getParent(), $submitAction);
        });

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
            'data_class' => Form::class,
        ));

    }
}