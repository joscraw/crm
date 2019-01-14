<?php

namespace App\Form;

use App\Entity\CustomObject;
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
                        'label' => $property->getLabel()
                    ], $options);
                    $builder->add($property->getInternalName(), TextType::class, $options);
                    break;
                case FieldCatalog::MULTI_LINE_TEXT:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel()
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
                            'class' => 'js-selectize-single-select'
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
                            'class' => 'js-selectize-single-select'
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
                            'class' => 'js-selectize-multiple-select'
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
                            'class' => 'js-selectize-single-select'
                        ]
                    ], $options);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::NUMBER:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel()
                    ], $options);
                    $builder->add($property->getInternalName(), NumberType::class, $options);
                    break;
                case FieldCatalog::DATE_PICKER:
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'widget' => 'single_text',
                        // prevents rendering it as type="date", to avoid HTML5 date pickers
                        'html5' => false,
                        // adds a class that can be selected in JavaScript
                        'attr' => ['class' => 'js-datepicker'],
                    ], $options);
                    $builder->add($property->getInternalName(), DateType::class, $options);
                    break;
                case FieldCatalog::CUSTOM_OBJECT:
                    /*$customObject = $property->getField()->getCustomObject();
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'class' => 'js-selectize-single-select-with-search',
                            'placeholder' => 'Start typing to search..',
                            'data-allowed-custom-object-to-search' => $customObject->getId()
                        ],
                        'class' => Record::class,
                        'query_builder' => function (EntityRepository $er) use ($customObject) {
                            return $er->createQueryBuilder('record')
                                ->innerJoin('record.customObject', 'customObject')
                                //->setMaxResults(1)
                                ->where('customObject.id = :customObject')
                             //->andWhere('customObject.internalName = internalName')
                            // ->setParameter('internalName', $customObject->getInternalName())
                                ->setParameter('customObject', 1000);
                            //->orderBy('customObject.label', 'ASC');
                        },
                        'choice_label' => function ($choiceValue, $key, $value) {
                            $name = "hi";
                            return $value;
                        },
                        'expanded' => false,
                        'multiple' => false
                    ], $options);*/

                    /*$builder->add('customObject', EntityType::class, $options);*/


                    // This is the custom object that the property will be allowed to search on
              /*      $customObject = $property->getField()->getCustomObject();
                    $options = [
                        'choices'  => [],
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select-with-search',
                            'placeholder' => 'Start typing to search..',
                            'data-allowed-custom-object-to-search' => $customObject->getId()
                        ]
                    ];

                    $builder->add('customObject', ChoiceType::class, array(
                        'choices'  => array(
                            'Contact' => 1
                        ),
                        'attr' => [
                            'class' => 'js-selectize-single-select-with-search',
                            'placeholder' => 'Start typing to search..',
                            'data-allowed-custom-object-to-search' => $customObject->getId()
                        ],
                        'auto_initialize' => false
                    ));*/

                    /*$builder->add('customObject', ChoiceType::class, $options);*/

                    /*$builder->get('customObject')
                        ->addModelTransformer($this->transformer);*/

                   /* $builder->get('customObject')
                        ->addModelTransformer(new CallbackTransformer(
                            function ($customObject) {
                                if(null === $customObject) {
                                    return '';
                                }
                                // transform the object to string
                                return $customObject->getId();
                            },
                            function ($recordId) {
                                $name = "hi";
                                // transform the string back to an array
                                $hi = "yes";
                            }
                        ));*/

                    $customObject = $property->getField()->getCustomObject();

                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'class' => 'js-selectize-single-select-with-search',
                            'placeholder' => 'Start typing to search..',
                            'data-allowed-custom-object-to-search' => $customObject->getId()
                        ],
                        'expanded' => false,
                    ], $options);

                    if($property->getField()->isMultiple()) {
                        $options['multiple'] = true;
                    }

                    $builder->add($property->getInternalName(), RecordChoiceType::class, $options);

                    /*$builder->get('customObject')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'fieldModifier']);*/

/*                  $builder->get('customObject')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'fieldModifier2']);

                    $builder->get('customObject')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier3']);*/

                    break;
            }
        }

        $builder->add('submit', SubmitType::class);
    }


/*    public function fieldModifier2(FormEvent $event) {
        $form = $event->getForm()->getParent();
        $data = $event->getData();

        $event->setData("14");


        var_dump($data);
        $name = "Josh";
    }

    public function fieldModifier3(FormEvent $event) {
        $form = $event->getForm()->getParent();
        $data = $event->getData();

        var_dump($data);
        $name = "Josh";
    }*/

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     */
    public function fieldModifier(FormEvent $event) {

        $parentForm = $event->getForm()->getParent();
        $childForm = $event->getForm();
        $config = $childForm->getConfig();
        $name = $config->getName();

        $results = $this->recordRepository->createQueryBuilder('record')
            ->innerJoin('record.customObject', 'customObject')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach($results as $result) {
            $choices[$result->getId()] = $result->getId();
        }

        // This is a really important thing to NOTE!
        // event listeners can only be attached to a builder (FormBuilderInterface)
        // and NOT to a form (FormInterface). This is why we have to create our own builder
        // the builder is nothing more then a form field
        $builder = $parentForm->getConfig()->getFormFactory()->createNamedBuilder(
            $name,
            ChoiceType::class,
            null,
            [
                'choices'  => array(
                    'Contact1' => "14",
                    'Contact2' => "15"
                ),
                'attr' => [
                    'class' => 'js-selectize-single-select-with-search',
                    'placeholder' => 'Start typing to search..',
                    //'data-allowed-custom-object-to-search' => $customObject->getId()
                ],
                'auto_initialize' => false
            ]
        );


/*        $builder->addModelTransformer(new CallbackTransformer(
            function ($customObject) {
                if(null === $customObject) {
                    return '';
                }
                // transform the object to string
                return $customObject->getId();
            },
            function ($recordId) {
                $name = "hi";
                // transform the string back to an array
                $hi = "yes";
            }
        ));*/

        //->where('property.customObject = :customObject')
        //->andWhere('customObject.internalName = internalName')
        // ->setParameter('internalName', $customObject->getInternalName())
        //->setParameter('customObject', $customObject->getId());
        //->orderBy('customObject.label', 'ASC');

        $parentForm->add($builder->getForm());

        $name = "Josh";
        $hi = "yo";
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
           'properties'
        ]);
    }
}