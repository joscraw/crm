<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditUserType
 * @package App\Form\User
 */
class EditUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])->add('lastName', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])->add('email', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])->add(
                'password',
                PasswordType::class
            )->add(
                'passwordRepeat',
                PasswordType::class
            )->add(
                'customRoles',EntityType::class, [
                    'required'     => true,
                    'class'        => 'App\Entity\Role',
                    'choice_label' => 'name',
                    'label'        => 'Roles',
                    'expanded'     => false,
                    'multiple'     => true,
                    'attr' => [
                        'class' => 'js-selectize-multiple-select',
                        'autocomplete' => 'off'
                    ]
            ])->add('isAdminUser', CheckboxType::class, [

            ])->add('isActive', CheckboxType::class, [

            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => ['EDIT'],
        ));

    }
}