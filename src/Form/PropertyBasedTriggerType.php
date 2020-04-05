<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PropertyBasedTriggerType
 * @package App\Form
 */
class PropertyBasedTriggerType extends AbstractType
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

        /** @var Portal $portal */
        $portal = $options['portal'];

        $builder->add('customObject', ChoiceType::class, array(
            'choices'  => $this->customObjectRepository->findBy(['portal' => $portal->getId()]),
            'choice_value' => function ($choice) {
                return $choice !== null ? $choice->getId() : '';
            },
            'choice_label' => function($choice, $key, $value) {
                return $choice->getLabel();
            },
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Now go ahead and select a custom object.',
            'by_reference' => false,
            'placeholder' => 'Select a custom object please...',
            'attr' => [
                'class' => 'js-custom-object js-selectize-single-select'
            ]
        ));

        $builder->get('customObject')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $portal = $event->getForm()->getParent()->getConfig()->getOption('portal');
            $data = $event->getData();

            if(!$data) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $portal, $data);
        });

        $builder->get('customObject')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            $portal = $event->getForm()->getParent()->getConfig()->getOption('portal');

            $customObject = $event->getForm()->getData();

            if(!$customObject) {
                return;
            }

            $this->modifyForm($event->getForm()->getParent(), $portal, $customObject);
        });

    }

    /**
     * @param FormInterface $form
     * @param CustomObject|null $customObjectReference
     * @param Portal $portal
     */
    private function modifyForm(FormInterface $form, Portal $portal, CustomObject $customObjectReference = null) {

        $choices = null === $customObjectReference ? array() : $this->propertyRepository->findBy(['customObject' => $customObjectReference->getId()]);

        // create builder for field
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder('property', ChoiceType::class, null, array(
            'choices' => $choices,
            'choice_value' => function ($choice) {
                return $choice ? $choice->getId() : null;
            },
            'choice_label' => function($choice, $key, $value) {
                return $choice ? $choice->getLabel() : null;
            },
            'expanded' => false,
            'multiple' => false,
            'label' => 'Select the property for this trigger.',
            'placeholder' => 'Select a property please...',
            'required' => false,
            'attr' => [
                'class' => 'js-selectize-single-select js-property'
            ],
            'auto_initialize'=>false // it's important!!!
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();

            if(!$data) {
                return;
            }

            $this->addConditionField($event->getForm()->getParent(), $data);
        });

        // now you can add listener
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {

            /** @var Property $property */
            $property = $event->getForm()->getData();

            if(!$property) {
                return;
            }

            $this->addConditionField($event->getForm()->getParent(), $property);
        });

        // and only now you can add field to form
        $form->add($builder->getForm());
    }

    /**
     * @param FormInterface $form
     * @param Property|null $property
     */
    private function addConditionField(FormInterface $form, Property $property = null) {

        $form->add('condition', SingleLineTextFieldConditionType::class, array());

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PropertyBasedTrigger::class,
        ));

        $resolver->setRequired([
            'portal'
        ]);
    }
}