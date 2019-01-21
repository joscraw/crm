<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Model\FieldCatalog;
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RecordType
 * @package App\Form\Property
 */
class RecordType extends AbstractType
{

    /**
     * @var IdToRecordTransformer
     */
    private $transformer;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    public function __construct(IdToRecordTransformer $transformer, RecordRepository $recordRepository)
    {
        $this->transformer = $transformer;
        $this->recordRepository = $recordRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Property $properties[] */
        $properties = $options['properties'];

        /** @var Portal $portal */
        $portal = $options['portal'];

        foreach($properties as $property) {
            $options = [];

            if($property->isRequired()) {
                $options['constraints'] = [
                    new NotBlank(),
                ];
                $options['required'] = true;
            }

            switch($property->getFieldType()) {
                case FieldCatalog::SINGLE_LINE_TEXT:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), TextType::class, $options);
                    break;
                case FieldCatalog::MULTI_LINE_TEXT:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), TextareaType::class, $options);
                    break;
                case FieldCatalog::DROPDOWN_SELECT:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();
                    $options = array_merge([
                        'choices'  => $choices,
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select',
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $options = array_merge([
                        'choices'  => array(
                            'Yes' => true,
                            'No' => false,
                        ),
                        'label' => $property->getLabel(),
                        'expanded' => false,
                        'multiple' => false,
                        'required' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select',
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::MULTIPLE_CHECKBOX:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();
                    $options = array_merge([
                        'choices'  => $choices,
                        'label' => $property->getLabel(),
                        'expanded' => false,
                        'multiple' => true,
                        'required' => false,
                        'attr' => [
                            'class' => 'js-selectize-multiple-select',
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::RADIO_SELECT:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();
                    $options = array_merge([
                        'choices'  => $choices,
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select',
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::NUMBER:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), NumberType::class, $options);
                    break;
                case FieldCatalog::DATE_PICKER:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'widget' => 'single_text',
                        'format' => 'yyyy-MM-dd',
                        // prevents rendering it as type="date", to avoid HTML5 date pickers
                        'html5' => false,
                        // adds a class that can be selected in JavaScript
                        'attr' => [
                            'class' => 'js-datepicker',
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ],
                    ], $options);
                    $builder->add($property->getInternalName(), DateType::class, $options);
                    break;
                case FieldCatalog::CUSTOM_OBJECT:

                    $customObject = $property->getField()->getCustomObject();

                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'class' => 'js-selectize-single-select-with-search',
                            'placeholder' => 'Start typing to search..',
                            'data-allowed-custom-object-to-search' => $customObject->getId(),
                            'data-property-id' => $property->getId(),
                            'autocomplete' => 'off'
                        ],
                        'expanded' => false,
                    ], $options);

                    if($property->getField()->isMultiple()) {
                        $options['multiple'] = true;
                    }

                    $builder->add($property->getInternalName(), RecordChoiceType::class, $options);
                    break;
            }
        }

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
           'properties',
            'portal'
        ]);
    }
}