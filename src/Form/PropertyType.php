<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PropertyType
 * @package App\Form\Property
 */
class PropertyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CustomObject $customObject */
        $customObject = $options['customObject'];

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
                    'autocomplete' => 'off'
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

        $data = $event->getData();
        $builderData = null;
        $fieldClass = null;
        $options = [
            'auto_initialize' => false,
            'label' => false,
        ];

        switch($data) {
            case FieldCatalog::SINGLE_LINE_TEXT:
                $fieldClass = SingleLineTextFieldType::class;
                break;
            case FieldCatalog::MULTI_LINE_TEXT:
                $fieldClass = MultiLineTextFieldType::class;
                break;
            case FieldCatalog::DROPDOWN_SELECT:
                $fieldClass = DropdownSelectFieldType::class;
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $fieldClass = SingleCheckboxFieldType::class;
                break;
            case FieldCatalog::MULTIPLE_CHECKBOX:
                $fieldClass = MultipleCheckboxFieldType::class;
                break;
            case FieldCatalog::RADIO_SELECT:
                $fieldClass = RadioSelectFieldType::class;
                break;
            case FieldCatalog::NUMBER:
                $fieldClass = NumberFieldType::class;
                break;
            case FieldCatalog::DATE_PICKER:
                $fieldClass = DatePickerFieldType::class;
                break;
            case FieldCatalog::CUSTOM_OBJECT:
                $fieldClass = CustomObjectFieldType::class;
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
            'data_class' => Property::class
        ));

        $resolver->setRequired([
            'portal',
            'customObject'
        ]);
    }
}