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
use App\Form\DataTransformer\RecordMultipleCheckboxTransformer;
use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Model\DatePickerField;
use App\Model\FieldCatalog;
use App\Model\NumberField;
use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
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
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class FormType
 * @package App\Form\Property
 */
class FormType extends AbstractType
{
    use ArrayHelper;

    /**
     * @var IdToRecordTransformer
     */
    private $transformer;

    /**
     * @var IdArrayToRecordArrayTransformer
     */
    private $idArrayToRecordArrayTransformer;

    /**
     * @var RecordDateTimeTransformer
     */
    private $recordDateTimeTransformer;

    /**
     * @var RecordNumberCurrencyTransformer
     */
    private $recordNumberCurrencyTransformer;

    /**
     * @var RecordGenericTransformer
     */
    private $recordGenericTransformer;

    /**
     * @var RecordCheckboxTransformer
     */
    private $recordCheckboxTranformer;

    /**
     * @var RecordNumberUnformattedTransformer
     */
    private $recordNumberUnformattedTransformer;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var RecordMultipleCheckboxTransformer
     */
    private $recordMultipleCheckboxTransformer;

    /**
     * RecordType constructor.
     * @param IdToRecordTransformer $transformer
     * @param IdArrayToRecordArrayTransformer $idArrayToRecordArrayTransformer
     * @param RecordDateTimeTransformer $recordDateTimeTransformer
     * @param RecordNumberCurrencyTransformer $recordNumberCurrencyTransformer
     * @param RecordGenericTransformer $recordGenericTransformer
     * @param RecordCheckboxTransformer $recordCheckboxTranformer
     * @param RecordNumberUnformattedTransformer $recordNumberUnformattedTransformer
     * @param RecordRepository $recordRepository
     * @param RecordMultipleCheckboxTransformer $recordMultipleCheckboxTransformer
     */
    public function __construct(
        IdToRecordTransformer $transformer,
        IdArrayToRecordArrayTransformer $idArrayToRecordArrayTransformer,
        RecordDateTimeTransformer $recordDateTimeTransformer,
        RecordNumberCurrencyTransformer $recordNumberCurrencyTransformer,
        RecordGenericTransformer $recordGenericTransformer,
        RecordCheckboxTransformer $recordCheckboxTranformer,
        RecordNumberUnformattedTransformer $recordNumberUnformattedTransformer,
        RecordRepository $recordRepository,
        RecordMultipleCheckboxTransformer $recordMultipleCheckboxTransformer
    ) {
        $this->transformer = $transformer;
        $this->idArrayToRecordArrayTransformer = $idArrayToRecordArrayTransformer;
        $this->recordDateTimeTransformer = $recordDateTimeTransformer;
        $this->recordNumberCurrencyTransformer = $recordNumberCurrencyTransformer;
        $this->recordGenericTransformer = $recordGenericTransformer;
        $this->recordCheckboxTranformer = $recordCheckboxTranformer;
        $this->recordNumberUnformattedTransformer = $recordNumberUnformattedTransformer;
        $this->recordRepository = $recordRepository;
        $this->recordMultipleCheckboxTransformer = $recordMultipleCheckboxTransformer;
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

            $options = [
                'help' => isset($property['helpText']) ? $property['helpText'] : '',
                'attr' => [
                    'placeholder' => isset($property['placeholderText']) ? $property['placeholderText'] : ''
                ]
            ];

            if(isset($property['required']) && $property['required'] === 'Yes') {
                $options['constraints'] = [
                    new NotBlank(),
                ];
                $options['required'] = true;
            }

            switch($property['fieldType']) {
                case FieldCatalog::SINGLE_LINE_TEXT:
                    $options = $this->arrayMergeRecursive([
                        'required' => false,
                        'label' => $property['label'],
                        'attr' => [
                            'data-property-id' => $property['id'],
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property['internalName'], TextType::class, $options);
                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordGenericTransformer);

                    break;
                case FieldCatalog::MULTI_LINE_TEXT:
                    $options = $this->arrayMergeRecursive([
                            'required' => false,
                            'label' => $property['label'],
                            'attr' => [
                                'data-property-id' => $property['id'],
                                'autocomplete' => 'off'
                            ]
                        ], $options);

                    $builder->add($property['internalName'], TextareaType::class, $options);
                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordGenericTransformer);
                    break;
                case FieldCatalog::DROPDOWN_SELECT:
                    $fieldOptions = $property['field']['options'];
                    $choices = [];
                    foreach($fieldOptions as $fieldOption) {
                        $choices[$fieldOption['label']] = $fieldOption['label'];
                    }

                    $options = $this->arrayMergeRecursive([
                            'choices'  => $choices,
                            'label' => $property['label'],
                            'required' => false,
                            'expanded' => false,
                            'multiple' => false,
                            'attr' => [
                                'class' => 'js-selectize-single-select',
                                'data-property-id' => $property['id'],
                                'autocomplete' => 'off'
                            ]
                        ], $options);

                    $builder->add($property['internalName'], ChoiceType::class, $options);
                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordGenericTransformer);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:

                    // for a single checkbox you need to check for not null instead of not blank
                /*    if($property->isRequired()) {
                        $options['constraints'] = [
                            new NotNull(),
                        ];
                        $options['required'] = true;
                    }*/

                $options = $this->arrayMergeRecursive([
                        'choices'  => array(
                            'Yes' => true,
                            'No' => false,
                        ),
                        'label' => $property['label'],
                        'expanded' => false,
                        'multiple' => false,
                        'required' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select',
                            'data-property-id' => $property['id'],
                            'autocomplete' => 'off'
                        ]
                    ], $options);

                    $builder->add($property['internalName'], ChoiceType::class, $options);
                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordCheckboxTranformer);
                    break;
                case FieldCatalog::MULTIPLE_CHECKBOX:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();

                    $options = $this->arrayMergeRecursive([
                        'choices'  => $choices,
                        'label' => $property['label'],
                        'expanded' => false,
                        'multiple' => true,
                        'required' => false,
                        'attr' => [
                            'class' => 'js-selectize-multiple-select',
                            'data-property-id' => $property['id'],
                            'autocomplete' => 'off'
                        ]
                    ], $options);
                    $builder->add($property['internalName'], ChoiceType::class, $options);
                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordMultipleCheckboxTransformer);
                    break;
                case FieldCatalog::RADIO_SELECT:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();

                    $options = $this->arrayMergeRecursive([
                            'choices'  => $choices,
                            'label' => $property['label'],
                            'required' => false,
                            'expanded' => false,
                            'multiple' => false,
                            'attr' => [
                                'class' => 'js-selectize-single-select',
                                'data-property-id' => $property['id'],
                                'autocomplete' => 'off'
                            ]
                        ], $options);

                    $builder->add($property['internalName'], ChoiceType::class, $options);
                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordGenericTransformer);
                    break;
                case FieldCatalog::NUMBER:

                    $options = $this->arrayMergeRecursive([
                        'required' => false,
                        'label' => $property['label'],
                        'attr' => [
                            'data-property-id' => $property['id'],
                            'autocomplete' => 'off'
                        ]
                    ], $options);

                    $builder->add($property['internalName'], NumberType::class, $options);

                    $field = $property['field'];
                    if($field['type'] === NumberField::CURRENCY) {
                        $builder->get($property['internalName'])
                            ->addModelTransformer($this->recordNumberCurrencyTransformer);
                    } else if($field['type'] === NumberField::UNFORMATTED_NUMBER){
                        $builder->get($property['internalName'])
                            ->addModelTransformer($this->recordNumberUnformattedTransformer);
                    }
                    break;
                case FieldCatalog::DATE_PICKER:

                    $options = $this->arrayMergeRecursive([
                        'required' => false,
                        'label' => $property['label'],
                        'widget' => 'single_text',
                        'format' => 'MM-dd-yyyy',
                        // prevents rendering it as type="date", to avoid HTML5 date pickers
                        'html5' => false,
                        // adds a class that can be selected in JavaScript
                        'attr' => [
                            'class' => 'js-datepicker',
                            'data-property-id' => $property['id'],
                            'autocomplete' => 'off'
                        ],
                    ], $options);

                    $builder->add($property['internalName'], DateType::class, $options);

                    $builder->get($property['internalName'])
                        ->addModelTransformer($this->recordDateTimeTransformer);
                    break;
                case FieldCatalog::CUSTOM_OBJECT:

                    $options = $this->arrayMergeRecursive([
                        'required' => false,
                        'label' => $property['label'],
                        'attr' => [
                            'class' => 'js-selectize-single-select-with-search',
                            'placeholder' => 'Start typing to search..',
                            'data-property-id' => $property['id'],
                            'autocomplete' => 'off'
                        ],
                        'expanded' => false,
                    ], $options);

                    if($property['field']['multiple'] === 'true') {
                        $options['multiple'] = true;
                    }

                    $options['property'] = $property;

                    $builder->add($property['internalName'], RecordChoiceType::class, $options);

                    if($property['field']['multiple'] === 'true') {
                         $builder->get($property['internalName'])
                        ->addModelTransformer($this->idArrayToRecordArrayTransformer);
                    } else {
                         $builder->get($property['internalName'])
                        ->addModelTransformer($this->transformer);
                    }

                    break;
            }
        }

        $builder->add('submit', ButtonType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'properties',
            'portal'
        ]);
    }
}