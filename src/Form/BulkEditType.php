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


                if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                    continue;
                }

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
                'class' => 'js-selectize-single-select js-property'
            ]
        ]);

        $builder->get('propertyToUpdate')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     */
    public function fieldModifier(FormEvent $event) {

        // since we've added the listener to the child, we'll have
        // to grab the parent
        $form = $event->getForm()->getParent();

        $data = $event->getData();

        $fieldClass = null;
        $property = $this->propertyRepository->find($data);

        $builderData = null;

        $options = [
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
            case FieldCatalog::NUMBER:
                $fieldClass = BulkEditNumberFieldType::class;
                break;

        }

        // This is a really important thing to NOTE!
        // event listeners can only be attached to a builder (FormBuilderInterface)
        // and NOT to a form (FormInterface). This is why we have to create our own builder
        // the builder is nothing more then a form field
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'propertyValue',
            $fieldClass,
            $builderData,
            $options
        );

        // last but not least, let's add the builder (form field) to the main form
        $form->add($builder->getForm());

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'customObject'
        ]);
    }
}