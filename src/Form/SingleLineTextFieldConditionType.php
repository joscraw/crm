<?php

namespace App\Form;

use App\Model\SingleLineTextFieldCondition;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SingleLineTextFieldConditionType
 * @package App\Form
 */
class SingleLineTextFieldConditionType extends AbstractType
{

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * CustomObjectFieldType constructor.
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(CustomObjectRepository $customObjectRepository, PropertyRepository $propertyRepository)
    {
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('operator', ChoiceType::class, array(
            'choices'  => PropertyBasedTrigger::$availableConditions,
            'expanded' => false,
            'multiple' => false,
            'label' => 'Select a condition for this trigger.',
            'placeholder' => 'Select a condition please...',
            'required' => false,
            'attr' => [
                'class' => 'js-selectize-single-select js-condition'
            ],
            'auto_initialize'=>false // it's important!!!
        ));

        $builder->get('operator')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $operator = $event->getData();

            if(!$operator) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $operator, false);
        });

        $builder->get('operator')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $operator = $event->getForm()->getData();

            $this->modifyForm($event->getForm()->getParent(), $operator);
        });
    }

    /**
     * @param FormInterface $form
     * @param $operator
     * @param bool $createTrigger
     */
    private function modifyForm(FormInterface $form, $operator, $createTrigger = true) {

        $fieldClass = null;
        $builderData = null;
        $options = [
            'auto_initialize' => false,
            'label' => false,
        ];

        switch($operator) {
            case SingleLineTextFieldCondition::CONDITION_CONTAINS_EXACTLY:
            case SingleLineTextFieldCondition::CONDITION_DOESNT_CONTAIN_EXACTLY:

            $form->add('value', TextType::class, array(
                'attr' => [
                    'class' => 'js-form-field',
                    'placeholder' => 'Enter a value...',
                ]
            ));

            $form->add('submit', SubmitType::class, array(
                'label' => $createTrigger ? 'Create Trigger' : 'Edit Trigger'
            ));

                break;
            case SingleLineTextFieldCondition::CONDITION_IS_UNKNOWN:
            case SingleLineTextFieldCondition::CONDITION_IS_KNOWN:

                if($form->has('value')) {
                    $form->remove('value');
                }

                break;
        }
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SingleLineTextFieldCondition::class,
        ));
    }
}