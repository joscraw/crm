<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\MultiLineTextField;
use App\Model\MultipleCheckboxField;
use App\Model\NumberField;
use App\Model\RadioSelectField;
use App\Model\SingleCheckboxField;
use App\Model\SingleLineTextField;
use App\Repository\CustomObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditPropertyType
 * @package App\Form\Property
 */
class EditPropertyType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    public function __construct(EntityManagerInterface $entityManager, CustomObjectRepository $customObjectRepository)
    {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Portal $portal */
        $portal = $options['portal'];

        /** @var CustomObject $customObject */
        $customObject = $options['customObject'];

        /** @var Property $property */
        $property = $options['property'];

        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('internalName', TextType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'readonly' => 'readonly',
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('required', CheckboxType::class, [
                'required' => false,
            ])
            ->add('fieldType', ChoiceType::class, array(
                'choices'  => FieldCatalog::getOptionsForChoiceTypeField(),
                'required' => false,
                'placeholder' => false,
            ))
            ->add('submit', SubmitType::class)
            ->add('propertyGroup', EntityType::class, array(
                'required' => true,
                'placeholder' => false,
                'class' => PropertyGroup::class,
                'query_builder' => function (EntityRepository $er) use ($customObject) {
                    return $er->createQueryBuilder('propertyGroup')
                        ->innerJoin('propertyGroup.customObject', 'customObject')
                        ->where('propertyGroup.customObject = :customObject')
                        ->setParameter('customObject', $customObject)
                        ->orderBy('customObject.label', 'ASC');
                },
                'choice_label' => 'name',
        ));

        $builder->get('fieldType')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($portal, $property) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                if($data->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                    $customObject = $data->getField()->getCustomObject();
                    $form = $event->getForm();

                    $form->add('field', CustomObjectFieldType::class, [
                        'portal' => $portal,
                        'customObject' => $customObject
                        /*'data' => $property->getField()*/
                    ]);
                }
            }
        );
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     */
    public function fieldModifier(FormEvent $event) {

        // since we've added the listener to the child, we'll have
        // to grab the parent
        $form = $event->getForm()->getParent();
        $portal = $form->getConfig()->getOption('portal');
        $customObject = $form->getConfig()->getOption('customObject');
        $property = $form->getConfig()->getOption('property');

        $data = $event->getData();

        $builderData = null;
        $fieldClass = null;
        $options = [
            'auto_initialize' => false,
            'label' => false,
        ];

        switch($data) {
            case FieldCatalog::SINGLE_LINE_TEXT:
                $builderData = new SingleLineTextField();
                $fieldClass = SingleLineTextFieldType::class;
                break;
            case FieldCatalog::MULTI_LINE_TEXT:
                $builderData = new MultiLineTextField();
                $fieldClass = MultiLineTextFieldType::class;
                break;
            case FieldCatalog::DROPDOWN_SELECT:
                $builderData = new DropdownSelectField();
                $fieldClass = DropdownSelectFieldType::class;
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $builderData = new SingleCheckboxField();
                $fieldClass = SingleCheckboxFieldType::class;
                break;
            case FieldCatalog::MULTIPLE_CHECKBOX:
                $builderData = new MultipleCheckboxField();
                $fieldClass = MultipleCheckboxFieldType::class;
                break;
            case FieldCatalog::RADIO_SELECT:
                $builderData = new RadioSelectField();
                $fieldClass = RadioSelectFieldType::class;
                break;
            case FieldCatalog::NUMBER:
                $builderData = new NumberField();
                $fieldClass = NumberFieldType::class;
                break;
            case FieldCatalog::DATE_PICKER:
                $builderData = new DatePickerField();
                $fieldClass = DatePickerFieldType::class;
                break;
            case FieldCatalog::CUSTOM_OBJECT:
                $fieldClass = EditCustomObjectFieldType::class;
                $builderData = new CustomObjectField();
                $options['portal'] = $portal;
                $options['customObject'] = $customObject;
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
            'field',
            $fieldClass,
            $builderData,
            $options
        );

        // last but not least, let's add the builder (form field) to the main form
        $form->add($builder->getForm());
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Property::class,
            'validation_groups' => ['EDIT'],
        ));

        $resolver->setRequired([
            'portal',
            'customObject',
            'property'
        ]);
    }
}