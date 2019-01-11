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
use Symfony\Component\Validator\Constraints\NotBlank;

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
            $options = [];

            if($property->isRequired()) {
                $options['constraints'] = [
                    new NotBlank(),
                ];
            }

            switch($property->getFieldType()) {
                case FieldCatalog::SINGLE_LINE_TEXT:
                    $options = array_merge($options, [
                        'required' => false,
                        'label' => $property->getLabel()
                    ]);
                    $builder->add($property->getInternalName(), TextType::class, $options);
                    break;
                case FieldCatalog::MULTI_LINE_TEXT:
                    $options = array_merge($options,[
                        'required' => false,
                        'label' => $property->getLabel()
                    ]);
                    $builder->add($property->getInternalName(), TextareaType::class, $options);
                    break;
                case FieldCatalog::DROPDOWN_SELECT:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();
                    $options = array_merge($options, [
                        'choices'  => $choices,
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select'
                        ]
                    ]);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $options = array_merge($options, [
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
                    ]);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::MULTIPLE_CHECKBOX:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();
                    $options = array_merge($options, [
                        'choices'  => $choices,
                        'label' => $property->getLabel(),
                        'expanded' => false,
                        'multiple' => true,
                        'required' => false,
                        'attr' => [
                            'class' => 'js-selectize-multiple-select'
                        ]
                    ]);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::RADIO_SELECT:
                    $choices = $property->getField()->getOptionsForChoiceTypeField();
                    $options = array_merge($options, [
                        'choices'  => $choices,
                        'label' => $property->getLabel(),
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'attr' => [
                            'class' => 'js-selectize-single-select'
                        ]
                    ]);
                    $builder->add($property->getInternalName(), ChoiceType::class, $options);
                    break;
                case FieldCatalog::NUMBER:
                    $options = array_merge($options, [
                        'required' => false,
                        'label' => $property->getLabel()
                    ]);
                    $builder->add($property->getInternalName(), NumberType::class, $options);
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