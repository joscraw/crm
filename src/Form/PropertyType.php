<?php

namespace App\Form;

use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Model\FieldCatalog;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
        $builder
            ->add('label', TextType::class, [
                'required' => true,
            ])
            ->add('internalName', TextType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('fieldType', ChoiceType::class, array(
                'choices'  => FieldCatalog::getFields()
            ))
            ->add('submit', SubmitType::class)
            ->add('propertyGroup', EntityType::class, array(
            // looks for choices from this entity
            'class' => PropertyGroup::class,

            // uses the User.username property as the visible option string
            'choice_label' => 'name',

            // used to render a select box, check boxes or radios
            // 'multiple' => true,
            // 'expanded' => true,
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
        $data = $event->getData();
        $fieldClass = null;

        switch($data) {
            case 'single_line_text':
                $fieldClass = SingleLineTextFieldType::class;
                break;
            case 'dropdown_select':
                $fieldClass = DropdownSelectFieldType::class;
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
            null,
            [
                'auto_initialize' => false,
                'label' => false
            ]
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
        ));
    }
}