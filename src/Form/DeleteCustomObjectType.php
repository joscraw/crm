<?php

namespace App\Form;

use App\Entity\CustomObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * Class DeleteCustomObjectType
 * @package App\Form\Property
 */
class DeleteCustomObjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('submit', SubmitType::class, [
            'label' => 'Yes please!'
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CustomObject::class,
            'validation_groups' => new GroupSequence(['FIRST', 'DELETE'])
        ));
    }
}