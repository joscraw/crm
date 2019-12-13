<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\IdArrayToRecordArrayTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Form\DataTransformer\RecordCheckboxTransformer;
use App\Form\DataTransformer\RecordDateTimeTransformer;
use App\Form\DataTransformer\RecordGenericTransformer;
use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Model\DatePickerField;
use App\Model\FieldCatalog;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class BulkEditType
 * @package App\Form\Property
 */
class BulkEditType extends AbstractType
{

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * BulkEditType constructor.
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(
        PropertyGroupRepository $propertyGroupRepository,
        PropertyRepository $propertyRepository
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyRepository = $propertyRepository;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        $customObject = $options['customObject'];
        $propertyGroups = $this->propertyGroupRepository->getPropertyGroupsAndProperties($customObject);
        foreach($propertyGroups as $propertyGroup) {
            // We don't show Custom Objects on lists because lists are only for one object type
            foreach($propertyGroup->getProperties() as $property) {
                /*if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                    continue;
                }*/
                $choices[$propertyGroup->getName()][$property->getLabel()] = $property->getId();
            }
        }
        $builder->add('propertyToUpdate', ChoiceType::class, [
            'choices' => $choices,
            'label' => '',
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'attr' => [
                'class' => 'js-selectize-single-select-bulk-edit-property js-property'
            ]
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            if(!$data) {
                return;
            }
            $this->modifyForm($event->getForm(), $data->getCustomObject());
        });
        $builder->get('propertyToUpdate')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $propertyId = $event->getForm()->getData();
            if(!$propertyId) {
                return;
            }
            $this->modifyForm($event->getForm()->getParent(), $propertyId);
        });
    }

    private function modifyForm(FormInterface $form, $propertyId = null) {
        if(!$propertyId) {
            return;
        }
        $property = $this->propertyRepository->find($propertyId);
        $fieldClass = null;
        $builderData = null;
        $options = [
            'required' => false,
            'auto_initialize' => false,
            'property' => $property,
            'label' => $property->getLabel(),
            'attr' => [
                'data-property-id' => $property->getId(),
                'autocomplete' => 'off'
            ],
        ];
        switch($property->getFieldType()) {
            case FieldCatalog::SINGLE_LINE_TEXT:
                $fieldClass = BulkEditSingleLineTextFieldType::class;
                break;
            case FieldCatalog::MULTI_LINE_TEXT:
                $fieldClass = BulkEditMultiLineTextFieldType::class;
                break;
            case FieldCatalog::NUMBER:
                $fieldClass = BulkEditNumberFieldType::class;
                break;
            case FieldCatalog::RADIO_SELECT:
            case FieldCatalog::DROPDOWN_SELECT:
                $choices = $property->getField()->getOptionsForChoiceTypeField();
                $options = array_merge($options, [
                    'choices'  => $choices,
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'js-selectize-single-select',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off'
                    ]
                ]);

                $fieldClass = BulkEditRadioSelectFieldType::class;

                break;
            case FieldCatalog::SINGLE_CHECKBOX:

                $options = array_merge($options, [
                    'choices'  => array(
                        'Yes' => true,
                        'No' => false,
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'js-selectize-single-select',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off'
                    ]
                ]);

                $fieldClass = BulkEditSingleCheckboxFieldType::class;

                break;
            case FieldCatalog::MULTIPLE_CHECKBOX:
                $choices = $property->getField()->getOptionsForChoiceTypeField();
                $options = array_merge($options,[
                    'choices'  => $choices,
                    'expanded' => false,
                    'multiple' => true,
                    'attr' => [
                        'class' => 'js-selectize-multiple-select',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off'
                    ]
                ]);

                $fieldClass = BulkEditMultipleCheckboxFieldType::class;

                break;
            case FieldCatalog::DATE_PICKER:
                $options = array_merge($options, [
                    'widget' => 'single_text',
                    'format' => 'MM-dd-yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                    // adds a class that can be selected in JavaScript
                    'attr' => [
                        'class' => 'js-datepicker',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off'
                    ],
                ]);

                $fieldClass = BulkEditDatePickerFieldType::class;

                break;
            case FieldCatalog::CUSTOM_OBJECT:

                $customObject = $property->getField()->getCustomObject();

                $options = array_merge($options, [
                    'attr' => [
                        'class' => 'js-selectize-single-select-with-search',
                        'placeholder' => 'Start typing to search..',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off'
                    ],
                    'expanded' => false,
                ]);

                $fieldClass = BulkEditCustomObjectFieldType::class;

                if($property->getField()->isMultiple()) {
                    $options['multiple'] = true;
                }

                break;
        }
        // last but not least, let's add the builder (form field) to the main form
        $form->add('propertyValue', $fieldClass, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['allow_extra_fields' => true]);
        $resolver->setRequired([
            'customObject'
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}