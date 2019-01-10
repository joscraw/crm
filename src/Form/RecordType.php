<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Model\FieldCatalog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecordType
 * @package App\Form\Property
 */
class RecordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Property $properties[] */
        $properties = $options['properties'];

        foreach($properties as $property) {

            $name = "Josh";

            switch($property->getFieldType()) {
                case FieldCatalog::SINGLE_LINE_TEXT:
                    $builder->add($property->getInternalName(), TextType::class, [
                            'required' => false,
                            'label' => $property->getLabel()
                        ]);
                    break;
                case FieldCatalog::MULTI_LINE_TEXT:
                    $builder->add($property->getInternalName(), TextareaType::class, [
                        'required' => false,
                        'label' => $property->getLabel()
                    ]);
                    break;
                case FieldCatalog::DROPDOWN_SELECT:
                    $options = $property->getField()->getOptionsForChoiceTypeField();
                    $builder->add($property->getInternalName(), ChoiceType::class, array(
                        'choices'  => $options,
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select'
                        ]
                    ));
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $builder->add($property->getInternalName(), ChoiceType::class, array(
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
                    ));
                    break;
                case FieldCatalog::MULTIPLE_CHECKBOX:
                    $options = $property->getField()->getOptionsForChoiceTypeField();
                    $builder->add($property->getInternalName(), ChoiceType::class, array(
                        'choices'  => $options,
                        'label' => $property->getLabel(),
                        'expanded' => false,
                        'multiple' => true,
                        'required' => false,
                        'attr' => [
                            'class' => 'js-selectize-multiple-select'
                        ]
                    ));
                    break;
                case FieldCatalog::RADIO_SELECT:
                    $options = $property->getField()->getOptionsForChoiceTypeField();
                    $builder->add($property->getInternalName(), ChoiceType::class, array(
                        'choices'  => $options,
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select'
                        ]
                    ));
                    break;
                case FieldCatalog::NUMBER:
                    $builder->add($property->getInternalName(), NumberType::class, [
                        'required' => false,
                        'label' => $property->getLabel()
                    ]);
                    break;
            }
        }

        $builder->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
           'properties'
        ]);
    }
}