<?php

namespace App\Utils;

use App\Entity\Property;
use App\Form\RecordChoiceType;
use App\Model\FieldCatalog;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

trait FormHelper
{
    /**
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

    /**
     * @param Property $property
     * @param FormBuilderInterface $builder
     * @param array $formOptions
     */
    private function addField(Property $property, FormBuilderInterface $builder, array $formOptions) {

        $options = [];

        if($property->isRequired()) {
            $options['constraints'] = [
                new NotBlank(),
            ];
            $options['required'] = true;
        }

        if($property->getDescription()) {
            $options['help'] = $property->getDescription();
        }

        switch($property->getFieldType()) {
            case FieldCatalog::SINGLE_LINE_TEXT:
                $options = array_merge([
                    'required' => false,
                    'label' => $property->getLabel(),
                    'attr' => [
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), TextType::class, $options);
                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordGenericTransformer);

                break;
            case FieldCatalog::MULTI_LINE_TEXT:
                $options = array_merge([
                    'required' => false,
                    'label' => $property->getLabel(),
                    'attr' => [
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), TextareaType::class, $options);
                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordGenericTransformer);
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
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), ChoiceType::class, $options);
                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordGenericTransformer);
                break;
            case FieldCatalog::SINGLE_CHECKBOX:

                // for a single checkbox you need to check for not null instead of not blank
                if($property->isRequired()) {
                    $options['constraints'] = [
                        new NotNull(),
                    ];
                    $options['required'] = true;
                }

                $options = array_merge([
                    'choices'  => array(
                        'Yes' => '1',
                        'No' => '0',
                    ),
                    'label' => $property->getLabel(),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'js-selectize-single-select',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), ChoiceType::class, $options);
                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordCheckboxTranformer);
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
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), ChoiceType::class, $options);
                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordMultipleCheckboxTransformer);
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
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), ChoiceType::class, $options);
                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordGenericTransformer);
                break;
            case FieldCatalog::NUMBER:
                $options = array_merge([
                    'required' => false,
                    'label' => $property->getLabel(),
                    'attr' => [
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off',
                    ],
                    // This converts number to use commas instead of periods.
                    // https://symfony.com/doc/current/reference/forms/types/number.html#grouping
                    'grouping' => true,
                ], $options);

                $builder->add($property->getInternalName(), NumberType::class, $options);

                $field = $property->getField();
                if($field->isCurrency()) {
                    $builder->get($property->getInternalName())
                        ->addModelTransformer($this->recordNumberCurrencyTransformer);
                } else if($field->isUnformattedNumber()){
                    $builder->get($property->getInternalName())
                        ->addModelTransformer($this->recordNumberUnformattedTransformer);
                }
                break;
            case FieldCatalog::DATE_PICKER:
                $options = array_merge([
                    'required' => false,
                    'label' => $property->getLabel(),
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    // prevents rendering it as type="date", to avoid HTML5 date pickers
                    'html5' => false,
                    // adds a class that can be selected in JavaScript
                    'attr' => [
                        'class' => 'js-datepicker',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off',
                    ],
                ], $options);
                $builder->add($property->getInternalName(), DateType::class, $options);

                $builder->get($property->getInternalName())
                    ->addModelTransformer($this->recordDateTimeTransformer);
                break;
            case FieldCatalog::CUSTOM_OBJECT:

                $customObject = $property->getField()->getCustomObject();

                $options = array_merge([
                    'required' => false,
                    'label' => $property->getLabel(),
                    'attr' => [
                        'class' => 'js-selectize-single-select-with-search',
                        'placeholder' => 'Start typing to search..',
                        'data-property-id' => $property->getId(),
                        'autocomplete' => 'off',
                    ],
                    'expanded' => false,
                ], $options);

                if($property->getField()->isMultiple()) {
                    $options['multiple'] = true;
                }

                $options['property'] = $property;

                $builder->add($property->getInternalName(), RecordChoiceType::class, $options);

                if($property->getField()->isMultiple()) {
                    $builder->get($property->getInternalName())
                        ->addModelTransformer($this->idArrayToRecordArrayTransformer);
                } else {
                    $builder->get($property->getInternalName())
                        ->addModelTransformer($this->transformer);
                }

                break;
        }
    }

}
