<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Form\Type\SelectizeSearchResultPropertiesType;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\SingleLineTextField;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditCustomObjectFieldType
 * @package App\Form
 */
class EditCustomObjectFieldType extends AbstractType
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Portal $portal */
        $portal = $options['portal'];

        /** @var  $customObject */
        $customObject = $options['customObject'];

        $c = $this->customObjectRepository->findByPortal($portal);

        $builder->add('customObject', ChoiceType::class, array(
            /*'class' => CustomObject::class,*/
            'choices'  => $this->customObjectRepository->findByPortal($portal),
           /* 'query_builder' => function (EntityRepository $er) use ($portal) {
                return $er->createQueryBuilder('customObject')
                    ->where('customObject.portal = :portal')
                    ->setParameter('portal', $portal)
                    ->orderBy('customObject.label', 'ASC');
            },*/
            /*'choice_label' => 'label',
            'data' => 2,*/
            /*'choices'  => [
                'Maybe' => null,
                'Yes' => true,
                'No' => false,
            ],
            'data' => 'Yes',*/
            'choice_value' => function ($choice) {
                return $choice !== null ? $choice->getId() : '';
            },
            'choice_label' => function($choice, $key, $value) {
                return $choice->getLabel();
            },
            /*'data' => $customObject,*/
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'by_reference' => false,
            'placeholder' => 'Select a custom object please...',
            'attr' => [
                'class' => 'js-custom-object'
            ]
        ));

        $builder->add('multiple', CheckboxType::class, array(
            'label'    => 'Multiple',
            'required' => false,
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $this->modifyForm($event->getForm(), $data->getCustomObject());
        });

        $builder->get('customObject')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $customObject = $event->getForm()->getData();
            $this->modifyForm($event->getForm()->getParent(), $customObject);
        });

    }

    private function modifyForm(FormInterface $form, CustomObject $customObjectReference = null) {

        $portal = $form->getConfig()->getOption('portal');

        $choices = null === $customObjectReference ? array() : $this->propertyRepository->getSelectizeSearchResultProperties($portal, $customObjectReference);

        if(null === $customObjectReference) {
            $placeholder = 'Select an object up above to get started!';
        } elseif(empty($choices)) {
            $placeholder = 'Woah, hold up! No Properties exist for that object yet!';
        } else {
            $placeholder = 'Start typing to search..';
        }

        $form->add('selectizeSearchResultProperties', SelectizeSearchResultPropertiesType::class, array(
            /*'class' => Property::class,*/
            'choices' => $choices,
            'choice_value' => function ($choice) {
                return $choice->getId();
            },
            'choice_label' => function($choice, $key, $value) {
                return $choice->getLabel();
            },
            'expanded' => false,
            'multiple' => true,
            'label' => 'Search Result Properties',
            'help' => 'When using this property throughout the CRM, these SRP\'s (Search Result Properties) will show up in the dropdown of the HTML field.',
            'required' => false,
            'attr' => [
                'placeholder' => $placeholder,
                'class' => 'js-selectize-multiple-select'
            ]
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CustomObjectField::class,
        ));

        $resolver->setRequired([
            'portal',
            'customObject'
        ]);
    }
}