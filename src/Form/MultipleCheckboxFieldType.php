<?php

namespace App\Form;

use App\Model\MultipleCheckboxField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MultipleCheckboxFieldType
 * @package App\Form
 */
class MultipleCheckboxFieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('options', CollectionType::class, array(
                'entry_type' => FieldOptionType::class,
                'allow_add' => true,
                'error_bubbling' => false,
                'prototype' => true,
                'prototype_name' => '__prototype_one__',
                'label' => false
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MultipleCheckboxField::class,
        ));
    }
}