<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
use App\Model\FieldCatalog;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
                    $customObject = $property->getField()->getCustomObject();
                    $options = array_merge([
                        'required' => false,
                        'label' => $property->getLabel(),
                        'attr' => [
                            'class' => 'js-selectize-single-select'
                        ],
                        'class' => Record::class,
                        'query_builder' => function (EntityRepository $er) use ($customObject) {
                            return $er->createQueryBuilder('record')
                                ->innerJoin('record.customObject', 'customObject');
                            /*->where('property.customObject = :customObject')*/
                            /* ->andWhere('customObject.internalName = internalName')
                             ->setParameter('internalName', $customObject->getInternalName())*/
                            /*->setParameter('customObject', $customObject->getId());*/
                            /*->orderBy('customObject.label', 'ASC');*/
                        },
                        'choice_label' => function ($choiceValue, $key, $value) {
                            /* if ($value == $choiceValue) {
                                 return 'Definitely!';
                             }

                             return strtoupper($key);*/

                            return $value;

                            // or if you want to translate some key
                            //return 'form.choice.'.$key;
                        },
                        'expanded' => false,
                        'multiple' => false
                    ], $options);
                    $builder->add('customObject', EntityType::class, $options);
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