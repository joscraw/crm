<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\SingleLineTextField;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DatePickerFieldType
 * @package App\Form
 */
class CustomObjectFieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customObject', EntityType::class, array(
            'class' => CustomObject::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('customObject')
                    ->orderBy('customObject.label', 'ASC');
            },
            'choice_label' => 'label',
            'expanded' => false,
            'multiple' => false
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CustomObjectField::class,
        ));
    }
}